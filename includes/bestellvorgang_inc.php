<?php

use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\CouponValidator;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Zahlungsart;
use JTL\Customer\Customer;
use JTL\Customer\CustomerAttributes;
use JTL\Customer\CustomerFields;
use JTL\Customer\Registration\Form as RegistrationForm;
use JTL\Customer\Registration\Validator\AbstractValidator;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Order;
use JTL\Helpers\PaymentMethod as Helper;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use JTL\Staat;
use JTL\VerificationVAT\VATCheck;

/**
 * @deprecated since 5.2.0
 */
function pruefeBestellungMoeglich()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php') .
        '?fillOut=' . Frontend::getCart()->istBestellungMoeglich(), true, 303);
    exit;
}

/**
 * @param int  $shippingMethod
 * @param int  $formValues
 * @param bool $bMsg
 * @return bool
 * @deprecated since 5.2.0
 */
function pruefeVersandartWahl($shippingMethod, $formValues = 0, $bMsg = true): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    $nReturnValue = versandartKorrekt($shippingMethod, $formValues);
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI);

    if ($nReturnValue) {
        $step = 'Zahlung';
        Shop::Container()->getAlertService()->removeAlertByKey('fillShipping');

        return true;
    }
    if ($bMsg) {
        Shop::Container()->getAlertService()->addNotice(Shop::Lang()->get('fillShipping', 'checkout'), 'fillShipping');
    }
    $step = 'Versand';

    return false;
}

/**
 * @param array $post
 * @return int
 * @deprecated since 5.2.0
 */
function pruefeUnregistriertBestellen($post): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step, $Kunde, $Lieferadresse;
    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $cart = Frontend::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $Kunde              = getKundendaten($post, 0);
    $customerAttributes = getKundenattribute($post);
    $customerGroupID    = Frontend::getCustomerGroup()->getID();
    $checkBox           = new CheckBox();
    $missingInput       = getMissingInput($post, $customerGroupID, $checkBox);

    $Kunde->getCustomerAttributes()->assign($customerAttributes);
    Frontend::set('customerAttributes', $customerAttributes);
    if (isset($post['shipping_address'])) {
        if ((int)$post['shipping_address'] === 0) {
            $post['kLieferadresse'] = 0;
            $post['lieferdaten']    = 1;
            pruefeLieferdaten($post);
            $_SESSION['preferredDeliveryCountryCode'] = $_SESSION['Lieferadresse']->cLand ?? $post['land'];
            Tax::setTaxRates();
        } elseif (isset($post['kLieferadresse']) && (int)$post['kLieferadresse'] > 0) {
            pruefeLieferdaten($post);
            $_SESSION['preferredDeliveryCountryCode'] = $_SESSION['Lieferadresse']->cLand;
            Tax::setTaxRates();
        } elseif (isset($post['register']['shipping_address'])) {
            checkNewShippingAddress($post, $missingInput);
        }
    } elseif (isset($post['lieferdaten']) && (int)$post['lieferdaten'] === 1) {
        // compatibility with older template
        pruefeLieferdaten($post, $missingInput);
    }
    $nReturnValue = Form::hasNoMissingData($missingInput);

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
                Lieferadresse::createFromShippingAddress();
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
        $Lieferadresse                          = Lieferadresse::createFromPost($post['register']['shipping_address']);
        $_SESSION['Lieferadresse']              = $Lieferadresse;
    }

    setzeFehlendeAngaben($missingInput);
    Shop::Smarty()->assign('customerAttributes', $customerAttributes)
        ->assign('cPost_var', Text::filterXSS($post));

    return 0;
}

/**
 * Gibt mögliche fehlende Felder aus Formulareingaben zurück.
 *
 * @param array         $post
 * @param int|null      $customerGroupId
 * @param CheckBox|null $checkBox
 * @return array
 * @deprecated since 5.2.0
 */
function getMissingInput(array $post, ?int $customerGroupId = null, ?CheckBox $checkBox = null): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $missingInput    = checkKundenFormular(0);
    $customerGroupId = $customerGroupId ?? Frontend::getCustomerGroup()->getID();
    $checkBox        = $checkBox ?? new CheckBox();

    return array_merge($missingInput, $checkBox->validateCheckBox(
        CHECKBOX_ORT_REGISTRIERUNG,
        $customerGroupId,
        $post,
        true
    ));
}

/**
 * Prüft, ob eine neue Lieferadresse gültig ist.
 *
 * @param array      $post
 * @param array|null $missingInput
 * @deprecated since 5.2.0
 */
function checkNewShippingAddress(array $post, ?array $missingInput = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $missingInput = $missingInput ?? getMissingInput($post);
    pruefeLieferdaten($post['register']['shipping_address'], $missingInput);
}

/**
 * @param array      $post
 * @param array|null $missingData
 * @deprecated since 5.2.0
 */
function pruefeLieferdaten($post, &$missingData = null): void
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Customer\Registration\Form::pruefeLieferdaten() instead.',
        E_USER_DEPRECATED
    );
    (new RegistrationForm())->pruefeLieferdaten($post, $missingData);
}

/**
 * @param array $post
 * @deprecated since 5.2.0
 */
function plausiGuthaben($post): void
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Order::checkBalance() instead.',
        E_USER_DEPRECATED
    );
    Order::checkBalance($post);
}

