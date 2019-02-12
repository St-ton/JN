<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST),
        'saveSettings'
    );
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_PREISVERLAUF))
       ->assign('sprachen', Sprache::getAllLanguages())
       ->display('preisverlauf.tpl');

/**
 * @param string $cFarbCode
 * @return string
 */
function checkeFarbCode($cFarbCode)
{
    if (preg_match('/#[A-Fa-f0-9]{6}/', $cFarbCode) == 1) {
        return $cFarbCode;
    }
    $GLOBALS['cfehler'] = __('errorColorCode');

    return '#000000';
}
