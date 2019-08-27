<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */

if (Request::postInt('einstellungen') === 1) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST),
        'saveSettings'
    );
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_PREISVERLAUF))
    ->display('preisverlauf.tpl');

/**
 * @param string $colorCode
 * @return string
 */
function checkeFarbCode($colorCode)
{
    if (preg_match('/#[A-Fa-f0-9]{6}/', $colorCode) == 1) {
        return $colorCode;
    }
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, __('errorColorCode'), 'errorColorCode');

    return '#000000';
}
