<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';

$zipFile = $_FILES['data']['tmp_name'];
$return  = 3;
if (auth()) {
    $zipFile    = checkFile();
    $return     = 2;
    $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $entzippfad, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $entzippfad);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $i => $xmlFile) {
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);
            if (strpos($xmlFile, 'del_download.xml') !== false) {
                bearbeiteDeletes($xml);
            } else {
                bearbeiteInsert($xml);
            }
            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($entzippfad, 0, -1), true);
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (is_array($xml['del_downloads']['kDownload'])) {
        foreach ($xml['del_downloads']['kDownload'] as $kDownload) {
            if ((int)$kDownload > 0) {
                loescheDownload($kDownload);
            }
        }
    } elseif ((int)$xml['del_downloads']['kDownload'] > 0) {
        loescheDownload($xml['del_downloads']['kDownload']);
    }
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (isset($xml['tDownloads']['tDownload attr']) && is_array($xml['tDownloads']['tDownload attr'])) {
        // 1 Download
        $downloads = mapArray($xml['tDownloads'], 'tDownload', Mapper::getMapping('mDownload'));
        if ($downloads[0]->kDownload > 0) {
            $oDownloadSprache_arr = mapArray(
                $xml['tDownloads']['tDownload'],
                'tDownloadSprache',
                Mapper::getMapping('mDownloadSprache')
            );
            if (count($oDownloadSprache_arr) > 0) {
                DBUpdateInsert('tdownload', $downloads, 'kDownload');
                $lCount = count($oDownloadSprache_arr);
                for ($i = 0; $i < $lCount; ++$i) {
                    $oDownloadSprache_arr[$i]->kDownload = $downloads[0]->kDownload;
                    DBUpdateInsert('tdownloadsprache', [$oDownloadSprache_arr[$i]], 'kDownload', 'kSprache');
                }
            }
        }
    } else {
        // N-Downloads
        $downloads = mapArray($xml['tDownloads'], 'tDownload', Mapper::getMapping('mDownload'));
        foreach ($downloads as $i => $oDownload) {
            if ($oDownload->kDownload > 0) {
                $oDownloadSprache_arr = mapArray(
                    $xml['tDownloads']['tDownload'][$i],
                    'tDownloadSprache',
                    Mapper::getMapping('mDownloadSprache')
                );
                if (count($oDownloadSprache_arr) > 0) {
                    DBUpdateInsert('tdownload', [$oDownload], 'kDownload');
                    $cdsaCount = count($oDownloadSprache_arr);
                    for ($j = 0; $j < $cdsaCount; ++$j) {
                        $oDownloadSprache_arr[$j]->kDownload = $oDownload->kDownload;
                        DBUpdateInsert('tdownloadsprache', [$oDownloadSprache_arr[$j]], 'kDownload', 'kSprache');
                    }
                }
            }
        }
    }
}

/**
 * @param int $id
 */
function loescheDownload(int $id)
{
    if ($id > 0 && \Extensions\Download::checkLicense()) {
        $download = new \Extensions\Download($id);
        $rows     = $download->delete();
        Shop::Container()->getLogService()->debug($rows . ' Downloads geloescht');
    }
}
