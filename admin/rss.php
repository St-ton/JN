<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';

if (isset($_GET['f']) && (int)$_GET['f'] === 1 && Form::validateToken()) {
    if (generiereRSSXML()) {
        $cHinweis = __('successRSSCreate');
    } else {
        $cFehler = __('errorRSSCreate');
    }
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_RSS, $_POST);
}
if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $cFehler = sprintf(__('errorRSSCreatePermissions'), PFAD_ROOT . FILE_RSS_FEED);
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_RSS))
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('rss.tpl');
