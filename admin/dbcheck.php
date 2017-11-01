<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global AdminAccount $oAccount
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$cHinweis          = '';
$cFehler           = '';
$cDBError_arr      = [];
$cDBFileStruct_arr = getDBFileStruct();
$maintenanceResult = null;
$engineUpdate      = null;

if (isset($_POST['update']) && StringHandler::filterXSS($_POST['update']) === 'script' && validateToken()) {
    $scriptName = 'innodb_and_utf8_update_'
        . str_replace('.', '_', Shop::DB()->getConfig()['host']) . '_'
        . Shop::DB()->getConfig()['database'] . '_'
        . date('YmdHis') . '.sql';

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $scriptName . '"');
    echo doEngineUpdateScript($scriptName, array_keys($cDBFileStruct_arr));

    exit;
}

$cDBStruct_arr = getDBStruct(true);
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
]);

if (!empty($_POST['action']) && !empty($_POST['check'])) {
    $maintenanceResult = doDBMaintenance($_POST['action'], $_POST['check']);
}

if (!is_array($cDBFileStruct_arr)) {
    $cFehler = 'Fehler beim Lesen der Struktur-Datei.';
}

if (strlen($cFehler) === 0) {
    $cDBError_arr = compareDBStruct($cDBFileStruct_arr, $cDBStruct_arr);
}

if (count($cDBError_arr) > 0) {
    $cEngineError = array_filter($cDBError_arr, function ($item) {
        return strpos($item, 'keine InnoDB-Tabelle') !== false;
    });
    if (count($cEngineError) > 5) {
        $engineUpdate = determineEngineUpdate($cDBStruct_arr);
    }
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cDBFileStruct_arr', $cDBFileStruct_arr)
       ->assign('cDBStruct_arr', $cDBStruct_arr)
       ->assign('cDBError_arr', $cDBError_arr)
       ->assign('JTL_VERSION', JTL_VERSION)
       ->assign('maintenanceResult', $maintenanceResult)
       ->assign('engineUpdate', $engineUpdate)
       ->assign('scriptGenerationAvailable', defined('ADMIN_MIGRATION') && ADMIN_MIGRATION ? true : false)
       ->assign('tab', StringHandler::filterXSS($_REQUEST['tab']))
       ->assign('Einstellungen', $Einstellungen)
       ->display('dbcheck.tpl');
