<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Versandart;
use JTL\Checkout\Zahlungsart;
use JTL\Customer\Customer;
use JTL\Customer\CustomerAttribute;
use JTL\Customer\CustomerAttributes;
use JTL\Customer\CustomerField;
use JTL\Customer\CustomerFields;
use JTL\DB\ReturnType;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\PaymentMethod as Helper;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\SimpleMail;
use JTL\Staat;
use JTL\VerificationVAT\VATCheck;

/**
 *
 */
function pruefeBestellungMoeglich()
{
    header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php') .
        '?fillOut=' . Frontend::getCart()->istBestellungMoeglich(), true, 303);
    exit;
}

/**
 * @param int  $shippingMethod
 * @param int  $formValues
 * @param bool $bMsg
 * @return bool
 */
function pruefeVersandartWahl($shippingMethod, $formValues = 0, $bMsg = true): bool
{
    global $step;

    $nReturnValue = versandartKorrekt($shippingMethod, $formValues);
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI);

    if ($nReturnValue) {
        $step = 'Zahlung';

        return true;
    }
    if ($bMsg) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('fillShipping', 'checkout'),
            'fillShipping'
        );
    }
    $step = 'Versand';

    return false;
}

/**
 * @param array $post
 * @return int
 */
function pruefeUnregistriertBestellen($post): int
{
    global $step, $Kunde, $Lieferadresse;
    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $cart = Frontend::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $missingInput       = checkKundenFormular(0);
    $Kunde              = getKundendaten($post, 0);
    $customerAttributes = getKundenattribute($post);
    $customerGroupID    = Frontend::getCustomerGroup()->getID();
    $checkBox           = new CheckBox();
    $missingInput       = array_merge($missingInput, $checkBox->validateCheckBox(
        CHECKBOX_ORT_REGISTRIERUNG,
        $customerGroupID,
        $post,
        true
    ));

    $Kunde->getCustomerAttributes()->assign($customerAttributes);
    if (isset($post['shipping_address'])) {
        if ((int)$post['shipping_address'] === 0) {
            $post['kLieferadresse'] = 0;
            $post['lieferdaten']    = 1;
            pruefeLieferdaten($post);
        } elseif (isset($post['kLieferadresse']) && (int)$post['kLieferadresse'] > 0) {
            pruefeLieferdaten($post);
        } elseif (isset($post['register']['shipping_address'])) {
            pruefeLieferdaten($post['register']['shipping_address'], $missingInput);
        }
    } elseif (isset($post['lieferdaten']) && (int)$post['lieferdaten'] === 1) {
        // compatibility with older template
        pruefeLieferdaten($post, $missingInput);
    }
    $nReturnValue = angabenKorrekt($missingInput);

    executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI, [
        'nReturnValue'    => &$nReturnValue,
        'fehlendeAngaben' => &$missingInput,
        'Kunde'           => &$Kunde,
        'cPost_arr'       => &$post
    ]);

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $checkBox->triggerSpecialFunction(
            CHECKBOX_ORT_REGISTRIERUNG,
            $customerGroupID,
            true,
            $post,
            ['oKunde' => $Kunde]
        )->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $customerGroupID, $post, true);
        $Kunde->nRegistriert = 0;
        $_SESSION['Kunde']   = $Kunde;
        if (isset($_SESSION['Warenkorb']->kWarenkorb)
            && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
        ) {
            if (isset($_SESSION['Lieferadresse']) && (int)$_SESSION['Bestellung']->kLieferadresse === 0) {
                setzeLieferadresseAusRechnungsadresse();
            }
            Tax::setTaxRates();
            $cart->gibGesamtsummeWarenLocalized();
        }
        executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN);

        return 1;
    }
    //keep shipping address on error
    if (isset($post['register']['shipping_address'])) {
        $_SESSION['Bestellung']                 = $_SESSION['Bestellung'] ?? new stdClass();
        $_SESSION['Bestellung']->kLieferadresse = isset($post['kLieferadresse'])
            ? (int)$post['kLieferadresse']
            : -1;
        $Lieferadresse                          = getLieferdaten($post['register']['shipping_address']);
        $_SESSION['Lieferadresse']              = $Lieferadresse;
    }

    setzeFehlendeAngaben($missingInput);
    Shop::Smarty()->assign('customerAttributes', $customerAttributes)
        ->assign('cPost_var', Text::filterXSS($post));

    return 0;
}

/**
 * @param array $post
 * @param array|null $fehlendeAngaben
 */
function pruefeLieferdaten($post, &$fehlendeAngaben = null): void
{
    global $Lieferadresse;
    unset($_SESSION['Lieferadresse']);
    if (!isset($_SESSION['Bestellung'])) {
        $_SESSION['Bestellung'] = new stdClass();
    }
    $_SESSION['Bestellung']->kLieferadresse = isset($post['kLieferadresse'])
        ? (int)$post['kLieferadresse']
        : -1;
    Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
    unset($_SESSION['Versandart']);
    // neue lieferadresse
    if (!isset($post['kLieferadresse']) || (int)$post['kLieferadresse'] === -1) {
        $fehlendeAngaben           = \array_merge($fehlendeAngaben, checkLieferFormular($post));
        $Lieferadresse             = getLieferdaten($post);
        $nReturnValue              = angabenKorrekt($fehlendeAngaben);
        $_SESSION['Lieferadresse'] = $Lieferadresse;
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE_PLAUSI, [
            'nReturnValue'    => &$nReturnValue,
            'fehlendeAngaben' => &$fehlendeAngaben
        ]);
        if ($nReturnValue) {
            // Anrede mappen
            if ($Lieferadresse->cAnrede === 'm') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } elseif ($Lieferadresse->cAnrede === 'w') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationW');
            }
            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE);
            pruefeVersandkostenfreiKuponVorgemerkt();
        }
    } elseif ((int)$post['kLieferadresse'] > 0) {
        // vorhandene lieferadresse
        $addressData = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . Frontend::getCustomer()->getID() . '
                    AND kLieferadresse = ' . (int)$post['kLieferadresse'],
            ReturnType::SINGLE_OBJECT
        );
        if ($addressData->kLieferadresse > 0) {
            $deliveryAddress           = new Lieferadresse((int)$addressData->kLieferadresse);
            $_SESSION['Lieferadresse'] = $deliveryAddress;

            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_VORHANDENELIEFERADRESSE);
        }
    } elseif ((int)$post['kLieferadresse'] === 0 && isset($_SESSION['Kunde'])) {
        // lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_RECHNUNGLIEFERADRESSE);
    }
    Tax::setTaxRates();
    // lieferland hat sich geändert und versandart schon gewählt?
    if (isset($_SESSION['Lieferadresse'], $_SESSION['Versandart'])
        && $_SESSION['Lieferadresse']
        && $_SESSION['Versandart']
    ) {
        $delVersand = mb_stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false;
        // ist die plz im zuschlagsbereich?
        if (!empty((new Versandart((int)$_SESSION['Versandart']->kVersandart))->getShippingSurchargeForZip(
            $_SESSION['Lieferadresse']->cPLZ,
            $_SESSION['Lieferadresse']->cLand
        ))) {
            $delVersand = true;
        }
        if ($delVersand) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        } else {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }
    plausiGuthaben($post);
}

/**
 * @param array $post
 */
function plausiGuthaben($post): void
{
    if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
        || (isset($post['guthabenVerrechnen']) && (int)$post['guthabenVerrechnen'] === 1)
    ) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            Frontend::getCustomer()->fGuthaben,
            Frontend::getCart()->gibGesamtsummeWaren(true, false)
        );
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABENVERRECHNEN);
    }
}

/**
 *
 */
function pruefeVersandkostenStep(): void
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'])) {
        $cart = Frontend::getCart();
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        $arrArtikelabhaengigeVersandkosten = ShippingMethod::gibArtikelabhaengigeVersandkostenImWK(
            $_SESSION['Lieferadresse']->cLand,
            $cart->PositionenArr
        );
        foreach ($arrArtikelabhaengigeVersandkosten as $item) {
            $cart->erstelleSpezialPos(
                $item->cName,
                1,
                $item->fKosten,
                $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                false
            );
        }
        $step = 'Versand';
    }
}

/**
 *
 */
function pruefeZahlungStep(): void
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'])) {
        $step = 'Zahlung';
    }
}

/**
 *
 */
function pruefeBestaetigungStep(): void
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])) {
        $step = 'Bestaetigung';
    }
    if (isset($_SESSION['Zahlungsart'], $_SESSION['Zahlungsart']->cZusatzschrittTemplate)
        && mb_strlen($_SESSION['Zahlungsart']->cZusatzschrittTemplate) > 0
    ) {
        $paymentMethod = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
        if (is_object($paymentMethod) && !$paymentMethod->validateAdditional()) {
            $step = 'Zahlung';
        }
    }
}

/**
 * @param array $get
 */
function pruefeRechnungsadresseStep($get): void
{
    global $step, $Kunde;
    //sondersteps Rechnungsadresse ändern
    if (isset($get['editRechnungsadresse'])
        && (int)$get['editRechnungsadresse'] === 1
        && !empty(Frontend::getCustomer()->cOrt)
    ) {
        Kupon::resetNewCustomerCoupon();
        $Kunde = Frontend::getCustomer();
        $step  = 'edit_customer_address';
    }

    if (!empty(Frontend::getCustomer()->cOrt)
        && count(ShippingMethod::getPossibleShippingCountries(
            Frontend::getCustomerGroup()->getID(),
            false,
            false,
            [Frontend::getCustomer()->cLand]
        )) === 0
    ) {
        Shop::Smarty()->assign('forceDeliveryAddress', 1);

        if (!isset($_SESSION['Lieferadresse'])
            || count(ShippingMethod::getPossibleShippingCountries(
                Frontend::getCustomerGroup()->getID(),
                false,
                false,
                [$_SESSION['Lieferadresse']->cLand]
            )) === 0
        ) {
            $Kunde = Frontend::getCustomer();
            $step  = 'edit_customer_address';
        }
    }

    if (isset($_SESSION['checkout.register']) && (int)$_SESSION['checkout.register'] === 1) {
        if (isset($_SESSION['checkout.fehlendeAngaben'])) {
            setzeFehlendeAngaben($_SESSION['checkout.fehlendeAngaben']);
            unset($_SESSION['checkout.fehlendeAngaben']);
        }
        if (isset($_SESSION['checkout.cPost_arr'])) {
            $Kunde = getKundendaten($_SESSION['checkout.cPost_arr'], 0, 0);
            $Kunde->getCustomerAttributes()->assign(getKundenattribute($_SESSION['checkout.cPost_arr']));
            Shop::Smarty()->assign('Kunde', $Kunde)
                ->assign('cPost_var', $_SESSION['checkout.cPost_arr']);

            if (isset($_SESSION['Lieferadresse']) && (int)$_SESSION['checkout.cPost_arr']['shipping_address'] !== 0) {
                Shop::Smarty()->assign('Lieferadresse', $_SESSION['Lieferadresse']);
            }

            $_POST = array_merge($_POST, $_SESSION['checkout.cPost_arr']);
            unset($_SESSION['checkout.cPost_arr']);
        }
        unset($_SESSION['checkout.register']);
    }
    if (pruefeFehlendeAngaben()) {
        $step = isset($_SESSION['Kunde']) ? 'edit_customer_address' : 'accountwahl';
    }
}

/**
 * @param array $get
 */
function pruefeLieferadresseStep($get): void
{
    global $step, $Lieferadresse;
    //sondersteps Lieferadresse ändern
    if (!empty($_SESSION['Lieferadresse'])) {
        $Lieferadresse = $_SESSION['Lieferadresse'];
        if (isset($get['editLieferadresse']) && (int)$get['editLieferadresse'] === 1) {
            Kupon::resetNewCustomerCoupon();
            unset($_SESSION['Zahlungsart'], $_SESSION['Versandart']);
            $step = 'Lieferadresse';
        }
    }
    if (pruefeFehlendeAngaben('shippingAddress')) {
        $step = isset($_SESSION['Kunde']) ? 'Lieferadresse' : 'accountwahl';
    }
}

/**
 * Prüft ob im WK ein Versandfrei Kupon eingegeben wurde und falls ja,
 * wird dieser nach Eingabe der Lieferadresse gesetzt (falls Kriterien erfüllt)
 *
 * @return array
 */
function pruefeVersandkostenfreiKuponVorgemerkt(): array
{
    if ((isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cKuponTyp === Kupon::TYPE_SHIPPING)
        || (isset($_SESSION['oVersandfreiKupon']) && $_SESSION['oVersandfreiKupon']->cKuponTyp === Kupon::TYPE_SHIPPING)
    ) {
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        unset($_SESSION['Kupon']);
    }
    $Kuponfehler = [];
    if (isset($_SESSION['oVersandfreiKupon']->kKupon) && $_SESSION['oVersandfreiKupon']->kKupon > 0) {
        // Wurde im WK ein Versandfreikupon eingegeben?
        $Kuponfehler = Kupon::checkCoupon($_SESSION['oVersandfreiKupon']);
        if (angabenKorrekt($Kuponfehler)) {
            Kupon::acceptCoupon($_SESSION['oVersandfreiKupon']);
            Shop::Smarty()->assign('KuponMoeglich', Kupon::couponsAvailable());
        }
    }

    return $Kuponfehler;
}

