<?php declare(strict_types=1);

use JTL\Catalog\Separator;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\PlausiTrennzeichen;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_SEPARATOR_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'trennzeichen_inc.php';
setzeSprache();

$step        = 'trennzeichen_uebersicht';
$alertHelper = Shop::Container()->getAlertService();
if (Request::verifyGPCDataInt('save') === 1 && Form::validateToken()) {
    $checks = new PlausiTrennzeichen();
    $checks->setPostVar($_POST);
    $checks->doPlausi();
    $checkItems = $checks->getPlausiVar();
    if (count($checkItems) === 0) {
        if (speicherTrennzeichen($_POST)) {
            $alertHelper->addSuccess(__('successConfigSave'), 'successConfigSave');
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        } else {
            $alertHelper->addError(__('errorConfigSave'), 'errorConfigSave');
            $smarty->assign('xPostVar_arr', $checks->getPostVar());
        }
    } else {
        $alertHelper->addError(__('errorFillRequired'), 'errorFillRequired');
        $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
            ->assign('xPostVar_arr', $checks->getPostVar());
    }
}

$smarty->assign('step', $step)
    ->assign('oTrennzeichenAssoc_arr', Separator::getAll($_SESSION['editLanguageID']))
    ->display('trennzeichen.tpl');
