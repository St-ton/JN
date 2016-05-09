<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
        }
        if ($archive->extract(PCLZIP_OPT_PATH, PFAD_SYNC_TMP)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $zip['filename'] . ' size: ' . filesize(PFAD_SYNC_TMP . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
                }
                $d   = file_get_contents(PFAD_SYNC_TMP . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'ack_verfuegbarkeitsbenachrichtigungen.xml') {
                    bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml);
                } elseif ($zip['filename'] === 'ack_uploadqueue.xml') {
                    bearbeiteUploadQueueAck($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Data_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Data_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml)
{
    if (isset($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
        if (is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
            foreach ($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] as $kVerfuegbarkeitsbenachrichtigung) {
                $kVerfuegbarkeitsbenachrichtigung = (int)$kVerfuegbarkeitsbenachrichtigung;
                if ($kVerfuegbarkeitsbenachrichtigung > 0) {
                    Shop::DB()->query(
                        "UPDATE tverfuegbarkeitsbenachrichtigung
                            SET cAbgeholt = 'Y'
                            WHERE kVerfuegbarkeitsbenachrichtigung = " . $kVerfuegbarkeitsbenachrichtigung, 4
                    );
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Verfuegbarkeitsbenachrichtigung erfolgreich abgeholt: ' . $kVerfuegbarkeitsbenachrichtigung, JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
                    }
                }
            }
        } elseif (intval($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']) > 0) {
            Shop::DB()->query(
                "UPDATE tverfuegbarkeitsbenachrichtigung
                    SET cAbgeholt = 'Y'
                    WHERE kVerfuegbarkeitsbenachrichtigung = " . intval($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']), 4
            );
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Verfuegbarkeitsbenachrichtigung erfolgreich abgeholt: ' .
                    intval($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']), JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
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
                Shop::DB()->delete('tuploadqueue', 'kUploadqueue', $kUploadqueue);
            }
        }
    } elseif (intval($xml['ack_uploadqueue']['kuploadqueue']) > 0) {
        Shop::DB()->delete('tuploadqueue', 'kUploadqueue', (int)$xml['ack_uploadqueue']['kuploadqueue']);
    }
}
