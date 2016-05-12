<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';
//smarty lib
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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
            }
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' . filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'katdel.xml') {
                    bearbeiteDeletes($xml);
                } else {
                    bearbeiteInsert($xml);
                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            updateKategorieLevel();
            rebuildCategoryTree(0, 1);
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (isset($xml['del_kategorien']['kKategorie'])) {
        // Alle Shop Kundengruppen holen
        $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);
        if (is_array($xml['del_kategorien']['kKategorie'])) {
            foreach ($xml['del_kategorien']['kKategorie'] as $kKategorie) {
                $kKategorie = (int)$kKategorie;
                if ($kKategorie > 0) {
                    loescheKategorie($kKategorie);
                    //hole alle artikel raus in dieser Kategorie
                    $oArtikel_arr = Shop::DB()->query("SELECT kArtikel FROM tkategorieartikel WHERE kKategorie = " . $kKategorie, 2);
                    //gehe alle Artikel durch
                    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
                        foreach ($oArtikel_arr as $oArtikel) {
                            fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr);
                        }
                    }

                    executeHook(HOOK_KATEGORIE_XML_BEARBEITEDELETES, array('kKategorie' => $kKategorie));
                }
            }
        } elseif (intval($xml['del_kategorien']['kKategorie']) > 0) {
            loescheKategorie(intval($xml['del_kategorien']['kKategorie']));
            //hole alle artikel raus in dieser Kategorie
            $oArtikel_arr = Shop::DB()->query(
                "SELECT kArtikel
                    FROM tkategorieartikel
                    WHERE kKategorie = " . (int)$xml['del_kategorien']['kKategorie'], 2
            );
            //gehe alle Artikel durch
            if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
                foreach ($oArtikel_arr as $oArtikel) {
                    fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr);
                }
            }

            executeHook(HOOK_KATEGORIE_XML_BEARBEITEDELETES, array('kKategorie' => (int)$xml['del_kategorien']['kKategorie']));
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    $Kategorie                 = new stdClass();
    $Kategorie->kKategorie     = 0;
    $Kategorie->kOberKategorie = 0;
    if (is_array($xml['tkategorie attr'])) {
        $Kategorie->kKategorie     = intval($xml['tkategorie attr']['kKategorie']);
        $Kategorie->kOberKategorie = intval($xml['tkategorie attr']['kOberKategorie']);
    }
    if (!$Kategorie->kKategorie) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('kKategorie fehlt! XML: ' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
        }

        return;
    }
    if (is_array($xml['tkategorie'])) {
        // Altes SEO merken => falls sich es bei der aktualisierten Kategorie ändert => Eintrag in tredirect
        $oSeoOld       = Shop::DB()->query("SELECT cSeo FROM tkategorie WHERE kKategorie = {$Kategorie->kKategorie}", 1);
        $oSeoAssoc_arr = getSeoFromDB($Kategorie->kKategorie, 'kKategorie', null, 'kSprache');

        loescheKategorie($Kategorie->kKategorie);
        //Kategorie
        $kategorie_arr = mapArray($xml, 'tkategorie', $GLOBALS['mKategorie']);
        if ($kategorie_arr[0]->kKategorie > 0) {
            if (!$kategorie_arr[0]->cSeo) {
                $kategorie_arr[0]->cSeo = getFlatSeoPath($kategorie_arr[0]->cName);
            }
            $kategorie_arr[0]->cSeo                  = getSeo($kategorie_arr[0]->cSeo);
            $kategorie_arr[0]->cSeo                  = checkSeo($kategorie_arr[0]->cSeo);
            $kategorie_arr[0]->dLetzteAktualisierung = 'now()';
            DBUpdateInsert('tkategorie', $kategorie_arr, 'kKategorie');
            // Insert into tredirect weil sich das SEO geändert hat
            if (isset($oSeoOld->cSeo)) {
                checkDbeSXmlRedirect($oSeoOld->cSeo, $kategorie_arr[0]->cSeo);
            }
            //insert in tseo
            Shop::DB()->query(
                "INSERT INTO tseo
                    SELECT tkategorie.cSeo, 'kKategorie', tkategorie.kKategorie, tsprache.kSprache
                        FROM tkategorie, tsprache
                        WHERE tkategorie.kKategorie = " . (int)$kategorie_arr[0]->kKategorie . "
                            AND tsprache.cStandard = 'Y'
                            AND tkategorie.cSeo != ''", 4
            );

            executeHook(HOOK_KATEGORIE_XML_BEARBEITEINSERT, array('oKategorie' => $kategorie_arr[0]));
        }

        //Kategoriesprache
        $kategoriesprache_arr = mapArray($xml['tkategorie'], 'tkategoriesprache', $GLOBALS['mKategorieSprache']);
        if (is_array($kategoriesprache_arr)) {
            $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            $lCount                = count($kategoriesprache_arr);
            for ($i = 0; $i < $lCount; $i++) {
                // Sprachen die nicht im Shop vorhanden sind überspringen
                if (!Sprache::isShopLanguage($kategoriesprache_arr[$i]->kSprache, $oShopSpracheAssoc_arr)) {
                    continue;
                }
                if (!$kategoriesprache_arr[$i]->cSeo) {
                    $kategoriesprache_arr[$i]->cSeo = $kategoriesprache_arr[$i]->cName;
                }
                if (!$kategoriesprache_arr[$i]->cSeo) {
                    $kategoriesprache_arr[$i]->cSeo = $kategorie_arr[0]->cSeo;
                }
                if (!$kategoriesprache_arr[$i]->cSeo) {
                    $kategoriesprache_arr[$i]->cSeo = $kategorie_arr[0]->cName;
                }
                $kategoriesprache_arr[$i]->cSeo = getSeo($kategoriesprache_arr[$i]->cSeo);
                $kategoriesprache_arr[$i]->cSeo = checkSeo($kategoriesprache_arr[$i]->cSeo);
                DBUpdateInsert('tkategoriesprache', array($kategoriesprache_arr[$i]), 'kKategorie', 'kSprache');

                Shop::DB()->query(
                    "DELETE FROM tseo
                        WHERE cKey = 'kKategorie'
                            AND kKey = " . intval($kategoriesprache_arr[$i]->kKategorie) . "
                            AND kSprache = " . intval($kategoriesprache_arr[$i]->kSprache), 4
                );
                //insert in tseo
                $oSeo           = new stdClass();
                $oSeo->cSeo     = $kategoriesprache_arr[$i]->cSeo;
                $oSeo->cKey     = 'kKategorie';
                $oSeo->kKey     = $kategoriesprache_arr[$i]->kKategorie;
                $oSeo->kSprache = $kategoriesprache_arr[$i]->kSprache;
                Shop::DB()->insert('tseo', $oSeo);
                // Insert into tredirect weil sich das SEO vom geändert hat
                if (isset($oSeoAssoc_arr[$kategoriesprache_arr[$i]->kSprache])) {
                    checkDbeSXmlRedirect($oSeoAssoc_arr[$kategoriesprache_arr[$i]->kSprache]->cSeo, $kategoriesprache_arr[$i]->cSeo);
                }
            }
        }
        // Alle Shop Kundengruppen holen
        $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);
        updateXMLinDB($xml['tkategorie'], 'tkategoriekundengruppe', $GLOBALS['mKategorieKundengruppe'], 'kKundengruppe', 'kKategorie');
        if (is_array($oKundengruppe_arr) && count($oKundengruppe_arr) > 0) {
            //hole alle artikel raus in dieser Kategorie
            $oArtikel_arr = Shop::DB()->query(
                "SELECT kArtikel
                    FROM tkategorieartikel
                    WHERE kKategorie=" . $kategorie_arr[0]->kKategorie, 2
            );
            //gehe alle Artikel durch und ermittle max rabatt
            if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
                foreach ($oArtikel_arr as $oArtikel) {
                    fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr);
                }
            }
        }

        updateXMLinDB($xml['tkategorie'], 'tkategorieattribut', $GLOBALS['mKategorieAttribut'], 'kKategorieAttribut');
        updateXMLinDB($xml['tkategorie'], 'tkategoriesichtbarkeit', $GLOBALS['mKategorieSichtbarkeit'], 'kKundengruppe', 'kKategorie');

        $cache = Shop::Cache();
