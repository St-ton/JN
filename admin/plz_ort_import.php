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
$db         = Shop::Container()->getDB();
$itemBatch  = [];
$res        = handleCsvImportAction(
    'plz',
    static function ($entry, &$importDeleteDone, $importType) use ($db, &$landIsoMap, &$itemBatch) {
        if ($importType === 0 && $importDeleteDone === false) {
            $db->query('TRUNCATE TABLE tplz');
            $importDeleteDone = true;
        }
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
            $itemBatch[] = $importEntry;
        }
        if (\count($itemBatch) === 1024) {
            $db->insertBatch('tplz', $itemBatch, $importType !== 2);
            $itemBatch = [];
        }
    },
    ['plz', 'ort', 'land']
);

plzimportActionIndex($smarty, $messages);
plzimportFinalize($smarty, $messages);
