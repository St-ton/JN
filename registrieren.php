<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && verifyGPCDataInteger('editRechnungsadresse') === 0) {
    header('Location: jtl.php?', true, 301);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';

$AktuelleSeite = 'REGISTRIEREN';
$Einstellungen = Shop::getSettings(
    array(
        CONF_GLOBAL,
        CONF_RSS,
        CONF_KUNDEN,
        CONF_KUNDENFELD,
        CONF_KUNDENWERBENKUNDEN,
        CONF_NEWSLETTER
    )
);
pruefeHttps();
Shop::setPageType(PAGE_REGISTRIERUNG);
$linkHelper           = LinkHelper::getInstance();
$kLink                = $linkHelper->getSpecialPageLinkKey(LINKTYP_REGISTRIEREN);
$step                 = 'formular';
$hinweis              = '';
$titel                = Shop::Lang()->get('newAccount', 'login');
$editRechnungsadresse = (isset($_GET['editRechnungsadresse'])) ? intval($_GET['editRechnungsadresse']) : 0;
if (isset($_POST['editRechnungsadresse'])) {
    $editRechnungsadresse = (int)$_POST['editRechnungsadresse'];
}
// Kunde speichern
if (isset($_POST['form']) && intval($_POST['form']) === 1) {
    kundeSpeichern($_POST);
}
// Kunde ändern
if (isset($_GET['editRechnungsadresse']) && intval($_GET['editRechnungsadresse']) === 1) {
    gibKunde();
}
if ($step === 'formular') {
    gibFormularDaten(verifyGPCDataInteger('checkout'));
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
//specific assigns
$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('editRechnungsadresse', $editRechnungsadresse)
       ->assign('Ueberschrift', $titel)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('hinweis', $hinweis)
       ->assign('step', $step)
       ->assign('sess', $_SESSION)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
       ->assign('code_registrieren', generiereCaptchaCode($Einstellungen['kunden']['registrieren_captcha']));

$cCanonicalURL = Shop::getURL() . '/registrieren.php';
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_REGISTRIEREN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
//Zum prüfen wie lange ein User/Bot gebraucht hat um das Registrieren-Formular auszufüllen
if (isset($Einstellungen['kunden']['kundenregistrierung_pruefen_zeit']) && $Einstellungen['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y') {
    $_SESSION['dRegZeit'] = time();
}

executeHook(HOOK_REGISTRIEREN_PAGE);

$smarty->display('register/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