//        $flushArray = array();
//        $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kKategorie;
//        if (isset($Kategorie->kOberKategorie) && $Kategorie->kOberKategorie > 0) {
//            $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kOberKategorie;
//        }
//        $cache->flushTags($flushArray);
//        if ($cache->isPageCacheEnabled()) {
//            if (!isset($smarty)) {
//                global $smarty;
//            }
//            $smarty->clearCache(null, 'jtlc|category|cid' . $Kategorie->kKategorie);
//            if (isset($Kategorie->kOberKategorie) && $Kategorie->kOberKategorie > 0) {
//                $smarty->clearCache(null, 'jtlc|category|cid' . $Kategorie->kOberKategorie);
//            }
//        }
        //@todo: the above does not really work on parent categories when adding/deleting child categories
        $cache->flushTags(array(CACHING_GROUP_CATEGORY));
        if ($cache->isPageCacheEnabled()) {
            if (!isset($smarty)) {
                $smarty = Shop::Smarty();
            }
            $smarty->clearCache(null, 'jtlc|category');
        }
    }
}

/**
 * @param int $kKategorie
 */
function loescheKategorie($kKategorie)
{
    $kKategorie = (int)$kKategorie;
//    error_log('deleting category ' . $kKategorie);
//    $category = Shop::DB()->query("SELECT * FROM tkategorie WHERE kKategorie = " . $kKategorie, 2);
//    if (is_array($category)) {
//        foreach ($category as $_category) {
//            if (isset($_category->kOberKategorie)) {
//                error_log('flushing1 kat ' . $_category->kOberKategorie);
//                $cache->flushTags(array(CACHING_GROUP_CATEGORY . '_' . $_category->kOberKategorie));
//                if ($cache->isPageCacheEnabled()) {
//                    if (!isset($smarty)) {
//                        global $smarty;
//                    }
//                    $smarty->clearCache(null, 'jtlc|category|cid' . $_category->kOberKategorie);
//                }
//            }
//        }
//    } elseif (isset($category->kOberKategorie)) {
//        $cache->flushTags(array(CACHING_GROUP_CATEGORY . '_' . $category->kOberKategorie));
//        if ($cache->isPageCacheEnabled()) {
//            if (!isset($smarty)) {
//                global $smarty;
//            }
//            $smarty->clearCache(null, 'jtlc|category|cid' . $category->kOberKategorie);
//        }
//    }
//    $cache->flushTags(array(CACHING_GROUP_CATEGORY . '_' . $kKategorie));
//    if ($cache->isPageCacheEnabled()) {
//        if (!isset($smarty)) {
//            global $smarty;
//        }
//        $smarty->clearCache(null, 'jtlc|category|cid' . $kKategorie);
//    }
    //@todo: the above does not really work on parent categories when adding/deleting child categories - because of class.helper.KategorieListe getter/setter

    Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . $kKategorie . " AND cKey = 'kKategorie'", 4);
    Shop::DB()->query("DELETE FROM tkategorie WHERE kKategorie = " . $kKategorie, 4);
    Shop::DB()->query("DELETE FROM tkategorieattribut WHERE kKategorie = " . $kKategorie, 4);
    Shop::DB()->query("DELETE FROM tkategoriekundengruppe WHERE kKategorie = " . $kKategorie, 4);
    Shop::DB()->query("DELETE FROM tkategoriesichtbarkeit WHERE kKategorie = " . $kKategorie, 4);
    Shop::DB()->query("DELETE FROM tkategoriesprache WHERE kKategorie = " . $kKategorie, 4);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Kategorie geloescht: ' . $kKategorie, JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
    }
    Shop::Cache()->flushTags(array(CACHING_GROUP_CATEGORY));
    if (Shop::Cache()->isPageCacheEnabled()) {
        if (!isset($smarty)) {
            $smarty = Shop::Smarty();
        }
        $smarty->clearCache(null, 'jtlc|category');
    }
}

