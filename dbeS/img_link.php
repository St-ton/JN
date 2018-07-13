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
    $zipFile = checkFile();
    $return  = 2;
    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        if (Jtllog::doLog()) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'img_link');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'bearbeite: ' . $xmlFile . ' size: ' . filesize($xmlFile),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'img_link_xml'
                );
            }
            $xml = simplexml_load_file($xmlFile);
            if (strpos($xmlFile, 'del_bildartikellink.xml') !== false) {
                del_bildartikellink_xml($xml);
            } elseif (strpos($xmlFile, 'bildartikellink.xml') !== false) {
                bildartikellink_xml($xml);
            }
            removeTemporaryFiles($xmlFile);
        }
    }
}

echo $return;

/**
 * @param SimpleXMLElement $xml
 */
function bildartikellink_xml(SimpleXMLElement $xml)
{
    $items           = get_array($xml);
    $articleIDs      = [];
    $cacheArticleIDs = [];
    foreach ($items as $item) {
        // delete link first. Important because jtl-wawi does not send del_bildartikellink when image is updated.
        Shop::Container()->getDB()->delete(
            'tartikelpict',
            ['kArtikel', 'nNr'],
            [(int)$item->kArtikel, (int)$item->nNr]
        );
        $articleIDs[] = (int)$item->kArtikel;
        DBUpdateInsert('tartikelpict', [$item], 'kArtikelPict');
    }
    foreach (array_unique($articleIDs) as $_aid) {
        $cacheArticleIDs[] = CACHING_GROUP_ARTICLE . '_' . $_aid;
        MediaImage::clearCache(Image::TYPE_PRODUCT, $_aid);
    }
    Shop::Cache()->flushTags($cacheArticleIDs);
}

/**
 * @param SimpleXMLElement $xml
 */
function del_bildartikellink_xml(SimpleXMLElement $xml)
{
    $items           = get_del_array($xml);
    $articleIDs      = [];
    $cacheArticleIDs = [];
    foreach ($items as $item) {
        del_img_item($item);
        $articleIDs[] = $item->kArtikel;
    }
    foreach (array_unique($articleIDs) as $_aid) {
        $cacheArticleIDs[] = CACHING_GROUP_ARTICLE . '_' . $_aid;
        MediaImage::clearCache(Image::TYPE_PRODUCT, $_aid);
    }
    Shop::Cache()->flushTags($cacheArticleIDs);
}

/**
 * @param stdClass $item
 */
function del_img_item($item)
{
    $image = Shop::Container()->getDB()->select('tartikelpict', 'kArtikel', $item->kArtikel, 'nNr', $item->nNr);
    if (is_object($image)) {
        // is last reference
        $res = Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS cnt FROM tartikelpict WHERE kBild = ' . (int)$image->kBild,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ((int)$res->cnt === 1) {
            Shop::Container()->getDB()->delete('tbild', 'kBild', (int)$image->kBild);
            $storage = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $image->cPfad;
            if (file_exists($storage)) {
                @unlink($storage);
            }
            Jtllog::writeLog(
                'Removed last image link: ' . (int)$image->kBild,
                JTLLOG_LEVEL_NOTICE,
                false,
                'img_link_xml'
            );
        }
        Shop::Container()->getDB()->delete(
            'tartikelpict',
            ['kArtikel', 'nNr'],
            [(int)$item->kArtikel, (int)$item->nNr]
        );
    }
}

/**
 * @param SimpleXMLElement $xml
 * @return array
 */
function get_del_array(SimpleXMLElement $xml)
{
    $items = [];
    foreach ($xml->children() as $child) {
        $item    = (object)[
            'nNr'      => (int)$child->nNr,
            'kArtikel' => (int)$child->kArtikel
        ];
        $items[] = $item;
    }

    return $items;
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
        $item    = (object)[
            'cPfad'        => '',
            'kBild'        => (int)$child->attributes()->kBild,
            'nNr'          => (int)$child->attributes()->nNr,
            'kArtikel'     => (int)$child->attributes()->kArtikel,
            'kArtikelPict' => (int)$child->attributes()->kArtikelPict
        ];
        $imageId = (int)$child->attributes()->kBild;
        $image   = Shop::Container()->getDB()->select('tbild', 'kBild', $imageId);
        if (is_object($image)) {
            $item->cPfad = $image->cPfad;
            $items[]     = $item;
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog(
                'Missing reference in tbild (Key: ' . $imageId . ')',
                JTLLOG_LEVEL_DEBUG,
                false,
                'img_link_xml'
            );
        }
    }

    return $items;
}
