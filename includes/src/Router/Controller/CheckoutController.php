<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\CouponValidator;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Zahlungsart;
use JTL\Customer\AccountController;
use JTL\Customer\Customer;
use JTL\Customer\CustomerAttributes;
use JTL\Customer\CustomerFields;
use JTL\Customer\Registration\Form as RegistrationForm;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Order;
use JTL\Helpers\PaymentMethod as Helper;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Class CheckoutController
 * @package JTL\Router\Controller
 */
class CheckoutController extends RegistrationController
{
    private JTLSmarty $smarty;

    private Cart $cart;

    private Customer $customer;

    public function init(): bool
    {
        parent::init();
        Shop::setPageType(\PAGE_BESTELLVORGANG);
        $this->cart     = Frontend::getCart();
        $this->customer = Frontend::getCustomer();

        return true;
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'registrieren_inc.php';
        $this->step = 'accountwahl';

        global $step, $Kunde;
        $step  = $this->step;
        $Kunde = $this->customer;

        $_SESSION['deliveryCountryPrefLocked'] = true;

        $this->step  = 'accountwahl';
        $linkService = Shop::Container()->getLinkService();
        $controller  = new AccountController($this->db, $this->alertService, $linkService, $smarty);
        $valid       = Form::validateToken();

        unset($_SESSION['ajaxcheckout']);
        if (Request::postInt('login') === 1) {
            $controller->login($_POST['email'], $_POST['passwort']);
        }
        if (Request::verifyGPCDataInt('basket2Pers') === 1) {
            $controller->setzeWarenkorbPersInWarenkorb($this->customer->getID());

            return new RedirectResponse($linkService->getStaticRoute('bestellvorgang.php') . '?wk=1');
        }
        if ($this->cart->istBestellungMoeglich() !== 10) {
            return new RedirectResponse(
                $linkService->getStaticRoute('warenkorb.php')
                . '?fillOut=' . $this->cart->istBestellungMoeglich(),
                303
            );
        }
        if (!Upload::pruefeWarenkorbUploads($this->cart)) {
            Upload::redirectWarenkorb(\UPLOAD_ERROR_NEED_UPLOAD);
        }
        if (Download::hasDownloads($this->cart)) {
            // Nur registrierte Benutzer
            $this->config['kaufabwicklung']['bestellvorgang_unregistriert'] = 'N';
        }
        // oneClick? Darf nur einmal ausgeführt werden und nur dann, wenn man vom Warenkorb kommt.
        if (!isset($_SESSION['Lieferadresse'])
            && $this->config['kaufabwicklung']['bestellvorgang_kaufabwicklungsmethode'] === 'NO'
            && Request::verifyGPCDataInt('wk') === 1
        ) {
            $persCart = new PersistentCart($this->customer->getID());
            if (!(Request::postInt('login') === 1
                && $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y'
                && $this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P'
                && \count($persCart->getItems()) > 0)
            ) {
                $this->pruefeAjaxEinKlick();
            }
        }
        if (Request::verifyGPCDataInt('wk') === 1) {
            Kupon::resetNewCustomerCoupon();
        }
        $form = new RegistrationForm();
        if ($valid && Request::postInt('unreg_form') === 1) {
            if ($this->config['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
                $this->pruefeUnregistriertBestellen($_POST);
            } elseif (isset($_POST['shipping_address'], $_POST['register']['shipping_address'])) {
                $this->checkNewShippingAddress($_POST);
            } elseif (Request::postInt('kLieferadresse') > 0) {
                $form->pruefeLieferdaten($_POST);
            } elseif (Request::postInt('shipping_address') === 0) {
                $missingInput = $form->getMissingInput($_POST);
                $form->pruefeLieferdaten($_POST, $missingInput);
            }
        }
        if (isset($_GET['editLieferadresse'])) {
            // Shipping address and customer address are now on same site
            $_GET['editRechnungsadresse'] = Request::getInt($_GET['editLieferadresse']);
        }
        if (Request::postInt('unreg_form', -1) === 0) {
            $_POST['checkout'] = 1;
            $_POST['form']     = 1;
            $this->saveCustomer($smarty, $_POST);
        }

        if (($paymentMethodID = Request::getInt('kZahlungsart')) > 0) {
            $this->zahlungsartKorrekt($paymentMethodID);
        }
        if (Request::postInt('versandartwahl') === 1 || isset($_GET['kVersandart'])) {
            unset($_SESSION['Zahlungsart']);
            $this->pruefeVersandartWahl(Request::verifyGPCDataInt('kVersandart'));
        }
        if (Request::getInt('unreg') === 1 && $this->config['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
            $this->step = 'edit_customer_address';
        }

        //autom. step ermitteln
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']) {
            if (!isset($_SESSION['Lieferadresse'])) {
                $form->pruefeLieferdaten([
                    'kLieferadresse' => Order::getLastOrderRefIDs($this->customer->getID())->kLieferadresse
                ]);
                if (isset($_SESSION['Lieferadresse']) && $_SESSION['Lieferadresse']->kLieferadresse > 0) {
                    $_GET['editLieferadresse'] = 1;
                }
            }

            if (!isset($_SESSION['Versandart']) || !\is_object($_SESSION['Versandart'])) {
                $land            = $_SESSION['Lieferadresse']->cLand ?? $_SESSION['Kunde']->cLand;
                $plz             = $_SESSION['Lieferadresse']->cPLZ ?? $_SESSION['Kunde']->cPLZ;
                $shippingMethods = ShippingMethod::getPossibleShippingMethods(
                    $land,
                    $plz,
                    ShippingMethod::getShippingClasses($this->cart),
                    $this->customerGroupID
                );

                if (empty($shippingMethods)) {
                    $this->alertService->addDanger(
                        Shop::Lang()->get('noShippingAvailable', 'checkout'),
                        'noShippingAvailable'
                    );
                } else {
                    $activeShippingMethodID = $this->gibAktiveVersandart($shippingMethods);
                    $this->pruefeVersandartWahl(
                        $activeShippingMethodID,
                        ['kVerpackung' => \array_keys(
                            $this->gibAktiveVerpackung(ShippingMethod::getPossiblePackagings($this->customerGroupID))
                        )]
                    );
                }
            }
        }

        if (empty($_SESSION['Kunde']->cPasswort) && Download::hasDownloads($this->cart)) {
            // Falls unregistrierter Kunde bereits im Checkout war und einen Downloadartikel hinzugefuegt hat
            $this->step = 'accountwahl';

            $this->alertService->addNotice(
                Shop::Lang()->get('digitalProductsRegisterInfo', 'checkout'),
                'digiProdRegisterInfo'
            );

            unset($_SESSION['Kunde']);
            // unset not needed values to ensure the correct step
            $_POST = [];
            if (isset($_GET['editRechnungsadresse'])) {
                unset($_GET['editRechnungsadresse']);
            }
        }
        $this->pruefeVersandkostenStep();
        $this->pruefeZahlungStep();
        $this->pruefeBestaetigungStep();
        // sondersteps Rechnungsadresse aendern
        $this->pruefeRechnungsadresseStep();
        // sondersteps Lieferadresse aendern
        $this->pruefeLieferadresseStep(Text::filterXSS($_GET));
        // sondersteps Versandart aendern
        $this->pruefeVersandartStep(Text::filterXSS($_GET));
        // sondersteps Zahlungsart aendern
        $this->pruefeZahlungsartStep(Text::filterXSS($_GET));
        $this->pruefeZahlungsartwahlStep(Text::filterXSS($_POST));

        if ($this->step === 'accountwahl') {
            $this->gibStepAccountwahl();
            $this->gibStepUnregistriertBestellen();
            $this->gibStepLieferadresse();
        }
        if ($this->step === 'edit_customer_address' || $this->step === 'Lieferadresse') {
            $this->validateCouponInCheckout();
            $this->gibStepUnregistriertBestellen();
            $this->gibStepLieferadresse();
        }
        if ($this->step === 'Versand' || $this->step === 'Zahlung') {
            $this->validateCouponInCheckout();
            $this->gibStepVersand();
            $this->gibStepZahlung();
            Cart::refreshChecksum($this->cart);
        }
        if ($this->step === 'ZahlungZusatzschritt') {
            $this->gibStepZahlungZusatzschritt($_POST);
            Cart::refreshChecksum($this->cart);
        }
        if ($this->step === 'Bestaetigung') {
            $this->validateCouponInCheckout();
            Order::checkBalance($_POST);
            CouponValidator::validateCoupon($_POST, $this->customer);
            //evtl genutztes guthaben anpassen
            $this->pruefeGuthabenNutzen();
            // Eventuellen Zahlungsarten Aufpreis/Rabatt neusetzen
            $this->getPaymentSurchageDiscount($_SESSION['Zahlungsart']);
            $this->gibStepBestaetigung(Text::filterXSS($_GET));
            $this->cart->cEstimatedDelivery = $this->cart->getEstimatedDeliveryTime();
            Cart::refreshChecksum($this->cart);
        }
        if ($this->step === 'Bestaetigung' && $this->cart->gibGesamtsummeWaren(true) === 0.0) {
            $savedPayment  = $_SESSION['AktiveZahlungsart'];
            $paymentMethod = LegacyMethod::create('za_null_jtl');
            $this->zahlungsartKorrekt($paymentMethod->kZahlungsart ?? 0);

            if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
                || Request::postInt('guthabenVerrechnen') === 1
            ) {
                $_SESSION['Bestellung']->GuthabenNutzen   = 1;
                $_SESSION['Bestellung']->fGuthabenGenutzt = Order::getOrderCredit($_SESSION['Bestellung']);
            }
            Cart::refreshChecksum($this->cart);
            $_SESSION['AktiveZahlungsart'] = $savedPayment;
        }
        CartHelper::addVariationPictures($this->cart);
        $smarty->assign(
            'AGB',
            Shop::Container()->getLinkService()->getAGBWRB($this->languageID, $this->customerGroupID)
        )
            ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
            ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
            ->assign('Link', $linkService->getSpecialPage(\LINKTYP_BESTELLVORGANG))
            ->assign('alertNote', $this->alertService->alertTypeExists(Alert::TYPE_NOTE))
            ->assign('step', $this->step)
            ->assign(
                'editRechnungsadresse',
                $this->customer->nRegistriert === 1 ? 1 : Request::verifyGPCDataInt('editRechnungsadresse')
            )
            ->assign('WarensummeLocalized', $this->cart->gibGesamtsummeWarenLocalized())
            ->assign('Warensumme', $this->cart->gibGesamtsummeWaren())
            ->assign('Steuerpositionen', $this->cart->gibSteuerpositionen())
            ->assign('bestellschritt', $this->gibBestellschritt($this->step))
            ->assign('unregForm', Request::verifyGPCDataInt('unreg_form'))
            ->assignDeprecated('C_WARENKORBPOS_TYP_ARTIKEL', \C_WARENKORBPOS_TYP_ARTIKEL, '5.0.0')
            ->assignDeprecated('C_WARENKORBPOS_TYP_GRATISGESCHENK', \C_WARENKORBPOS_TYP_GRATISGESCHENK, '5.0.0');

        $this->preRender($smarty);
        \executeHook(\HOOK_BESTELLVORGANG_PAGE);

        return $smarty->getResponse('checkout/index.tpl');
    }

