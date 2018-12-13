<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\RequestHelper;
use Helpers\VersandartHelper;
use Helpers\WarenkorbHelper;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'trustedshops_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';

Shop::setPageType(PAGE_BESTELLVORGANG);
$Einstellungen = Shopsetting::getInstance()->getAll();
$step          = 'accountwahl';
$cHinweis      = '';
$cart          = \Session\Session::getCart();
unset($_SESSION['ajaxcheckout']);
// Loginbenutzer?
if (isset($_POST['login']) && (int)$_POST['login'] === 1) {
    fuehreLoginAus($_POST['email'], $_POST['passwort']);
}
if (RequestHelper::verifyGPCDataInt('basket2Pers') === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';

    setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
    header('Location: bestellvorgang.php?wk=1');
    exit();
}
// Ist Bestellung moeglich?
if ($cart->istBestellungMoeglich() !== 10) {
    pruefeBestellungMoeglich();
}
// Pflicht-Uploads vorhanden?
if (class_exists('Upload') && !Upload::pruefeWarenkorbUploads($cart)) {
    Upload::redirectWarenkorb(UPLOAD_ERROR_NEED_UPLOAD);
}
// Download-Artikel vorhanden?
if (class_exists('Download') && Download::hasDownloads($cart)) {
    // Nur registrierte Benutzer
    $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] = 'N';
}
// oneClick? Darf nur einmal ausgeführt werden und nur dann, wenn man vom Warenkorb kommt.
if ($Einstellungen['kaufabwicklung']['bestellvorgang_kaufabwicklungsmethode'] === 'NO'
    && RequestHelper::verifyGPCDataInt('wk') === 1
) {
    $kKunde = 0;
    if (isset($_SESSION['Kunde']->kKunde)) {
        $kKunde = $_SESSION['Kunde']->kKunde;
    }
    $oWarenkorbPers = new WarenkorbPers($kKunde);
    if (!(isset($_POST['login']) && (int)$_POST['login'] === 1
        && $Einstellungen['global']['warenkorbpers_nutzen'] === 'Y'
        && $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P'
        && count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0)
    ) {
        pruefeAjaxEinKlick();
    }
}
if (RequestHelper::verifyGPCDataInt('wk') === 1) {
    Kupon::resetNewCustomerCoupon();
}
if (isset($_FILES['vcard'])
    && $Einstellungen['kunden']['kundenregistrierung_vcardupload'] === 'Y'
    && FormHelper::validateToken()
) {
    gibKundeFromVCard($_FILES['vcard']['tmp_name']);
}
if (isset($_POST['unreg_form'])
    && (int)$_POST['unreg_form'] === 1
    && $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y'
) {
    pruefeUnregistriertBestellen($_POST);
}
if (isset($_GET['editLieferadresse'])) {
    // Shipping address and customer address are now on same site
    $_GET['editRechnungsadresse'] = $_GET['editLieferadresse'];
}
if (isset($_POST['unreg_form']) && (int)$_POST['unreg_form'] === 0) {
    $_POST['checkout'] = 1;
    $_POST['form']     = 1;
    include PFAD_ROOT . 'registrieren.php';
}
if (isset($_GET['kZahlungsart']) && (int)$_GET['kZahlungsart'] > 0) {
    zahlungsartKorrekt((int)$_GET['kZahlungsart']);
}
if ((isset($_POST['versandartwahl']) && (int)$_POST['versandartwahl'] === 1) || isset($_GET['kVersandart'])) {
    unset($_SESSION['Zahlungsart']);
    $kVersandart = null;

    if (isset($_GET['kVersandart'])) {
        $kVersandart = (int)$_GET['kVersandart'];
    } elseif (isset($_POST['Versandart'])) {
        $kVersandart = (int)$_POST['Versandart'];
    }

    pruefeVersandartWahl($kVersandart);
}
if (isset($_GET['unreg'])
    && (int)$_GET['unreg'] === 1
    && $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y'
) {
    $step = 'edit_customer_address';
}
//autom. step ermitteln
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']) {
    if (!isset($_SESSION['Lieferadresse'])) {
        pruefeLieferdaten(['kLieferadresse' => 0]);
    }

    if (!isset($_SESSION['Versandart']) || !is_object($_SESSION['Versandart'])) {
        $land          = $_SESSION['Lieferadresse']->cLand ?? $_SESSION['Kunde']->cLand;
        $plz           = $_SESSION['Lieferadresse']->cPLZ ?? $_SESSION['Kunde']->cPLZ;
        $kKundengruppe = \Session\Session::getCustomerGroup()->getID();

        $oVersandart_arr  = VersandartHelper::getPossibleShippingMethods(
            $land,
            $plz,
            VersandartHelper::getShippingClasses($cart),
            $kKundengruppe
        );
        $activeVersandart = gibAktiveVersandart($oVersandart_arr);

        pruefeVersandartWahl(
            $activeVersandart,
            ['kVerpackung' => array_keys(gibAktiveVerpackung(VersandartHelper::getPossiblePackagings($kKundengruppe)))]
        );
    }
}
// Download-Artikel vorhanden?
if (class_exists('Download') && Download::hasDownloads($cart)) {
    if ($step !== 'accountwahl' && empty($_SESSION['Kunde']->cPasswort)) {
        // Falls unregistrierter Kunde bereits im Checkout war und einen Downloadartikel hinzugefuegt hat
        $step      = 'accountwahl';
        $cHinweis  = Shop::Lang()->get('digitalProductsRegisterInfo', 'checkout');
        $cPost_arr = StringHandler::filterXSS($_POST);

        Shop::Smarty()->assign('cKundenattribut_arr', getKundenattribute($cPost_arr))
            ->assign('kLieferadresse', $cPost_arr['kLieferadresse'])
            ->assign('cPost_var', $cPost_arr);

        if ((int)$cPost_arr['shipping_address'] === 1) {
            Shop::Smarty()->assign(
                'Lieferadresse',
                mappeLieferadresseKontaktdaten($cPost_arr['register']['shipping_address'])
            );
        }

        unset($_SESSION['Kunde']);
    } elseif ($step === 'accountwahl') {
        $cHinweis .= Shop::Lang()->get('digitalProductsRegisterInfo', 'checkout');
    }
}
// autom. step ermitteln
pruefeVersandkostenStep();
// autom. step ermitteln
pruefeZahlungStep();
// autom. step ermitteln
pruefeBestaetigungStep();
// sondersteps Rechnungsadresse aendern
pruefeRechnungsadresseStep($_GET);
// sondersteps Lieferadresse aendern
pruefeLieferadresseStep($_GET);
// sondersteps Versandart aendern
pruefeVersandartStep($_GET);
// sondersteps Zahlungsart aendern
pruefeZahlungsartStep($_GET);
pruefeZahlungsartwahlStep($_POST);

