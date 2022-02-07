<?php declare(strict_types=1);

use JTL\Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);

if (Request::postInt('einstellungen') === 1) {
    saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST);
}
getAdminSectionSettings(CONF_PREISVERLAUF);
$smarty->display('preisverlauf.tpl');
