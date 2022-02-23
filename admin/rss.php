<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
$alertService = Shop::Container()->getAlertService();
if (Request::getInt('f') === 1 && Form::validateToken()) {
    if (generiereRSSXML()) {
        $alertService->addAlert(Alert::TYPE_SUCCESS, __('successRSSCreate'), 'successRSSCreate');
    } else {
        $alertService->addAlert(Alert::TYPE_ERROR, __('errorRSSCreate'), 'errorRSSCreate');
    }
}
if (Request::postInt('einstellungen') > 0) {
    saveAdminSectionSettings(CONF_RSS, $_POST);
}
if (!file_exists(PFAD_ROOT . FILE_RSS_FEED)) {
    @touch(PFAD_ROOT . FILE_RSS_FEED);
}
if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $alertService->addAlert(
        Alert::TYPE_ERROR,
        sprintf(__('errorRSSCreatePermissions'), PFAD_ROOT . FILE_RSS_FEED),
        'errorRSSCreatePermissions'
    );
}
getAdminSectionSettings(CONF_RSS);
$smarty->assign('alertError', $alertService->alertTypeExists(Alert::TYPE_ERROR))
    ->display('rss.tpl');
