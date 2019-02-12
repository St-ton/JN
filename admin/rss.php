<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
$alertHelper = Shop::Container()->getAlertService();
if (isset($_GET['f']) && (int)$_GET['f'] === 1 && Form::validateToken()) {
    if (generiereRSSXML()) {
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successRSSCreate'), 'successRSSCreate');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorRSSCreate'), 'errorRSSCreate');
    }
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_NOTE,
        saveAdminSectionSettings(CONF_RSS, $_POST),
        'saveSettings'
    );
    $cHinweis .= saveAdminSectionSettings(CONF_RSS, $_POST);
}
if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        sprintf(__('errorRSSCreatePermissions'), PFAD_ROOT . FILE_RSS_FEED),
        'errorRSSCreatePermissions'
    );
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_RSS))
       ->display('rss.tpl');