/**
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeVersandkostenStep(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'])) {
        $cart = Frontend::getCart();
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        $dependent = ShippingMethod::gibArtikelabhaengigeVersandkostenImWK(
            $_SESSION['Lieferadresse']->cLand,
            $cart->PositionenArr
        );
        foreach ($dependent as $item) {
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

    return $step;
}

/**
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeZahlungStep(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'])) {
        $step = 'Zahlung';
    }

    return $step;
}

/**
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeBestaetigungStep(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])) {
        $step = 'Bestaetigung';
    }
    if (isset($_SESSION['Zahlungsart'], $_SESSION['Zahlungsart']->cZusatzschrittTemplate)
        && mb_strlen($_SESSION['Zahlungsart']->cZusatzschrittTemplate) > 0
    ) {
        $paymentMethod = LegacyMethod::create($_SESSION['Zahlungsart']->cModulId);
        if ($paymentMethod !== null && is_object($paymentMethod) && !$paymentMethod->validateAdditional()) {
            $step = 'Zahlung';
        }
    }

    return $step;
}

/**
 * @param array $get
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeRechnungsadresseStep(array $get): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step, $Kunde;
    //sondersteps Rechnungsadresse ändern
    if (!empty(Frontend::getCustomer()->cOrt)
        && (Request::getInt('editRechnungsadresse') === 1 || Request::getInt('editLieferadresse') === 1)
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
            $Kunde              = getKundendaten($_SESSION['checkout.cPost_arr'], 0, 0);
            $customerAttributes = getKundenattribute($_SESSION['checkout.cPost_arr']);
            $Kunde->getCustomerAttributes()->assign($customerAttributes);
            Frontend::set('customerAttributes', $customerAttributes);
            Shop::Smarty()->assign('Kunde', $Kunde)
                ->assign('cPost_var', Text::filterXSS($_SESSION['checkout.cPost_arr']));

            if (isset($_SESSION['Lieferadresse']) && (int)$_SESSION['checkout.cPost_arr']['shipping_address'] !== 0) {
                Shop::Smarty()->assign('Lieferadresse', $_SESSION['Lieferadresse']);
            }

            $_POST = Text::filterXSS(array_merge($_POST, $_SESSION['checkout.cPost_arr']));
            unset($_SESSION['checkout.cPost_arr']);
        }
        unset($_SESSION['checkout.register']);
    }
    if (pruefeFehlendeAngaben()) {
        $step = isset($_SESSION['Kunde']) ? 'edit_customer_address' : 'accountwahl';
    }

    return $step;
}

/**
 * @param array       $get
 * @param string|null $currentStep
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeLieferadresseStep(array $get, ?string $currentStep = null): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if ($currentStep === null) {
        global $step;
    } else {
        $step = $currentStep;
    }
    global $Lieferadresse;
    //sondersteps Lieferadresse ändern
    if (!empty($_SESSION['Lieferadresse'])) {
        $Lieferadresse = $_SESSION['Lieferadresse'];
        if ((isset($get['editLieferadresse']) && (int)$get['editLieferadresse'] === 1)
            || (isset($_SESSION['preferredDeliveryCountryCode'])
                && $_SESSION['preferredDeliveryCountryCode'] !== $Lieferadresse->cLand)
        ) {
            Kupon::resetNewCustomerCoupon();
            unset($_SESSION['Zahlungsart'], $_SESSION['Versandart']);
            $step = 'Lieferadresse';
        }
    }
    if (pruefeFehlendeAngaben('shippingAddress')) {
        $step = isset($_SESSION['Kunde']) ? 'Lieferadresse' : 'accountwahl';
    }

    return $step;
}

/**
 * Prüft ob im WK ein Versandfrei Kupon eingegeben wurde und falls ja,
 * wird dieser nach Eingabe der Lieferadresse gesetzt (falls Kriterien erfüllt)
 *
 * @return array
 * @deprecated since 5.2.0
 */
function pruefeVersandkostenfreiKuponVorgemerkt(): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Cart\CartHelper::applyShippingFreeCoupon() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::applyShippingFreeCoupon();
}

/**
 * @param array $get
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeVersandartStep(array $get): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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

    return $step;
}

/**
 * @param array $get
 * @return string
 * @deprecated since 5.2.0
 */
function pruefeZahlungsartStep(array $get): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
        $step = pruefeVersandartStep(['editVersandart' => 1], $step);
    }

    if (isset($get['nHinweis']) && (int)$get['nHinweis'] > 0) {
        Shop::Container()->getAlertService()->addNotice(
            mappeBestellvorgangZahlungshinweis((int)$get['nHinweis']),
            'paymentNote'
        );
    }

    return $step;
}

/**
 * @param array $post
 * @return int|null
 * @deprecated since 5.2.0
 */
function pruefeZahlungsartwahlStep(array $post): ?int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step, $zahlungsangaben;
    if (!isset($post['zahlungsartwahl']) || (int)$post['zahlungsartwahl'] !== 1) {
        if (isset($_SESSION['Zahlungsart'])
            && Request::getInt('editRechnungsadresse') !== 1
            && Request::getInt('editLieferadresse') !== 1
        ) {
            $zahlungsangaben = zahlungsartKorrekt((int)$_SESSION['Zahlungsart']->kZahlungsart);
        } else {
            return null;
        }
    } else {
        $zahlungsangaben = zahlungsartKorrekt((int)$post['Zahlungsart']);
    }
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG_PLAUSI);

    switch ($zahlungsangaben) {
        case 0:
            Shop::Container()->getAlertService()->addNotice(
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
 * @deprecated since 5.2.0
 */
function pruefeGuthabenNutzen(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use JTL\Helpers\Order::setUsedBalance() instead.', E_USER_DEPRECATED);
    Order::setUsedBalance();
}

/**
 * @param string|null $context
 * @return bool
 * @deprecated since 5.2.0
 */
function pruefeFehlendeAngaben($context = null): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $missingData = Shop::Smarty()->getTemplateVars('fehlendeAngaben');
    if (!$context) {
        return !empty($missingData);
    }

    return (isset($missingData[$context])
        && is_array($missingData[$context])
        && count($missingData[$context]));
}

/**
 * @deprecated since 5.2.0
 */
function gibStepAccountwahl(?JTLSmarty $smarty = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    // Einstellung global_kundenkonto_aktiv ist auf 'A'
    // und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
    if (isset($_REQUEST['reg'])
        && (int)$_REQUEST['reg'] === 1
        && Shop::getSettingValue(CONF_GLOBAL, 'global_kundenkonto_aktiv') === 'A'
        && empty(Shop::Smarty()->getTemplateVars('fehlendeAngaben'))
    ) {
        Shop::Container()->getAlertService()->addNotice(
            Shop::Lang()->get('accountCreated') . '. ' . Shop::Lang()->get('activateAccountDesc'),
            'accountCreatedLoginNotActivated'
        );
        Shop::Container()->getAlertService()->addNotice(
            Shop::Lang()->get('continueAfterActivation', 'messages'),
            'continueAfterActivation'
        );
    }
    $smarty = $smarty ?? Shop::Smarty();
    $smarty->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Frontend::getCart()))
        ->assign('one_step_wk', Request::verifyGPCDataInt('wk'));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
}

