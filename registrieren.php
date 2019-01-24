<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
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
$conf  = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KUNDENFELD,
    CONF_KUNDENWERBENKUNDEN,
    CONF_NEWSLETTER
]);
$kLink = $linkHelper->getSpecialPageLinkKey(LINKTYP_REGISTRIEREN);
$link  = $linkHelper->getPageLink($kLink);
$step  = 'formular';
$titel = Shop::Lang()->get('newAccount', 'login');
$edit  = isset($_GET['editRechnungsadresse'])
    ? (int)$_GET['editRechnungsadresse']
    : 0;
if (isset($_POST['editRechnungsadresse'])) {
    $edit = (int)$_POST['editRechnungsadresse'];
}
if (isset($_POST['form']) && (int)$_POST['form'] === 1) {
    kundeSpeichern($_POST);
}
if (isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] === 1) {
    gibKunde();
}
if ($step === 'formular') {
    gibFormularDaten(Request::verifyGPCDataInt('checkout'));
}
if (isset($_FILES['vcard'])
    && $conf['kunden']['kundenregistrierung_vcardupload'] === 'Y'
    && Form::validateToken()
) {
    gibKundeFromVCard($_FILES['vcard']['tmp_name']);
}
Shop::Smarty()->assign('editRechnungsadresse', $edit)
    ->assign('Ueberschrift', $titel)
    ->assign('Link', $link)
    ->assign('step', $step)
    ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
    ->assign('code_registrieren', false)
    ->assign('unregForm', 0);

$cCanonicalURL = $linkHelper->getStaticRoute('registrieren.php');

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
if (isset($conf['kunden']['kundenregistrierung_pruefen_zeit'])
    && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
) {
    $_SESSION['dRegZeit'] = time();
}

executeHook(HOOK_REGISTRIEREN_PAGE);

Shop::Smarty()->display('register/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