    /**
     * @return int
     * @since 5.2.0
     * @former pruefeAjaxEinKlick()
     */
    public function pruefeAjaxEinKlick(): int
    {
        if (($customerID = $this->customer->getID()) <= 0) {
            return 0;
        }
        // Prüfe ob Kunde schon bestellt hat, falls ja --> Lieferdaten laden
        $lastOrder = $this->db->getSingleObject(
            "SELECT tbestellung.kBestellung, tbestellung.kLieferadresse,
            tbestellung.kZahlungsart, tbestellung.kVersandart
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
            ['cgid' => $this->customerGroupID, 'cid' => $customerID]
        );

        if ($lastOrder === null || $lastOrder->kBestellung <= 0) {
            return 2;
        }
        // Hat der Kunde eine Lieferadresse angegeben?
        if ($lastOrder->kLieferadresse > 0) {
            $addressID = $this->db->getSingleInt(
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
                $this->smarty->assign('Lieferadresse', $addressData);
            }
        } else {
            $this->smarty->assign('Lieferadresse', Lieferadresse::createFromShippingAddress());
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
            $bVersandart = $this->pruefeVersandartWahl((int)$lastOrder->kVersandart, null, false);
        }
        if (!$bVersandart) {
            return 3;
        }
        if ($lastOrder->kZahlungsart > 0) {
            if (isset($_SESSION['Zahlungsart'])) {
                return 5;
            }
            if ($this->zahlungsartKorrekt((int)$lastOrder->kZahlungsart) === 2) {
                $this->gibStepZahlung();

                return 5;
            }
            unset($_SESSION['Zahlungsart']);

            return 4;
        }
        unset($_SESSION['Zahlungsart']);

        return 4;
    }

    /**
     * @param array $post
     * @return int
     */
    public function pruefeUnregistriertBestellen(array $post): int
    {
        unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART);
        $form               = new RegistrationForm();
        $this->customer     = $form->getCustomerData($post, false);
        $customerAttributes = $form->getCustomerAttributes($post);
        $checkBox           = new CheckBox();
        $missingInput       = $form->getMissingInput($post, $this->customerGroupID, $checkBox);
        $this->customer->getCustomerAttributes()->assign($customerAttributes);
        Frontend::set('customerAttributes', $customerAttributes);
        if (isset($post['shipping_address'])) {
            if ((int)$post['shipping_address'] === 0) {
                $post['kLieferadresse'] = 0;
                $post['lieferdaten']    = 1;
                $form->pruefeLieferdaten($post);
                $_SESSION['preferredDeliveryCountryCode'] = $_SESSION['Lieferadresse']->cLand ?? $post['land'];
                Tax::setTaxRates();
            } elseif (isset($post['kLieferadresse']) && (int)$post['kLieferadresse'] > 0) {
                $form->pruefeLieferdaten($post);
                $_SESSION['preferredDeliveryCountryCode'] = $_SESSION['Lieferadresse']->cLand;
                Tax::setTaxRates();
            } elseif (isset($post['register']['shipping_address'])) {
                $this->checkNewShippingAddress($post, $missingInput);
            }
        } elseif (isset($post['lieferdaten']) && (int)$post['lieferdaten'] === 1) {
            // compatibility with older template
            $form->pruefeLieferdaten($post, $missingInput);
        }
        $nReturnValue = Form::hasNoMissingData($missingInput);