/**
 * @deprecated since 5.2.0
 */
function gibStepUnregistriertBestellen(?JTLSmarty $smarty = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $Kunde;
    $origins         = Shop::Container()->getDB()->getObjects(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort'
    );
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if ($Kunde !== null) {
        $customerAttributes = $Kunde->getCustomerAttributes();

        if ($Kunde->getID() === 0) {
            $customerAttributes->assign(Frontend::get('customerAttributes') ?? new CustomerAttributes());
        }
    } else {
        $customerAttributes = getKundenattribute($_POST);
    }
    $smarty = $smarty ?? Shop::Smarty();
    $smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $origins)
        ->assign('Kunde', $Kunde ?? null)
        ->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID, false, true))
        ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
        ->assign('code_registrieren', false)
        ->assign('customerAttributes', $customerAttributes);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPUNREGISTRIERTBESTELLEN);
}

/**
 * @deprecated since 5.2.0
 */
function validateCouponInCheckout()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (!isset($_SESSION['Kupon'])) {
        return;
    }
    $checkCouponResult = Kupon::checkCoupon($_SESSION['Kupon']);
    if (count($checkCouponResult) !== 0) {
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        $_SESSION['checkCouponResult'] = $checkCouponResult;
        unset($_SESSION['Kupon']);
        header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true));
        exit(0);
    }
}

/**
 * @return mixed
 * @deprecated since 5.2.0
 */
function gibStepLieferadresse()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $Lieferadresse;

    $smarty          = Shop::Smarty();
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->kKunde > 0) {
        $addresses = [];
        $data      = Shop::Container()->getDB()->getObjects(
            'SELECT DISTINCT(kLieferadresse)
                FROM tlieferadresse
                WHERE kKunde = :cid',
            ['cid' => Frontend::getCustomer()->getID()]
        );
        foreach ($data as $item) {
            if ($item->kLieferadresse > 0) {
                $addresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $smarty->assign('Lieferadressen', $addresses);
        $customerGroupID = Frontend::getCustomer()->getGroupID();
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
 * @deprecated since 5.2.0
 */
function gibStepZahlung()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    $cart       = Frontend::getCart();
    $smarty     = Shop::Smarty();
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = Frontend::getCustomer()->cLand;
    }
    $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$poCode) {
        $poCode = Frontend::getCustomer()->cPLZ;
    }
    $customerGroupID = Frontend::getCustomer()->getGroupID();
    if (!$customerGroupID) {
        $customerGroupID = Frontend::getCustomerGroup()->getID();
    }
    $shippingMethods = ShippingMethod::getPossibleShippingMethods(
        $lieferland,
        $poCode,
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
                $shippingMethod . ', Kundengruppe: ' . $customerGroupID
            );
            $paymentMethod  = null;
            $paymentMethods = [];
        } else {
            $paymentMethod = gibAktiveZahlungsart($paymentMethods);
        }

        $packaging = gibAktiveVerpackung($packagings);
        if (!isset($_SESSION['Versandart']) && !empty($shippingMethod)) {
            // dieser Workaround verhindert die Anzeige der Standardzahlungsarten wenn ein Zahlungsplugin aktiv ist
            $_SESSION['Versandart'] = (object)[
                'kVersandart' => $shippingMethod,
            ];
        }
        $selectablePayments = array_filter(
            $paymentMethods,
            static function ($method) {
                $paymentMethod = LegacyMethod::create($method->cModulId);
                if ($paymentMethod !== null) {
                    return $paymentMethod->isSelectable();
                }

                return true;
            }
        );
        $smarty->assign('Zahlungsarten', $selectablePayments)
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
 * @deprecated since 5.2.0
 */
function gibStepZahlungZusatzschritt(array $post): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $paymentID     = $post['Zahlungsart'] ?? $_SESSION['Zahlungsart']->kZahlungsart;
    $paymentMethod = gibZahlungsart((int)$paymentID);
    $smarty        = Shop::Smarty();
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $customerAccountData = gibKundenKontodaten(Frontend::getCustomer()->getID());
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
 * @deprecated since 5.2.0
 */
function gibStepBestaetigung($get)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $linkHelper = Shop::Container()->getLinkService();
    //check currenct shipping method again to avoid using invalid methods when using one click method (#9566)
    if (isset($_SESSION['Versandart']->kVersandart) && !versandartKorrekt((int)$_SESSION['Versandart']->kVersandart)) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editVersandart=1', true, 303);
    }
    // Bei Standardzahlungsarten mit Zahlungsinformationen prüfen ob Daten vorhanden sind
    if (isset($_SESSION['Zahlungsart'])
        && $_SESSION['Zahlungsart']->cModulId === 'za_lastschrift_jtl'
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
    if (Frontend::getCustomer()->getID() > 0) {
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
 * @deprecated since 5.2.0
 */
function gibStepVersand(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    global $step;
    CartHelper::applyShippingFreeCoupon();
    $cart            = Frontend::getCart();
    $deliveryCountry = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$deliveryCountry) {
        $deliveryCountry = Frontend::getCustomer()->cLand;
    }
    $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$poCode) {
        $poCode = Frontend::getCustomer()->cPLZ;
    }
    $customerGroupID = Frontend::getCustomer()->getGroupID();
    if (!$customerGroupID) {
        $customerGroupID = Frontend::getCustomerGroup()->getID();
    }
    $shippingMethods = ShippingMethod::getPossibleShippingMethods(
        $deliveryCountry,
        $poCode,
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
            'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: {cntry},'
            . 'PLZ: {plz}, Versandklasse: {sclass}, Kundengruppe: {cgroup}',
            [
                'cntry'  => $deliveryCountry,
                'plz'    => $poCode,
                'sclass' => ShippingMethod::getShippingClasses(Frontend::getCart()),
                'cgroup' => $customerGroupID
            ]
        );
    }
    Shop::Smarty()->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND);
}

