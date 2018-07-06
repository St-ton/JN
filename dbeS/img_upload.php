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
    $zipFile   = checkFile();
    $return    = 2;
    $newTmpDir = PFAD_SYNC_TMP . uniqid('images_') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $newTmpDir, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $newTmpDir);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $found  = false;
        $count  = count($syncFiles);
        foreach ($syncFiles as $xmlFile) {
            if (strpos($xmlFile, 'images.xml') !== false) {
                $found = true;
            }
        }

        if ($found) {
            images_xml($newTmpDir, simplexml_load_file($newTmpDir . 'images.xml'));
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
                Shop::Container()->getDB()->update('tartikelpict', 'kBild', (int)$item->kBild, (object)['cPfad' => $item->cPfad]);
            } else {
                Shop::Container()->getLogService()->error(sprintf(
                    'Copy "%s" to "%s"',
                    $tmpfile,
                    PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $item->cPfad
                ));
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
