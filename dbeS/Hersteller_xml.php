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
        foreach ($xml['del_hersteller']['kHersteller'] as $kHersteller) {
            $kHersteller = (int)$kHersteller;
            if ($kHersteller > 0) {
                $affectedArticles = Shop::Container()->getDB()->selectAll('tartikel', 'kHersteller', $kHersteller, 'kArtikel');
                Shop::Container()->getDB()->delete('tseo', ['kKey', 'cKey'], [$kHersteller, 'kHersteller']);
                Shop::Container()->getDB()->delete('thersteller', 'kHersteller', $kHersteller);
                Shop::Container()->getDB()->delete('therstellersprache', 'kHersteller', $kHersteller);

                executeHook(HOOK_HERSTELLER_XML_BEARBEITEDELETES, ['kHersteller' => $kHersteller]);
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . $kHersteller;
                if (is_array($affectedArticles)) {
                    $articleCacheTags = [];
                    foreach ($affectedArticles as $article) {
                        $articleCacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
                    }
                    Shop::Cache()->flushTags($articleCacheTags);
                }
            }
        }
        Shop::Cache()->flushTags($cacheTags);
    }
}

/**
 * @param array $xml
 */
function bearbeiteHersteller($xml)
{
    if (isset($xml['hersteller']['thersteller']) && is_array($xml['hersteller']['thersteller'])) {
        $hersteller_arr = mapArray($xml['hersteller'], 'thersteller', $GLOBALS['mHersteller']);
        if (is_array($hersteller_arr)) {
            $oSprache_arr = Sprache::getAllLanguages();
            $mfCount      = count($hersteller_arr);
            $cacheTags    = [];
            for ($i = 0; $i < $mfCount; $i++) {
                $affectedArticles = Shop::Container()->getDB()->selectAll('tartikel', 'kHersteller', (int)$hersteller_arr[$i]->kHersteller, 'kArtikel');
                Shop::Container()->getDB()->delete('tseo', ['kKey', 'cKey'], [(int)$hersteller_arr[$i]->kHersteller,'kHersteller']);
                if (!trim($hersteller_arr[$i]->cSeo)) {
                    $hersteller_arr[$i]->cSeo = getFlatSeoPath($hersteller_arr[$i]->cName);
                }
                //alten Bildpfad merken
                $oHerstellerBild               = Shop::Container()->getDB()->query(
                    'SELECT cBildPfad 
                        FROM thersteller 
                        WHERE kHersteller = ' . (int)$hersteller_arr[$i]->kHersteller,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $hersteller_arr[$i]->cBildPfad = $oHerstellerBild->cBildPfad ?? '';
                $hersteller_arr[$i]->cSeo      = getSeo($hersteller_arr[$i]->cSeo);
                $hersteller_arr[$i]->cSeo      = checkSeo($hersteller_arr[$i]->cSeo);
                DBUpdateInsert('thersteller', [$hersteller_arr[$i]], 'kHersteller');

                $cXMLSprache = '';
                if (isset($xml['hersteller']['thersteller'][$i])) {
                    $cXMLSprache = $xml['hersteller']['thersteller'][$i];
                } elseif (isset($xml['hersteller']['thersteller']['therstellersprache'])) {
                    $cXMLSprache = $xml['hersteller']['thersteller'];
                }
                $_herstellerSeo = mapArray($cXMLSprache, 'therstellersprache', $GLOBALS['mHerstellerSpracheSeo']);
                if (is_array($oSprache_arr)) {
                    foreach ($oSprache_arr as $oSprache) {
                        $_baseSeo = $hersteller_arr[$i]->cSeo;
                        foreach ($_herstellerSeo as $_hs) {
                            if (isset($_hs->kSprache) && (int)$_hs->kSprache === (int)$oSprache->kSprache && !empty($_hs->cSeo)) {
                                $_baseSeo = getSeo($_hs->cSeo);
                                break;
                            }
                        }
                        $oSeo           = new stdClass();
                        $oSeo->cSeo     = checkSeo($_baseSeo);
                        $oSeo->cKey     = 'kHersteller';
                        $oSeo->kKey     = (int)$hersteller_arr[$i]->kHersteller;
                        $oSeo->kSprache = (int)$oSprache->kSprache;
                        Shop::Container()->getDB()->insert('tseo', $oSeo);
                    }
                }
                //therstellersprache
                Shop::Container()->getDB()->delete('therstellersprache', 'kHersteller', (int)$hersteller_arr[$i]->kHersteller);

                updateXMLinDB($cXMLSprache, 'therstellersprache', $GLOBALS['mHerstellerSprache'], 'kHersteller', 'kSprache');

                executeHook(HOOK_HERSTELLER_XML_BEARBEITEINSERT, ['oHersteller' => $hersteller_arr[$i]]);
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$hersteller_arr[$i]->kHersteller;
                if (is_array($affectedArticles)) {
                    $articleCacheTags = [];
                    foreach ($affectedArticles as $article) {
                        $articleCacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
                    }
                    Shop::Cache()->flushTags($articleCacheTags);
                }
            }
            Shop::Cache()->flushTags($cacheTags);
        }
    }
}
