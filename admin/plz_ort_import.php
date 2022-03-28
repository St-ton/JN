<?php declare(strict_types=1);

use JTL\CSV\Import;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

$action     = 'index';
$messages   = [
    'notice' => '',
    'error'  => '',
];
$landIsoMap = [];
$itemBatch  = [];
$db         = Shop::Container()->getDB();
$service    = Shop::Container()->getCountryService();
if (Request::verifyGPDataString('importcsv') === 'plz' && Form::validateToken()) {
    $import = new Import($db);
    $import->import(
        'plz',
        static function ($entry, &$importDeleteDone, $importType) use ($db, &$landIsoMap, &$itemBatch) {
            if ($importType === 0 && $importDeleteDone === false) {
                $db->query('TRUNCATE TABLE tplz');
                $importDeleteDone = true;
            }
            $iso = null;
            if (array_key_exists($entry->land, $landIsoMap)) {
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
            if (count($itemBatch) === 1024) {
                $db->insertBatch('tplz', $itemBatch, $importType !== 2);
                $itemBatch = [];
            }
        },
        ['plz', 'ort', 'land']
    );
}
$data = $db->getObjects(
    'SELECT tplz.cLandISO, tland.cDeutsch, tland.cKontinent, COUNT(tplz.kPLZ) AS nPLZOrte, backup.nBackup
        FROM tplz
        INNER JOIN tland ON tland.cISO = tplz.cLandISO
        LEFT JOIN (
            SELECT tplz_backup.cLandISO, COUNT(tplz_backup.kPLZ) AS nBackup
            FROM tplz_backup
            GROUP BY tplz_backup.cLandISO
        ) AS backup ON backup.cLandISO = tplz.cLandISO
        GROUP BY tplz.cLandISO, tland.cDeutsch, tland.cKontinent
        ORDER BY tplz.cLandISO'
);

foreach ($data as $item) {
    $country = $service->getCountry($item->cLandISO);
    if ($country !== null) {
        $item->cDeutsch   = $country->getName();
        $item->cKontinent = $country->getContinent();
    }
}

$smarty->assign('oPlzOrt_arr', $data)
    ->display('plz_ort_import.tpl');
