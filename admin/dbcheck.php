<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$cHinweis          = '';
$cFehler           = '';
$cDBStruct_arr     = getDBStruct();
$cDBFileStruct_arr = getDBFileStruct();
$cDBError_arr      = array();

if (!is_array($cDBFileStruct_arr)) {
    $cFehler = 'Fehler beim Lesen der Struktur-Datei.';
}

if (strlen($cFehler) === 0) {
    foreach ($cDBFileStruct_arr as $cTable => $cColumn_arr) {
        if (!array_key_exists($cTable, $cDBStruct_arr)) {
            $cDBError_arr[$cTable] = 'Tabelle nicht vorhanden';
        } else {
            foreach ($cColumn_arr as $cColumn) {
                if (!in_array($cColumn, $cDBStruct_arr[$cTable])) {
                    $cDBError_arr[$cTable] = "Spalte $cColumn in $cTable nicht vorhanden";
                    break;
                }
            }
        }
    }
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cDBFileStruct_arr', $cDBFileStruct_arr)
       ->assign('cDBError_arr', $cDBError_arr)
       ->assign('JTL_VERSION', JTL_VERSION)
       ->display('dbcheck.tpl');
