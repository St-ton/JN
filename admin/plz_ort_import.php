<?php
/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/plz_ort_import_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

$action     = 'index';
$messages   = [
    'notice' => '',
    'error'  => '',
];
$landIsoMap = [];
$entries    = [];
$db         = Shop::Container()->getDB();
$cnt        = 0;
$itemList   = [];
$res        = handleCsvImportAction('plz', static function ($entry) use ($db, &$landIsoMap, &$entries, &$cnt, &$itemList) {
    ++$cnt;
    $iso = null;
    if (\array_key_exists($entry->land, $landIsoMap)) {
        $iso = $landIsoMap[$entry->land];
    } else {
        $land = $db->getSingleObject('SELECT cIso FROM tland WHERE cDeutsch = :land', ['land' => $entry->land]);
        if ($land !== null) {
            $iso                      = $land->cIso;
            $landIsoMap[$entry->land] = $iso;
        }
    }
    if ($iso !== null) {
        $importEntry = (object)[
            'cPLZ'     => $entry->plz,
            'cOrt'     => $entry->ort,
            'cLandISO' => $iso,
        ];
        $itemList[]  = $importEntry;
    }
    if ($cnt % 1000 === 0) {
        $db->insertBatch('tplz', $itemList);
        $itemList = [];
    }
}, ['plz', 'ort', 'land']);

plzimportActionIndex($smarty, $messages);
plzimportFinalize($smarty, $messages);