/**
 * @param array $post
 * @return array|int
 * @deprecated since 5.2.0
 */
function plausiKupon(array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CouponValidator::validateCoupon() instead.', E_USER_DEPRECATED);
    return CouponValidator::validateCoupon($post, Frontend::getCustomer());
}

/**
 * @deprecated since 5.2.0
 */
function plausiNeukundenKupon()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use CouponValidator::validateNewCustomerCoupon() instead.',
        E_USER_DEPRECATED
    );
    return CouponValidator::validateNewCustomerCoupon(Frontend::getCustomer());
}

/**
 * @param Zahlungsart|object $paymentMethod
 * @return array
 * @deprecated since 5.2.0
 */
function checkAdditionalPayment($paymentMethod): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    foreach (['iban', 'bic'] as $dataKey) {
        if (!empty($_POST[$dataKey])) {
            $_POST[$dataKey] = mb_convert_case($_POST[$dataKey], MB_CASE_UPPER);
        }
    }

    $post   = Text::filterXSS($_POST);
    $errors = [];
    switch ($paymentMethod->cModulId) {
        case 'za_lastschrift_jtl':
            $conf = Shop::getSettingSection(CONF_ZAHLUNGSARTEN);
            if (empty($post['bankname']) && $conf['zahlungsart_lastschrift_kreditinstitut_abfrage'] === 'Y') {
                $errors['bankname'] = 1;
            }
            if (empty($post['inhaber']) && $conf['zahlungsart_lastschrift_kontoinhaber_abfrage'] === 'Y') {
                $errors['inhaber'] = 1;
            }
            if (empty($post['bic'])) {
                if ($conf['zahlungsart_lastschrift_bic_abfrage'] === 'Y') {
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
        default:
            break;
    }

    return $errors;
}

/**
 * @param string $bic
 * @return bool
 * @deprecated since 5.2.0
 */
function checkBIC($bic): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Text::checkBIC() instead.',
        E_USER_DEPRECATED
    );
    return Text::checkBIC($bic);
}

/**
 * @param string $iban
 * @return bool|mixed
 * @deprecated since 5.2.0
 */
function plausiIban($iban)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Text::checkIBAN() instead.',
        E_USER_DEPRECATED
    );
    return Text::checkIBAN($iban);
}

/**
 * @return stdClass
 * @deprecated since 5.2.0
 */
function gibPostZahlungsInfo(): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $info = new stdClass();

    $info->cKartenNr    = null;
    $info->cGueltigkeit = null;
    $info->cCVV         = null;
    $info->cKartenTyp   = null;
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
 * @deprecated since 5.2.0
 */
