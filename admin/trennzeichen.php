<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\PlausiTrennzeichen;
use JTL\Shop;
use JTL\Sprache;
use JTL\Catalog\Trennzeichen;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SEPARATOR_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'trennzeichen_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
setzeSprache();

$step        = 'trennzeichen_uebersicht';
$alertHelper = Shop::Container()->getAlertService();
if (Request::verifyGPCDataInt('save') === 1 && Form::validateToken()) {
    $oPlausiTrennzeichen = new PlausiTrennzeichen();
    $oPlausiTrennzeichen->setPostVar($_POST);
    $oPlausiTrennzeichen->doPlausi();

    $xPlausiVar_arr = $oPlausiTrennzeichen->getPlausiVar();
    if (count($xPlausiVar_arr) === 0) {
        if (speicherTrennzeichen($_POST)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorConfigSave'), 'errorConfigSave');
            $smarty->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        $idx = 'nDezimal_' . JTL_SEPARATOR_WEIGHT;
        if (isset($xPlausiVar_arr[$idx]) && $xPlausiVar_arr[$idx] === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorWeightDecimals'), 'errorWeightDecimals');
        }
        $idx = 'nDezimal_' . JTL_SEPARATOR_AMOUNT;
        if (isset($xPlausiVar_arr[$idx]) && $xPlausiVar_arr[$idx] === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAmountDecimals'), 'errorAmountDecimals');
        }
        $smarty->assign('xPlausiVar_arr', $oPlausiTrennzeichen->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
    }
}

$smarty->assign('step', $step)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('oTrennzeichenAssoc_arr', Trennzeichen::getAll($_SESSION['kSprache']))
       ->display('trennzeichen.tpl');