/**
 * @param array $get
 */
function pruefeVersandartStep($get): void
{
    global $step;
    // sondersteps Versandart ändern
    if (isset($get['editVersandart'], $_SESSION['Versandart']) && (int)$get['editVersandart'] === 1) {
        Kupon::resetNewCustomerCoupon();
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
        unset($_SESSION['Zahlungsart'], $_SESSION['Versandart']);

        $step = 'Versand';
        pruefeZahlungsartStep(['editZahlungsart' => 1]);
    }
}

/**
 * @param array $get
 */
function pruefeZahlungsartStep($get): void
{
    global $step;
    // sondersteps Zahlungsart ändern
    if (isset($_SESSION['Zahlungsart'], $get['editZahlungsart']) && (int)$get['editZahlungsart'] === 1) {
        Kupon::resetNewCustomerCoupon();
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                             ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        unset($_SESSION['Zahlungsart']);
        $step = 'Zahlung';
        pruefeVersandartStep(['editVersandart' => 1]);
    }

    if (isset($get['nHinweis']) && (int)$get['nHinweis'] > 0) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_NOTE,
            mappeBestellvorgangZahlungshinweis((int)$get['nHinweis']),
            'paymentNote'
        );
    }
}

/**
 * @param array $post
 * @return int|null
 */
function pruefeZahlungsartwahlStep($post)
{
    global $zahlungsangaben, $step;
    if (!isset($post['zahlungsartwahl']) || (int)$post['zahlungsartwahl'] !== 1) {
        return null;
    }
    $zahlungsangaben = zahlungsartKorrekt($post['Zahlungsart']);
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG_PLAUSI);

    switch ($zahlungsangaben) {
        case 0:
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('fillPayment', 'checkout'),
                'fillPayment'
            );
            $step = 'Zahlung';

            return 0;
        case 1:
            $step = 'ZahlungZusatzschritt';

            return 1;
        case 2:
            $step = 'Bestaetigung';

            return 2;
        default:
            return null;
    }
}

/**
 *
 */
function pruefeGuthabenNutzen(): void
{
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen) {
        $_SESSION['Bestellung']->fGuthabenGenutzt   = min(
            Frontend::getCustomer()->fGuthaben,
            Frontend::getCart()->gibGesamtsummeWaren(true, false)
        );
        $_SESSION['Bestellung']->GutscheinLocalized = Preise::getLocalizedPriceString(
            $_SESSION['Bestellung']->fGuthabenGenutzt
        );
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABEN_PLAUSI);
}

/**
 * @param string $context
 * @return bool
 */
function pruefeFehlendeAngaben($context = null): bool
{
    $fehlendeAngaben = Shop::Smarty()->getTemplateVars('fehlendeAngaben');
    if (!$context) {
        return !empty($fehlendeAngaben);
    }

    return (isset($fehlendeAngaben[$context])
        && is_array($fehlendeAngaben[$context])
        && count($fehlendeAngaben[$context]));
}

/**
 *
 */
function gibStepAccountwahl(): void
{
    // Einstellung global_kundenkonto_aktiv ist auf 'A'
    // und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
    if (isset($_REQUEST['reg']) && (int)$_REQUEST['reg'] === 1) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('accountCreated') . '. ' . Shop::Lang()->get('activateAccountDesc'),
            'accountCreatedLoginNotActivated'
        );
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('continueAfterActivation', 'messages'),
            'continueAfterActivation'
        );
    }
    Shop::Smarty()
        ->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Frontend::getCart()))
        ->assign('one_step_wk', Request::verifyGPCDataInt('wk'));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
}

/**
 *
 */
function gibStepUnregistriertBestellen(): void
{
    /** @var Customer $Kunde */
    global $Kunde;
    $herkunfte       = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $Kunde ?? null)
        ->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID, false, true))
        ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
        ->assign('code_registrieren', false)
        ->assign('customerAttributes', $Kunde !== null ? $Kunde->getCustomerAttributes() : getKundenattribute($_POST));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPUNREGISTRIERTBESTELLEN);
}

/**
 * fix für /jtl-shop/issues#219
 */
function validateCouponInCheckout()
{
    if (isset($_SESSION['Kupon'])) {
        $checkCouponResult = Kupon::checkCoupon($_SESSION['Kupon']);
        if (count($checkCouponResult) !== 0) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
            $_SESSION['checkCouponResult'] = $checkCouponResult;
            unset($_SESSION['Kupon']);
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true));
            exit(0);
        }
    }
}
/**
 * @return mixed
 */
function gibStepLieferadresse()
{
    global $Lieferadresse;

    $smarty          = Shop::Smarty();
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->kKunde > 0) {
        $addresses = [];
        $data      = Shop::Container()->getDB()->query(
            'SELECT DISTINCT(kLieferadresse)
                FROM tlieferadresse
                WHERE kKunde = ' . Frontend::getCustomer()->getID(),
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            if ($item->kLieferadresse > 0) {
                $addresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $smarty->assign('Lieferadressen', $addresses);
        $customerGroupID = Frontend::getCustomer()->kKundengruppe;
    }
    $smarty->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID, false, true))
           ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
           ->assign('Kunde', $_SESSION['Kunde'] ?? null)
           ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse ?? null);
    if (isset($_SESSION['Bestellung']->kLieferadresse) && (int)$_SESSION['Bestellung']->kLieferadresse === -1) {
        $smarty->assign('Lieferadresse', $Lieferadresse);
    }
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE);

    return $Lieferadresse;
}

/**
 *
 */
function gibStepZahlung()
{
    global $step;
    $cart       = Frontend::getCart();
    $smarty     = Shop::Smarty();
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = Frontend::getCustomer()->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = Frontend::getCustomer()->cPLZ;
    }
    $customerGroupID = Frontend::getCustomer()->kKundengruppe ?? null;
    if (!$customerGroupID) {
        $customerGroupID = Frontend::getCustomerGroup()->getID();
    }
    $shippingMethods = ShippingMethod::getPossibleShippingMethods(
        $lieferland,
        $plz,
        ShippingMethod::getShippingClasses(Frontend::getCart()),
        $customerGroupID
    );
    $packagings      = ShippingMethod::getPossiblePackagings($customerGroupID);
    if (!empty($packagings) && $cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG)) {
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($packagings as $oPack) {
                    if ($oPack->cName === $item->cName[$oPack->cISOSprache]) {
                        $oPack->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }

    if (GeneralObject::hasCount($shippingMethods)) {
        $shippingMethod = gibAktiveVersandart($shippingMethods);
        $paymentMethods = gibZahlungsarten($shippingMethod, $customerGroupID);
        if (!is_array($paymentMethods) || count($paymentMethods) === 0) {
            Shop::Container()->getLogService()->error(
                'Es konnte keine Zahlungsart für folgende Daten gefunden werden: Versandart: ' .
                $_SESSION['Versandart']->kVersandart .
                ', Kundengruppe: ' . $customerGroupID
            );
        }

        $packaging     = gibAktiveVerpackung($packagings);
        $paymentMethod = gibAktiveZahlungsart($paymentMethods);
        if (!isset($_SESSION['Versandart']) && !empty($shippingMethod)) {
            // dieser Workaround verhindert die Anzeige der Standardzahlungsarten wenn ein Zahlungsplugin aktiv ist
            $_SESSION['Versandart'] = (object)[
                'kVersandart' => $shippingMethod,
            ];
        }
        $smarty->assign('Zahlungsarten', $paymentMethods)
               ->assign('Versandarten', $shippingMethods)
               ->assign('Verpackungsarten', $packagings)
               ->assign('AktiveVersandart', $shippingMethod)
               ->assign('AktiveZahlungsart', $paymentMethod)
               ->assign('AktiveVerpackung', $packaging)
               ->assign('Kunde', Frontend::getCustomer())
               ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
               ->assign('OrderAmount', Frontend::getCart()->gibGesamtsummeWaren(true))
               ->assign('ShopCreditAmount', Frontend::getCustomer()->fGuthaben);

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG);

        /**
         * This is for compatibility in 3-step checkout and will prevent form in form tags trough payment plugins
         * @see /templates/Evo/checkout/step4_payment_options.tpl
         * ToDo: Replace with more convenient solution in later versions (after 4.06)
         */
        $step4PaymentContent = Shop::Smarty()->fetch('checkout/step4_payment_options.tpl');
        if (preg_match('/<form([^>]*)>/', $step4PaymentContent, $hits)) {
            $step4PaymentContent = str_replace($hits[0], '<div' . $hits[1] . '>', $step4PaymentContent);
            $step4PaymentContent = str_replace('</form>', '</div>', $step4PaymentContent);
        }
        $smarty->assign('step4_payment_content', $step4PaymentContent);
    }
}

/**
 * @param array $post
 */
function gibStepZahlungZusatzschritt($post): void
{
    $paymentMethod = gibZahlungsart((int)$post['Zahlungsart']);
    $smarty        = Shop::Smarty();
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $customerAccountData = gibKundenKontodaten(Frontend::getCustomer()->kKunde);
    if (isset($customerAccountData->kKunde) && $customerAccountData->kKunde > 0) {
        $smarty->assign('oKundenKontodaten', $customerAccountData);
    }
    if (!isset($post['zahlungsartzusatzschritt']) || !$post['zahlungsartzusatzschritt']) {
        $smarty->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo ?? null);
    } else {
        setzeFehlendeAngaben(checkAdditionalPayment($paymentMethod));
        unset($_SESSION['checkout.fehlendeAngaben']);
        $smarty->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
    $smarty->assign('Zahlungsart', $paymentMethod)
           ->assign('Kunde', Frontend::getCustomer())
           ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNGZUSATZSCHRITT);
}

/**
 * @param array $get
 * @return string
 */
function gibStepBestaetigung($get)
{
    $linkHelper = Shop::Container()->getLinkService();
    //check currenct shipping method again to avoid using invalid methods when using one click method (#9566)
    if (isset($_SESSION['Versandart']->kVersandart) && !versandartKorrekt($_SESSION['Versandart']->kVersandart)) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editVersandart=1', true, 303);
    }
    // Bei Standardzahlungsarten mit Zahlungsinformationen prüfen ob Daten vorhanden sind
    if (isset($_SESSION['Zahlungsart'])
        && in_array($_SESSION['Zahlungsart']->cModulId, ['za_lastschrift_jtl', 'za_kreditkarte_jtl'], true)
        && (empty($_SESSION['Zahlungsart']->ZahlungsInfo) || !is_object($_SESSION['Zahlungsart']->ZahlungsInfo))
    ) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editZahlungsart=1', true, 303);
    }

    if (empty($get['fillOut'])) {
        unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
    }
    //falls zahlungsart extern und Einstellung, dass Bestellung für Kaufabwicklung notwendig, füllte tzahlungsession
    Shop::Smarty()->assign('Kunde', Frontend::getCustomer())
        ->assign('customerAttributes', Frontend::getCustomer()->getCustomerAttributes())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
        ->assign('currentCouponName', !empty($_SESSION['Kupon']->translationList)
            ? $_SESSION['Kupon']->translationList
            : null)
        ->assign('currentShippingCouponName', !empty($_SESSION['oVersandfreiKupon']->translationList)
            ? $_SESSION['oVersandfreiKupon']->translationList
            : null)
        ->assign('GuthabenMoeglich', guthabenMoeglich())
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_BESTELLABSCHLUSS)
        ->assign('cPost_arr', (isset($_SESSION['cPost_arr']) ? Text::filterXSS($_SESSION['cPost_arr']) : []));
    if (Frontend::getCustomer()->kKunde > 0) {
        Shop::Smarty()->assign('GuthabenLocalized', Frontend::getCustomer()->gibGuthabenLocalized());
    }
    $cart = Frontend::getCart();
    if (isset($cart->PositionenArr)
        && !empty($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']])
        && count($cart->PositionenArr) > 0
    ) {
        foreach ($cart->PositionenArr as $item) {
            if ((int)$item->nPosTyp === C_WARENKORBPOS_TYP_VERSANDPOS) {
                $item->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']];
            }
        }
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG);
}

/**
 *
 */
function gibStepVersand(): void
{
    global $step;
    pruefeVersandkostenfreiKuponVorgemerkt();
    $cart       = Frontend::getCart();
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = Frontend::getCustomer()->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = Frontend::getCustomer()->cPLZ;
    }
    $customerGroupID = Frontend::getCustomer()->kKundengruppe ?? null;
    if (!$customerGroupID) {
        $customerGroupID = Frontend::getCustomerGroup()->getID();
    }
    $shippingMethods = ShippingMethod::getPossibleShippingMethods(
        $lieferland,
        $plz,
        ShippingMethod::getShippingClasses($cart),
        $customerGroupID
    );
    $packagings      = ShippingMethod::getPossiblePackagings($customerGroupID);
    if (!empty($packagings) && $cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG)) {
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($packagings as $packaging) {
                    if ($packaging->cName === $item->cName[$packaging->cISOSprache]) {
                        $packaging->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }
    if (GeneralObject::hasCount($shippingMethods)
        || (is_array($shippingMethods) && count($shippingMethods) === 1 && GeneralObject::hasCount($packagings))
    ) {
        Shop::Smarty()->assign('Versandarten', $shippingMethods)
            ->assign('Verpackungsarten', $packagings);
    } elseif (is_array($shippingMethods) && count($shippingMethods) === 1
        && (is_array($packagings) && count($packagings) === 0)
    ) {
        pruefeVersandartWahl($shippingMethods[0]->kVersandart);
    } elseif (!is_array($shippingMethods) || count($shippingMethods) === 0) {
        Shop::Container()->getLogService()->error(
            'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: ' . $lieferland .
            ', PLZ: ' . $plz . ', Versandklasse: ' . ShippingMethod::getShippingClasses(Frontend::getCart()) .
            ', Kundengruppe: ' . $customerGroupID
        );
    }
    Shop::Smarty()->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND);
}