function zahlungsartKorrekt(int $paymentMethodID): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $cart   = Frontend::getCart();
    $zaInfo = $_SESSION['Zahlungsart']->ZahlungsInfo ?? null;
    unset($_SESSION['Zahlungsart']);
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    if ($paymentMethodID > 0
        && isset($_SESSION['Versandart']->kVersandart)
        && (int)$_SESSION['Versandart']->kVersandart > 0
    ) {
        $paymentMethod = Shop::Container()->getDB()->getSingleObject(
            'SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = :session_kversandart
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tversandartzahlungsart.kZahlungsart = :kzahlungsart',
            [
                'session_kversandart' => (int)$_SESSION['Versandart']->kVersandart,
                'kzahlungsart'        => $paymentMethodID
            ]
        );
        if ($paymentMethod === null) {
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
        $specialItem        = new stdClass();
        $specialItem->cName = [];
        foreach ($_SESSION['Sprachen'] as $lang) {
            if ($paymentMethod->kZahlungsart > 0) {
                $localized = Shop::Container()->getDB()->select(
                    'tzahlungsartsprache',
                    'kZahlungsart',
                    (int)$paymentMethod->kZahlungsart,
                    'cISOSprache',
                    $lang->cISO,
                    null,
                    null,
                    false,
                    'cName'
                );
                if (isset($localized->cName)) {
                    $specialItem->cName[$lang->cISO] = $localized->cName;
                }
            }
        }
        $paymentMethod->angezeigterName = $specialItem->cName;
        $_SESSION['Zahlungsart']        = $paymentMethod;
        $_SESSION['AktiveZahlungsart']  = $paymentMethod->kZahlungsart;
        if ($paymentMethod->cZusatzschrittTemplate) {
            $info                 = new stdClass();
            $additionalInfoExists = false;
            switch ($paymentMethod->cModulId) {
                case 'za_null_jtl':
                    // the null-paymentMethod did not has any additional-steps
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
                    } elseif ($zaInfo !== null && (isset($zaInfo->cKontoNr) || isset($zaInfo->cIBAN))) {
                        $info                 = $zaInfo;
                        $additionalInfoExists = true;
                    }
                    break;
                default:
                    // Plugin-Zusatzschritt
                    $additionalInfoExists = true;
                    $paymentMethod        = LegacyMethod::create($paymentMethod->cModulId);
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
 * @param object $paymentMethod
 * @deprecated since 5.2.0
 */
function getPaymentSurchageDiscount($paymentMethod)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (!isset($paymentMethod->fAufpreis) || $paymentMethod->fAufpreis == 0) {
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
    $specialItem               = new stdClass();
    $specialItem->cGebuehrname = [];
    foreach ($_SESSION['Sprachen'] as $lang) {
        if ($paymentMethod->kZahlungsart > 0) {
            $loc = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$paymentMethod->kZahlungsart,
                'cISOSprache',
                $lang->cISO,
                null,
                null,
                false,
                'cGebuehrname'
            );

            $specialItem->cGebuehrname[$lang->cISO] = $loc->cGebuehrname ?? '';
            if ($paymentMethod->cAufpreisTyp === 'prozent') {
                if ($paymentMethod->fAufpreis > 0) {
                    $specialItem->cGebuehrname[$lang->cISO] .= ' +';
                }
                $specialItem->cGebuehrname[$lang->cISO] .= $paymentMethod->fAufpreis . '%';
            }
        }
    }
    if ($paymentMethod->cModulId === 'za_nachnahme_jtl') {
        $cart->erstelleSpezialPos(
            $specialItem->cGebuehrname,
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
            $specialItem->cGebuehrname,
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
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @deprecated since 5.2.0
 */
function gibZahlungsart(int $paymentMethodID)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $method = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    foreach (Frontend::getLanguages() as $language) {
        $localized                                     = Shop::Container()->getDB()->select(
            'tzahlungsartsprache',
            'kZahlungsart',
            $paymentMethodID,
            'cISOSprache',
            $language->getCode(),
            null,
            null,
            false,
            'cName'
        );
        $method->angezeigterName[$language->getCode()] = $localized->cName ?? null;
    }
    $confData = Shop::Container()->getDB()->getObjects(
        'SELECT *
            FROM teinstellungen
            WHERE kEinstellungenSektion = :sec
                AND cModulId = :mod',
        ['mod' => $method->cModulId, 'sec' => CONF_ZAHLUNGSARTEN]
    );
    foreach ($confData as $conf) {
        $method->einstellungen[$conf->cName] = $conf->cWert;
    }
    $plugin = gibPluginZahlungsart($method->cModulId);
    if ($plugin) {
        $paymentMethod                  = $plugin->getPaymentMethods()->getMethodByID($method->cModulId);
        $method->cZusatzschrittTemplate = $paymentMethod !== null ? $paymentMethod->getAdditionalTemplate() : '';
    }

    return $method;
}

/**
 * @param null|int $customerID
 * @return object|bool
 * @deprecated since 5.2.0
 */
function gibKundenKontodaten(?int $customerID)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @deprecated since 5.2.0
 */
function gibZahlungsarten(int $shippingMethodID, int $customerGroupID)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $taxRate = 0.0;
    $methods = [];
    if ($shippingMethodID > 0) {
        $methods = Shop::Container()->getDB()->getObjects(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = :sid
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen = ''
                    OR FIND_IN_SET(:cgid, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                    AND tzahlungsart.nActive = 1
                    AND tzahlungsart.nNutzbar = 1
                ORDER BY tzahlungsart.nSort",
            ['sid' => $shippingMethodID, 'cgid' => $customerGroupID]
        );
    }
    $valid    = [];
    $currency = Frontend::getCurrency();
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
        foreach ($_SESSION['Sprachen'] as $lang) {
            $loc = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart,
                'cISOSprache',
                $lang->cISO,
                null,
                null,
                false,
                'cName, cGebuehrname, cHinweisTextShop'
            );
            if (isset($loc->cName)) {
                $method->angezeigterName[$lang->cISO] = $loc->cName;
                $method->cGebuehrname[$lang->cISO]    = $loc->cGebuehrname;
                $method->cHinweisText[$lang->cISO]    = $loc->cHinweisTextShop;
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
        $method->cPreisLocalized = Preise::getLocalizedPriceString($method->fAufpreis, $currency);
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
 * @deprecated since 5.2.0
 */
function gibAktiveVersandart($shippingMethods)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (isset($_SESSION['Versandart'])) {
        $_SESSION['AktiveVersandart'] = (int)$_SESSION['Versandart']->kVersandart;
    } elseif (!empty($_SESSION['AktiveVersandart']) && GeneralObject::hasCount($shippingMethods)) {
        $active = (int)$_SESSION['AktiveVersandart'];
        if (array_reduce($shippingMethods, static function ($carry, $item) use ($active) {
                return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
        }, 0) !== (int)$_SESSION['AktiveVersandart']) {
            $_SESSION['AktiveVersandart'] = ShippingMethod::getFirstShippingMethod(
                $shippingMethods,
                (int)($_SESSION['Zahlungsart']->kZahlungsart ?? 0)
            )->kVersandart ?? 0;
        }
    } else {
        $_SESSION['AktiveVersandart'] = ShippingMethod::getFirstShippingMethod(
            $shippingMethods,
            $_SESSION['Zahlungsart']->kZahlungsart ?? 0
        )->kVersandart ?? 0;
    }

    return (int)$_SESSION['AktiveVersandart'];
}

/**
 * @param object[] $shippingMethods
 * @return int
 * @deprecated since 5.2.0
 */
function gibAktiveZahlungsart($shippingMethods)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (isset($_SESSION['Zahlungsart'])) {
        $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
    } elseif (!empty($_SESSION['AktiveZahlungsart']) && GeneralObject::hasCount($shippingMethods)) {
        $active = (int)$_SESSION['AktiveZahlungsart'];
        if (array_reduce($shippingMethods, static function ($carry, $item) use ($active) {
                return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
        }, 0) !== (int)$_SESSION['AktiveZahlungsart']) {
            $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
        }
    } else {
        $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
    }

    return (int)$_SESSION['AktiveZahlungsart'];
}

/**
 * @param object[] $packagings
 * @return array
 * @deprecated since 5.2.0
 */
function gibAktiveVerpackung(array $packagings): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (isset($_SESSION['Verpackung']) && count($_SESSION['Verpackung']) > 0) {
        $_SESSION['AktiveVerpackung'] = [];
        foreach ($_SESSION['Verpackung'] as $packaging) {
            $_SESSION['AktiveVerpackung'][(int)$packaging->kVerpackung] = 1;
        }
    } elseif (!empty($_SESSION['AktiveVerpackung']) && count($packagings) > 0) {
        foreach (array_keys($_SESSION['AktiveVerpackung']) as $active) {
            if (array_reduce($packagings, static function ($carry, $item) use ($active) {
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
 * @deprecated since 5.2.0
 */
function zahlungsartGueltig($paymentMethod): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (!isset($paymentMethod->cModulId)) {
        return false;
    }
    $moduleID = $paymentMethod->cModulId;
    $pluginID = PluginHelper::getIDByModuleID($moduleID);
    if ($pluginID > 0) {
        $loader = PluginHelper::getLoaderByPluginID($pluginID);
        try {
            $plugin = $loader->init($pluginID);
        } catch (InvalidArgumentException $e) {
            return false;
        }
        if ($plugin === null || $plugin->getState() !== State::ACTIVATED) {
            return false;
        }
        if (!PluginHelper::licenseCheck($plugin, ['cModulId' => $moduleID])) {
            return false;
        }
        global $oPlugin;
        $oPlugin = $plugin;
    }

    $method = LegacyMethod::create($moduleID);
    if ($method !== null) {
        if (!$method->isValid(Frontend::getCustomer(), Frontend::getCart())) {
            Shop::Container()->getLogService()->withName('cModulId')->debug(
                'Die Zahlungsartprüfung (' . $moduleID . ') wurde nicht erfolgreich validiert (isValidIntern).',
                [$moduleID]
            );

            return false;
        }

        return true;
    }

    return Helper::shippingMethodWithValidPaymentMethod($paymentMethod);
}

/**
 * @param int $minOrders
 * @return bool
 * @deprecated since 5.2.0
 */
function pruefeZahlungsartMinBestellungen(int $minOrders): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use  JTL\Helpers\PaymentMethod::checkMinOrders() instead.',
        E_USER_DEPRECATED
    );
    return Helper::checkMinOrders($minOrders, Frontend::getCustomer()->getID());
}

/**
 * @param float|string $minOrderValue
 * @return bool
 * @deprecated since 5.2.0
 */
function pruefeZahlungsartMinBestellwert($minOrderValue): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use  JTL\Helpers\PaymentMethod::checkMinOrderValue() instead.',
        E_USER_DEPRECATED
    );
    return Helper::checkMinOrderValue($minOrderValue);
}

/**
 * @param float|string $maxOrderValue
 * @return bool
 * @deprecated since 5.2.0
 */
function pruefeZahlungsartMaxBestellwert($maxOrderValue): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use  JTL\Helpers\PaymentMethod::checkMaxOrderValue() instead.',
        E_USER_DEPRECATED
    );
    return Helper::checkMaxOrderValue($maxOrderValue);
}

/**
 * @param int       $shippingMethodID
 * @param int|array $formValues
 * @return bool
 * @deprecated since 5.2.0
 */
function versandartKorrekt(int $shippingMethodID, $formValues = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $cart                   = Frontend::getCart();
    $packagingIDs           = GeneralObject::hasCount('kVerpackung', $_POST)
        ? $_POST['kVerpackung']
        : ($formValues['kVerpackung'] ?? []);
    $cartTotal              = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
    $_SESSION['Verpackung'] = [];
    $db                     = Shop::Container()->getDB();
    if (GeneralObject::hasCount($packagingIDs)) {
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        foreach ($packagingIDs as $packagingID) {
            $packagingID = (int)$packagingID;
            $packagings  = $db->getSingleObject(
                "SELECT *
                    FROM tverpackung
                    WHERE kVerpackung = :pid
                        AND (tverpackung.cKundengruppe = '-1'
                            OR FIND_IN_SET(:cgid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                        AND :sum >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1",
                [
                    'pid'  => $packagingID,
                    'cgid' => Frontend::getCustomerGroup()->getID(),
                    'sum'  => $cartTotal
                ]
            );
            if ($packagings === null) {
                return false;
            }
            $packagings->kVerpackung = (int)$packagings->kVerpackung;

            $localizedNames     = [];
            $localizedPackaging = $db->selectAll(
                'tverpackungsprache',
                'kVerpackung',
                (int)$packagings->kVerpackung
            );
            foreach ($localizedPackaging as $item) {
                $localizedNames[$item->cISOSprache] = $item->cName;
            }
            $fBrutto = $packagings->fBrutto;
            if ($cartTotal >= $packagings->fKostenfrei
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
                (int)$packagings->kSteuerklasse,
                C_WARENKORBPOS_TYP_VERPACKUNG,
                false
            );
            unset($packagings);
        }
    } elseif (Request::postInt('zahlungsartwahl') > 0) {
        $_SESSION['AktiveVerpackung'] = [];
    }
    unset($_SESSION['Versandart']);
    if ($shippingMethodID <= 0) {
        return false;
    }
    $deliveryCountry = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$deliveryCountry) {
        $deliveryCountry = Frontend::getCustomer()->cLand;
    }
    $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$poCode) {
        $poCode = Frontend::getCustomer()->cPLZ;
    }
    $shippingClasses = ShippingMethod::getShippingClasses(Frontend::getCart());
    $depending       = 'N';
    if (ShippingMethod::normalerArtikelversand($deliveryCountry) === false) {
        $depending = 'Y';
    }
    $countryCode    = $deliveryCountry;
    $shippingMethod = $db->getSingleObject(
        "SELECT *
            FROM tversandart
            WHERE cLaender LIKE :iso
                AND cNurAbhaengigeVersandart = :dep
                AND (cVersandklassen = '-1' OR cVersandklassen RLIKE :scl)
                AND kVersandart = :sid",
        [
            'iso' => '%' . $countryCode . '%',
            'dep' => $depending,
            'scl' => '^([0-9 -]* )?' . $shippingClasses . ' ',
            'sid' => $shippingMethodID
        ]
    );

    if ($shippingMethod === null || $shippingMethod->kVersandart <= 0) {
        return false;
    }
    $shippingMethod->kVersandart        = (int)$shippingMethod->kVersandart;
    $shippingMethod->kVersandberechnung = (int)$shippingMethod->kVersandberechnung;
    $shippingMethod->nSort              = (int)$shippingMethod->nSort;
    $shippingMethod->nMinLiefertage     = (int)$shippingMethod->nMinLiefertage;
    $shippingMethod->nMaxLiefertage     = (int)$shippingMethod->nMaxLiefertage;
    $shippingMethod->Zuschlag           = ShippingMethod::getAdditionalFees($shippingMethod, $countryCode, $poCode);
    $shippingMethod->fEndpreis          = ShippingMethod::calculateShippingFees($shippingMethod, $countryCode, null);
    if ($shippingMethod->fEndpreis == -1) {
        return false;
    }
    $specialItem        = new stdClass();
    $specialItem->cName = [];
    foreach ($_SESSION['Sprachen'] as $lang) {
        $loc = $db->select(
            'tversandartsprache',
            'kVersandart',
            (int)$shippingMethod->kVersandart,
            'cISOSprache',
            $lang->cISO,
            null,
            null,
            false,
            'cName, cHinweisTextShop'
        );
        if (isset($loc->cName)) {
            $specialItem->cName[$lang->cISO]                     = $loc->cName;
            $shippingMethod->angezeigterName[$lang->cISO]        = $loc->cName;
            $shippingMethod->angezeigterHinweistext[$lang->cISO] = $loc->cHinweisTextShop;
        }
    }
    $taxItem = $shippingMethod->eSteuer !== 'netto';
    // Ticket #1298 Inselzuschläge müssen bei Versandkostenfrei berücksichtigt werden
    $shippingCosts = $shippingMethod->fEndpreis;
    if (isset($shippingMethod->Zuschlag->fZuschlag)) {
        $shippingCosts = $shippingMethod->fEndpreis - $shippingMethod->Zuschlag->fZuschlag;
    }
    if ($shippingMethod->fEndpreis == 0
        && isset($shippingMethod->Zuschlag->fZuschlag)
        && $shippingMethod->Zuschlag->fZuschlag > 0
    ) {
        $shippingCosts = $shippingMethod->fEndpreis;
    }
    $cart->erstelleSpezialPos(
        $specialItem->cName,
        1,
        $shippingCosts,
        $cart->gibVersandkostenSteuerklasse($countryCode),
        C_WARENKORBPOS_TYP_VERSANDPOS,
        true,
        $taxItem
    );
    CartHelper::applyShippingFreeCoupon();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
    if (isset($shippingMethod->Zuschlag->fZuschlag) && $shippingMethod->Zuschlag->fZuschlag != 0) {
        $specialItem->cName = [];
        foreach (Frontend::getLanguages() as $lang) {
            $loc                             = $db->select(
                'tversandzuschlagsprache',
                'kVersandzuschlag',
                (int)$shippingMethod->Zuschlag->kVersandzuschlag,
                'cISOSprache',
                $lang->cISO,
                null,
                null,
                false,
                'cName'
            );
            $specialItem->cName[$lang->cISO] = $loc->cName;
        }
        $cart->erstelleSpezialPos(
            $specialItem->cName,
            1,
            $shippingMethod->Zuschlag->fZuschlag,
            $cart->gibVersandkostenSteuerklasse($countryCode),
            C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
            true,
            $taxItem
        );
    }
    $_SESSION['Versandart']       = $shippingMethod;
    $_SESSION['AktiveVersandart'] = $shippingMethod->kVersandart;

    return true;
}

/**
 * @param array $missingData
 * @return int
 * @deprecated since 5.2.0
 */
function angabenKorrekt(array $missingData): int
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use JTL\Helpers\Form::hasNoMissingData() instead.', E_USER_DEPRECATED);
    return Form::hasNoMissingData($missingData);
}

/**
 * @param array $data
 * @param int   $kundenaccount
 * @param int   $checkpass
 * @return array
 * @deprecated since 5.2.0
 */
function checkKundenFormularArray($data, int $kundenaccount, $checkpass = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (new RegistrationForm())->checkKundenFormularArray($data, (bool)$kundenaccount, (bool)$checkpass);
}

/**
 * @param int $customerAccount
 * @param int $checkpass
 * @return array
 * @deprecated since 5.2.0
 */
function checkKundenFormular(int $customerAccount, $checkpass = 1): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $data = Text::filterXSS($_POST); // create a copy

    return checkKundenFormularArray($data, $customerAccount, $checkpass);
}

