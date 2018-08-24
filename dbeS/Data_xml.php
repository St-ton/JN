<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return = 3;
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;
    $zipFile = $_FILES['data']['tmp_name'];
    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);
            if (strpos($xmlFile, 'ack_verfuegbarkeitsbenachrichtigungen.xml') !== false) {
                bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml);
            } elseif (strpos($xmlFile, 'ack_uploadqueue.xml') !== false) {
                bearbeiteUploadQueueAck($xml);
            }
        }
    }
}


echo $return;

/**
 * @param array $xml
 */
function bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml)
{
    if (!isset($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
        return;
    }
    if (!is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])
        && (int)$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] > 0
    ) {
        $xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] =
            [$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']];
    }
    if (is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
        foreach ($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] as $msg) {
            $msg = (int)$msg;
            if ($msg > 0) {
                Shop::Container()->getDB()->update(
                    'tverfuegbarkeitsbenachrichtigung',
                    'kVerfuegbarkeitsbenachrichtigung',
                    $msg,
                    (object)['cAbgeholt' => 'Y']
                );
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteUploadQueueAck($xml)
{
    if (is_array($xml['ack_uploadqueue']['kuploadqueue'])) {
        foreach ($xml['ack_uploadqueue']['kuploadqueue'] as $kUploadqueue) {
            $kUploadqueue = (int)$kUploadqueue;
            if ($kUploadqueue > 0) {
                Shop::Container()->getDB()->delete('tuploadqueue', 'kUploadqueue', $kUploadqueue);
            }
        }
    } elseif ((int)$xml['ack_uploadqueue']['kuploadqueue'] > 0) {
        Shop::Container()->getDB()->delete(
            'tuploadqueue',
            'kUploadqueue',
            (int)$xml['ack_uploadqueue']['kuploadqueue']
        );
    }
}