/**
 * @param array $post
 * @return array|int
 */
function plausiKupon($post)
{
    $errors = [];
    if (isset($post['Kuponcode'])
        && (isset($_SESSION['Bestellung']->lieferadresseGleich) || $_SESSION['Lieferadresse'])
    ) {
        $coupon = new Kupon();
        $coupon = $coupon->getByCode($_POST['Kuponcode']);
        if ($coupon !== false && $coupon->kKupon > 0) {
            $errors = Kupon::checkCoupon($coupon);
            if (angabenKorrekt($errors)) {
                Kupon::acceptCoupon($coupon);
                if ($coupon->cKuponTyp === Kupon::TYPE_SHIPPING) { // Versandfrei Kupon
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                }
            }
        } else {
            $errors['ungueltig'] = 11;
        }
    }
    plausiNeukundenKupon();
    Kupon::mapCouponErrorMessage($errors['ungueltig'] ?? 0);

    return (count($errors) > 0)
        ? $errors
        : 0;
}

/**
 *
 */
function plausiNeukundenKupon()
{
    if (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen'] === true) {
        return;
    }
    $customer = Frontend::getCustomer();
    if ((!isset($_SESSION['Kupon']->cKuponTyp) || $_SESSION['Kupon']->cKuponTyp !== 'standard')
        && !empty($customer->cMail)
    ) {
        $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        if ($customer->kKunde <= 0 && $conf['kaufabwicklung']['bestellvorgang_unregneukundenkupon_zulassen'] === 'N') {
            //unregistrierte Neukunden, keine Kupons für Gastbestellungen zugelassen
            return;
        }
        // not for already registered customers with order(s)
        if ($customer->kKunde > 0) {
            $order = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT kBestellung
                    FROM tbestellung
                    WHERE kKunde = :customerID
                    LIMIT 1',
                ['customerID' => $customer->kKunde],
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($order)) {
                return;
            }
        }

        $coupons = (new Kupon())->getNewCustomerCoupon();
        if (!empty($coupons) && !Kupon::newCustomerCouponUsed($customer->cMail)) {
            foreach ($coupons as $coupon) {
                if (angabenKorrekt(Kupon::checkCoupon($coupon))) {
                    Kupon::acceptCoupon($coupon);
                    break;
                }
            }
        }
    }
}

/**
 * @param Zahlungsart|object $paymentMethod
 * @return array
 */
function checkAdditionalPayment($paymentMethod): array
{
    foreach (['iban', 'bic'] as $dataKey) {
        if (!empty($_POST[$dataKey])) {
            $_POST[$dataKey] = mb_convert_case($_POST[$dataKey], MB_CASE_UPPER);
        }
    }

    $conf   = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
    $post   = Text::filterXSS($_POST);
    $errors = [];
    switch ($paymentMethod->cModulId) {
        case 'za_kreditkarte_jtl':
            if (empty($post['kreditkartennr'])) {
                $errors['kreditkartennr'] = 1;
            }
            if (empty($post['gueltigkeit'])) {
                $errors['gueltigkeit'] = 1;
            }
            if (empty($post['cvv'])) {
                $errors['cvv'] = 1;
            }
            if (empty($post['kartentyp'])) {
                $errors['kartentyp'] = 1;
            }
            if (empty($post['inhaber'])) {
                $errors['inhaber'] = 1;
            }
            break;

        case 'za_lastschrift_jtl':
            if (empty($post['bankname'])
                && $conf['zahlungsarten']['zahlungsart_lastschrift_kreditinstitut_abfrage'] === 'Y'
            ) {
                $errors['bankname'] = 1;
            }
            if (empty($post['inhaber'])
                && $conf['zahlungsarten']['zahlungsart_lastschrift_kontoinhaber_abfrage'] === 'Y'
            ) {
                $errors['inhaber'] = 1;
            }
            if (empty($post['bic'])) {
                if ($conf['zahlungsarten']['zahlungsart_lastschrift_bic_abfrage'] === 'Y') {
                    $errors['bic'] = 1;
                }
            } elseif (!checkBIC($post['bic'])) {
                $errors['bic'] = 2;
            }
            if (empty($post['iban'])) {
                $errors['iban'] = 1;
            } elseif (!plausiIban($post['iban'])) {
                $errors['iban'] = 2;
            }
            break;
    }

    return $errors;
}

/**
 * @param string $bic
 * @return bool
 */
function checkBIC($bic): bool
{
    return preg_match('/^[A-Z]{6}[A-Z\d]{2}([A-Z\d]{3})?$/i', $bic) === 1;
}

/**
 * @param string $iban
 * @return bool|mixed
 */
function plausiIban($iban)
{
    if ($iban === '' || mb_strlen($iban) < 6) {
        return false;
    }
    $iban  = str_replace(' ', '', $iban);
    $iban1 = mb_substr($iban, 4)
        . (string)(mb_ord($iban{0}) - 55)
        . (string)(mb_ord($iban{1}) - 55)
        . mb_substr($iban, 2, 2);
    $len   = mb_strlen($iban1);
    for ($i = 0; $i < $len; $i++) {
        if (mb_ord($iban1{$i}) > 64 && mb_ord($iban1{$i}) < 91) {
            $iban1 = mb_substr($iban1, 0, $i) . (string)(mb_ord($iban1{$i}) - 55) . mb_substr($iban1, $i + 1);
        }
    }

    $rest = 0;
    $len  = mb_strlen($iban1);
    for ($pos = 0; $pos < $len; $pos += 7) {
        $part = (string)$rest . mb_substr($iban1, $pos, 7);
        $rest = (int)$part % 97;
    }

    $pz = sprintf('%02d', 98 - $rest);

    if (mb_substr($iban, 2, 2) == '00') {
        return substr_replace($iban, $pz, 2, 2);
    }

    return $rest == 1;
}

/**
 * @return stdClass
 */
function gibPostZahlungsInfo(): stdClass
{
    $info = new stdClass();

    $info->cKartenNr    = isset($_POST['kreditkartennr'])
        ? Text::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES)
        : null;
    $info->cGueltigkeit = isset($_POST['gueltigkeit'])
        ? Text::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES)
        : null;
    $info->cCVV         = isset($_POST['cvv'])
        ? Text::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES) : null;
    $info->cKartenTyp   = isset($_POST['kartentyp'])
        ? Text::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES)
        : null;
    $info->cBankName    = isset($_POST['bankname'])
        ? Text::htmlentities(stripslashes(trim($_POST['bankname'])), ENT_QUOTES)
        : null;
    $info->cKontoNr     = isset($_POST['kontonr'])
        ? Text::htmlentities(stripslashes(trim($_POST['kontonr'])), ENT_QUOTES)
        : null;
    $info->cBLZ         = isset($_POST['blz'])
        ? Text::htmlentities(stripslashes(trim($_POST['blz'])), ENT_QUOTES)
        : null;
    $info->cIBAN        = isset($_POST['iban'])
        ? Text::htmlentities(stripslashes(trim($_POST['iban'])), ENT_QUOTES)
        : null;
    $info->cBIC         = isset($_POST['bic'])
        ? Text::htmlentities(stripslashes(trim($_POST['bic'])), ENT_QUOTES)
        : null;
    $info->cInhaber     = isset($_POST['inhaber'])
        ? Text::htmlentities(stripslashes(trim($_POST['inhaber'])), ENT_QUOTES)
        : null;

    return $info;
}

/**
 * @param int $paymentMethodID
 * @return int
 */
function zahlungsartKorrekt(int $paymentMethodID): int
{
    $cart = Frontend::getCart();
    unset($_SESSION['Zahlungsart']);
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    if ($paymentMethodID > 0
        && isset($_SESSION['Versandart']->kVersandart)
        && (int)$_SESSION['Versandart']->kVersandart > 0
    ) {
        $paymentMethod = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = :session_kversandart
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tversandartzahlungsart.kZahlungsart = :kzahlungsart',
            [
                'session_kversandart' => (int)$_SESSION['Versandart']->kVersandart,
                'kzahlungsart'        => $paymentMethodID
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (!$paymentMethod) {
            $paymentMethod = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            // only the null-payment-method is allowed to go ahead in this case
            if ($paymentMethod->cModulId !== 'za_null_jtl') {
                return 0;
            }
        }
        if (isset($paymentMethod->cModulId) && mb_strlen($paymentMethod->cModulId) > 0) {
            $config = Shop::Container()->getDB()->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cModulId'],
                [CONF_ZAHLUNGSARTEN, $paymentMethod->cModulId]
            );
            foreach ($config as $conf) {
                $paymentMethod->einstellungen[$conf->cName] = $conf->cWert;
            }
        }
        if (!zahlungsartGueltig($paymentMethod)) {
            return 0;
        }
        $note                        = Shop::Container()->getDB()->select(
            'tzahlungsartsprache',
            'kZahlungsart',
            (int)$paymentMethod->kZahlungsart,
            'cISOSprache',
            $_SESSION['cISOSprache'],
            null,
            null,
            false,
            'cHinweisTextShop'
        );
        $paymentMethod->cHinweisText = $note->cHinweisTextShop ?? '';
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren)
            && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y'
            && $paymentMethod->fAufpreis > 0
            && $paymentMethod->cName === 'Nachnahme'
        ) {
            $paymentMethod->fAufpreis = 0;
        }
        getPaymentSurchageDiscount($paymentMethod);
        $specialPosition        = new stdClass();
        $specialPosition->cName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if ($paymentMethod->kZahlungsart > 0) {
                $localized = Shop::Container()->getDB()->select(
                    'tzahlungsartsprache',
                    'kZahlungsart',
                    (int)$paymentMethod->kZahlungsart,
                    'cISOSprache',
                    $Sprache->cISO,
                    null,
                    null,
                    false,
                    'cName'
                );
                if (isset($localized->cName)) {
                    $specialPosition->cName[$Sprache->cISO] = $localized->cName;
                }
            }
        }
        $paymentMethod->angezeigterName = $specialPosition->cName;
        $_SESSION['Zahlungsart']        = $paymentMethod;
        $_SESSION['AktiveZahlungsart']  = $paymentMethod->kZahlungsart;
        if ($paymentMethod->cZusatzschrittTemplate) {
            $info                 = new stdClass();
            $additionalInfoExists = false;
            switch ($paymentMethod->cModulId) {
                case 'za_null_jtl':
                    // the null-paymentMethod did not has any additional-steps
                    break;
                case 'za_kreditkarte_jtl':
                    if (isset($_POST['kreditkartennr'])
                        && $_POST['kreditkartennr']
                        && $_POST['gueltigkeit']
                        && $_POST['cvv']
                        && $_POST['kartentyp']
                        && $_POST['inhaber']
                    ) {
                        $info->cKartenNr      = Text::htmlentities(
                            stripslashes($_POST['kreditkartennr']),
                            ENT_QUOTES
                        );
                        $info->cGueltigkeit   = Text::htmlentities(
                            stripslashes($_POST['gueltigkeit']),
                            ENT_QUOTES
                        );
                        $info->cCVV           = Text::htmlentities(
                            stripslashes($_POST['cvv']),
                            ENT_QUOTES
                        );
                        $info->cKartenTyp     = Text::htmlentities(
                            stripslashes($_POST['kartentyp']),
                            ENT_QUOTES
                        );
                        $info->cInhaber       = Text::htmlentities(
                            stripslashes($_POST['inhaber']),
                            ENT_QUOTES
                        );
                        $additionalInfoExists = true;
                    }
                    break;
                case 'za_lastschrift_jtl':
                    $fehlendeAngaben = checkAdditionalPayment($paymentMethod);

                    if (count($fehlendeAngaben) === 0) {
                        $info->cBankName      = Text::htmlentities(
                            stripslashes($_POST['bankname'] ?? ''),
                            ENT_QUOTES
                        );
                        $info->cKontoNr       = Text::htmlentities(
                            stripslashes($_POST['kontonr'] ?? ''),
                            ENT_QUOTES
                        );
                        $info->cBLZ           = Text::htmlentities(
                            stripslashes($_POST['blz'] ?? ''),
                            ENT_QUOTES
                        );
                        $info->cIBAN          = Text::htmlentities(
                            stripslashes($_POST['iban']),
                            ENT_QUOTES
                        );
                        $info->cBIC           = Text::htmlentities(
                            stripslashes($_POST['bic'] ?? ''),
                            ENT_QUOTES
                        );
                        $info->cInhaber       = Text::htmlentities(
                            stripslashes($_POST['inhaber'] ?? ''),
                            ENT_QUOTES
                        );
                        $additionalInfoExists = true;
                    }
                    break;
                default:
                    // Plugin-Zusatzschritt
                    $additionalInfoExists = true;
                    $paymentMethod        = PaymentMethod::create($paymentMethod->cModulId);
                    if ($paymentMethod && !$paymentMethod->handleAdditional($_POST)) {
                        $additionalInfoExists = false;
                    }
                    break;
            }
            if (!$additionalInfoExists) {
                return 1;
            }
            $paymentMethod->ZahlungsInfo = $info;
        }

        return 2;
    }

    return 0;
}