/**
 * @param array $data
 * @return array
 * @deprecated since 5.2.0
 */
function checkLieferFormularArray($data): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Customer\Registration\Form::checkLieferFormularArray() instead.',
        E_USER_DEPRECATED
    );
    return (new RegistrationForm())->checkLieferFormularArray($data);
}

/**
 * liefert Gesamtsumme der Artikel im Warenkorb, welche dem Kupon zugeordnet werden können
 *
 * @param Kupon|object $coupon
 * @param array        $cartItems
 * @return float
 * @deprecated since 5.2.0
 */
function gibGesamtsummeKuponartikelImWarenkorb($coupon, array $cartItems)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use CartHelper::getCouponProductsTotal() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::getCouponProductsTotal($coupon, $cartItems);
}

/**
 * @param Kupon|object $coupon
 * @param array        $items
 * @return bool
 * @deprecated since 5.2.0
 */
function warenkorbKuponFaehigArtikel($coupon, array $items): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use CartHelper::cartHasCouponValidProducts() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::cartHasCouponValidProducts($coupon, $items);
}

/**
 * @param Kupon|object $coupon
 * @param array        $items
 * @return bool
 * @deprecated since 5.2.0
 */
function warenkorbKuponFaehigHersteller($coupon, array $items): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use CartHelper::cartHasCouponValidManufacturers() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::cartHasCouponValidManufacturers($coupon, $items);
}

