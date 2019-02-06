<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

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
            $data = file_get_contents($xmlFile);
            $xml  = simplexml_load_string($data);
            switch (pathinfo($xmlFile)['basename']) {
                case 'lief.xml':
                    bearbeiteInsert($xml);
                    break;
                case 'del_lief.xml':
                    bearbeiteDelete($xml);
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
    foreach ($oXml->tlieferschein as $item) {
        $deliverySlip = JTLMapArr($item, Mapper::getMapping('mLieferschein'));
        if ((int)$deliverySlip->kInetBestellung <= 0) {
            continue;
        }
        $deliverySlip->dErstellt = date_format(date_create($deliverySlip->dErstellt), 'U');
        DBUpdateInsert('tlieferschein', [$deliverySlip], 'kLieferschein');

        foreach ($item->tlieferscheinpos as $oXmlLieferscheinpos) {
            $position                = JTLMapArr($oXmlLieferscheinpos, Mapper::getMapping('mLieferscheinpos'));
            $position->kLieferschein = $deliverySlip->kLieferschein;
            DBUpdateInsert('tlieferscheinpos', [$position], 'kLieferscheinPos');

            foreach ($oXmlLieferscheinpos->tlieferscheinposInfo as $oXmlLieferscheinposinfo) {
                $positionInfo                   = JTLMapArr(
                    $oXmlLieferscheinposinfo,
                    Mapper::getMapping('mLieferscheinposinfo')
                );
                $positionInfo->kLieferscheinPos = $position->kLieferscheinPos;
                DBUpdateInsert('tlieferscheinposinfo', [$positionInfo], 'kLieferscheinPosInfo');
            }
        }

        foreach ($item->tversand as $oXmlVersand) {
            $shipping                = JTLMapArr($oXmlVersand, Mapper::getMapping('mVersand'));
            $shipping->kLieferschein = $deliverySlip->kLieferschein;
            $shipping->dErstellt     = date_format(date_create($shipping->dErstellt), 'U');
            DBUpdateInsert('tversand', [$shipping], 'kVersand');
        }
    }
}

/**
 * @param object $oXml
 */
function bearbeiteDelete($oXml)
{
    $items = $oXml->kLieferschein;
    $db    = Shop::Container()->getDB();
    if (!is_array($items)) {
        $items = (array)$items;
    }
    foreach ($items as $id) {
        $id = (int)$id;
        $db->delete('tversand', 'kLieferschein', $id);
        $db->delete('tlieferschein', 'kLieferschein', $id);

        $positions = $db->selectAll(
            'tlieferscheinpos',
            'kLieferschein',
            $id,
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