/**
 * @param $paymentMethod
 */
function getPaymentSurchageDiscount($paymentMethod)
{
    if ($paymentMethod->fAufpreis == 0) {
        return;
    }
    $cart = Frontend::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    $paymentMethod->cPreisLocalized = Preise::getLocalizedPriceString($paymentMethod->fAufpreis);
    $surcharge                      = $paymentMethod->fAufpreis;
    if ($paymentMethod->cAufpreisTyp === 'prozent') {
        $fGuthaben = $_SESSION['Bestellung']->fGuthabenGenutzt ?? 0;
        $surcharge = (($cart->gibGesamtsummeWarenExt(
            [
                C_WARENKORBPOS_TYP_ARTIKEL,
                C_WARENKORBPOS_TYP_VERSANDPOS,
                C_WARENKORBPOS_TYP_KUPON,
                C_WARENKORBPOS_TYP_GUTSCHEIN,
                C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
                C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                C_WARENKORBPOS_TYP_VERPACKUNG
            ],
            true
        ) - $fGuthaben) * $paymentMethod->fAufpreis) / 100.0;

        $paymentMethod->cPreisLocalized = Preise::getLocalizedPriceString($surcharge);
    }
    $specialPosition               = new stdClass();
    $specialPosition->cGebuehrname = [];
    foreach ($_SESSION['Sprachen'] as $Sprache) {
        if ($paymentMethod->kZahlungsart > 0) {
            $name_spr = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$paymentMethod->kZahlungsart,
                'cISOSprache',
                $Sprache->cISO,
                null,
                null,
                false,
                'cGebuehrname'
            );

            $specialPosition->cGebuehrname[$Sprache->cISO] = $name_spr->cGebuehrname ?? '';
            if ($paymentMethod->cAufpreisTyp === 'prozent') {
                if ($paymentMethod->fAufpreis > 0) {
                    $specialPosition->cGebuehrname[$Sprache->cISO] .= ' +';
                }
                $specialPosition->cGebuehrname[$Sprache->cISO] .= $paymentMethod->fAufpreis . '%';
            }
        }
    }
    if ($paymentMethod->cModulId === 'za_nachnahme_jtl') {
        $cart->erstelleSpezialPos(
            $specialPosition->cGebuehrname,
            1,
            $surcharge,
            $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
            C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
            true,
            true,
            $paymentMethod->cHinweisText
        );
    } else {
        $cart->erstelleSpezialPos(
            $specialPosition->cGebuehrname,
            1,
            $surcharge,
            $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
            C_WARENKORBPOS_TYP_ZAHLUNGSART,
            true,
            true,
            $paymentMethod->cHinweisText
        );
    }
}

/**
 * @param string $moduleID
 * @return bool|PluginInterface
 */
function gibPluginZahlungsart($moduleID)
{
    $pluginID = PluginHelper::getIDByModuleID($moduleID);
    if ($pluginID > 0) {
        $loader = PluginHelper::getLoaderByPluginID($pluginID);
        try {
            return $loader->init($pluginID);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    return false;
}

/**
 * @param int $paymentMethodID
 * @return mixed
 */
function gibZahlungsart(int $paymentMethodID)
{
    $method = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    foreach (Frontend::getLanguages() as $language) {
        $localized                                = Shop::Container()->getDB()->select(
            'tzahlungsartsprache',
            'kZahlungsart',
            $paymentMethodID,
            'cISOSprache',
            $language->cISO,
            null,
            null,
            false,
            'cName'
        );
        $method->angezeigterName[$language->cISO] = $localized->cName ?? null;
    }
    $confData = Shop::Container()->getDB()->queryPrepared(
        'SELECT *
            FROM teinstellungen
            WHERE kEinstellungenSektion = :sec
                AND cModulId = :mod',
        ['mod' => $method->cModulId, 'sec' => CONF_ZAHLUNGSARTEN],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($confData as $conf) {
        $method->einstellungen[$conf->cName] = $conf->cWert;
    }
    $plugin = gibPluginZahlungsart($method->cModulId);
    if ($plugin) {
        $method->cZusatzschrittTemplate =
            $plugin->oPluginZahlungsmethodeAssoc_arr[$method->cModulId]->cZusatzschrittTemplate;
    }

    return $method;
}

/**
 * @param null|int $customerID
 * @return object|bool
 */
function gibKundenKontodaten(?int $customerID)
{
    if (empty($customerID)) {
        return false;
    }
    $accountData = Shop::Container()->getDB()->select('tkundenkontodaten', 'kKunde', $customerID);

    if (isset($accountData->kKunde) && $accountData->kKunde > 0) {
        $cryptoService = Shop::Container()->getCryptoService();
        if (mb_strlen($accountData->cBLZ) > 0) {
            $accountData->cBLZ = (int)$cryptoService->decryptXTEA($accountData->cBLZ);
        }
        if (mb_strlen($accountData->cInhaber) > 0) {
            $accountData->cInhaber = trim($cryptoService->decryptXTEA($accountData->cInhaber));
        }
        if (mb_strlen($accountData->cBankName) > 0) {
            $accountData->cBankName = trim($cryptoService->decryptXTEA($accountData->cBankName));
        }
        if (mb_strlen($accountData->nKonto) > 0) {
            $accountData->nKonto = trim($cryptoService->decryptXTEA($accountData->nKonto));
        }
        if (mb_strlen($accountData->cIBAN) > 0) {
            $accountData->cIBAN = trim($cryptoService->decryptXTEA($accountData->cIBAN));
        }
        if (mb_strlen($accountData->cBIC) > 0) {
            $accountData->cBIC = trim($cryptoService->decryptXTEA($accountData->cBIC));
        }

        return $accountData;
    }

    return false;
}

/**
 * @param int $shippingMethodID
 * @param int $customerGroupID
 * @return array
 */
function gibZahlungsarten(int $shippingMethodID, int $customerGroupID)
{
    $taxRate = 0.0;
    $methods = [];
    if ($shippingMethodID > 0) {
        $methods = Shop::Container()->getDB()->queryPrepared(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = :sid
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen=''
                    OR FIND_IN_SET(:cgid, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                    AND tzahlungsart.nActive = 1
                    AND tzahlungsart.nNutzbar = 1
                ORDER BY tzahlungsart.nSort",
            ['sid' => $shippingMethodID, 'cgid' => $customerGroupID],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $valid = [];
    foreach ($methods as $method) {
        if (!$method->kZahlungsart) {
            continue;
        }
        $method->kVersandartZahlungsart = (int)$method->kVersandartZahlungsart;
        $method->kVersandart            = (int)$method->kVersandart;
        $method->kZahlungsart           = (int)$method->kZahlungsart;
        $method->nSort                  = (int)$method->nSort;
        //posname lokalisiert ablegen
        $method->angezeigterName = [];
        $method->cGebuehrname    = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $name_spr = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart,
                'cISOSprache',
                $Sprache->cISO,
                null,
                null,
                false,
                'cName, cGebuehrname, cHinweisTextShop'
            );
            if (isset($name_spr->cName)) {
                $method->angezeigterName[$Sprache->cISO] = $name_spr->cName;
                $method->cGebuehrname[$Sprache->cISO]    = $name_spr->cGebuehrname;
                $method->cHinweisText[$Sprache->cISO]    = $name_spr->cHinweisTextShop;
            }
        }
        $confData = Shop::Container()->getDB()->selectAll(
            'teinstellungen',
            ['kEinstellungenSektion', 'cModulId'],
            [CONF_ZAHLUNGSARTEN, $method->cModulId]
        );
        foreach ($confData as $config) {
            $method->einstellungen[$config->cName] = $config->cWert;
        }
        if (!zahlungsartGueltig($method)) {
            continue;
        }
        $method->Specials = null;
        //evtl. Versandkupon anwenden / Nur Nachname fällt weg
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren)
            && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y'
            && $method->fAufpreis > 0
            && $method->cName === 'Nachnahme'
        ) {
            $method->fAufpreis = 0;
        }
        //lokalisieren
        if ($method->cAufpreisTyp === 'festpreis') {
            $method->fAufpreis *= ((100 + $taxRate) / 100);
        }
        $method->cPreisLocalized = Preise::getLocalizedPriceString($method->fAufpreis);
        if ($method->cAufpreisTyp === 'prozent') {
            $method->cPreisLocalized  = ($method->fAufpreis < 0) ? ' ' : '+ ';
            $method->cPreisLocalized .= $method->fAufpreis . '%';
        }
        if ($method->fAufpreis == 0) {
            $method->cPreisLocalized = '';
        }
        if (!empty($method->angezeigterName)) {
            $valid[] = $method;
        }
    }

    return $valid;
}

/**
 * @param object[] $shippingMethods
 * @return int
 */
function gibAktiveVersandart($shippingMethods)
{
    if (isset($_SESSION['Versandart'])) {
        $_SESSION['AktiveVersandart'] = $_SESSION['Versandart']->kVersandart;
    } elseif (!empty($_SESSION['AktiveVersandart']) && GeneralObject::hasCount($shippingMethods)) {
        $active = (int)$_SESSION['AktiveVersandart'];
        if (array_reduce($shippingMethods, function ($carry, $item) use ($active) {
            return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
        }, 0) !== (int)$_SESSION['AktiveVersandart']) {
            $_SESSION['AktiveVersandart'] = $shippingMethods[0]->kVersandart;
        }
    } else {
        $_SESSION['AktiveVersandart'] = $shippingMethods[0]->kVersandart ?? 0;
    }

    return $_SESSION['AktiveVersandart'];
}

/**
 * @param object[] $shippingMethods
 * @return int
 */
function gibAktiveZahlungsart($shippingMethods)
{
    if (isset($_SESSION['Zahlungsart'])) {
        $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
    } elseif (!empty($_SESSION['AktiveZahlungsart']) && GeneralObject::hasCount($shippingMethods)) {
        $active = (int)$_SESSION['AktiveZahlungsart'];
        if (array_reduce($shippingMethods, function ($carry, $item) use ($active) {
            return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
        }, 0) !== (int)$_SESSION['AktiveZahlungsart']) {
            $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
        }
    } else {
        $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
    }

    return $_SESSION['AktiveZahlungsart'];
}

/**
 * @param object[] $packagings
 * @return array
 */
function gibAktiveVerpackung($packagings): array
{
    if (isset($_SESSION['Verpackung']) && count($_SESSION['Verpackung']) > 0) {
        $_SESSION['AktiveVerpackung'] = [];
        foreach ($_SESSION['Verpackung'] as $verpackung) {
            $_SESSION['AktiveVerpackung'][$verpackung->kVerpackung] = 1;
        }
    } elseif (!empty($_SESSION['AktiveVerpackung']) && is_array($packagings) && count($packagings) > 0) {
        foreach (array_keys($_SESSION['AktiveVerpackung']) as $active) {
            if (array_reduce($packagings, function ($carry, $item) use ($active) {
                $kVerpackung = (int)$item->kVerpackung;
                return $kVerpackung === $active ? $kVerpackung : $carry;
            }, 0) === 0) {
                unset($_SESSION['AktiveVerpackung'][$active]);
            }
        }
    } else {
        $_SESSION['AktiveVerpackung'] = [];
    }

    return $_SESSION['AktiveVerpackung'];
}

/**
 * @param Zahlungsart|stdClass $paymentMethod
 * @return bool
 */
function zahlungsartGueltig($paymentMethod): bool
{
    if (!isset($paymentMethod->cModulId)) {
        return false;
    }
    $pluginID = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
    if ($pluginID > 0) {
        $loader = PluginHelper::getLoaderByPluginID($pluginID);
        $plugin = $loader->init($pluginID);
        if ($plugin !== null) {
            global $oPlugin;
            $oPlugin = $plugin;
            if ($plugin->getState() !== State::ACTIVATED) {
                return false;
            }
            $methods = $plugin->getPaymentMethods()->getMethodsAssoc();
            require_once $plugin->getPaths()->getVersionedPath() . PFAD_PLUGIN_PAYMENTMETHOD
                . $methods[$paymentMethod->cModulId]->cClassPfad;
            $className        = $methods[$paymentMethod->cModulId]->cClassName;
            $method           = new $className($paymentMethod->cModulId);
            $method->cModulId = $paymentMethod->cModulId;
            /** @var PaymentMethod $method */
            if ($method && $method->isSelectable() === false) {
                return false;
            }
            if ($method && !$method->isValidIntern()) {
                Shop::Container()->getLogService()->withName('cModulId')->debug(
                    'Die Zahlungsartprüfung (' . $paymentMethod->cModulId .
                    ') wurde nicht erfolgreich validiert (isValidIntern).',
                    [$paymentMethod->cModulId]
                );

                return false;
            }
            if (!PluginHelper::licenseCheck($plugin, ['cModulId' => $paymentMethod->cModulId])) {
                return false;
            }

            return $method->isValid(Frontend::getCustomer(), Frontend::getCart());
        }
    } else {
        $instance = new PaymentMethod($paymentMethod->cModulId);
        $method   = $instance::create($paymentMethod->cModulId);
        if ($method && $method->isSelectable() === false) {
            return false;
        }
        if ($method && !$method->isValidIntern()) {
            Shop::Container()->getLogService()->withName('cModulId')->debug(
                'Die Zahlungsartprüfung (' .
                    $paymentMethod->cModulId . ') wurde nicht erfolgreich validiert (isValidIntern).',
                [$paymentMethod->cModulId]
            );

            return false;
        }

        return Helper::shippingMethodWithValidPaymentMethod($paymentMethod);
    }

    return false;
}

/**
 * @param int $minOrders
 * @return bool
 */
function pruefeZahlungsartMinBestellungen($minOrders): bool
{
    if ($minOrders <= 0) {
        return true;
    }
    if (Frontend::getCustomer()->kKunde > 0) {
        $count = Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS anz
                FROM tbestellung
                WHERE kKunde = ' . Frontend::getCustomer()->kKunde . '
                    AND (cStatus = ' . BESTELLUNG_STATUS_BEZAHLT . '
                    OR cStatus = ' . BESTELLUNG_STATUS_VERSANDT . ')',
            ReturnType::SINGLE_OBJECT
        );
        if ($count->anz < $minOrders) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl ' .
                $count->anz . ' < ' . $minOrders
            );

            return false;
        }
    } else {
        Shop::Container()->getLogService()->debug('pruefeZahlungsartMinBestellungen erhielt keinen kKunden');

        return false;
    }

    return true;
}