/**
 * @param Kupon|object $coupon
 * @param array        $items
 * @return bool
 * @deprecated since 5.2.0
 */
function warenkorbKuponFaehigKategorien($coupon, array $items): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use CartHelper::cartHasCouponValidCategories instead',
        E_USER_DEPRECATED
    );
    return CartHelper::cartHasCouponValidCategories($coupon, $items);
}

/**
 * @param array $post
 * @param int   $customerAccount
 * @param int   $htmlentities
 * @return Customer
 * @deprecated since 5.2.0
 */
function getKundendaten(array $post, $customerAccount, $htmlentities = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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

    if ($customerAccount !== 0) {
        $mapping['pass'] = 'cPasswort';
    }
    $customerID = Frontend::getCustomer()->getID();
    $customer   = new Customer($customerID);
    foreach ($mapping as $external => $internal) {
        if (isset($post[$external])) {
            $val = $external === 'pass' ? $post[$external] : Text::filterXSS($post[$external]);
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
        $region = Staat::getRegionByIso($customer->cBundesland, $customer->cLand);
        if (is_object($region)) {
            $customer->cBundesland = $region->cName;
        }
    }

    return $customer;
}

/**
 * @param array $post
 * @return CustomerAttributes
 * @deprecated since 5.2.0
 */
function getKundenattribute(array $post): CustomerAttributes
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Customer\Registration::getCustomerAttributes() instead.',
        E_USER_DEPRECATED
    );
    return (new RegistrationForm())->getCustomerAttributes($post);
}