if ($step === 'accountwahl') {
    gibStepAccountwahl();
    gibStepUnregistriertBestellen();
    gibStepLieferadresse();
}
if ($step === 'edit_customer_address' || $step === 'Lieferadresse') {
    validateCouponInCheckout();
    gibStepUnregistriertBestellen();
    gibStepLieferadresse();
}
if ($step === 'Versand' || $step === 'Zahlung') {
    gibStepVersand();
    gibStepZahlung();
    Warenkorb::refreshChecksum($cart);
}
if ($step === 'ZahlungZusatzschritt') {
    gibStepZahlungZusatzschritt($_POST);
    Warenkorb::refreshChecksum($cart);
}
if ($step === 'Bestaetigung') {
    plausiGuthaben($_POST);
    Shop::Smarty()->assign('cKuponfehler_arr', plausiKupon($_POST));
    //evtl genutztes guthaben anpassen
    pruefeGuthabenNutzen();
    // Eventuellen Zahlungsarten Aufpreis/Rabatt neusetzen
    getPaymentSurchageDiscount($_SESSION['Zahlungsart']);
    gibStepBestaetigung($_GET);
    $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();
    Warenkorb::refreshChecksum($cart);
}
// Billpay
if (isset($_SESSION['Zahlungsart'])
    && $_SESSION['Zahlungsart']->cModulId === 'za_billpay_jtl'
    && $step === 'Bestaetigung'
) {
    /** @var Billpay $paymentMethod */
    $paymentMethod = PaymentMethod::create('za_billpay_jtl');
    $paymentMethod->handleConfirmation();
}
if ($step === 'Bestaetigung'
    && $cart->gibGesamtsummeWaren(true) === 0.0
) {
    $savedPayment   = $_SESSION['AktiveZahlungsart'];
    $oPaymentMethod = PaymentMethod::create('za_null_jtl');
    zahlungsartKorrekt($oPaymentMethod->kZahlungsart);

    if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
        || (isset($cPost_arr['guthabenVerrechnen']) && (int)$cPost_arr['guthabenVerrechnen'] === 1)
    ) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            $_SESSION['Kunde']->fGuthaben,
            \Session\Session::getCart()->gibGesamtsummeWaren(true, false)
        );
    }
    Warenkorb::refreshChecksum($cart);
    $_SESSION['AktiveZahlungsart'] = $savedPayment;
}
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$linkHelper = Shop::Container()->getLinkService();
$kLink      = $linkHelper->getSpecialPageLinkKey(LINKTYP_BESTELLVORGANG);
$link       = $linkHelper->getPageLink($kLink);
WarenkorbHelper::addVariationPictures($cart);
Shop::Smarty()->assign(
    'AGB',
    Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        \Session\Session::getCustomerGroup()->getID()
    )
)
    ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
    ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
    ->assign('Link', $link)
    ->assign('hinweis', $cHinweis)
    ->assign('step', $step)
    ->assign('editRechnungsadresse', RequestHelper::verifyGPCDataInt('editRechnungsadresse'))
    ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
    ->assign('Warensumme', $cart->gibGesamtsummeWaren())
    ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
    ->assign('bestellschritt', gibBestellschritt($step))
    ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
    ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
    ->assign('unregForm', RequestHelper::verifyGPCDataInt('unreg_form'));

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_BESTELLVORGANG_PAGE);

Shop::Smarty()->display('checkout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
