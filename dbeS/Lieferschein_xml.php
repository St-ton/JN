<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile   = checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $i => $xmlFile) {
            $cData = file_get_contents($xmlFile);
            $oXml  = simplexml_load_string($cData);
            switch (pathinfo($xmlFile)['basename']) {
                case 'lief.xml':
                    bearbeiteInsert($oXml);
                    break;
                case 'del_lief.xml':
                    bearbeiteDelete($oXml);
                    break;
            }
            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param object $oXml
 */
function bearbeiteInsert($oXml)
{
    foreach ($oXml->tlieferschein as $oXmlLieferschein) {
        $oLieferschein = JTLMapArr($oXmlLieferschein, $GLOBALS['mLieferschein']);
        if ((int)$oLieferschein->kInetBestellung > 0) {
            $oLieferschein->dErstellt = date_format(date_create($oLieferschein->dErstellt), 'U');
            DBUpdateInsert('tlieferschein', [$oLieferschein], 'kLieferschein');

            foreach ($oXmlLieferschein->tlieferscheinpos as $oXmlLieferscheinpos) {
                $oLieferscheinpos                = JTLMapArr($oXmlLieferscheinpos, $GLOBALS['mLieferscheinpos']);
                $oLieferscheinpos->kLieferschein = $oLieferschein->kLieferschein;
                DBUpdateInsert('tlieferscheinpos', [$oLieferscheinpos], 'kLieferscheinPos');

                foreach ($oXmlLieferscheinpos->tlieferscheinposInfo as $oXmlLieferscheinposinfo) {
                    $oLieferscheinposinfo                   = JTLMapArr(
                        $oXmlLieferscheinposinfo,
                        $GLOBALS['mLieferscheinposinfo']
                    );
                    $oLieferscheinposinfo->kLieferscheinPos = $oLieferscheinpos->kLieferscheinPos;
                    DBUpdateInsert('tlieferscheinposinfo', [$oLieferscheinposinfo], 'kLieferscheinPosInfo');
                }
            }

            foreach ($oXmlLieferschein->tversand as $oXmlVersand) {
                $oVersand                = JTLMapArr($oXmlVersand, $GLOBALS['mVersand']);
                $oVersand->kLieferschein = $oLieferschein->kLieferschein;
                $oVersand->dErstellt     = date_format(date_create($oVersand->dErstellt), 'U');
                DBUpdateInsert('tversand', [$oVersand], 'kVersand');
            }
        }
    }
}

/**
 * @param object $oXml
 */
function bearbeiteDelete($oXml)
{
    $kLieferschein_arr = $oXml->kLieferschein;
    $db                = Shop::Container()->getDB();
    if (!is_array($kLieferschein_arr)) {
        $kLieferschein_arr = (array)$kLieferschein_arr;
    }
    foreach ($kLieferschein_arr as $kLieferschein) {
        $kLieferschein = (int)$kLieferschein;
        $db->delete('tversand', 'kLieferschein', $kLieferschein);
        $db->delete('tlieferschein', 'kLieferschein', $kLieferschein);

        $positions = $db->selectAll(
            'tlieferscheinpos',
            'kLieferschein',
            $kLieferschein,
            'kLieferscheinPos'
        );
        foreach ($positions as $position) {
            $db->delete(
                'tlieferscheinpos',
                'kLieferscheinPos',
                (int)$position->kLieferscheinPos
            );
            $db->delete(
                'tlieferscheinposinfo',
                'kLieferscheinPos',
                (int)$position->kLieferscheinPos
            );
        }
    }
}