/**
 * @param float $fMinBestellwert
 * @return bool
 */
function pruefeZahlungsartMinBestellwert($fMinBestellwert): bool
{
    if ($fMinBestellwert > 0
        && Frontend::getCart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true) <
        $fMinBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMinBestellwert Bestellwert zu niedrig: Wert ' .
            Frontend::getCart()->gibGesamtsummeWaren(true) . ' < ' . $fMinBestellwert
        );

        return false;
    }

    return true;
}

/**
 * @param float $fMaxBestellwert
 * @return bool
 */
function pruefeZahlungsartMaxBestellwert($fMaxBestellwert): bool
{
    if ($fMaxBestellwert > 0
        && Frontend::getCart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true)
        >= $fMaxBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert ' .
            Frontend::getCart()->gibGesamtsummeWaren(true) . ' > ' . $fMaxBestellwert
        );

        return false;
    }

    return true;
}

/**
 * @param int $kVersandart
 * @param int $aFormValues
 * @return bool
 */
function versandartKorrekt(int $kVersandart, $aFormValues = 0)
{
    $cart                   = Frontend::getCart();
    $packagingIDs           = GeneralObject::hasCount('kVerpackung', $_POST)
        ? $_POST['kVerpackung']
        : $aFormValues['kVerpackung'];
    $fSummeWarenkorb        = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
    $_SESSION['Verpackung'] = [];
    if (GeneralObject::hasCount($packagingIDs)) {
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        foreach ($packagingIDs as $kVerpackung) {
            $kVerpackung = (int)$kVerpackung;
            $packagings  = Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tverpackung
                    WHERE kVerpackung = :pid
                        AND (tverpackung.cKundengruppe = '-1'
                            OR FIND_IN_SET(:cgid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                        AND :sum >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1",
                [
                    'pid'  => $kVerpackung,
                    'cgid' => Frontend::getCustomerGroup()->getID(),
                    'sum'  => $fSummeWarenkorb
                ],
                ReturnType::SINGLE_OBJECT
            );

            $packagings->kVerpackung = (int)$packagings->kVerpackung;
            if (empty($packagings->kVerpackung)) {
                return false;
            }
            $localizedNames     = [];
            $localizedPackaging = Shop::Container()->getDB()->selectAll(
                'tverpackungsprache',
                'kVerpackung',
                (int)$packagings->kVerpackung
            );
            foreach ($localizedPackaging as $item) {
                $localizedNames[$item->cISOSprache] = $item->cName;
            }
            $fBrutto = $packagings->fBrutto;
            if ($fSummeWarenkorb >= $packagings->fKostenfrei
                && $packagings->fBrutto > 0
                && $packagings->fKostenfrei != 0
            ) {
                $fBrutto = 0;
            }
            if ($packagings->kSteuerklasse == -1) {
                $packagings->kSteuerklasse = $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand);
            }
            $_SESSION['Verpackung'][] = $packagings;

            $_SESSION['AktiveVerpackung'][$packagings->kVerpackung] = 1;
            $cart->erstelleSpezialPos(
                $localizedNames,
                1,
                $fBrutto,
                $packagings->kSteuerklasse,
                C_WARENKORBPOS_TYP_VERPACKUNG,
                false
            );
            unset($packagings);
        }
    }
    unset($_SESSION['Versandart']);
    if ($kVersandart <= 0) {
        return false;
    }
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = Frontend::getCustomer()->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = Frontend::getCustomer()->cPLZ;
    }
    $versandklassen           = ShippingMethod::getShippingClasses(Frontend::getCart());
    $cNurAbhaengigeVersandart = 'N';
    if (ShippingMethod::normalerArtikelversand($lieferland) === false) {
        $cNurAbhaengigeVersandart = 'Y';
    }
    $cISO       = $lieferland;
    $versandart = Shop::Container()->getDB()->queryPrepared(
        "SELECT *
            FROM tversandart
            WHERE cLaender LIKE :iso
                AND cNurAbhaengigeVersandart = :dep
                AND (cVersandklassen = '-1' OR cVersandklassen RLIKE :scl)
                AND kVersandart = :sid",
        [
            'iso' => '%' . $cISO . '%',
            'dep' => $cNurAbhaengigeVersandart,
            'scl' => '^([0-9 -]* )?' . $versandklassen,
            'sid' => $kVersandart
        ],
        ReturnType::SINGLE_OBJECT
    );

    if (!isset($versandart->kVersandart) || $versandart->kVersandart <= 0) {
        return false;
    }
    $versandart->Zuschlag  = ShippingMethod::getAdditionalFees($versandart, $cISO, $plz);
    $versandart->fEndpreis = ShippingMethod::calculateShippingFees($versandart, $cISO, null);
    if ($versandart->fEndpreis == -1) {
        return false;
    }
    $Spezialpos        = new stdClass();
    $Spezialpos->cName = [];
    foreach ($_SESSION['Sprachen'] as $language) {
        $localized = Shop::Container()->getDB()->select(
            'tversandartsprache',
            'kVersandart',
            (int)$versandart->kVersandart,
            'cISOSprache',
            $language->cISO,
            null,
            null,
            false,
            'cName, cHinweisTextShop'
        );
        if (isset($localized->cName)) {
            $Spezialpos->cName[$language->cISO]                  = $localized->cName;
            $versandart->angezeigterName[$language->cISO]        = $localized->cName;
            $versandart->angezeigterHinweistext[$language->cISO] = $localized->cHinweisTextShop;
        }
    }
    $bSteuerPos = $versandart->eSteuer !== 'netto';
    // Ticket #1298 Inselzuschläge müssen bei Versandkostenfrei berücksichtigt werden
    $fVersandpreis = $versandart->fEndpreis;
    if (isset($versandart->Zuschlag->fZuschlag)) {
        $fVersandpreis = $versandart->fEndpreis - $versandart->Zuschlag->fZuschlag;
    }
    if ($versandart->fEndpreis == 0
        && isset($versandart->Zuschlag->fZuschlag)
        && $versandart->Zuschlag->fZuschlag > 0
    ) {
        $fVersandpreis = $versandart->fEndpreis;
    }
    $cart->erstelleSpezialPos(
        $Spezialpos->cName,
        1,
        $fVersandpreis,
        $cart->gibVersandkostenSteuerklasse($cISO),
        C_WARENKORBPOS_TYP_VERSANDPOS,
        true,
        $bSteuerPos
    );
    pruefeVersandkostenfreiKuponVorgemerkt();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
    if (isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag != 0) {
        $Spezialpos->cName = [];
        foreach (Frontend::getLanguages() as $language) {
            $localized                          = Shop::Container()->getDB()->select(
                'tversandzuschlagsprache',
                'kVersandzuschlag',
                (int)$versandart->Zuschlag->kVersandzuschlag,
                'cISOSprache',
                $language->cISO,
                null,
                null,
                false,
                'cName'
            );
            $Spezialpos->cName[$language->cISO] = $localized->cName;
        }
        $cart->erstelleSpezialPos(
            $Spezialpos->cName,
            1,
            $versandart->Zuschlag->fZuschlag,
            $cart->gibVersandkostenSteuerklasse($cISO),
            C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
            true,
            $bSteuerPos
        );
    }
    $_SESSION['Versandart']       = $versandart;
    $_SESSION['AktiveVersandart'] = $versandart->kVersandart;

    return true;
}

/**
 * @param array $missingData
 * @return int
 */
