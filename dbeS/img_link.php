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
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
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
    $db              = Shop::Container()->getDB();
    foreach ($items as $item) {
        // delete link first. Important because jtl-wawi does not send del_bildartikellink when image is updated.
        $db->delete(
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
    Shop::Container()->getCache()->flushTags($cacheArticleIDs);
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
    Shop::Container()->getCache()->flushTags($cacheArticleIDs);
}

/**
 * @param stdClass $item
 */
function del_img_item($item)
{
    $db    = Shop::Container()->getDB();
    $image = $db->select('tartikelpict', 'kArtikel', $item->kArtikel, 'nNr', $item->nNr);
    if (is_object($image)) {
        // is last reference
        $res = $db->query(
            'SELECT COUNT(*) AS cnt FROM tartikelpict WHERE kBild = ' . (int)$image->kBild,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ((int)$res->cnt === 1) {
            $db->delete('tbild', 'kBild', (int)$image->kBild);
            $storage = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $image->cPfad;
            if (file_exists($storage)) {
                @unlink($storage);
            }
        }
        $db->delete(
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
    $db    = Shop::Container()->getDB();
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
        $image   = $db->select('tbild', 'kBild', $imageId);
        if (is_object($image)) {
            $item->cPfad = $image->cPfad;
            $items[]     = $item;
        } else {
            Shop::Container()->getLogService()->debug('Missing reference in tbild (Key: ' . $imageId . ')');
        }
    }

    return $items;
}