/**
 * @param array $post
 * @return Lieferadresse
 * @deprecated since 5.2.0
 */
function getLieferdaten(array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Lieferadresse::createFromPost() instead.', E_USER_DEPRECATED);
    return Lieferadresse::createFromPost($post);
}

/**
 * @return bool
 * @deprecated since 5.2.0
 */
function guthabenMoeglich(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (Frontend::getCustomer()->fGuthaben > 0
        && (empty($_SESSION['Bestellung']->GuthabenNutzen) || !$_SESSION['Bestellung']->GuthabenNutzen));
}

/**
 * @param Cart|null $cart
 * @return bool
 * @deprecated since 5.2.0
 */
function freeGiftStillValid(Cart $cart = null): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CartHelper::freeGiftStillValid() instead.', E_USER_DEPRECATED);
    return CartHelper::freeGiftStillValid($cart ?? Frontend::getCart());
}

/**
 * @param string $poCode
 * @param string $city
 * @param string $country
 * @return bool
 * @deprecated since 5.2.0
 */
function valid_plzort(string $poCode, string $city, string $country): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. '
        . 'Use JTL\Customer\Registration\Validator::AbstractValidator::isValidAddress() instead.',
        E_USER_DEPRECATED
    );

    return AbstractValidator::isValidAddress($poCode, $city, $country);
}

/**
 * @param string $step
 * @return array
 * @deprecated since 5.2.0
 */
function gibBestellschritt(string $step)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @param array|null $post
 * @return Lieferadresse
 * @deprecated since 5.2.0
 */
function setzeLieferadresseAusRechnungsadresse(?array $post = null): Lieferadresse
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Checkout\Lieferadresse::createFromShippingAddress() instead.',
        E_USER_DEPRECATED
    );
    return Lieferadresse::createFromShippingAddress($post);
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function pruefeAjaxEinKlick(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (($customerID = Frontend::getCustomer()->getID()) <= 0) {
        return 0;
    }
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    // Prüfe ob Kunde schon bestellt hat, falls ja --> Lieferdaten laden
    $lastOrder = Shop::Container()->getDB()->getSingleObject(
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
        ['cgid' => $customerGroupID, 'cid' => $customerID]
    );

    if ($lastOrder === null || $lastOrder->kBestellung <= 0) {
        return 2;
    }
    // Hat der Kunde eine Lieferadresse angegeben?
    if ($lastOrder->kLieferadresse > 0) {
        $addressID = Shop::Container()->getDB()->getSingleInt(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = :cid
                    AND kLieferadresse = :daid',
            'kLieferadresse',
            ['cid' => $customerID, 'daid' => (int)$lastOrder->kLieferadresse]
        );
        if ($addressID > 0) {
            $addressData               = new Lieferadresse($addressID);
            $_SESSION['Lieferadresse'] = $addressData;
            if (!isset($_SESSION['Bestellung'])) {
                $_SESSION['Bestellung'] = new stdClass();
            }
            $_SESSION['Bestellung']->kLieferadresse = $lastOrder->kLieferadresse;
            Shop::Smarty()->assign('Lieferadresse', $addressData);
        }
    } else {
        Shop::Smarty()->assign('Lieferadresse', Lieferadresse::createFromShippingAddress());
    }
    CartHelper::applyShippingFreeCoupon();
    Tax::setTaxRates();
    // Prüfe Versandart, falls korrekt --> laden
    if (empty($lastOrder->kVersandart)) {
        return 3;
    }
    if (isset($_SESSION['Versandart'])) {
        $bVersandart = true;
    } else {
        $bVersandart = pruefeVersandartWahl((int)$lastOrder->kVersandart, 0, false);
    }
    if ($bVersandart) {
        if ($lastOrder->kZahlungsart > 0) {
            if (isset($_SESSION['Zahlungsart'])) {
                return 5;
            }
            if (zahlungsartKorrekt((int)$lastOrder->kZahlungsart) === 2) {
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
 * @param array       $missingData
 * @param string|null $context
 * @deprecated since 5.2.0
 */
function setzeFehlendeAngaben(array $missingData, $context = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @param int $noteCode
 * @return string
 * @todo: check if this is only used by the old EOS payment method
 * @deprecated since 5.2.0
 */
function mappeBestellvorgangZahlungshinweis(int $noteCode)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @param int    $customerID
 * @return bool
 * @deprecated since 5.2.0
 */
function isEmailAvailable(string $email, int $customerID = 0): bool
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. '
        . 'Use JTL\Customer\Registration\Validator\AbstractValidator::isEmailAvailable() instead.',
        E_USER_DEPRECATED
    );
    return AbstractValidator::isEmailAvailable($email, $customerID);
}