/**
 * update lft/rght values for categories in the nested set model
 *
 * @param int $parent_id
 * @param int $left
 * @return int
 */
function rebuildCategoryTree($parent_id, $left)
{
    $left = intval($left);
    // the right value of this node is the left value + 1
    $right = $left + 1;
    // get all children of this node
    $result = Shop::DB()->query("SELECT kKategorie FROM tkategorie WHERE kOberKategorie = " . (int)$parent_id . " ORDER BY nSort, cName", 2);
    foreach ($result as $_res) {
        $right = rebuildCategoryTree($_res->kKategorie, $right);
    }
    // we've got the left value, and now that we've processed the children of this node we also know the right value
    Shop::DB()->query("UPDATE tkategorie SET lft = " . $left . ", rght = " . $right . " WHERE kKategorie = " . $parent_id, 3);

    // return the right value of this node + 1
    return $right + 1;
}

/**
 * @param array $kOberKategorie_arr
 * @param int   $nLevel
 */
function updateKategorieLevel(array $kOberKategorie_arr = null, $nLevel = 1)
{
    $nLevel = (int)$nLevel;
    $cSql   = 'WHERE kOberKategorie = 0';
    if ($kOberKategorie_arr === null) {
        Shop::DB()->query("TRUNCATE tkategorielevel", 4);
    } else {
        $cSql = 'WHERE kOberKategorie IN (' . implode(',', $kOberKategorie_arr) . ')';
    }

    $oKategorie_arr = Shop::DB()->query(
        "SELECT kKategorie, kOberKategorie
            FROM tkategorie
            {$cSql}", 2
    );
    if (count($oKategorie_arr) > 0) {
        $kKategorie_arr = array();
        foreach ($oKategorie_arr as $oKategorie) {
            $kKategorie_arr[] = (int)$oKategorie->kKategorie;

            $oKategorieLevel             = new stdClass();
            $oKategorieLevel->kKategorie = (int)$oKategorie->kKategorie;
            $oKategorieLevel->nLevel     = (int)$nLevel;

            Shop::DB()->insert('tkategorielevel', $oKategorieLevel);
        }

        updateKategorieLevel($kKategorie_arr, $nLevel + 1);
    }
}
