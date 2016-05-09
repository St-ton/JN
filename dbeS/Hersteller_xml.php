<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';

global $smarty;

if (!isset($smarty)) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    $smarty = Shop::Smarty();
}

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Hersteller_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Hersteller_xml');
        }
        if ($archive->extract(PCLZIP_OPT_PATH, PFAD_SYNC_TMP)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $zip['filename'] . ' size: ' . filesize(PFAD_SYNC_TMP . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Hersteller_xml');
                }
                $d   = file_get_contents(PFAD_SYNC_TMP . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'del_hersteller.xml') {
                    bearbeiteHerstellerDeletes($xml);
                } elseif ($zip['filename'] === 'hersteller.xml') {
                    bearbeiteHersteller($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Hersteller_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Hersteller_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteHerstellerDeletes($xml)
{
    $cacheTags = array();
    if (isset($xml['del_hersteller']['kHersteller']) && is_array($xml['del_hersteller']['kHersteller'])) {
        foreach ($xml['del_hersteller']['kHersteller'] as $kHersteller) {
            $kHersteller = (int)$kHersteller;
            if ($kHersteller > 0) {
                Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . $kHersteller . " AND cKey = 'kHersteller'", 4);
                Shop::DB()->query("DELETE FROM thersteller WHERE kHersteller = " . $kHersteller, 4);
                Shop::DB()->query("DELETE FROM therstellersprache WHERE kHersteller = " . $kHersteller, 4);

                executeHook(HOOK_HERSTELLER_XML_BEARBEITEDELETES, array('kHersteller' => $kHersteller));
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . $kHersteller;
            }
        }
    } elseif (isset($xml['del_hersteller']['kHersteller']) && intval($xml['del_hersteller']['kHersteller']) > 0) {
        $kHersteller = (int)$xml['del_hersteller']['kHersteller'];
        Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . $kHersteller . " AND cKey = 'kHersteller'", 4);
        Shop::DB()->query("DELETE FROM thersteller WHERE kHersteller = " . $kHersteller, 4);
        Shop::DB()->query("DELETE FROM therstellersprache WHERE kHersteller = " . $kHersteller, 4);

        executeHook(HOOK_HERSTELLER_XML_BEARBEITEDELETES, array('kHersteller' => $kHersteller));
        $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . $kHersteller;
    }
    Shop::Cache()->flushTags($cacheTags);
    if (Shop::Cache()->isPageCacheEnabled()) {
        if (!isset($smarty)) {
            $smarty = Shop::Smarty();
        }
        $smarty->clearCache(null, 'jtlc|manufacturer');
    }
}

/**
 * @param array $xml
 */
function bearbeiteHersteller($xml)
{
    if (is_array($xml['hersteller']['thersteller'])) {
        $hersteller_arr = mapArray($xml['hersteller'], 'thersteller', $GLOBALS['mHersteller']);
        if (is_array($hersteller_arr)) {
            $oSprache_arr = gibAlleSprachen();
            $mfCount      = count($hersteller_arr);
            $cacheTags    = array();
            for ($i = 0; $i < $mfCount; $i++) {
                Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . (int)$hersteller_arr[$i]->kHersteller . " AND cKey = 'kHersteller'", 4);
                if (!trim($hersteller_arr[$i]->cSeo)) {
                    $hersteller_arr[$i]->cSeo = getFlatSeoPath($hersteller_arr[$i]->cName);
                }
                //alten Bildpfad merken
                $oHerstellerBild               = Shop::DB()->query("SELECT cBildPfad FROM thersteller WHERE kHersteller = " . (int)$hersteller_arr[$i]->kHersteller, 1);
                $hersteller_arr[$i]->cBildPfad = (isset($oHerstellerBild->cBildPfad)) ? $oHerstellerBild->cBildPfad : '';
                //seo checken
                $hersteller_arr[$i]->cSeo = getSeo($hersteller_arr[$i]->cSeo);
                $hersteller_arr[$i]->cSeo = checkSeo($hersteller_arr[$i]->cSeo);
                DBUpdateInsert('thersteller', array($hersteller_arr[$i]), 'kHersteller');

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
                        Shop::DB()->insert('tseo', $oSeo);
                    }
                }
                //therstellersprache
                Shop::DB()->delete('therstellersprache', 'kHersteller', (int)$hersteller_arr[$i]->kHersteller);

                updateXMLinDB($cXMLSprache, 'therstellersprache', $GLOBALS['mHerstellerSprache'], 'kHersteller', 'kSprache');

                executeHook(HOOK_HERSTELLER_XML_BEARBEITEINSERT, array('oHersteller' => $hersteller_arr[$i]));
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$hersteller_arr[$i]->kHersteller;
            }
            Shop::Cache()->flushTags($cacheTags);
            if (Shop::Cache()->isPageCacheEnabled()) {
                if (!isset($smarty)) {
                    $smarty = Shop::Smarty();
                }
                $smarty->clearCache(null, 'jtlc|manufacturer');
            }
        }
    }
}