function angabenKorrekt(array $missingData): int
{
    foreach ($missingData as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}

/**
 * @param array $data
 * @param int   $kundenaccount
 * @param int   $checkpass
 * @return array
 */
function checkKundenFormularArray($data, int $kundenaccount, $checkpass = 1)
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KUNDEN, CONF_KUNDENFELD, CONF_GLOBAL]);

    foreach (['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'email'] as $dataKey) {
        $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

        if (!$data[$dataKey]) {
            $ret[$dataKey] = 1;
        }
    }

    foreach ([
                 'kundenregistrierung_abfragen_anrede' => 'anrede',
                 'kundenregistrierung_pflicht_vorname' => 'vorname',
                 'kundenregistrierung_abfragen_firma' => 'firma',
                 'kundenregistrierung_abfragen_firmazusatz' => 'firmazusatz',
                 'kundenregistrierung_abfragen_titel' => 'titel',
                 'kundenregistrierung_abfragen_adresszusatz' => 'adresszusatz',
                 'kundenregistrierung_abfragen_www' => 'www',
                 'kundenregistrierung_abfragen_bundesland' => 'bundesland'
             ] as $confKey => $dataKey) {
        if ($conf['kunden'][$confKey] === 'Y') {
            $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

            if (!$data[$dataKey]) {
                $ret[$dataKey] = 1;
            }
        }
    }

    if (!empty($data['www']) && !Text::filterURL($data['www'])) {
        $ret['www'] = 2;
    }

    if (isset($ret['email']) && $ret['email'] === 1) {
        // email is empty
    } elseif (Text::filterEmailAddress($data['email']) === false) {
        $ret['email'] = 2;
    } elseif (SimpleMail::checkBlacklist($data['email'])) {
        $ret['email'] = 3;
    } elseif (isset($conf['kunden']['kundenregistrierung_pruefen_email'])
        && $conf['kunden']['kundenregistrierung_pruefen_email'] === 'Y'
        && !checkdnsrr(mb_substr($data['email'], mb_strpos($data['email'], '@') + 1))
    ) {
        $ret['email'] = 4;
    }

    if (empty($_SESSION['check_plzort'])
        && empty($_SESSION['check_liefer_plzort'])
        && $conf['kunden']['kundenregistrierung_abgleichen_plz'] === 'Y'
    ) {
        if (!valid_plzort($data['plz'], $data['ort'], $data['land'])) {
            $ret['plz']               = 2;
            $ret['ort']               = 2;
            $_SESSION['check_plzort'] = 1;
        }
    } else {
        unset($_SESSION['check_plzort']);
    }

    foreach ([
             'kundenregistrierung_abfragen_tel' => 'tel',
             'kundenregistrierung_abfragen_mobil' => 'mobil',
             'kundenregistrierung_abfragen_fax' => 'fax'
             ] as $confKey => $dataKey) {
        if (isset($data[$dataKey])
            && ($errCode = Text::checkPhoneNumber($data[$dataKey], $conf['kunden'][$confKey] === 'Y')) > 0
        ) {
            $ret[$dataKey] = $errCode;
        }
    }

    $deliveryCountry = ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N')
        ? Shop::Container()->getCountryService()->getCountry($data['land'])
        : null;

    if (isset($deliveryCountry)
        && !$deliveryCountry->isEU()
        && $conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y'
    ) {
        //skip
    } elseif (empty($data['ustid']) && $conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y') {
        $ret['ustid'] = 1;
    } elseif (isset($data['ustid'])
        && $data['ustid'] !== ''
        && $conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N'
    ) {
        if (!isset(Frontend::getCustomer()->cUSTID)
            || (isset(Frontend::getCustomer()->cUSTID)
                && Frontend::getCustomer()->cUSTID !== $data['ustid'])
        ) {
            $analizeCheck   = false;
            $resultVatCheck = null;
            if ($conf['kunden']['shop_ustid_bzstpruefung'] === 'Y') {
                $vatCheck       = new VATCheck(trim($data['ustid']));
                $resultVatCheck = $vatCheck->doCheckID();
                $analizeCheck   = true;
            }
            if ($analizeCheck === true && $resultVatCheck['success'] === true) {
                $ret['ustid'] = 0;
            } elseif (isset($resultVatCheck)) {
                switch ($resultVatCheck['errortype']) {
                    case 'vies':
                        // vies-error: the ID is invalid according to the VIES-system
                        $ret['ustid'] = $resultVatCheck['errorcode']; // (old value 5)
                        break;
                    case 'parse':
                        // parse-error: the ID-string is misspelled in any way
                        if ($resultVatCheck['errorcode'] === 1) {
                            $ret['ustid'] = 1; // parse-error: no id was given
                        } elseif ($resultVatCheck['errorcode'] > 1) {
                            $ret['ustid'] = 2; // parse-error: with the position of error in given ID-string
                            switch ($resultVatCheck['errorcode']) {
                                case 120:
                                    // build a string with error-code and error-information
                                    $ret['ustid_err'] = $resultVatCheck['errorcode'] . ',' .
                                        mb_substr($data['ustid'], 0, $resultVatCheck['errorinfo']) .
                                        '<span style="color:red;">'.
                                        mb_substr($data['ustid'], $resultVatCheck['errorinfo']) .
                                        '</span>';
                                    break;
                                case 130:
                                    $ret['ustid_err'] = $resultVatCheck['errorcode'] . ',' .
                                        $resultVatCheck['errorinfo'];
                                    break;
                                default:
                                    $ret['ustid_err'] = $resultVatCheck['errorcode'];
                                    break;
                            }
                        }
                        break;
                    case 'time':
                        // according to the backend-setting:
                        // "Einstellungen -> (Formular)einstellungen -> UstID-Nummer"-check active
                        if ($conf['kunden']['shop_ustid_force_remote_check'] === 'Y') {
                            // parsing ok, but the remote-service is in a down slot and unreachable
                            $ret['ustid']     = 4;
                            $ret['ustid_err'] = $resultVatCheck['errorcode'] . ',' . $resultVatCheck['errorinfo'];
                        }
                        break;
                }
            }
        }
    }
    if (isset($data['geburtstag'])
        && ($errCode = Text::checkDate(
            $data['geburtstag'],
            $conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y'
        )) > 0
    ) {
        $ret['geburtstag'] = $errCode;
    }
    if ($kundenaccount === 1) {
        if ($checkpass) {
            if ($data['pass'] !== $data['pass2']) {
                $ret['pass_ungleich'] = 1;
            }
            if (mb_strlen($data['pass']) < $conf['kunden']['kundenregistrierung_passwortlaenge']) {
                $ret['pass_zu_kurz'] = 1;
            }
        }
        //existiert diese email bereits?
        if (!isset($ret['email']) && !isEmailAvailable($data['email'], Frontend::getCustomer()->kKunde ?? 0)) {
            if (!(isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0)) {
                $ret['email_vorhanden'] = 1;
            }
            $ret['email'] = 5;
        }
    }
    // Selbstdef. Kundenfelder
    if (isset($conf['kundenfeld']['kundenfeld_anzeigen']) && $conf['kundenfeld']['kundenfeld_anzeigen'] === 'Y') {
        $customerFields = new CustomerFields(Shop::getLanguage());
        /** @var CustomerField $customerField */
        foreach ($customerFields as $customerField) {
            // Kundendaten ändern?
            $customerFieldIdx = 'custom_' . $customerField->getID();
            if (isset($data[$customerFieldIdx])
                && ($check = $customerField->validate($data[$customerFieldIdx])) !== CustomerField::VALIDATE_OK
            ) {
                $ret['custom'][$customerField->getID()] = $check;
            }
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_ort'])
        && $conf['kunden']['kundenregistrierung_pruefen_ort'] === 'Y'
        && preg_match('#[0-9]+#', $data['ort'])
    ) {
        $ret['ort'] = 3;
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_name'])
        && $conf['kunden']['kundenregistrierung_pruefen_name'] === 'Y'
        && preg_match('#[0-9]+#', $data['nachname'])
    ) {
        $ret['nachname'] = 2;
    }

    if (isset($conf['kunden']['kundenregistrierung_pruefen_zeit'], $data['editRechnungsadresse'])
        && (int)$data['editRechnungsadresse'] !== 1
        && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
    ) {
        $dRegZeit = $_SESSION['dRegZeit'] ?? 0;
        if (!($dRegZeit + 5 < time())) {
            $ret['formular_zeit'] = 1;
        }
    }

    if (isset($conf['kunden']['registrieren_captcha'])
        && $conf['kunden']['registrieren_captcha'] !== 'N'
        && !Form::validateCaptcha($data)
    ) {
        $ret['captcha'] = 2;
    }

    return $ret;
}

/**
 * @param int $kundenaccount
 * @param int $checkpass
 * @return array
 */
function checkKundenFormular(int $kundenaccount, $checkpass = 1): array
{
    $data = Text::filterXSS($_POST); // create a copy

    return checkKundenFormularArray($data, $kundenaccount, $checkpass);
}

/**
 * @param array $data
 * @return array
 */
function checkLieferFormularArray($data): array
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KUNDEN]);

    foreach (['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land'] as $dataKey) {
        $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

        if (!isset($data[$dataKey]) || !$data[$dataKey]) {
            $ret[$dataKey] = 1;
        }
    }

    foreach ([
             'lieferadresse_abfragen_titel' => 'titel',
             'lieferadresse_abfragen_adresszusatz' => 'adresszusatz',
             'lieferadresse_abfragen_bundesland' => 'bundesland',
             ] as $confKey => $dataKey) {
        if ($conf['kunden'][$confKey] === 'Y') {
            $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

            if (!$data[$dataKey]) {
                $ret[$dataKey] = 1;
            }
        }
    }

    if ($conf['kunden']['lieferadresse_abfragen_email'] !== 'N') {
        $data['email'] = trim($data['email']);

        if (empty($data['email'])) {
            if ($conf['kunden']['lieferadresse_abfragen_email'] === 'Y') {
                $ret['email'] = 1;
            }
        } elseif (Text::filterEmailAddress($data['email']) === false) {
            $ret['email'] = 2;
        }
    }

    foreach (['tel', 'mobil', 'fax'] as $telType) {
        if ($conf['kunden']['lieferadresse_abfragen_' . $telType] !== 'N') {
            $result = Text::checkPhoneNumber($data[$telType]);
            if ($result === 1 && $conf['kunden']['lieferadresse_abfragen_' . $telType] === 'Y') {
                $ret[$telType] = 1;
            } elseif ($result > 1) {
                $ret[$telType] = $result;
            }
        }
    }

    if (empty($_SESSION['check_liefer_plzort'])
        && $conf['kunden']['kundenregistrierung_abgleichen_plz'] === 'Y'
    ) {
        if (!valid_plzort($data['plz'], $data['ort'], $data['land'])) {
            $ret['plz']                      = 2;
            $ret['ort']                      = 2;
            $_SESSION['check_liefer_plzort'] = 1;
        }
    } else {
        unset($_SESSION['check_liefer_plzort']);
    }

    return !empty($ret) ? ['shippingAddress' => $ret] : $ret;
}

/**
 * @param array $post
 * @return array
 */
function checkLieferFormular($post = null): array
{
    return checkLieferFormularArray($post ?? $_POST);
}

/**
 * @param object|Kupon $coupon
 * @return array
 * @deprecated since 5.0.0
 */
function checkeKupon($coupon): array
{
    return Kupon::checkCoupon($coupon);
}

/**
 * @param Kupon|object $coupon
 * @deprecated since 5.0.0
 */
function kuponAnnehmen($coupon)
{
    Kupon::acceptCoupon($coupon);
}

/**
 * liefert Gesamtsumme der Artikel im Warenkorb, welche dem Kupon zugeordnet werden können
 *
 * @param Kupon|object $coupon
 * @param array $cartPositions
 * @return float
 */
function gibGesamtsummeKuponartikelImWarenkorb($coupon, array $cartPositions)
{
    $total = 0;
    foreach ($cartPositions as $item) {
        if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && warenkorbKuponFaehigArtikel($coupon, [$item])
            && warenkorbKuponFaehigHersteller($coupon, [$item])
            && warenkorbKuponFaehigKategorien($coupon, [$item])
        ) {
            $total += $item->fPreis
                * $item->nAnzahl
                * ((100 + Tax::getSalesTax($item->kSteuerklasse)) / 100);
        }
    }

    return round($total, 2);
}

/**
 * @param Kupon|object $coupon
 * @param array $items
 * @return bool
 */
