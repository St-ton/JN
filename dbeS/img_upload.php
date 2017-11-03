<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

ob_start();
require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    checkFile();
    $return    = 2;
    $newTmpDir = PFAD_SYNC_TMP . uniqid('images_') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $newTmpDir)) === false) {
        if (Jtllog::doLog()) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'img_upload_xml');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $found  = false;
        $count  = count($syncFiles);
        foreach ($syncFiles as $xmlFile) {
            if (strpos($xmlFile, 'images.xml') !== false) {
                $found = true;
            } elseif (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'Received image: ' . $xmlFile . ' size: ' . filesize($xmlFile),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'img_upload_xml'
                );
            }
        }

        if ($found) {
            $xml = simplexml_load_file($newTmpDir . 'images.xml');
            images_xml($newTmpDir, $xml);

            if ($count <= 1) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('Zip-File contains no images', JTLLOG_LEVEL_DEBUG, false, 'img_upload_xml');
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Missing images.xml', JTLLOG_LEVEL_DEBUG, false, 'img_upload_xml');
        }
        removeTemporaryFiles($newTmpDir);
    }
}

echo $return;

/**
 * @param string           $tmpDir
 * @param SimpleXMLElement $xml
 */
function images_xml($tmpDir, SimpleXMLElement $xml)
{
    $items = get_array($xml);
    foreach ($items as $item) {
        $tmpfile = $tmpDir . $item->kBild;
        if (file_exists($tmpfile)) {
            if (copy($tmpfile, PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $item->cPfad)) {
                DBUpdateInsert('tbild', [$item], 'kBild');
                Shop::DB()->update('tartikelpict', 'kBild', (int)$item->kBild, (object)['cPfad' => $item->cPfad]);
            } else {
                Jtllog::writeLog(sprintf(
                    'Copy "%s" to "%s"',
                    $tmpfile,
                    PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $item->cPfad
                ), JTLLOG_LEVEL_ERROR, false, 'img_upload_xml');
            }
        }
    }
}

/**
 * @param SimpleXMLElement $xml
 * @return array
 */
function get_array(SimpleXMLElement $xml)
{
    $items = [];
    /** @var SimpleXMLElement $child */
    foreach ($xml->children() as $child) {
        $items[] = (object)[
            'kBild' => (int)$child->attributes()->kBild,
            'cPfad' => (string)$child->attributes()->cHash
        ];
    }

    return $items;
}