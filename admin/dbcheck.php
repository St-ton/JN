<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global AdminAccount $oAccount
 * @global JTLSmarty $smarty
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$cHinweis          = '';
$cFehler           = '';
$dbErrors          = [];
$dbFileStruct      = getDBFileStruct();
$maintenanceResult = null;
$engineUpdate      = null;
$fulltextIndizes   = null;

if (isset($_POST['update']) && StringHandler::filterXSS($_POST['update']) === 'script' && Form::validateToken()) {
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
    CONF_ARTIKELUEBERSICHT,
]);

if (!empty($_POST['action']) && !empty($_POST['check'])) {
    $maintenanceResult = doDBMaintenance($_POST['action'], $_POST['check']);
}

if (empty($dbFileStruct)) {
    $cFehler = 'Fehler beim Lesen der Struktur-Datei.';
}

if (strlen($cFehler) === 0) {
    $dbErrors = compareDBStruct($dbFileStruct, $dbStruct);
}

if (count($dbErrors) > 0) {
    $cEngineError = array_filter($dbErrors, function ($item) {
        return strpos($item, 'keine InnoDB-Tabelle') !== false
            || strpos($item, 'falsche Kollation') !== false
            || strpos($item, 'Datentyp text in Spalte') !== false;
    });
    if (count($cEngineError) > 5) {
        $engineUpdate    = determineEngineUpdate($dbStruct);
        $fulltextIndizes = DBMigrationHelper::getFulltextIndizes();
    }
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cDBFileStruct_arr', $dbFileStruct)
       ->assign('cDBStruct_arr', $dbStruct)
       ->assign('cDBError_arr', $dbErrors)
       ->assign('maintenanceResult', $maintenanceResult)
       ->assign('scriptGenerationAvailable', defined('ADMIN_MIGRATION') && ADMIN_MIGRATION)
       ->assign('tab', isset($_REQUEST['tab']) ? StringHandler::filterXSS($_REQUEST['tab']) : '')
       ->assign('Einstellungen', $conf)
       ->assign('DB_Version', DBMigrationHelper::getMySQLVersion())
       ->assign('FulltextIndizes', $fulltextIndizes)
       ->assign('engineUpdate', $engineUpdate)
       ->display('dbcheck.tpl');