function warenkorbKuponFaehigArtikel($coupon, array $items): bool
{
    if (empty($coupon->cArtikel)) {
        return true;
    }
    foreach ($items as $item) {
        if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && preg_match('/;' . preg_quote($item->Artikel->cArtNr, '/') . ';/i', $coupon->cArtikel)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * @param Kupon|object $coupon
 * @param array $items
 * @return bool
 */
function warenkorbKuponFaehigHersteller($coupon, array $items): bool
{
    if (empty($coupon->cHersteller) || (int)$coupon->cHersteller === -1) {
        return true;
    }
    foreach ($items as $item) {
        if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && preg_match('/;' . preg_quote($item->Artikel->kHersteller, '/') . ';/i', $coupon->cHersteller)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * @param Kupon|object $coupon
 * @param array $items
 * @return bool
 */
function warenkorbKuponFaehigKategorien($coupon, array $items): bool
{
    if (empty($coupon->cKategorien) || (int)$coupon->cKategorien === -1) {
        return true;
    }
    $products = [];
    foreach ($items as $item) {
        if (empty($item->Artikel)) {
            continue;
        }
        $products[] = $item->Artikel->kVaterArtikel !== 0 ? $item->Artikel->kVaterArtikel : $item->Artikel->kArtikel;
    }
    //check if at least one product is in at least one category valid for this coupon
    $category = Shop::Container()->getDB()->query(
        'SELECT kKategorie
            FROM tkategorieartikel
              WHERE kArtikel IN (' . \implode(',', $products) . ')
                AND kKategorie IN (' . str_replace(';', ',', trim($coupon->cKategorien, ';')) . ')
                LIMIT 1',
        ReturnType::SINGLE_OBJECT
    );

    return !empty($category);
}

/**
 * @param array $post
 * @param int   $kundenaccount
 * @param int   $htmlentities
 * @return Customer
 */
function getKundendaten(array $post, $kundenaccount, $htmlentities = 1)
{
    $mapping = [
        'anrede'         => 'cAnrede',
        'vorname'        => 'cVorname',
        'nachname'       => 'cNachname',
        'strasse'        => 'cStrasse',
        'hausnummer'     => 'cHausnummer',
        'plz'            => 'cPLZ',
        'ort'            => 'cOrt',
        'land'           => 'cLand',
        'email'          => 'cMail',
        'tel'            => 'cTel',
        'fax'            => 'cFax',
        'firma'          => 'cFirma',
        'firmazusatz'    => 'cZusatz',
        'bundesland'     => 'cBundesland',
        'titel'          => 'cTitel',
        'adresszusatz'   => 'cAdressZusatz',
        'mobil'          => 'cMobil',
        'www'            => 'cWWW',
        'ustid'          => 'cUSTID',
        'geburtstag'     => 'dGeburtstag',
        'kundenherkunft' => 'cHerkunft'
    ];

    if ($kundenaccount !== 0) {
        $mapping['pass'] = 'cPasswort';
    }
    $customerID = Frontend::getCustomer()->kKunde;
    $customer   = new Customer($customerID);
    foreach ($mapping as $external => $internal) {
        if (isset($post[$external])) {
            $val = Text::filterXSS($post[$external]);
            if ($htmlentities) {
                $val = Text::htmlentities($val);
            }
            $customer->$internal = $val;
        }
    }

    $customer->cMail                 = mb_convert_case($customer->cMail, MB_CASE_LOWER);
    $customer->dGeburtstag           = Date::convertDateToMysqlStandard($customer->dGeburtstag ?? '');
    $customer->dGeburtstag_formatted = $customer->dGeburtstag === '_DBNULL_'
        ? ''
        : DateTime::createFromFormat('Y-m-d', $customer->dGeburtstag)->format('d.m.Y');
    $customer->angezeigtesLand       = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
    if (!empty($customer->cBundesland)) {
        $oISO = Staat::getRegionByIso($customer->cBundesland, $customer->cLand);
        if (is_object($oISO)) {
            $customer->cBundesland = $oISO->cName;
        }
    }

    return $customer;
}

/**
 * @param array $post
 * @return CustomerAttributes
 */
function getKundenattribute(array $post): CustomerAttributes
{
    $customerAttributes = new CustomerAttributes(Session::getCustomer()->getID());
    /** @var CustomerAttribute $customerAttribute */
    foreach ($customerAttributes as $customerAttribute) {
        if ($customerAttribute->isEditable()) {
            $idx = 'custom_' . $customerAttribute->getCustomerFieldID();
            $customerAttribute->setValue(isset($post[$idx]) ? Text::filterXSS($post[$idx]) : null);
        }
    }

    return $customerAttributes;
}

/**
 * @return array
 * @deprecated since 5.0.0 - use @see CustomerFields::getNonEditableFields instead
 */
function getKundenattributeNichtEditierbar(): array
{
    \trigger_error(__FUNCTION__ . ' is deprecated.', \E_USER_DEPRECATED);

    return (new CustomerFields())->getNonEditableFields();
}

/**
 * @return array - non editable customer fields
 * @deprecated since 5.0.0 - use @see CustomerFields::getNonEditableFields instead
 */
function getNonEditableCustomerFields(): array
{
    \trigger_error(__FUNCTION__ . ' is deprecated.', \E_USER_DEPRECATED);

    return (new CustomerFields())->getNonEditableFields();
}

/**
 * @param array $post
 * @return Lieferadresse
 */
function getLieferdaten(array $post)
{
    $post = Text::filterXSS($post);
    //erstelle neue Lieferadresse
    $shippingAddress                  = new Lieferadresse();
    $shippingAddress->cAnrede         = $post['anrede'] ?? null;
    $shippingAddress->cVorname        = $post['vorname'];
    $shippingAddress->cNachname       = $post['nachname'];
    $shippingAddress->cStrasse        = $post['strasse'];
    $shippingAddress->cHausnummer     = $post['hausnummer'];
    $shippingAddress->cPLZ            = $post['plz'];
    $shippingAddress->cOrt            = $post['ort'];
    $shippingAddress->cLand           = $post['land'];
    $shippingAddress->cMail           = $post['email'] ?? '';
    $shippingAddress->cTel            = $post['tel'] ?? null;
    $shippingAddress->cFax            = $post['fax'] ?? null;
    $shippingAddress->cFirma          = $post['firma'] ?? null;
    $shippingAddress->cZusatz         = $post['firmazusatz'] ?? null;
    $shippingAddress->cTitel          = $post['titel'] ?? null;
    $shippingAddress->cAdressZusatz   = $post['adresszusatz'] ?? null;
    $shippingAddress->cMobil          = $post['mobil'] ?? null;
    $shippingAddress->cBundesland     = $post['bundesland'] ?? null;
    $shippingAddress->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($shippingAddress->cLand);

    if (!empty($shippingAddress->cBundesland)) {
        $region = Staat::getRegionByIso($shippingAddress->cBundesland, $shippingAddress->cLand);
        if (is_object($region)) {
            $shippingAddress->cBundesland = $region->cName;
        }
    }

    return $shippingAddress;
}

/**
 * @param array $items
 * @return string
 */
function getArtikelQry(array $items): string
{
    $ret = '';
    foreach ($items as $item) {
        if (isset($item->Artikel->cArtNr) && mb_strlen($item->Artikel->cArtNr) > 0) {
            $ret .= " OR FIND_IN_SET('" .
                str_replace('%', '\%', Shop::Container()->getDB()->escape($item->Artikel->cArtNr))
                . "', REPLACE(cArtikel, ';', ',')) > 0";
        }
    }

    return $ret;
}

/**
 * @return bool
 */
function guthabenMoeglich(): bool
{
    return (Frontend::getCustomer()->fGuthaben > 0
            && (empty($_SESSION['Bestellung']->GuthabenNutzen) || !$_SESSION['Bestellung']->GuthabenNutzen));
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function kuponMoeglich()
{
    return Kupon::couponsAvailable();
}


/**
 * @return bool
 */
function freeGiftStillValid(): bool
{
    $cart  = Frontend::getCart();
    $valid = true;
    foreach ($cart->PositionenArr as $item) {
        if ($item->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            continue;
        }
        // Prüfen ob der Artikel wirklich ein Gratisgeschenk ist und ob die Mindestsumme erreicht wird
        $girft = Shop::Container()->getDB()->queryPrepared(
            'SELECT kArtikel
                FROM tartikelattribut
                WHERE kArtikel = :pid
                   AND cName = :attr
                   AND CAST(cWert AS DECIMAL) <= :sum',
            [
                'pid'  => $item->kArtikel,
                'attr' => FKT_ATTRIBUT_GRATISGESCHENK,
                'sum'  => $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
            ],
            ReturnType::SINGLE_OBJECT
        );

        if (empty($girft->kArtikel)) {
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
            $valid = false;
        }
        break;
    }

    return $valid;
}

/**
 * @param string $plz
 * @param string $ort
 * @param string $land
 * @return bool
 */
function valid_plzort(string $plz, string $ort, string $land): bool
{
    // Länder die wir mit Ihren Postleitzahlen in der Datenbank haben
    $supportedCountryCodes = ['DE', 'AT', 'CH'];
    if (!in_array(mb_convert_case($land, MB_CASE_UPPER), $supportedCountryCodes, true)) {
        return true;
    }
    $obj = Shop::Container()->getDB()->executeQueryPrepared(
        'SELECT kPLZ
        FROM tplz
        WHERE cPLZ = :plz
            AND INSTR(cOrt COLLATE utf8_german2_ci, :ort)
            AND cLandISO = :land',
        [
            'plz'  => $plz,
            'ort'  => $ort,
            'land' => $land
        ],
        ReturnType::SINGLE_OBJECT
    );

    return isset($obj->kPLZ) && $obj->kPLZ > 0;
}

/**
 * @param string $step
 * @return array
 */
function gibBestellschritt(string $step)
{
    $res    = [];
    $res[1] = 3;
    $res[2] = 3;
    $res[3] = 3;
    $res[4] = 3;
    $res[5] = 3;
    switch ($step) {
        case 'accountwahl':
        case 'edit_customer_address':
            $res[1] = 1;
            $res[2] = 3;
            $res[3] = 3;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Lieferadresse':
            $res[1] = 2;
            $res[2] = 1;
            $res[3] = 3;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Versand':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 1;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Zahlung':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 1;
            $res[5] = 3;
            break;

        case 'ZahlungZusatzschritt':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 1;
            $res[5] = 3;
            break;

        case 'Bestaetigung':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 2;
            $res[5] = 1;
            break;

        default:
            break;
    }

    return $res;
}

/**
 * @return Lieferadresse
 */
function setzeLieferadresseAusRechnungsadresse(): Lieferadresse
{
    $customer                         = Frontend::getCustomer();
    $shippingAddress                  = new Lieferadresse();
    $shippingAddress->kKunde          = $customer->kKunde;
    $shippingAddress->cAnrede         = $customer->cAnrede;
    $shippingAddress->cVorname        = $customer->cVorname;
    $shippingAddress->cNachname       = $customer->cNachname;
    $shippingAddress->cStrasse        = $customer->cStrasse;
    $shippingAddress->cHausnummer     = $customer->cHausnummer;
    $shippingAddress->cPLZ            = $customer->cPLZ;
    $shippingAddress->cOrt            = $customer->cOrt;
    $shippingAddress->cLand           = $customer->cLand;
    $shippingAddress->cMail           = $customer->cMail;
    $shippingAddress->cTel            = $customer->cTel;
    $shippingAddress->cFax            = $customer->cFax;
    $shippingAddress->cFirma          = $customer->cFirma;
    $shippingAddress->cZusatz         = $customer->cZusatz;
    $shippingAddress->cTitel          = $customer->cTitel;
    $shippingAddress->cAdressZusatz   = $customer->cAdressZusatz;
    $shippingAddress->cMobil          = $customer->cMobil;
    $shippingAddress->cBundesland     = $customer->cBundesland;
    $shippingAddress->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($shippingAddress->cLand);
    $_SESSION['Lieferadresse']        = $shippingAddress;

    return $shippingAddress;
}

/**
 * @return CustomerFields
 * @deprecated since 5.0.0 - use @see CustomerFields class instead
 */
function gibSelbstdefKundenfelder(): CustomerFields
{
    return new CustomerFields(Shop::getLanguageID());
}

/**
 * @return int
 */
function pruefeAjaxEinKlick(): int
{
    if (($customerID = Frontend::getCustomer()->getID()) <= 0) {
        return 0;
    }
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    // Prüfe ob Kunde schon bestellt hat, falls ja --> Lieferdaten laden
    $lastOrder = Shop::Container()->getDB()->queryPrepared(
        "SELECT tbestellung.kBestellung, tbestellung.kLieferadresse, tbestellung.kZahlungsart, tbestellung.kVersandart
            FROM tbestellung
            JOIN tzahlungsart
                ON tzahlungsart.kZahlungsart = tbestellung.kZahlungsart
                AND (tzahlungsart.cKundengruppen IS NULL
                    OR tzahlungsart.cKundengruppen = ''
                    OR FIND_IN_SET(:cgid, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
            JOIN tversandart
                ON tversandart.kVersandart = tbestellung.kVersandart
                AND (tversandart.cKundengruppen = '-1'
                    OR FIND_IN_SET(:cgid, REPLACE(tversandart.cKundengruppen, ';', ',')) > 0)
            JOIN tversandartzahlungsart
                ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kKunde = :cid
            ORDER BY tbestellung.dErstellt
            DESC LIMIT 1",
        ['cgid' => $customerGroupID, 'cid' => $customerID],
        ReturnType::SINGLE_OBJECT
    );

    if (!isset($lastOrder->kBestellung) || $lastOrder->kBestellung <= 0) {
        return 2;
    }
    // Hat der Kunde eine Lieferadresse angegeben?
    if ($lastOrder->kLieferadresse > 0) {
        $addressData = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . $customerID . '
                    AND kLieferadresse = ' . (int)$lastOrder->kLieferadresse,
            ReturnType::SINGLE_OBJECT
        );
        if ($addressData->kLieferadresse > 0) {
            $addressData               = new Lieferadresse((int)$addressData->kLieferadresse);
            $_SESSION['Lieferadresse'] = $addressData;
            if (!isset($_SESSION['Bestellung'])) {
                $_SESSION['Bestellung'] = new stdClass();
            }
            $_SESSION['Bestellung']->kLieferadresse = $lastOrder->kLieferadresse;
            Shop::Smarty()->assign('Lieferadresse', $addressData);
        }
    } else {
        Shop::Smarty()->assign('Lieferadresse', setzeLieferadresseAusRechnungsadresse());
    }
    pruefeVersandkostenfreiKuponVorgemerkt();
    Tax::setTaxRates();
    // Prüfe Versandart, falls korrekt --> laden
    if (empty($lastOrder->kVersandart)) {
        return 3;
    }
    if (isset($_SESSION['Versandart'])) {
        $bVersandart = true;
    } else {
        $bVersandart = pruefeVersandartWahl($lastOrder->kVersandart, 0, false);
    }
    if ($bVersandart) {
        if ($lastOrder->kZahlungsart > 0) {
            if (isset($_SESSION['Zahlungsart'])) {
                return 5;
            }
            if (zahlungsartKorrekt($lastOrder->kZahlungsart) === 2) {
                gibStepZahlung();

                return 5;
            }
            unset($_SESSION['Zahlungsart']);

            return 4;
        }
        unset($_SESSION['Zahlungsart']);

        return 4;
    }

    return 3;
}

/**
 *
 */
function ladeAjaxEinKlick(): void
{
    global $aFormValues;
    gibKunde();
    gibFormularDaten();
    gibStepLieferadresse();
    gibStepVersand();
    gibStepZahlung();
    gibStepBestaetigung($aFormValues);

    Shop::Smarty()->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        Frontend::getCustomerGroup()->getID()
    ))
    ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
    ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren());
}

/**
 * @param string $cUserLogin
 * @param string $cUserPass
 * @return int
 */
function plausiAccountwahlLogin($cUserLogin, $cUserPass): int
{
    global $Kunde;
    if (mb_strlen($cUserLogin) > 0 && mb_strlen($cUserPass) > 0) {
        $Kunde = new Customer();
        $Kunde->holLoginKunde($cUserLogin, $cUserPass);
        if ($Kunde->kKunde > 0) {
            return 10;
        }

        return 2;
    }

    return 1;
}

/**
 * @param Customer $customer
 * @return bool
 */
function setzeSesssionAccountwahlLogin($customer): bool
{
    if (empty($customer->kKunde)) {
        return false;
    }
    if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
        $upd         = new stdClass();
        $upd->kKunde = (int)$customer->kKunde;
        Shop::Container()->getDB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $upd);
    }
    Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                         ->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    unset(
        $_SESSION['Zahlungsart'],
        $_SESSION['Versandart'],
        $_SESSION['Lieferadresse'],
        $_SESSION['ks'],
        $_SESSION['VersandKupon'],
        $_SESSION['oVersandfreiKupon'],
        $_SESSION['NeukundenKupon'],
        $_SESSION['Kupon']
    );
    $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
    $session                   = Frontend::getInstance();
    $session->setCustomer($customer);

    return true;
}

/**
 *
 */
function setzeSmartyAccountwahl()
{
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Frontend::getCart()));
}

/**
 * @param string $cFehler
 */
function setzeFehlerSmartyAccountwahl($cFehler)
{
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        $cFehler,
        'smartyAccountwahlError'
    );
}

/**
 * @param array $post
 * @param array $missingData
 * @return bool
 */
function setzeSessionRechnungsadresse(array $post, $missingData)
{
    $customer           = getKundendaten($post, 0);
    $customerAttributes = getKundenattribute($post);
    if (count($missingData) > 0) {
        return false;
    }
    $customer->getCustomerAttributes()->assign($customerAttributes);
    $customer->nRegistriert = 0;
    $_SESSION['Kunde']      = $customer;
    if (isset($_SESSION['Warenkorb']->kWarenkorb)
        && Frontend::getCart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
    ) {
        if ((int)$_SESSION['Bestellung']->kLieferadresse === 0 && $_SESSION['Lieferadresse']) {
            setzeLieferadresseAusRechnungsadresse();
        }
        Tax::setTaxRates();
        Frontend::getCart()->gibGesamtsummeWarenLocalized();
    }

    return true;
}

