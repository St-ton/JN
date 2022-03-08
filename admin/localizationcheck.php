<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\LocalizationCheck\LocalizationCheckFactory;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('DIAGNOSTIC_VIEW', true, true);
$action = Request::postVar('action');
$type   = Request::postVar('type');
if ($action === 'deleteExcess' && $type !== null && Form::validateToken()) {
    $languages = \collect(LanguageHelper::getAllLanguages(0, true, true));
    $factory   = new LocalizationCheckFactory(Shop::Container()->getDB(), $languages);
    $check     = $factory->getCheckByClassName($type);
    if ($check === null) {
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, 'nope', 'clearerr');
    }
    $deleted = $check->deleteExcessLocalizations();
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('Deleted %d item(s).'), $deleted),
        'clearsuccess'
    );
}
$status       = Status::getInstance(Shop::Container()->getDB(), Shop::Container()->getCache());
$checkResults = $status->getLocalizationProblems(false);
$smarty->assign('passed', false)
    ->assign('checkResults', $checkResults)
    ->display('localizationcheck.tpl');