        \executeHook(\HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI, [
            'nReturnValue'    => &$nReturnValue,
            'fehlendeAngaben' => &$missingInput,
            'Kunde'           => $this->customer,
            'cPost_arr'       => &$post
        ]);

        if ($nReturnValue) {
            // CheckBox Spezialfunktion ausführen
            $checkBox->triggerSpecialFunction(
                \CHECKBOX_ORT_REGISTRIERUNG,
                $this->customerGroupID,
                true,
                $post,
                ['oKunde' => $this->customer]
            )->checkLogging(\CHECKBOX_ORT_REGISTRIERUNG, $this->customerGroupID, $post, true);
            $this->customer->nRegistriert = 0;
            $_SESSION['Kunde']            = $this->customer;
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && $this->cart->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                if (isset($_SESSION['Lieferadresse']) && (int)$_SESSION['Bestellung']->kLieferadresse === 0) {
                    Lieferadresse::createFromShippingAddress();
                }
                Tax::setTaxRates();
                $this->cart->gibGesamtsummeWarenLocalized();
            }
            \executeHook(\HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN);

            return 1;
        }
        //keep shipping address on error
        if (isset($post['register']['shipping_address'])) {
            $_SESSION['Bestellung']                 = $_SESSION['Bestellung'] ?? new stdClass();
            $_SESSION['Bestellung']->kLieferadresse = (int)($post['kLieferadresse'] ?? -1);

            $_SESSION['Lieferadresse'] = Lieferadresse::createFromPost($post['register']['shipping_address']);
        }
        $this->setzeFehlendeAngaben($missingInput);
        $this->smarty->assign('customerAttributes', $customerAttributes)
            ->assign('cPost_var', Text::filterXSS($post));

        return 0;
    }

    /**
     * Prüft, ob eine neue Lieferadresse gültig ist.
     *
     * @param array      $post
     * @param array|null $missingInput
     * @since 5.2.0
     * @former checkNewShippingAddress()
     */
    public function checkNewShippingAddress(array $post, ?array $missingInput = null): void
    {
        $form         = new RegistrationForm();
        $missingInput = $missingInput ?? $form->getMissingInput($post);
        $form->pruefeLieferdaten($post['register']['shipping_address'], $missingInput);
    }

    /**
     * @param int $paymentMethodID
     * @return int
     * @since 5.2.0
     * @former zahlungsartKorrekt()
     */
    public function zahlungsartKorrekt(int $paymentMethodID): int
    {
        $zaInfo = $_SESSION['Zahlungsart']->ZahlungsInfo ?? null;
        unset($_SESSION['Zahlungsart']);
        $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        if ($paymentMethodID <= 0 || (int)($_SESSION['Versandart']->kVersandart ?? 0) <= 0) {
            return 0;
        }
        $paymentMethod = $this->db->getSingleObject(
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
            $paymentMethod = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            // only the null-payment-method is allowed to go ahead in this case
            if ($paymentMethod->cModulId !== 'za_null_jtl') {
                return 0;
            }
        }
        if (isset($paymentMethod->cModulId) && \mb_strlen($paymentMethod->cModulId) > 0) {
            $config = $this->db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cModulId'],
                [\CONF_ZAHLUNGSARTEN, $paymentMethod->cModulId]
            );
            foreach ($config as $conf) {
                $paymentMethod->einstellungen[$conf->cName] = $conf->cWert;
            }
        }
        if (!$this->zahlungsartGueltig($paymentMethod)) {
            return 0;
        }
        $note                        = $this->db->select(
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
        $this->getPaymentSurchageDiscount($paymentMethod);
        $specialItem        = new stdClass();
        $specialItem->cName = [];
        foreach ($_SESSION['Sprachen'] as $lang) {
            if ($paymentMethod->kZahlungsart > 0) {
                $localized = $this->db->select(
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
                    $fehlendeAngaben = $this->checkAdditionalPayment($paymentMethod);

                    if (\count($fehlendeAngaben) === 0) {
                        $info->cBankName = Text::htmlentities(\stripslashes($_POST['bankname'] ?? ''), \ENT_QUOTES);
                        $info->cKontoNr  = Text::htmlentities(\stripslashes($_POST['kontonr'] ?? ''), \ENT_QUOTES);
                        $info->cBLZ      = Text::htmlentities(\stripslashes($_POST['blz'] ?? ''), \ENT_QUOTES);
                        $info->cIBAN     = Text::htmlentities(\stripslashes($_POST['iban']), \ENT_QUOTES);
                        $info->cBIC      = Text::htmlentities(\stripslashes($_POST['bic'] ?? ''), \ENT_QUOTES);
                        $info->cInhaber  = Text::htmlentities(\stripslashes($_POST['inhaber'] ?? ''), \ENT_QUOTES);

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

    /**
     * @param int        $shippingMethodID
     * @param array|null $formValues
     * @param bool       $msg
     * @return bool
     * @since 5.2.0
     * @former pruefeVersandartWahl()
     */
    public function pruefeVersandartWahl(int $shippingMethodID, ?array $formValues = null, bool $msg = true): bool
    {
        $return = $this->versandartKorrekt($shippingMethodID, $formValues);
        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI);

        if ($return) {
            $this->step = 'Zahlung';
            $this->alertService->removeAlertByKey('fillShipping');

            return true;
        }
        if ($msg) {
            $this->alertService->addNotice(Shop::Lang()->get('fillShipping', 'checkout'), 'fillShipping');
        }
        $this->step = 'Versand';

        return false;
    }

    /**
     * @param object[] $shippingMethods
     * @return int
     * @former gibAktiveZahlungsart()
     */
    public function gibAktiveZahlungsart(array $shippingMethods): int
    {
        if (isset($_SESSION['Zahlungsart'])) {
            $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
        } elseif (!empty($_SESSION['AktiveZahlungsart']) && GeneralObject::hasCount($shippingMethods)) {
            $active = (int)$_SESSION['AktiveZahlungsart'];
            if (\array_reduce($shippingMethods, static function ($carry, $item) use ($active) {
                    return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
            }, 0) !== (int)$_SESSION['AktiveZahlungsart']
            ) {
                $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
            }
        } else {
            $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
        }

        return (int)$_SESSION['AktiveZahlungsart'];
    }

    /**
     * @param int $shippingMethodID
     * @param int $customerGroupID
     * @return array
     * @former gibZahlungsarten()
     */
    public function gibZahlungsarten(int $shippingMethodID, int $customerGroupID): array
    {
        $taxRate = 0.0;
        $methods = [];
        if ($shippingMethodID > 0) {
            $methods = $this->db->getObjects(
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
                $loc = $this->db->select(
                    'tzahlungsartsprache',
                    'kZahlungsart',
                    $method->kZahlungsart,
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
            $confData = $this->db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cModulId'],
                [\CONF_ZAHLUNGSARTEN, $method->cModulId]
            );
            foreach ($confData as $config) {
                $method->einstellungen[$config->cName] = $config->cWert;
            }
            if (!$this->zahlungsartGueltig($method)) {
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
     * @since 5.2.0
     * @former gibAktiveVersandart()
     */
    public function gibAktiveVersandart(array $shippingMethods): int
    {
        if (isset($_SESSION['Versandart'])) {
            $_SESSION['AktiveVersandart'] = (int)$_SESSION['Versandart']->kVersandart;
        } elseif (!empty($_SESSION['AktiveVersandart']) && GeneralObject::hasCount($shippingMethods)) {
            $active  = (int)$_SESSION['AktiveVersandart'];
            $reduced = \array_reduce($shippingMethods, static function ($carry, $item) use ($active) {
                return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
            }, 0);
            if ($reduced !== (int)$_SESSION['AktiveVersandart']) {
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
     * @param int $paymentMethodID
     * @return mixed
     * @former gibZahlungsart()
     * @since 5.2.0
     */
    public function gibZahlungsart(int $paymentMethodID)
    {
        $method = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
        foreach (Frontend::getLanguages() as $language) {
            $localized                                     = $this->db->select(
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
        $confData = $this->db->getObjects(
            'SELECT *
                FROM teinstellungen
                WHERE kEinstellungenSektion = :sec
                    AND cModulId = :mod',
            ['mod' => $method->cModulId, 'sec' => \CONF_ZAHLUNGSARTEN]
        );
        foreach ($confData as $conf) {
            $method->einstellungen[$conf->cName] = $conf->cWert;
        }
        $plugin = $this->gibPluginZahlungsart($method->cModulId);
        if ($plugin) {
            $paymentMethod                  = $plugin->getPaymentMethods()->getMethodByID($method->cModulId);
            $method->cZusatzschrittTemplate = $paymentMethod !== null ? $paymentMethod->getAdditionalTemplate() : '';
        }

        return $method;
    }

    /**
     * @param string $moduleID
     * @return bool|PluginInterface
     * @former gibPluginZahlungsart()
     * @since 5.2.0
     */
    public function gibPluginZahlungsart(string $moduleID)
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
     * @param object[] $packagings
     * @return array
     * @since 5.2.0
     * @former gibAktiveVerpackung()
     */
    public function gibAktiveVerpackung(array $packagings): array
    {
        if (isset($_SESSION['Verpackung']) && \count($_SESSION['Verpackung']) > 0) {
            $_SESSION['AktiveVerpackung'] = [];
            foreach ($_SESSION['Verpackung'] as $packaging) {
                $_SESSION['AktiveVerpackung'][(int)$packaging->kVerpackung] = 1;
            }
        } elseif (!empty($_SESSION['AktiveVerpackung']) && \count($packagings) > 0) {
            foreach (\array_keys($_SESSION['AktiveVerpackung']) as $active) {
                if (\array_reduce($packagings, static function ($carry, $item) use ($active) {
                        $kVerpackung = (int)$item->kVerpackung;
                        return $kVerpackung === $active ? $kVerpackung : $carry;
                }, 0) === 0
                ) {
                    unset($_SESSION['AktiveVerpackung'][$active]);
                }
            }
        } else {
            $_SESSION['AktiveVerpackung'] = [];
        }

        return $_SESSION['AktiveVerpackung'];
    }

    /**
     * @param null|int $customerID
     * @return object|bool
     * @former gibKundenKontodaten()
     */
    public function gibKundenKontodaten(?int $customerID)
    {
        if (empty($customerID)) {
            return false;
        }
        $accountData = $this->db->select('tkundenkontodaten', 'kKunde', $customerID);

        if (isset($accountData->kKunde) && $accountData->kKunde > 0) {
            $cryptoService = Shop::Container()->getCryptoService();
            if (\mb_strlen($accountData->cBLZ) > 0) {
                $accountData->cBLZ = (int)$cryptoService->decryptXTEA($accountData->cBLZ);
            }
            if (\mb_strlen($accountData->cInhaber) > 0) {
                $accountData->cInhaber = \trim($cryptoService->decryptXTEA($accountData->cInhaber));
            }
            if (\mb_strlen($accountData->cBankName) > 0) {
                $accountData->cBankName = \trim($cryptoService->decryptXTEA($accountData->cBankName));
            }
            if (\mb_strlen($accountData->nKonto) > 0) {
                $accountData->nKonto = \trim($cryptoService->decryptXTEA($accountData->nKonto));
            }
            if (\mb_strlen($accountData->cIBAN) > 0) {
                $accountData->cIBAN = \trim($cryptoService->decryptXTEA($accountData->cIBAN));
            }
            if (\mb_strlen($accountData->cBIC) > 0) {
                $accountData->cBIC = \trim($cryptoService->decryptXTEA($accountData->cBIC));
            }

            return $accountData;
        }

        return false;
    }

    /**
     * @param Zahlungsart|object $paymentMethod
     * @return array
     * @former checkAdditionalPayment()
     */
    public function checkAdditionalPayment($paymentMethod): array
    {
        foreach (['iban', 'bic'] as $dataKey) {
            if (!empty($_POST[$dataKey])) {
                $_POST[$dataKey] = \mb_convert_case($_POST[$dataKey], \MB_CASE_UPPER);
            }
        }

        $post   = Text::filterXSS($_POST);
        $errors = [];
        if ($paymentMethod->cModulId === 'za_lastschrift_jtl') {
            $conf = $this->config['zahlungsarten'];
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
            } elseif (!Text::checkBIC($post['bic'])) {
                $errors['bic'] = 2;
            }
            if (empty($post['iban'])) {
                $errors['iban'] = 1;
            } elseif (!Text::checkIBAN($post['iban'])) {
                $errors['iban'] = 2;
            }
        }

        return $errors;
    }

    /**
     * @return stdClass
     * @former gibPostZahlungsInfo()
     * @since 5.2.0
     */
    public function gibPostZahlungsInfo(): stdClass
    {
        $info = new stdClass();

        $info->cKartenNr    = null;
        $info->cGueltigkeit = null;
        $info->cCVV         = null;
        $info->cKartenTyp   = null;
        $info->cBankName    = isset($_POST['bankname'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['bankname'])), \ENT_QUOTES)
            : null;
        $info->cKontoNr     = isset($_POST['kontonr'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['kontonr'])), \ENT_QUOTES)
            : null;
        $info->cBLZ         = isset($_POST['blz'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['blz'])), \ENT_QUOTES)
            : null;
        $info->cIBAN        = isset($_POST['iban'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['iban'])), \ENT_QUOTES)
            : null;
        $info->cBIC         = isset($_POST['bic'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['bic'])), \ENT_QUOTES)
            : null;
        $info->cInhaber     = isset($_POST['inhaber'])
            ? Text::htmlentities(\stripslashes(\trim($_POST['inhaber'])), \ENT_QUOTES)
            : null;

        return $info;
    }

    /**
     * @return bool
     * @former guthabenMoeglich()
     */
    public function guthabenMoeglich(): bool
    {
        return ($this->customer->fGuthaben > 0
            && (empty($_SESSION['Bestellung']->GuthabenNutzen) || !$_SESSION['Bestellung']->GuthabenNutzen));
    }

    /**
     * @return string
     * @former pruefeVersandkostenStep()
     */
    public function pruefeVersandkostenStep(): string
    {
        if (!isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'])) {
            return $this->step;
        }
        $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        $dependent = ShippingMethod::gibArtikelabhaengigeVersandkostenImWK(
            $_SESSION['Lieferadresse']->cLand,
            $this->cart->PositionenArr
        );
        foreach ($dependent as $item) {
            $this->cart->erstelleSpezialPos(
                $item->cName,
                1,
                $item->fKosten,
                $this->cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                false
            );
        }
        $this->step = 'Versand';

        return $this->step;
    }

    /**
     * @return string
     * @former pruefeZahlungStep()
     */
    public function pruefeZahlungStep(): string
    {
        if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'])) {
            $this->step = 'Zahlung';
        }

        return $this->step;
    }

    /**
     * @return string
     * @former pruefeBestaetigungStep()
     */
    public function pruefeBestaetigungStep(): string
    {
        if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])) {
            $this->step = 'Bestaetigung';
        }
        if (isset($_SESSION['Zahlungsart'], $_SESSION['Zahlungsart']->cZusatzschrittTemplate)
            && \mb_strlen($_SESSION['Zahlungsart']->cZusatzschrittTemplate) > 0
        ) {
            $paymentMethod = LegacyMethod::create($_SESSION['Zahlungsart']->cModulId);
            if ($paymentMethod !== null && \is_object($paymentMethod) && !$paymentMethod->validateAdditional()) {
                $this->step = 'Zahlung';
            }
        }

        return $this->step;
    }

    /**
     * @return string
     * @former pruefeRechnungsadresseStep()
     */
    public function pruefeRechnungsadresseStep(): string
    {
        // sondersteps Rechnungsadresse ändern
        if (!empty($this->customer->cOrt)
            && (Request::getInt('editRechnungsadresse') === 1 || Request::getInt('editLieferadresse') === 1)
        ) {
            Kupon::resetNewCustomerCoupon();
            $this->step = 'edit_customer_address';
        }

        if (!empty($this->customer->cOrt)
            && \count(ShippingMethod::getPossibleShippingCountries(
                $this->customerGroupID,
                false,
                false,
                [$this->customer->cLand]
            )) === 0
        ) {
            $this->smarty->assign('forceDeliveryAddress', 1);

            if (!isset($_SESSION['Lieferadresse'])
                || \count(ShippingMethod::getPossibleShippingCountries(
                    $this->customerGroupID,
                    false,
                    false,
                    [$_SESSION['Lieferadresse']->cLand]
                )) === 0
            ) {
                $this->step = 'edit_customer_address';
            }
        }

        if (isset($_SESSION['checkout.register']) && (int)$_SESSION['checkout.register'] === 1) {
            if (isset($_SESSION['checkout.fehlendeAngaben'])) {
                $this->setzeFehlendeAngaben($_SESSION['checkout.fehlendeAngaben']);
                unset($_SESSION['checkout.fehlendeAngaben']);
            }
            if (isset($_SESSION['checkout.cPost_arr'])) {
                $form               = new RegistrationForm();
                $this->customer     = $form->getCustomerData($_SESSION['checkout.cPost_arr'], false, false);
                $customerAttributes = $form->getCustomerAttributes($_SESSION['checkout.cPost_arr']);
                $this->customer->getCustomerAttributes()->assign($customerAttributes);
                Frontend::set('customerAttributes', $customerAttributes);
                $this->smarty->assign('Kunde', $this->customer)
                    ->assign('cPost_var', Text::filterXSS($_SESSION['checkout.cPost_arr']));
                if (isset($_SESSION['Lieferadresse'])
                    && (int)$_SESSION['checkout.cPost_arr']['shipping_address'] !== 0
                ) {
                    $this->smarty->assign('Lieferadresse', $_SESSION['Lieferadresse']);
                }

                $_POST = Text::filterXSS(\array_merge($_POST, $_SESSION['checkout.cPost_arr']));
                unset($_SESSION['checkout.cPost_arr']);
            }
            unset($_SESSION['checkout.register']);
        }
        if ($this->pruefeFehlendeAngaben()) {
            $this->step = isset($_SESSION['Kunde']) ? 'edit_customer_address' : 'accountwahl';
        }

        return $this->step;
    }

    /**
     * @param array $get
     * @return string
     * @former pruefeLieferadresseStep()
     */
    public function pruefeLieferadresseStep(array $get): string
    {
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
                $this->step = 'Lieferadresse';
            }
        }
        if ($this->pruefeFehlendeAngaben('shippingAddress')) {
            $this->step = isset($_SESSION['Kunde']) ? 'Lieferadresse' : 'accountwahl';
        }

        return $this->step;
    }

    /**
     * @param array $get
     * @return string
     * @former pruefeVersandartStep()
     */
    public function pruefeVersandartStep(array $get): string
    {
        // sondersteps Versandart ändern
        if (isset($get['editVersandart'], $_SESSION['Versandart']) && (int)$get['editVersandart'] === 1) {
            Kupon::resetNewCustomerCoupon();
            $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERPACKUNG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Zahlungsart'], $_SESSION['Versandart']);

            $this->step = 'Versand';
            $this->pruefeZahlungsartStep(['editZahlungsart' => 1]);
        }

        return $this->step;
    }

    /**
     * @param array $get
     * @return string
     * @former pruefeZahlungsartStep()
     */
    public function pruefeZahlungsartStep(array $get): string
    {
        // sondersteps Zahlungsart ändern
        if (isset($_SESSION['Zahlungsart'], $get['editZahlungsart']) && (int)$get['editZahlungsart'] === 1) {
            Kupon::resetNewCustomerCoupon();
            $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            unset($_SESSION['Zahlungsart']);
            $this->step = $this->pruefeVersandartStep(['editVersandart' => 1]);
        }

        if (isset($get['nHinweis']) && (int)$get['nHinweis'] > 0) {
            $this->alertService->addNotice(
                $this->mappeBestellvorgangZahlungshinweis((int)$get['nHinweis']),
                'paymentNote'
            );
        }

        return $this->step;
    }

    /**
     * @param array $post
     * @return int|null
     * @former pruefeZahlungsartwahlStep()
     */
    public function pruefeZahlungsartwahlStep(array $post): ?int
    {
        global $zahlungsangaben;
        if (!isset($post['zahlungsartwahl']) || (int)$post['zahlungsartwahl'] !== 1) {
            if (isset($_SESSION['Zahlungsart'])
                && Request::getInt('editRechnungsadresse') !== 1
                && Request::getInt('editLieferadresse') !== 1
            ) {
                $zahlungsangaben = $this->zahlungsartKorrekt((int)$_SESSION['Zahlungsart']->kZahlungsart);
            } else {
                return null;
            }
        } else {
            $zahlungsangaben = $this->zahlungsartKorrekt((int)$post['Zahlungsart']);
        }
        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG_PLAUSI);

        switch ($zahlungsangaben) {
            case 0:
                $this->alertService->addNotice(Shop::Lang()->get('fillPayment', 'checkout'), 'fillPayment');
                $this->step = 'Zahlung';
                return 0;
            case 1:
                $this->step = 'ZahlungZusatzschritt';

                return 1;
            case 2:
                $this->step = 'Bestaetigung';

                return 2;
            default:
                return null;
        }
    }

    /**
     * @return void
     * @former gibStepAccountwahl()
     */
    public function gibStepAccountwahl(): void
    {
        // Einstellung global_kundenkonto_aktiv ist auf 'A'
        // und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
        if (isset($_REQUEST['reg'])
            && (int)$_REQUEST['reg'] === 1
            && $this->config['global']['global_kundenkonto_aktiv'] === 'A'
            && empty($this->smarty->getTemplateVars('fehlendeAngaben'))
        ) {
            $this->alertService->addNotice(
                Shop::Lang()->get('accountCreated') . '. ' . Shop::Lang()->get('activateAccountDesc'),
                'accountCreatedLoginNotActivated'
            );
            $this->alertService->addNotice(
                Shop::Lang()->get('continueAfterActivation', 'messages'),
                'continueAfterActivation'
            );
        }
        $this->smarty->assign('untertitel', \lang_warenkorb_bestellungEnthaeltXArtikel($this->cart))
            ->assign('one_step_wk', Request::verifyGPCDataInt('wk'));

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
    }

    /**
     * @former gibStepUnregistriertBestellen()
     */
    public function gibStepUnregistriertBestellen(): void
    {
        $origins = $this->db->getObjects(
            'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort'
        );
        if ($this->customer !== null) {
            $customerAttributes = $this->customer->getCustomerAttributes();
            if ($this->customer->getID() === 0) {
                $customerAttributes->assign(Frontend::get('customerAttributes') ?? new CustomerAttributes());
            }
        } else {
            $form               = new RegistrationForm();
            $customerAttributes = $form->getCustomerAttributes($_POST);
        }
        $this->smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
            ->assign('herkunfte', $origins)
            ->assign('Kunde', $this->customer ?? null)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID, false, true))
            ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID))
            ->assign('oKundenfeld_arr', new CustomerFields($this->languageID))
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_REGISTRIERUNG)
            ->assign('code_registrieren', false)
            ->assign('customerAttributes', $customerAttributes);

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPUNREGISTRIERTBESTELLEN);
    }

    /**
     * @return mixed
     * @former gibStepLieferadresse()
     */
    public function gibStepLieferadresse()
    {
        global $Lieferadresse;

        if ($this->customer->getID() > 0) {
            $addresses = [];
            $data      = $this->db->getObjects(
                'SELECT DISTINCT(kLieferadresse)
                    FROM tlieferadresse
                    WHERE kKunde = :cid',
                ['cid' => $this->customer->getID()]
            );
            foreach ($data as $item) {
                $addresses[] = new Lieferadresse((int)$item->kLieferadresse);
            }
            $this->smarty->assign('Lieferadressen', $addresses);
        }
        $countries = ShippingMethod::getPossibleShippingCountries($this->customerGroupID, false, true);
        $this->smarty->assign('laender', $countries)
            ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID))
            ->assign('Kunde', $_SESSION['Kunde'] ?? null)
            ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse ?? null);
        if (isset($_SESSION['Bestellung']->kLieferadresse) && (int)$_SESSION['Bestellung']->kLieferadresse === -1) {
            $this->smarty->assign('Lieferadresse', $Lieferadresse);
        }
        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE);

        return $Lieferadresse;
    }

    /**
     * @former validateCouponInCheckout()
     */
    public function validateCouponInCheckout(): void
    {
        if (!isset($_SESSION['Kupon'])) {
            return;
        }
        $checkCouponResult = Kupon::checkCoupon($_SESSION['Kupon']);
        if (\count($checkCouponResult) !== 0) {
            $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
            $_SESSION['checkCouponResult'] = $checkCouponResult;
            unset($_SESSION['Kupon']);
            \header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true));
            exit(0);
        }
    }

    /**
     * @return void
     * @former gibStepVersand()
     */
    public function gibStepVersand(): void
    {
        CartHelper::applyShippingFreeCoupon();
        $deliveryCountry = $_SESSION['Lieferadresse']->cLand ?? null;
        if (!$deliveryCountry) {
            $deliveryCountry = $this->customer->cLand;
        }
        $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
        if (!$poCode) {
            $poCode = $this->customer->cPLZ;
        }
        $shippingMethods = ShippingMethod::getPossibleShippingMethods(
            $deliveryCountry,
            $poCode,
            ShippingMethod::getShippingClasses($this->cart),
            $this->customerGroupID
        );
        $packagings      = ShippingMethod::getPossiblePackagings($this->customerGroupID);
        if (!empty($packagings) && $this->cart->posTypEnthalten(\C_WARENKORBPOS_TYP_VERPACKUNG)) {
            foreach ($this->cart->PositionenArr as $item) {
                if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERPACKUNG) {
                    foreach ($packagings as $packaging) {
                        if ($packaging->cName === $item->cName[$packaging->cISOSprache]) {
                            $packaging->bWarenkorbAktiv = true;
                        }
                    }
                }
            }
        }
        if (GeneralObject::hasCount($shippingMethods)
            || (\is_array($shippingMethods) && \count($shippingMethods) === 1 && GeneralObject::hasCount($packagings))
        ) {
            $this->smarty->assign('Versandarten', $shippingMethods)
                ->assign('Verpackungsarten', $packagings);
        } elseif (\is_array($shippingMethods) && \count($shippingMethods) === 1
            && (\is_array($packagings) && \count($packagings) === 0)
        ) {
            $this->pruefeVersandartWahl($shippingMethods[0]->kVersandart);
        } elseif (!\is_array($shippingMethods) || \count($shippingMethods) === 0) {
            Shop::Container()->getLogService()->error(
                'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: ' . $deliveryCountry .
                ', PLZ: ' . $poCode . ', Versandklasse: ' . ShippingMethod::getShippingClasses($this->cart) .
                ', Kundengruppe: ' . $this->customerGroupID
            );
        }
        $this->smarty->assign('Kunde', $this->customer)
            ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPVERSAND);
    }

    /**
     * @former gibStepZahlung()
     */
    public function gibStepZahlung(): void
    {
        $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
        if (!$lieferland) {
            $lieferland = $this->customer->cLand;
        }
        $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
        if (!$poCode) {
            $poCode = $this->customer->cPLZ;
        }
        $shippingMethods = ShippingMethod::getPossibleShippingMethods(
            $lieferland,
            $poCode,
            ShippingMethod::getShippingClasses($this->cart),
            $this->customerGroupID
        );
        $packagings      = ShippingMethod::getPossiblePackagings($this->customerGroupID);
        if (!empty($packagings) && $this->cart->posTypEnthalten(\C_WARENKORBPOS_TYP_VERPACKUNG)) {
            foreach ($this->cart->PositionenArr as $item) {
                if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERPACKUNG) {
                    foreach ($packagings as $oPack) {
                        if ($oPack->cName === $item->cName[$oPack->cISOSprache]) {
                            $oPack->bWarenkorbAktiv = true;
                        }
                    }
                }
            }
        }

        if (GeneralObject::hasCount($shippingMethods)) {
            $shippingMethod = $this->gibAktiveVersandart($shippingMethods);
            $paymentMethods = $this->gibZahlungsarten($shippingMethod, $this->customerGroupID);
            if (!\is_array($paymentMethods) || \count($paymentMethods) === 0) {
                Shop::Container()->getLogService()->error(
                    'Es konnte keine Zahlungsart für folgende Daten gefunden werden: Versandart: ' .
                    $shippingMethod . ', Kundengruppe: ' . $this->customerGroupID
                );
                $paymentMethod  = null;
                $paymentMethods = [];
            } else {
                $paymentMethod = $this->gibAktiveZahlungsart($paymentMethods);
            }

            if (!isset($_SESSION['Versandart']) && !empty($shippingMethod)) {
                // dieser Workaround verhindert die Anzeige der Standardzahlungsarten wenn ein Zahlungsplugin aktiv ist
                $_SESSION['Versandart'] = (object)['kVersandart' => $shippingMethod];
            }
            $selectablePayments = \array_filter(
                $paymentMethods,
                static function ($method) {
                    $paymentMethod = LegacyMethod::create($method->cModulId);
                    if ($paymentMethod !== null) {
                        return $paymentMethod->isSelectable();
                    }

                    return true;
                }
            );
            $this->smarty->assign('Zahlungsarten', $selectablePayments)
                ->assign('Versandarten', $shippingMethods)
                ->assign('Verpackungsarten', $packagings)
                ->assign('AktiveVersandart', $shippingMethod)
                ->assign('AktiveZahlungsart', $paymentMethod)
                ->assign('AktiveVerpackung', $this->gibAktiveVerpackung($packagings))
                ->assign('Kunde', $this->customer)
                ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
                ->assign('OrderAmount', $this->cart->gibGesamtsummeWaren(true))
                ->assign('ShopCreditAmount', $this->customer->fGuthaben);

            \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG);

            /**
             * This is for compatibility in 3-step checkout and will prevent form in form tags trough payment plugins
             * @see /templates/Evo/checkout/step4_payment_options.tpl
             * @todo: Replace with more convenient solution in later versions (after 4.06)
             */
            $step4PaymentContent = $this->smarty->fetch('checkout/step4_payment_options.tpl');
            if (\preg_match('/<form([^>]*)>/', $step4PaymentContent, $hits)) {
                $step4PaymentContent = \str_replace(
                    [$hits[0], '</form>'],
                    ['<div' . $hits[1] . '>', '</div>'],
                    $step4PaymentContent
                );
            }
            $this->smarty->assign('step4_payment_content', $step4PaymentContent);
        }
    }

    /**
     * @param array $post
     * @former gibStepZahlungZusatzschritt()
     */
    public function gibStepZahlungZusatzschritt(array $post): void
    {
        $paymentID     = $post['Zahlungsart'] ?? $_SESSION['Zahlungsart']->kZahlungsart;
        $paymentMethod = $this->gibZahlungsart((int)$paymentID);
        // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
        $customerAccountData = $this->gibKundenKontodaten($this->customer->getID());
        if (isset($customerAccountData->kKunde) && $customerAccountData->kKunde > 0) {
            $this->smarty->assign('oKundenKontodaten', $customerAccountData);
        }
        if (!isset($post['zahlungsartzusatzschritt']) || !$post['zahlungsartzusatzschritt']) {
            $this->smarty->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo ?? null);
        } else {
            $this->setzeFehlendeAngaben($this->checkAdditionalPayment($paymentMethod));
            unset($_SESSION['checkout.fehlendeAngaben']);
            $this->smarty->assign('ZahlungsInfo', $this->gibPostZahlungsInfo());
        }
        $this->smarty->assign('Zahlungsart', $paymentMethod)
            ->assign('Kunde', $this->customer)
            ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNGZUSATZSCHRITT);
    }

    /**
     * @former pruefeGuthabenNutzen()
     */
    public function pruefeGuthabenNutzen(): void
    {
        if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen) {
            $_SESSION['Bestellung']->fGuthabenGenutzt = Order::getOrderCredit($_SESSION['Bestellung']);
        }

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABEN_PLAUSI);
    }

    /**
     * @param object|null $paymentMethod
     * @former getPaymentSurchageDiscount()
     */
    public function getPaymentSurchageDiscount(?object $paymentMethod): void
    {
        if ($paymentMethod === null || !isset($paymentMethod->fAufpreis) || $paymentMethod->fAufpreis == 0) {
            return;
        }
        $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        $paymentMethod->cPreisLocalized = Preise::getLocalizedPriceString($paymentMethod->fAufpreis);
        $surcharge                      = $paymentMethod->fAufpreis;
        if ($paymentMethod->cAufpreisTyp === 'prozent') {
            $fGuthaben = $_SESSION['Bestellung']->fGuthabenGenutzt ?? 0;
            $surcharge = (($this->cart->gibGesamtsummeWarenExt(
                [
                                \C_WARENKORBPOS_TYP_ARTIKEL,
                                \C_WARENKORBPOS_TYP_VERSANDPOS,
                                \C_WARENKORBPOS_TYP_KUPON,
                                \C_WARENKORBPOS_TYP_GUTSCHEIN,
                                \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                                \C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
                                \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                                \C_WARENKORBPOS_TYP_VERPACKUNG
                            ],
                true
            ) - $fGuthaben) * $paymentMethod->fAufpreis) / 100.0;

            $paymentMethod->cPreisLocalized = Preise::getLocalizedPriceString($surcharge);
        }
        $specialItem               = new stdClass();
        $specialItem->cGebuehrname = [];
        foreach (Frontend::getLanguages() as $lang) {
            if ($paymentMethod->kZahlungsart > 0) {
                $loc = $this->db->select(
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
            $this->cart->erstelleSpezialPos(
                $specialItem->cGebuehrname,
                1,
                $surcharge,
                $this->cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
                true,
                true,
                $paymentMethod->cHinweisText
            );
        } else {
            $this->cart->erstelleSpezialPos(
                $specialItem->cGebuehrname,
                1,
                $surcharge,
                $this->cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                \C_WARENKORBPOS_TYP_ZAHLUNGSART,
                true,
                true,
                $paymentMethod->cHinweisText
            );
        }
    }

    /**
     * @param array $get
     * @former gibStepBestaetigung()
     */
    public function gibStepBestaetigung(array $get): void
    {
        $linkHelper = Shop::Container()->getLinkService();
        // check currenct shipping method again to avoid using invalid methods when using one click method (#9566)
        if (isset($_SESSION['Versandart']->kVersandart)
            && !$this->versandartKorrekt((int)$_SESSION['Versandart']->kVersandart)
        ) {
            \header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editVersandart=1', true, 303);
        }
        // Bei Standardzahlungsarten mit Zahlungsinformationen prüfen ob Daten vorhanden sind
        if (isset($_SESSION['Zahlungsart'])
            && $_SESSION['Zahlungsart']->cModulId === 'za_lastschrift_jtl'
            && (empty($_SESSION['Zahlungsart']->ZahlungsInfo) || !\is_object($_SESSION['Zahlungsart']->ZahlungsInfo))
        ) {
            \header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editZahlungsart=1', true, 303);
        }

        if (empty($get['fillOut'])) {
            unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
        }
        //falls zahlungsart extern und Einstellung, dass Bestellung für Kaufabwicklung notwendig, füllte tzahlungsession
        $this->smarty->assign('Kunde', $this->customer)
            ->assign('customerAttributes', $this->customer->getCustomerAttributes())
            ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
            ->assign('KuponMoeglich', Kupon::couponsAvailable())
            ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
            ->assign('currentCouponName', !empty($_SESSION['Kupon']->translationList)
                ? $_SESSION['Kupon']->translationList
                : null)
            ->assign('currentShippingCouponName', !empty($_SESSION['oVersandfreiKupon']->translationList)
                ? $_SESSION['oVersandfreiKupon']->translationList
                : null)
            ->assign('GuthabenMoeglich', $this->guthabenMoeglich())
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_BESTELLABSCHLUSS)
            ->assign('cPost_arr', (isset($_SESSION['cPost_arr']) ? Text::filterXSS($_SESSION['cPost_arr']) : []));
        if ($this->customer->getID() > 0) {
            $this->smarty->assign('GuthabenLocalized', $this->customer->gibGuthabenLocalized());
        }
        if (isset($this->cart->PositionenArr)
            && !empty($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']])
            && \count($this->cart->PositionenArr) > 0
        ) {
            foreach ($this->cart->PositionenArr as $item) {
                if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS) {
                    $item->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']];
                }
            }
        }

        \executeHook(\HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG);
    }

    /**
     * @param Zahlungsart|stdClass $paymentMethod
     * @return bool
     * @former zahlungsartGueltig()
     */
    public function zahlungsartGueltig($paymentMethod): bool
    {
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
            if (!$method->isValid($this->customer, $this->cart)) {
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
     * @param int        $shippingMethodID
     * @param array|null $formValues
     * @return bool
     * @former versandartKorrekt()
     */
    public function versandartKorrekt(int $shippingMethodID, ?array $formValues = null): bool
    {
        $packagingIDs           = GeneralObject::hasCount('kVerpackung', $_POST)
            ? $_POST['kVerpackung']
            : ($formValues['kVerpackung'] ?? []);
        $cartTotal              = $this->cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
        $_SESSION['Verpackung'] = [];
        if (GeneralObject::hasCount($packagingIDs)) {
            $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERPACKUNG);
            foreach ($packagingIDs as $packagingID) {
                $packagingID = (int)$packagingID;
                $packagings  = $this->db->getSingleObject(
                    "SELECT *
                    FROM tverpackung
                    WHERE kVerpackung = :pid
                        AND (tverpackung.cKundengruppe = '-1'
                            OR FIND_IN_SET(:cgid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                        AND :sum >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1",
                    [
                        'pid'  => $packagingID,
                        'cgid' => $this->customerGroupID,
                        'sum'  => $cartTotal
                    ]
                );
                if ($packagings === null) {
                    return false;
                }
                $packagings->kVerpackung = (int)$packagings->kVerpackung;

                $localizedNames     = [];
                $localizedPackaging = $this->db->selectAll(
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
                    $packagings->kSteuerklasse = $this->cart->gibVersandkostenSteuerklasse(
                        $_SESSION['Lieferadresse']->cLand
                    );
                }
                $_SESSION['Verpackung'][] = $packagings;

                $_SESSION['AktiveVerpackung'][$packagings->kVerpackung] = 1;
                $this->cart->erstelleSpezialPos(
                    $localizedNames,
                    1,
                    $fBrutto,
                    $packagings->kSteuerklasse,
                    \C_WARENKORBPOS_TYP_VERPACKUNG,
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
            $deliveryCountry = $this->customer->cLand;
        }
        $poCode = $_SESSION['Lieferadresse']->cPLZ ?? null;
        if (!$poCode) {
            $poCode = $this->customer->cPLZ;
        }
        $shippingClasses = ShippingMethod::getShippingClasses($this->cart);
        $depending       = 'N';
        if (ShippingMethod::normalerArtikelversand($deliveryCountry) === false) {
            $depending = 'Y';
        }
        $countryCode    = $deliveryCountry;
        $shippingMethod = $this->db->getSingleObject(
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
        $shippingMethod->fEndpreis          = ShippingMethod::calculateShippingFees(
            $shippingMethod,
            $countryCode,
            null
        );
        if ($shippingMethod->fEndpreis == -1) {
            return false;
        }
        $specialItem        = new stdClass();
        $specialItem->cName = [];
        foreach (Frontend::getLanguages() as $lang) {
            $loc = $this->db->select(
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
        $this->cart->erstelleSpezialPos(
            $specialItem->cName,
            1,
            $shippingCosts,
            $this->cart->gibVersandkostenSteuerklasse($countryCode),
            \C_WARENKORBPOS_TYP_VERSANDPOS,
            true,
            $taxItem
        );
        CartHelper::applyShippingFreeCoupon();
        $this->cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        if (isset($shippingMethod->Zuschlag->fZuschlag) && $shippingMethod->Zuschlag->fZuschlag != 0) {
            $specialItem->cName = [];
            foreach (Frontend::getLanguages() as $lang) {
                $loc                             = $this->db->select(
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
            $this->cart->erstelleSpezialPos(
                $specialItem->cName,
                1,
                $shippingMethod->Zuschlag->fZuschlag,
                $this->cart->gibVersandkostenSteuerklasse($countryCode),
                \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                true,
                $taxItem
            );
        }
        $_SESSION['Versandart']       = $shippingMethod;
        $_SESSION['AktiveVersandart'] = $shippingMethod->kVersandart;

        return true;
    }

    /**
     * @param string|null $context
     * @return bool
     * @former pruefeFehlendeAngaben()
     */
    public function pruefeFehlendeAngaben(?string $context = null): bool
    {
        $missingData = $this->smarty->getTemplateVars('fehlendeAngaben');
        if (!$context) {
            return !empty($missingData);
        }

        return (isset($missingData[$context])
            && \is_array($missingData[$context])
            && \count($missingData[$context]));
    }

    /**
     * @param array $missingData
     */
    public function setzeFehlendeAngaben(array $missingData): void
    {
        $all = $this->smarty->getTemplateVars('fehlendeAngaben');
        if (!\is_array($all)) {
            $all = [];
        }
        $this->smarty->assign('fehlendeAngaben', \array_merge($all, $missingData));
    }

    /**
     * @param int $noteCode
     * @return string
     * @todo: check if this is only used by the old EOS payment method
     * @former mappeBestellvorgangZahlungshinweis()
     */
    public function mappeBestellvorgangZahlungshinweis(int $noteCode): string
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

        \executeHook(\HOOK_BESTELLVORGANG_INC_MAPPEBESTELLVORGANGZAHLUNGSHINWEIS, [
            'cHinweis'     => &$note,
            'nHinweisCode' => $noteCode
        ]);

        return $note;
    }

    /**
     * @param string $step
     * @return array
     * @former gibBestellschritt()
     */
    public function gibBestellschritt(string $step): array
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
}
