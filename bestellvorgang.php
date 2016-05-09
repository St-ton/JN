<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'trustedshops_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$AktuelleSeite = 'BESTELLVORGANG';
$Einstellungen = Shop::getSettings(array(
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDENFELD,
    CONF_TRUSTEDSHOPS,
    CONF_ARTIKELDETAILS
));
Shop::setPageType(PAGE_BESTELLVORGANG);
$step    = 'accountwahl';
$hinweis = '';
// Kill Ajaxcheckout falls vorhanden
unset($_SESSION['ajaxcheckout']);
// Loginbenutzer?
if (isset($_POST['login']) && (int)$_POST['login'] === 1) {
    fuehreLoginAus($_POST['userLogin'], $_POST['passLogin']);
}
if (verifyGPCDataInteger('basket2Pers') === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';

    setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
    header('Location: bestellvorgang.php?wk=1');
    exit();
}
// Ist Bestellung moeglich?
if ($_SESSION['Warenkorb']->istBestellungMoeglich() != 10) {
    pruefeBestellungMoeglich();
}
// Pflicht-Uploads vorhanden?
if (class_exists('Upload')) {
    if (!Upload::pruefeWarenkorbUploads($_SESSION['Warenkorb'])) {
        Upload::redirectWarenkorb(UPLOAD_ERROR_NEED_UPLOAD);
    }
}
// Download-Artikel vorhanden?
if (class_exists('Download')) {
    if (Download::hasDownloads($_SESSION['Warenkorb'])) {
        // Nur registrierte Benutzer
        $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] = 'N';
    }
}
// oneClick? Darf nur einmal ausgeführt werden und nur dann, wenn man vom Warenkorb kommt.
if ($Einstellungen['kaufabwicklung']['bestellvorgang_kaufabwicklungsmethode'] === 'NO' && verifyGPCDataInteger('wk') === 1) {
    $kKunde = 0;
    if (isset($_SESSION['Kunde']->kKunde)) {
        $kKunde = $_SESSION['Kunde']->kKunde;
    }
    $oWarenkorbPers = new WarenkorbPers($kKunde);
    if (!(count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0 && isset($_POST['login']) && (int)$_POST['login'] === 1 &&
        $Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' && $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P')
    ) {
        pruefeAjaxEinKlick();
    }
}
if (verifyGPCDataInteger('wk') === 1) {
    resetNeuKundenKupon();
}
//https? wenn erwuenscht reload mit https
pruefeHttps();

if (isset($_POST['versandartwahl']) && (int)$_POST['versandartwahl'] === 1) {
    pruefeVersandartWahl((isset($_POST['Versandart'])) ? $_POST['Versandart'] : null);
}
if (isset($_POST['unreg_form']) && (int)$_POST['unreg_form'] === 1 && $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
    pruefeUnregistriertBestellen($_POST);
}
if (isset($_GET['unreg']) && (int)$_GET['unreg'] === 1 && $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y') {
    $step = 'unregistriert bestellen';
}
if (isset($_POST['lieferdaten']) && (int)$_POST['lieferdaten'] === 1) {
    pruefeLieferdaten($_POST);
}
//autom. step ermitteln
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']) {
    $step = 'Lieferadresse';
}
//Download-Artikel vorhanden?
if (class_exists('Download')) {
    if (Download::hasDownloads($_SESSION['Warenkorb'])) {
        // Falls unregistrierter Kunde bereits im Checkout war und einen Downloadartikel hinzugefuegt hat
        if ((!isset($_SESSION['Kunde']->cPasswort) || strlen($_SESSION['Kunde']->cPasswort) === 0) && $step !== 'accountwahl') {
            $step = 'accountwahl';
            unset($_SESSION['Kunde']);
        }
    }
}
//autom. step ermitteln
pruefeVersandkostenStep();
//autom. step ermitteln
pruefeZahlungStep();
//autom. step ermitteln
pruefeBestaetigungStep();
//sondersteps Rechnungsadresse aendern
pruefeRechnungsadresseStep($_GET);
//sondersteps Lieferadresse aendern
pruefeLieferadresseStep($_GET);
//sondersteps Versandart aendern
pruefeVersandartStep($_GET);
//sondersteps Zahlungsart aendern
pruefeZahlungsartStep($_GET);
pruefeZahlungsartwahlStep($_POST);

if ($step === 'accountwahl') {
    gibStepAccountwahl();
}
if ($step === 'unregistriert bestellen') {
    gibStepUnregistriertBestellen();
}
if ($step === 'Lieferadresse') {
    validateCouponInCheckout();
    gibStepLieferadresse();
}
if ($step === 'Versand') {
    gibStepVersand();
}
if ($step === 'Zahlung') {
    gibStepZahlung();
}
if ($step === 'ZahlungZusatzschritt') {
    gibStepZahlungZusatzschritt($_POST);
}
if ($step === 'Bestaetigung') {
    plausiGuthaben($_POST);
    $smarty->assign('cKuponfehler_arr', plausiKupon($_POST));
    //evtl genutztes guthaben anpassen
    pruefeGuthabenNutzen();
    gibStepBestaetigung($_GET);
    $_SESSION['Warenkorb']->cEstimatedDelivery = $_SESSION['Warenkorb']->getEstimatedDeliveryTime();
}
//SafetyPay Work Around
if (isset($_SESSION['Zahlungsart']->cModulId) && $_SESSION['Zahlungsart']->cModulId === 'za_safetypay' && $step === 'Bestaetigung') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/safetypay.php';
    $smarty->assign('safetypay_form', gib_safetypay_form($_SESSION['Kunde'], $_SESSION['Warenkorb'], $Einstellungen['zahlungsarten']));
}
//Billpay
if (isset($_SESSION['Zahlungsart']) && $_SESSION['Zahlungsart']->cModulId === 'za_billpay_jtl' && $step === 'Bestaetigung') {
    $paymentMethod = PaymentMethod::create('za_billpay_jtl');
    $paymentMethod->handleConfirmation();
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
//specific assigns
$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('AGB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe))
       ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
       ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
       ->assign('Einstellungen', $Einstellungen)
       ->assign('hinweis', $hinweis)
       ->assign('step', $step)
       ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
       ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren())
       ->assign('Steuerpositionen', $_SESSION['Warenkorb']->gibSteuerpositionen())
       ->assign('bestellschritt', gibBestellschritt($step))
       ->assign('requestURL', (isset($requestURL) ? $requestURL : null))
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_BESTELLVORGANG_PAGE);

$smarty->display('checkout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
