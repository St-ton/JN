<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global \JTL\Backend\AdminAccount $oAccount
 * @global \JTL\Smarty\JTLSmarty     $smarty
 */

use JTL\Helpers\Form;
use JTL\Update\DBMigrationHelper;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$errorMsg          = '';
$dbErrors          = [];
$dbFileStruct      = getDBFileStruct();
$maintenanceResult = null;
$engineUpdate      = null;
$fulltextIndizes   = null;

if (isset($_POST['update']) && Text::filterXSS($_POST['update']) === 'script' && Form::validateToken()) {
    $scriptName = 'innodb_and_utf8_update_'
        . str_replace('.', '_', Shop::Container()->getDB()->getConfig()['host']) . '_'
        . Shop::Container()->getDB()->getConfig()['database'] . '_'
        . date('YmdHis') . '.sql';

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $scriptName . '"');
    echo doEngineUpdateScript($scriptName, array_keys($dbFileStruct));

    exit;
}

$dbStruct = getDBStruct(true, true);
$conf     = Shop::getSettings([
    CONF_GLOBAL,
    CONF_ARTIKELUEBERSICHT
]);

if (!empty($_POST['action']) && !empty($_POST['check'])) {
    $maintenanceResult = doDBMaintenance($_POST['action'], $_POST['check']);
}

if (empty($dbFileStruct)) {
    $errorMsg = __('errorReadStructureFile');
}

if ($errorMsg === '') {
    $dbErrors = compareDBStruct($dbFileStruct, $dbStruct);
}

if (count($dbErrors) > 0) {
    $engineErrors = array_filter($dbErrors, function ($item) {
        return mb_strpos($item, __('errorNoInnoTable')) !== false
            || mb_strpos($item, __('errorWrongCollation')) !== false
            || mb_strpos($item, __('errorDatatTypeInRow')) !== false;
    });
    if (count($engineErrors) > 5) {
        $engineUpdate    = determineEngineUpdate($dbStruct);
        $fulltextIndizes = DBMigrationHelper::getFulltextIndizes();
    }
}

Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $errorMsg, 'errorDBCheck');

$smarty->assign('cDBFileStruct_arr', $dbFileStruct)
       ->assign('cDBStruct_arr', $dbStruct)
       ->assign('cDBError_arr', $dbErrors)
       ->assign('maintenanceResult', $maintenanceResult)
       ->assign('scriptGenerationAvailable', defined('ADMIN_MIGRATION') && ADMIN_MIGRATION)
       ->assign('tab', isset($_REQUEST['tab']) ? Text::filterXSS($_REQUEST['tab']) : '')
       ->assign('Einstellungen', $conf)
       ->assign('DB_Version', DBMigrationHelper::getMySQLVersion())
       ->assign('FulltextIndizes', $fulltextIndizes)
       ->assign('engineUpdate', $engineUpdate)
       ->display('dbcheck.tpl');
