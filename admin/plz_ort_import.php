<?php
/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/plz_ort_import_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

$action   = 'index';
$messages = [
    'notice' => '',
    'error'  => '',
];

$landIsoMap    = [];
$entries       = [];
$db            = Shop::Container()->getDB();

handleCsvImportAction('plz', static function($entry) use ($db, &$landIsoMap, &$entries){
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
//        $entries[] = [
//            $db->escape($entry->plz),
//            $entry->ort,
//            $iso,
//        ];
        $res = $db->insert('tplz', $importEntry);
    }
}, ['plz', 'ort', 'land']);

//$query = "INSERT INTO tplz (cPLZ, cOrt, cLandISO) VALUES ";
//
//foreach ($entries as $i => $entry) {
//    if ($i > 0) $query .= ', ';
//    $query .= '("' . $entry->cPLZ . '", "' . $entry->cOrt . '", "' . $entry->cLandISO . '")';
//}
//$res = $db->query($query);

plzimportActionIndex($smarty, $messages);
plzimportFinalize($smarty, $messages);
