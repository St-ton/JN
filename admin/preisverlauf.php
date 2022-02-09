<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);

if (Request::postInt('einstellungen') === 1) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST),
        'saveSettings'
    );
}
getAdminSectionSettings(CONF_PREISVERLAUF);
$smarty->display('preisverlauf.tpl');
