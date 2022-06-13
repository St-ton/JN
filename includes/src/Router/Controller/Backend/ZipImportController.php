<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\CSV\Import;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ZipImportController
 * @package JTL\Router\Controller\Backend
 */
class ZipImportController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::PLZ_ORT_IMPORT_VIEW);
        $this->getText->loadAdminLocale('pages/plz_ort_import');

        $isoMap    = [];
        $itemBatch = [];
        $service   = Shop::Container()->getCountryService();
        if (Request::verifyGPDataString('importcsv') === 'plz' && Form::validateToken()) {
            $import = new Import($this->db);
            $import->import(
                'plz',
                function ($entry, &$importDeleteDone, $importType) use (&$isoMap, &$itemBatch) {
                    if ($importType === 0 && $importDeleteDone === false) {
                        $this->db->query('TRUNCATE TABLE tplz');
                        $importDeleteDone = true;
                    }
                    $iso = null;
                    if (\array_key_exists($entry->land, $isoMap)) {
                        $iso = $isoMap[$entry->land];
                    } else {
                        $country = $this->db->getSingleObject(
                            'SELECT cIso FROM tland WHERE cDeutsch = :land',
                            ['land' => $entry->land]
                        );
                        if ($country !== null) {
                            $iso                  = $country->cIso;
                            $isoMap[$entry->land] = $iso;
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
                        $this->db->insertBatch('tplz', $itemBatch, $importType !== 2);
                        $itemBatch = [];
                    }
                },
                ['plz', 'ort', 'land']
            );
        }
        $data = $this->db->getObjects(
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

        return $smarty->assign('oPlzOrt_arr', $data)
            ->assign('route', $this->route)
            ->getResponse('plz_ort_import.tpl');
    }
}
