<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;
    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
        // @todo cleanup?? master didn't do it..
//        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (strpos($xmlFile, 'del_hersteller.xml') !== false) {
                bearbeiteHerstellerDeletes($xml);
            } elseif (strpos($xmlFile, 'hersteller.xml') !== false) {
                bearbeiteHersteller($xml);
            }
        }
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteHerstellerDeletes($xml)
{
    $cacheTags = [];
    if (isset($xml['del_hersteller']['kHersteller']) && (int)$xml['del_hersteller']['kHersteller'] > 0) {
        $xml['del_hersteller']['kHersteller'] = [$xml['del_hersteller']['kHersteller']];
    }
    if (isset($xml['del_hersteller']['kHersteller']) && is_array($xml['del_hersteller']['kHersteller'])) {
        $db = Shop::Container()->getDB();
        foreach ($xml['del_hersteller']['kHersteller'] as $kHersteller) {
            $kHersteller = (int)$kHersteller;
            if ($kHersteller > 0) {
                $affectedArticles = $db->selectAll(
                    'tartikel',
                    'kHersteller',
                    $kHersteller,
                    'kArtikel'
                );
                $db->delete('tseo', ['kKey', 'cKey'], [$kHersteller, 'kHersteller']);
                $db->delete('thersteller', 'kHersteller', $kHersteller);
                $db->delete('therstellersprache', 'kHersteller', $kHersteller);

                executeHook(HOOK_HERSTELLER_XML_BEARBEITEDELETES, ['kHersteller' => $kHersteller]);
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . $kHersteller;
                if (is_array($affectedArticles)) {
                    $articleCacheTags = [];
                    foreach ($affectedArticles as $article) {
                        $articleCacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
                    }
                    Shop::Container()->getCache()->flushTags($articleCacheTags);
                }
            }
        }
        Shop::Container()->getCache()->flushTags($cacheTags);
    }
}

/**
 * @param array $xml
 */
function bearbeiteHersteller($xml)
{
    if (!isset($xml['hersteller']['thersteller']) || !is_array($xml['hersteller']['thersteller'])) {
        return;
    }
    $manufacturers = mapArray($xml['hersteller'], 'thersteller', $GLOBALS['mHersteller']);
    $oSprache_arr  = Sprache::getAllLanguages();
    $mfCount       = count($manufacturers);
    $cacheTags     = [];
    $db            = Shop::Container()->getDB();
    for ($i = 0; $i < $mfCount; $i++) {
        $id               = (int)$manufacturers[$i]->kHersteller;
        $affectedArticles = $db->selectAll('tartikel', 'kHersteller', $id, 'kArtikel');
        $db->delete('tseo', ['kKey', 'cKey'], [$id, 'kHersteller']);
        if (!trim($manufacturers[$i]->cSeo)) {
            $manufacturers[$i]->cSeo = getFlatSeoPath($manufacturers[$i]->cName);
        }
        //alten Bildpfad merken
        $oHerstellerBild              = $db->query(
            'SELECT cBildPfad 
                FROM thersteller 
                WHERE kHersteller = ' . $id,
            \DB\ReturnType::SINGLE_OBJECT
        );
        $manufacturers[$i]->cBildPfad = $oHerstellerBild->cBildPfad ?? '';
        $manufacturers[$i]->cSeo      = getSeo($manufacturers[$i]->cSeo);
        $manufacturers[$i]->cSeo      = checkSeo($manufacturers[$i]->cSeo);
        DBUpdateInsert('thersteller', [$manufacturers[$i]], 'kHersteller');

        $cXMLSprache = '';
        if (isset($xml['hersteller']['thersteller'][$i])) {
            $cXMLSprache = $xml['hersteller']['thersteller'][$i];
        } elseif (isset($xml['hersteller']['thersteller']['therstellersprache'])) {
            $cXMLSprache = $xml['hersteller']['thersteller'];
        }
        $_herstellerSeo = mapArray($cXMLSprache, 'therstellersprache', $GLOBALS['mHerstellerSpracheSeo']);
        foreach ($oSprache_arr as $oSprache) {
            $_baseSeo = $manufacturers[$i]->cSeo;
            foreach ($_herstellerSeo as $_hs) {
                if (isset($_hs->kSprache) && (int)$_hs->kSprache === (int)$oSprache->kSprache && !empty($_hs->cSeo)) {
                    $_baseSeo = getSeo($_hs->cSeo);
                    break;
                }
            }
            $oSeo           = new stdClass();
            $oSeo->cSeo     = checkSeo($_baseSeo);
            $oSeo->cKey     = 'kHersteller';
            $oSeo->kKey     = $id;
            $oSeo->kSprache = (int)$oSprache->kSprache;
            $db->insert('tseo', $oSeo);
        }
        $db->delete('therstellersprache', 'kHersteller', $id);

        updateXMLinDB($cXMLSprache, 'therstellersprache', $GLOBALS['mHerstellerSprache'], 'kHersteller', 'kSprache');

        executeHook(HOOK_HERSTELLER_XML_BEARBEITEINSERT, ['oHersteller' => $manufacturers[$i]]);
        $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . $id;
        if (is_array($affectedArticles)) {
            $articleCacheTags = [];
            foreach ($affectedArticles as $article) {
                $articleCacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
            }
            Shop::Container()->getCache()->flushTags($articleCacheTags);
        }
    }
    Shop::Container()->getCache()->flushTags($cacheTags);
}