/**
 * @param int $nUnreg
 * @param int $nCheckout
 */
function setzeSmartyRechnungsadresse($nUnreg, $nCheckout = 0): void
{
    global $step;
    $smarty    = Shop::Smarty();
    $conf      = Shop::getSettings([CONF_KUNDEN]);
    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    if ($nUnreg) {
        $smarty->assign('step', 'formular');
    } else {
        $_POST['editRechnungsadresse'] = 1;
        $smarty->assign('editRechnungsadresse', 1)
               ->assign('step', 'rechnungsdaten');
        $step = 'rechnungsdaten';
    }
    if (count(Frontend::getCustomer()->getCustomerAttributes()) === 0) {
        Frontend::getCustomer()->getCustomerAttributes()->assign(getKundenattribute($_POST));
    }
    $smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
           ->assign('herkunfte', $herkunfte)
           ->assign('Kunde', Frontend::getCustomer())
           ->assign(
               'laender',
               ShippingMethod::getPossibleShippingCountries(
                   Frontend::getCustomerGroup()->getID(),
                   false,
                   true
               )
           )
           ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
           ->assign('customerAttributes', Frontend::getCustomer()->getCustomerAttributes())
           ->assign(
               'warning_passwortlaenge',
               lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge'])
           );
    if ((int)$nCheckout === 1) {
        $smarty->assign('checkout', 1);
    }
}

/**
 * @param array $missingData
 * @param int   $nUnreg
 * @param array $post
 */
function setzeFehlerSmartyRechnungsadresse($missingData, $nUnreg = 0, $post = null): void
{
    $conf   = Shop::getSettings([CONF_KUNDEN]);
    $smarty = Shop::Smarty();
    setzeFehlendeAngaben($missingData);
    $origins = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
            ->assign('herkunfte', $origins)
            ->assign('Kunde', getKundendaten($post, 0))
            ->assign(
                'laender',
                ShippingMethod::getPossibleShippingCountries(Frontend::getCustomerGroup()->getID(), false, true)
            )
            ->assign(
                'LieferLaender',
                ShippingMethod::getPossibleShippingCountries(Frontend::getCustomerGroup()->getID())
            )
            ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
            ->assign(
                'warning_passwortlaenge',
                lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge'])
            )
            ->assign('customerAttributes', Frontend::getCustomer()->getCustomerAttributes());
    if ($nUnreg) {
        $smarty->assign('step', 'formular');
    } else {
        $smarty->assign('editRechnungsadresse', 1);
    }
}

/**
 * @param array $post
 * @return array
 */
function plausiLieferadresse(array $post): array
{
    $missingData = [];

    $_SESSION['Bestellung']->kLieferadresse = (int)$post['kLieferadresse'];
    //neue lieferadresse
    if ((int)$post['kLieferadresse'] === -1) {
        $missingData = checkLieferFormular($post);
        if (angabenKorrekt($missingData)) {
            return $missingData;
        }

        return $missingData;
    }
    if ((int)$post['kLieferadresse'] > 0) {
        // vorhandene lieferadresse
        $shippingAddress = Shop::Container()->getDB()->select(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->kKunde,
            'kLieferadresse',
            (int)$post['kLieferadresse']
        );
        if (isset($shippingAddress->kLieferadresse) && $shippingAddress->kLieferadresse > 0) {
            $shippingAddress           = new Lieferadresse($shippingAddress->kLieferadresse);
            $_SESSION['Lieferadresse'] = $shippingAddress;
        }
    } elseif ((int)$post['kLieferadresse'] === 0) {
        //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    Tax::setTaxRates();
    //lieferland hat sich geändert und versandart schon gewählt?
    if ($_SESSION['Lieferadresse'] && $_SESSION['Versandart']) {
        $delVersand = (mb_stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false);
        //ist die plz im zuschlagsbereich?
        $plzData = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                        AND tversandzuschlagplz.cPLZBis >= :plz)
                        OR tversandzuschlagplz.cPLZ = :plz)',
            [
                'id'  => (int)$_SESSION['Versandart']->kVersandart,
                'plz' => $_SESSION['Lieferadresse']->cPLZ
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($plzData->kVersandzuschlagPlz) && $plzData->kVersandzuschlagPlz) {
            $delVersand = true;
        }
        if ($delVersand) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        }
        if (!$delVersand) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }

    return $missingData;
}

/**
 * @param array $post
 */
function setzeSessionLieferadresse(array $post): void
{
    $kLieferadresse = isset($post['kLieferadresse']) ? (int)$post['kLieferadresse'] : -1;

    $_SESSION['Bestellung']->kLieferadresse = $kLieferadresse;
    //neue lieferadresse
    if ($kLieferadresse === -1) {
        $_SESSION['Lieferadresse'] = getLieferdaten($post);
    } elseif ($kLieferadresse > 0) {
        // vorhandene lieferadresse
        $address = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . Frontend::getCustomer()->getID() . '
                AND kLieferadresse = ' . (int)$post['kLieferadresse'],
            ReturnType::SINGLE_OBJECT
        );
        if ($address->kLieferadresse > 0) {
            $_SESSION['Lieferadresse'] = new Lieferadresse($address->kLieferadresse);
        }
    } elseif ($kLieferadresse === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    Tax::setTaxRates();
    if ((int)$post['guthabenVerrechnen'] === 1) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            Frontend::getCustomer()->fGuthaben,
            Frontend::getCart()->gibGesamtsummeWaren(true, false)
        );
    } else {
        unset($_SESSION['Bestellung']->GuthabenNutzen, $_SESSION['Bestellung']->fGuthabenGenutzt);
    }
}

/**
 *
 */
function setzeSmartyLieferadresse(): void
{
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->getID() > 0) {
        $shippingAddresses = [];
        $deliveryData      = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->getID(),
            'kLieferadresse'
        );
        foreach ($deliveryData as $item) {
            if ($item->kLieferadresse > 0) {
                $shippingAddresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $customerGroupID = Frontend::getCustomer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $shippingAddresses)
            ->assign('GuthabenLocalized', Frontend::getCustomer()->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', null);
    }
}

/**
 * @param array $missingData
 * @param array $post
 */
function setzeFehlerSmartyLieferadresse($missingData, array $post): void
{
    /** @var array('Kunde' => Kunde) $_SESSION */
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->getID() > 0) {
        $shippingAddresses = [];
        $deliveryData      = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->kKunde,
            'kLieferadresse'
        );
        foreach ($deliveryData as $item) {
            if ($item->kLieferadresse > 0) {
                $shippingAddresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $customerGroupID = Frontend::getCustomer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $shippingAddresses)
            ->assign('GuthabenLocalized', Frontend::getCustomer()->gibGuthabenLocalized());
    }
    setzeFehlendeAngaben($missingData, 'shipping_address');
    Shop::Smarty()->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID, false, true))
        ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse)
        ->assign('kLieferadresse', $post['kLieferadresse']);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', mappeLieferadresseKontaktdaten($post));
    }
}

/**
 * @param array $shippingAddress
 * @return stdClass
 */
function mappeLieferadresseKontaktdaten(array $shippingAddress): stdClass
{
    $form                = new stdClass();
    $form->cAnrede       = $shippingAddress['anrede'];
    $form->cTitel        = $shippingAddress['titel'];
    $form->cVorname      = $shippingAddress['vorname'];
    $form->cNachname     = $shippingAddress['nachname'];
    $form->cFirma        = $shippingAddress['firma'];
    $form->cZusatz       = $shippingAddress['firmazusatz'];
    $form->cStrasse      = $shippingAddress['strasse'];
    $form->cHausnummer   = $shippingAddress['hausnummer'];
    $form->cAdressZusatz = $shippingAddress['adresszusatz'];
    $form->cPLZ          = $shippingAddress['plz'];
    $form->cOrt          = $shippingAddress['ort'];
    $form->cBundesland   = $shippingAddress['bundesland'];
    $form->cLand         = $shippingAddress['land'];
    $form->cMail         = $shippingAddress['email'];
    $form->cTel          = $shippingAddress['tel'];
    $form->cMobil        = $shippingAddress['mobil'];
    $form->cFax          = $shippingAddress['fax'];

    return $form;
}

/**
 *
 */
function setzeSmartyVersandart(): void
{
    gibStepVersand();
}

/**
 *
 */
function setzeFehlerSmartyVersandart(): void
{
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('fillShipping', 'checkout'),
        'fillShipping'
    );
}

/**
 * @param Zahlungsart $paymentMethod
 * @param array       $post
 * @return array
 * @deprecated since 5.0.0
 */
function plausiZahlungsartZusatz($paymentMethod, array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return checkAdditionalPayment($paymentMethod);
}

/**
 * @param array     $post
 * @param int|array $missingData
 */
function setzeSmartyZahlungsartZusatz($post, $missingData = 0): void
{
    $paymentMethod = gibZahlungsart($post['Zahlungsart']);
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $customerAccountData = gibKundenKontodaten(Frontend::getCustomer()->kKunde);
    if (!empty($customerAccountData->kKunde)) {
        Shop::Smarty()->assign('oKundenKontodaten', $customerAccountData);
    }
    if (empty($post['zahlungsartzusatzschritt'])) {
        Shop::Smarty()->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo);
    } else {
        setzeFehlendeAngaben($missingData);
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
    Shop::Smarty()->assign('Zahlungsart', $paymentMethod)
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);
}

/**
 *
 */
function setzeFehlerSmartyZahlungsart()
{
    gibStepZahlung();
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('fillPayment', 'checkout'),
        'fillPayment'
    );
}

/**
 *
 */
function setzeSmartyBestaetigung()
{
    Shop::Smarty()->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguage(),
            Frontend::getCustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren());
}

/**
 * @param array $missingData
 * @param null $context
 */
function setzeFehlendeAngaben($missingData, $context = null)
{
    $all = Shop::Smarty()->getTemplateVars('fehlendeAngaben');
    if (!is_array($all)) {
        $all = [];
    }
    if (empty($context)) {
        $all = array_merge($all, $missingData);
    } else {
        $all[$context] = isset($all[$context])
            ? array_merge($all[$context], $missingData)
            : $missingData;
    }

    Shop::Smarty()->assign('fehlendeAngaben', $all);
}

/**
 * Globale Funktionen
 */
function globaleAssigns()
{
    global $step;
    Shop::Smarty()->assign(
        'AGB',
        Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguageID(),
            Frontend::getCustomerGroup()->getID()
        )
    )
        ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
        ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
        ->assign('Einstellungen', Shopsetting::getInstance()->getAll())
        ->assign('alertNote', Shop::Container()->getAlertService()->alertTypeExists(Alert::TYPE_NOTE))
        ->assign('step', $step)
        ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren())
        ->assign('Steuerpositionen', Frontend::getCart()->gibSteuerpositionen())
        ->assign('bestellschritt', gibBestellschritt($step))
        ->assign('sess', $_SESSION);
}

/**
 * @param int $step
 */
function loescheSession(int $step)
{
    switch ($step) {
        case 0:
            unset(
                $_SESSION['Kunde'],
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 1:
            unset(
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 2:
            unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['oVersandfreiKupon']);
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            unset($_SESSION['Zahlungsart']);
            break;

        case 3:
            unset(
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 4:
            unset($_SESSION['Zahlungsart']);
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                 ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        default:
            break;
    }
}

/**
 * @param int $noteCode
 * @return string
 * @todo: check if this is only used by the old EOS payment method
 */
function mappeBestellvorgangZahlungshinweis(int $noteCode)
{
    $note = '';
    if ($noteCode > 0) {
        switch ($noteCode) {
            // 1-30 EOS
            case 1: // EOS_BACKURL_CODE
                $note = Shop::Lang()->get('eosErrorBack', 'checkout');
                break;

            case 3: // EOS_FAILURL_CODE
                $note = Shop::Lang()->get('eosErrorFailure', 'checkout');
                break;

            case 4: // EOS_ERRORURL_CODE
                $note = Shop::Lang()->get('eosErrorError', 'checkout');
                break;
            default:
                break;
        }
    }

    executeHook(HOOK_BESTELLVORGANG_INC_MAPPEBESTELLVORGANGZAHLUNGSHINWEIS, [
        'cHinweis'     => &$note,
        'nHinweisCode' => $noteCode
    ]);

    return $note;
}

/**
 * @param string $email
 * @param int $customerID
 * @return bool
 */
function isEmailAvailable(string $email, int $customerID = 0): bool
{
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT *
            FROM tkunde
            WHERE cmail = :email
              AND nRegistriert = 1
            AND kKunde != :customerID',
        ['email' => $email, 'customerID' => $customerID],
        ReturnType::SINGLE_OBJECT
    ) === false;
}

/**
 * @param string $datum
 * @return string
 */
function convertDate2German($datum)
{
    if (is_string($datum)) {
        [$tag, $monat, $jahr] = explode('.', $datum);
        if ($tag && $monat && $jahr) {
            return $jahr . '-' . $monat . '-' . $tag;
        }
    }

    return $datum;
}

/**
 * @param string $name
 * @param mixed $obj
 * @deprecated since 4.06
 */
function setzeInSession($name, $obj)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    //an die Session anhängen
    unset($_SESSION[$name]);
    $_SESSION[$name] = $obj;
}

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenA2AE(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $rpl = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenAE2A(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $rpl = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $src = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}
