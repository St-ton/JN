<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';

if (isset($_GET['f']) && (int)$_GET['f'] === 1 && FormHelper::validateToken()) {
    if (generiereRSSXML()) {
        $cHinweis = 'RSS Feed wurde erstellt!';
    } else {
        $cFehler = 'RSS Feed konnte nicht erstellt werden!';
    }
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_RSS, $_POST);
}
if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $cFehler = 'Datei "' . PFAD_ROOT . FILE_RSS_FEED . '" kann nicht geschrieben werden. 
        Bitte achten Sie darauf, dass diese Datei ausreichende Schreibrechte besitzt.
        Ansonsten kann keine RSS XML Datei erstellt werden.';
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_RSS))
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('rss.tpl');
