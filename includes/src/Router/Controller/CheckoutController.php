<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Alert\Alert;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Checkout\Kupon;
use JTL\Customer\AccountController;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Order;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CheckoutController
 * @package JTL\Router\Controller
 */
class CheckoutController extends PageController
{
    public function init(): bool
    {
        parent::init();
        Shop::setPageType(\PAGE_BESTELLVORGANG);

        return true;
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'registrieren_inc.php';

        global $step; // @todo

        $_SESSION['deliveryCountryPrefLocked'] = true;

        $customerID  = Frontend::getCustomer()->getID();
        $step        = 'accountwahl';
        $cart        = Frontend::getCart();
        $linkService = Shop::Container()->getLinkService();
        $controller  = new AccountController($this->db, $this->alertService, $linkService, $smarty);
        $valid       = Form::validateToken();

        unset($_SESSION['ajaxcheckout']);
        if (Request::postInt('login') === 1) {
            $controller->login($_POST['email'], $_POST['passwort']);
        }
        if (Request::verifyGPCDataInt('basket2Pers') === 1) {
            $controller->setzeWarenkorbPersInWarenkorb($customerID);
            \header('Location: bestellvorgang.php?wk=1');
            exit();
        }
        if ($cart->istBestellungMoeglich() !== 10) {
            \pruefeBestellungMoeglich();
        }
        if (!Upload::pruefeWarenkorbUploads($cart)) {
            Upload::redirectWarenkorb(\UPLOAD_ERROR_NEED_UPLOAD);
        }
        if (Download::hasDownloads($cart)) {
            // Nur registrierte Benutzer
            $this->config['kaufabwicklung']['bestellvorgang_unregistriert'] = 'N';
        }
        // oneClick? Darf nur einmal ausgeführt werden und nur dann, wenn man vom Warenkorb kommt.
        if (!isset($_SESSION['Lieferadresse'])
            && $this->config['kaufabwicklung']['bestellvorgang_kaufabwicklungsmethode'] === 'NO'
            && Request::verifyGPCDataInt('wk') === 1
        ) {
            $persCart = new PersistentCart($customerID);
            if (!(Request::postInt('login') === 1
                && $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y'
                && $this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P'
                && count($persCart->getItems()) > 0)
            ) {
                \pruefeAjaxEinKlick();
            }
        }
        if (Request::verifyGPCDataInt('wk') === 1) {
            Kupon::resetNewCustomerCoupon();
        }

        if ($valid && Request::postInt('unreg_form') === 1) {
            if ($this->config['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
                \pruefeUnregistriertBestellen($_POST);
            } elseif (isset($_POST['shipping_address'], $_POST['register']['shipping_address'])) {
                \checkNewShippingAddress($_POST);
            } elseif (Request::postInt('kLieferadresse') > 0) {
                \pruefeLieferdaten($_POST);
            } elseif (Request::postInt('shipping_address') === 0) {
                $missingInput = \getMissingInput($_POST);
                \pruefeLieferdaten($_POST, $missingInput);
            }
        }
        if (isset($_GET['editLieferadresse'])) {
            // Shipping address and customer address are now on same site
            $_GET['editRechnungsadresse'] = Request::getInt($_GET['editLieferadresse']);
        }
        if (Request::postInt('unreg_form', -1) === 0) {
            $_POST['checkout'] = 1;
            $_POST['form']     = 1;
            include PFAD_ROOT . 'registrieren.php';
        }
        if (($paymentMethodID = Request::getInt('kZahlungsart')) > 0) {
            \zahlungsartKorrekt($paymentMethodID);
        }
        if (Request::postInt('versandartwahl') === 1 || isset($_GET['kVersandart'])) {
            unset($_SESSION['Zahlungsart']);
            \pruefeVersandartWahl(Request::verifyGPCDataInt('kVersandart'));
        }
        if (Request::getInt('unreg') === 1 && $this->config['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
            $step = 'edit_customer_address';
        }
        //autom. step ermitteln
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']) {
            if (!isset($_SESSION['Lieferadresse'])) {
                \pruefeLieferdaten([
                    'kLieferadresse' => Order::getLastOrderRefIDs($customerID)->kLieferadresse
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
                    ShippingMethod::getShippingClasses($cart),
                    $this->customerGroupID
                );

                if (empty($shippingMethods)) {
                    $this->alertService->addDanger(
                        Shop::Lang()->get('noShippingAvailable', 'checkout'),
                        'noShippingAvailable'
                    );
                } else {
                    $activeVersandart = \gibAktiveVersandart($shippingMethods);
                    \pruefeVersandartWahl(
                        $activeVersandart,
                        ['kVerpackung' => \array_keys(
                            \gibAktiveVerpackung(ShippingMethod::getPossiblePackagings($this->customerGroupID))
                        )]
                    );
                }
            }
        }
        if (empty($_SESSION['Kunde']->cPasswort) && Download::hasDownloads($cart)) {
            // Falls unregistrierter Kunde bereits im Checkout war und einen Downloadartikel hinzugefuegt hat
            $step = 'accountwahl';

            $this->alertService->addNotice(
                Shop::Lang()->get('digitalProductsRegisterInfo', 'checkout'),
                'digiProdRegisterInfo'
            );

            unset($_SESSION['Kunde']);
            // unset not needed values to ensure the correct $step
            $_POST = [];
            if (isset($_GET['editRechnungsadresse'])) {
                unset($_GET['editRechnungsadresse']);
            }
        }
        // autom. step ermitteln
        $step = \pruefeVersandkostenStep($step);
        // autom. step ermitteln
        $step = \pruefeZahlungStep($step);
        // autom. step ermitteln
        $step = \pruefeBestaetigungStep($step);
        // sondersteps Rechnungsadresse aendern
        $step = \pruefeRechnungsadresseStep(Text::filterXSS($_GET), $step);
        // sondersteps Lieferadresse aendern
        $step = \pruefeLieferadresseStep(Text::filterXSS($_GET), $step);
        // sondersteps Versandart aendern
        $step = \pruefeVersandartStep(Text::filterXSS($_GET), $step);
        // sondersteps Zahlungsart aendern
        $step = \pruefeZahlungsartStep(Text::filterXSS($_GET), $step);
        $step = \pruefeZahlungsartwahlStep(Text::filterXSS($_POST), $step);

        if ($step === 'accountwahl') {
            \gibStepAccountwahl($smarty);
            \gibStepUnregistriertBestellen($smarty);
            \gibStepLieferadresse();
        }
        if ($step === 'edit_customer_address' || $step === 'Lieferadresse') {
            \validateCouponInCheckout();
            \gibStepUnregistriertBestellen($smarty);
            \gibStepLieferadresse();
        }
        if ($step === 'Versand' || $step === 'Zahlung') {
            \validateCouponInCheckout();
            \gibStepVersand();
            \gibStepZahlung();
            Cart::refreshChecksum($cart);
        }
        if ($step === 'ZahlungZusatzschritt') {
            \gibStepZahlungZusatzschritt($_POST);
            Cart::refreshChecksum($cart);
        }
        if ($step === 'Bestaetigung') {
            \validateCouponInCheckout();
            \plausiGuthaben($_POST);
            \plausiKupon($_POST);
            //evtl genutztes guthaben anpassen
            \pruefeGuthabenNutzen();
            // Eventuellen Zahlungsarten Aufpreis/Rabatt neusetzen
            \getPaymentSurchageDiscount($_SESSION['Zahlungsart']);
            \gibStepBestaetigung(Text::filterXSS($_GET));
            $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();
            Cart::refreshChecksum($cart);
        }
        if ($step === 'Bestaetigung' && $cart->gibGesamtsummeWaren(true) === 0.0) {
            $savedPayment  = $_SESSION['AktiveZahlungsart'];
            $paymentMethod = LegacyMethod::create('za_null_jtl');
            \zahlungsartKorrekt($paymentMethod->kZahlungsart ?? 0);

            if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
                || Request::postInt('guthabenVerrechnen') === 1
            ) {
                $_SESSION['Bestellung']->GuthabenNutzen   = 1;
                $_SESSION['Bestellung']->fGuthabenGenutzt = Order::getOrderCredit($_SESSION['Bestellung']);
            }
            Cart::refreshChecksum($cart);
            $_SESSION['AktiveZahlungsart'] = $savedPayment;
        }
        CartHelper::addVariationPictures($cart);
        $smarty->assign(
            'AGB',
            Shop::Container()->getLinkService()->getAGBWRB($this->languageID, $this->customerGroupID)
        )
            ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
            ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
            ->assign('Link', $linkService->getSpecialPage(\LINKTYP_BESTELLVORGANG))
            ->assign('alertNote', $this->alertService->alertTypeExists(Alert::TYPE_NOTE))
            ->assign('step', $step)
            ->assign(
                'editRechnungsadresse',
                Frontend::getCustomer()->nRegistriert === 1 ? 1 : Request::verifyGPCDataInt('editRechnungsadresse')
            )
            ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
            ->assign('Warensumme', $cart->gibGesamtsummeWaren())
            ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
            ->assign('bestellschritt', \gibBestellschritt($step))
            ->assign('unregForm', Request::verifyGPCDataInt('unreg_form'))
            ->assignDeprecated('C_WARENKORBPOS_TYP_ARTIKEL', \C_WARENKORBPOS_TYP_ARTIKEL, '5.0.0')
            ->assignDeprecated('C_WARENKORBPOS_TYP_GRATISGESCHENK', \C_WARENKORBPOS_TYP_GRATISGESCHENK, '5.0.0');

        $this->preRender($smarty);
        \executeHook(\HOOK_BESTELLVORGANG_PAGE);

        return $smarty->getResponse('checkout/index.tpl');
    }
}
