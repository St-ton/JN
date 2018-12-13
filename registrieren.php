<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';

$linkHelper = Shop::Container()->getLinkService();
if (isset($_SESSION['Kunde']->kKunde)
    && $_SESSION['Kunde']->kKunde > 0
    && Request::verifyGPCDataInt('editRechnungsadresse') === 0
) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 301);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';

Shop::setPageType(PAGE_REGISTRIERUNG);
$Einstellungen        = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KUNDENFELD,
    CONF_KUNDENWERBENKUNDEN,
    CONF_NEWSLETTER
]);
$kLink                = $linkHelper->getSpecialPageLinkKey(LINKTYP_REGISTRIEREN);
$link                 = $linkHelper->getPageLink($kLink);
$step                 = 'formular';
$hinweis              = '';
$titel                = Shop::Lang()->get('newAccount', 'login');
$editRechnungsadresse = isset($_GET['editRechnungsadresse'])
    ? (int)$_GET['editRechnungsadresse']
    : 0;
if (isset($_POST['editRechnungsadresse'])) {
    $editRechnungsadresse = (int)$_POST['editRechnungsadresse'];
}
if (isset($_POST['form']) && (int)$_POST['form'] === 1) {
    kundeSpeichern($_POST);
}
// Kunde ändern
if (isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] === 1) {
    gibKunde();
}
if ($step === 'formular') {
    gibFormularDaten(Request::verifyGPCDataInt('checkout'));
}
if (isset($_FILES['vcard'])
    && $Einstellungen['kunden']['kundenregistrierung_vcardupload'] === 'Y'
    && FormHelper::validateToken()
) {
    gibKundeFromVCard($_FILES['vcard']['tmp_name']);
}
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
Shop::Smarty()->assign('editRechnungsadresse', $editRechnungsadresse)
    ->assign('Ueberschrift', $titel)
    ->assign('Link', $link)
    ->assign('hinweis', $hinweis)
    ->assign('step', $step)
    ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
    ->assign('code_registrieren', false)
    ->assign('unregForm', 0);

$cCanonicalURL    = $linkHelper->getStaticRoute('registrieren.php');
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_REGISTRIEREN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
if (isset($Einstellungen['kunden']['kundenregistrierung_pruefen_zeit'])
    && $Einstellungen['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
) {
    $_SESSION['dRegZeit'] = time();
}

executeHook(HOOK_REGISTRIEREN_PAGE);

Shop::Smarty()->display('register/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
