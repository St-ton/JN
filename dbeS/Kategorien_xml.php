<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/syncinclude.php';
//smarty lib
global $smarty;

if ($smarty === null) {
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
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if (isset($xml['tkategorie attr']['nGesamt']) || isset($xml['tkategorie attr']['nAktuell'])) {
                    setMetaLimit($xml['tkategorie attr']['nAktuell'], $xml['tkategorie attr']['nGesamt']);
                    unset($xml['tkategorie attr']['nGesamt']);
                    unset($xml['tkategorie attr']['nAktuell']);
                }

                if ($zip['filename'] === 'katdel.xml') {
                    bearbeiteDeletes($xml);
                } else {
                    bearbeiteInsert($xml);
                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }

            LastJob::getInstance()->run(LASTJOBS_KATEGORIEUPDATE, 'Kategorien_xml');
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
    }
}

if ($return === 2) {
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
        if (!is_array($xml['del_kategorien']['kKategorie']) && (int)$xml['del_kategorien']['kKategorie'] > 0) {
            $xml['del_kategorien']['kKategorie'] = [$xml['del_kategorien']['kKategorie']];
        }
        if (is_array($xml['del_kategorien']['kKategorie'])) {
            foreach ($xml['del_kategorien']['kKategorie'] as $kKategorie) {
                $kKategorie = (int)$kKategorie;
                if ($kKategorie > 0) {
                    loescheKategorie($kKategorie);
                    //hole alle artikel raus in dieser Kategorie
                    $oArtikel_arr = Shop::DB()->selectAll('tkategorieartikel', 'kKategorie', $kKategorie, 'kArtikel');
                    //gehe alle Artikel durch
                    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
                        foreach ($oArtikel_arr as $oArtikel) {
                            fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr);
                        }
                    }

                    executeHook(HOOK_KATEGORIE_XML_BEARBEITEDELETES, ['kKategorie' => $kKategorie]);
                }
            }
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
        $Kategorie->kKategorie     = (int)$xml['tkategorie attr']['kKategorie'];
        $Kategorie->kOberKategorie = (int)$xml['tkategorie attr']['kOberKategorie'];
    }
    if (!$Kategorie->kKategorie) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('kKategorie fehlt! XML: ' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'Kategorien_xml');
        }

        return;
    }
    if (is_array($xml['tkategorie'])) {
        // Altes SEO merken => falls sich es bei der aktualisierten Kategorie ändert => Eintrag in tredirect
        $oDataOld      = Shop::DB()->query(
            "SELECT cSeo, lft, rght, nLevel
                FROM tkategorie
                WHERE kKategorie = " . $Kategorie->kKategorie, 1
        );
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
            $kategorie_arr[0]->lft                   = isset($oDataOld->lft) ? $oDataOld->lft : 0;
            $kategorie_arr[0]->rght                  = isset($oDataOld->rght) ? $oDataOld->rght : 0;
            $kategorie_arr[0]->nLevel                = isset($oDataOld->nLevel) ? $oDataOld->nLevel : 0;
            DBUpdateInsert('tkategorie', $kategorie_arr, 'kKategorie');
            // Insert into tredirect weil sich das SEO geändert hat
            if (isset($oDataOld->cSeo)) {
                checkDbeSXmlRedirect($oDataOld->cSeo, $kategorie_arr[0]->cSeo);
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

            executeHook(HOOK_KATEGORIE_XML_BEARBEITEINSERT, ['oKategorie' => $kategorie_arr[0]]);
        }

        //Kategoriesprache
        $kategoriesprache_arr = mapArray($xml['tkategorie'], 'tkategoriesprache', $GLOBALS['mKategorieSprache']);
        if (is_array($kategoriesprache_arr)) {
            $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            $lCount                = count($kategoriesprache_arr);
            for ($i = 0; $i < $lCount; ++$i) {
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
                DBUpdateInsert('tkategoriesprache', [$kategoriesprache_arr[$i]], 'kKategorie', 'kSprache');

                Shop::DB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kKategorie', (int)$kategoriesprache_arr[$i]->kKategorie, (int)$kategoriesprache_arr[$i]->kSprache]
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
            $oArtikel_arr = Shop::DB()->selectAll('tkategorieartikel', 'kKategorie', $kategorie_arr[0]->kKategorie, 'kArtikel');
            //gehe alle Artikel durch und ermittle max rabatt
            if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
                foreach ($oArtikel_arr as $oArtikel) {
                    fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr);
                }
            }
        }

        updateXMLinDB($xml['tkategorie'], 'tkategorieattribut', $GLOBALS['mKategorieAttribut'], 'kKategorieAttribut');
        updateXMLinDB($xml['tkategorie'], 'tkategoriesichtbarkeit', $GLOBALS['mKategorieSichtbarkeit'], 'kKundengruppe', 'kKategorie');

        $oAttribute_arr = mapArray($xml['tkategorie'], 'tattribut', $GLOBALS['mNormalKategorieAttribut']);
        if (is_array($oAttribute_arr) && count($oAttribute_arr)) {
            // Jenachdem ob es ein oder mehrere Attribute gibt, unterscheidet sich die Struktur des XML-Arrays
            $single = isset($xml['tkategorie']['tattribut attr']) && is_array($xml['tkategorie']['tattribut attr']);
            $i      = 0;
            foreach ($oAttribute_arr as $oAttribut) {
                $parentXML = $single ? $xml['tkategorie']['tattribut'] : $xml['tkategorie']['tattribut'][$i++];
                saveKategorieAttribut($parentXML, $oAttribut);
            }
        }

        $cache = Shop::Cache();
//        $flushArray = [];
//        $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kKategorie;
//        if (isset($Kategorie->kOberKategorie) && $Kategorie->kOberKategorie > 0) {
//            $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kOberKategorie;
//        }
//        $cache->flushTags($flushArray);
        //@todo: the above does not really work on parent categories when adding/deleting child categories
        $cache->flushTags([CACHING_GROUP_CATEGORY]);
    }
}

/**
 * @param int $kKategorie
 */
function loescheKategorie($kKategorie)
{
    $kKategorie           = (int)$kKategorie;
    $deleteAttributes_arr = Shop::DB()->selectAll('tkategorieattribut', 'kKategorie', $kKategorie, 'kKategorieAttribut');
    if (is_array($deleteAttributes_arr)) {
        foreach ($deleteAttributes_arr as $deleteAttribute) {
            deleteKategorieAttribut($deleteAttribute->kKategorieAttribut);
        }
    }
    Shop::DB()->delete('tseo', ['kKey', 'cKey'], [$kKategorie, 'kKategorie']);
    Shop::DB()->delete('tkategorie', 'kKategorie', $kKategorie);
    Shop::DB()->delete('tkategoriekundengruppe', 'kKategorie', $kKategorie);
    Shop::DB()->delete('tkategoriesichtbarkeit', 'kKategorie', $kKategorie);
    Shop::DB()->delete('tkategoriesprache', 'kKategorie', $kKategorie);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Kategorie geloescht: ' . $kKategorie, JTLLOG_LEVEL_DEBUG, false, 'Kategorien_xml');
    }
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY]);
}

/**
 * @param int $kKategorieAttribut
 */
function deleteKategorieAttribut($kKategorieAttribut)
{
    $kKategorieAttribut = (int)$kKategorieAttribut;

    Shop::DB()->delete('tkategorieattributsprache', 'kAttribut', $kKategorieAttribut);
    Shop::DB()->delete('tkategorieattribut', 'kKategorieAttribut', $kKategorieAttribut);
}

/**
 * @param array $xmlParent
 * @param object $oAttribut
 * @return int
 */
function saveKategorieAttribut($xmlParent, $oAttribut)
{
    // Fix: die Wawi überträgt für die normalen Attribute die ID in kAttribut statt in kKategorieAttribut
    if (!isset($oAttribut->kKategorieAttribut) && isset($oAttribut->kAttribut)) {
        $oAttribut->kKategorieAttribut = (int)$oAttribut->kAttribut;
        unset($oAttribut->kAttribut);
    }

    Jtllog::writeLog('Speichere Kategorieattribut: ' . var_export($oAttribut, true), JTLLOG_LEVEL_DEBUG);

    DBUpdateInsert('tkategorieattribut', [$oAttribut], 'kKategorieAttribut', 'kKategorie');
    $oAttribSprache_arr = mapArray($xmlParent, 'tattributsprache', $GLOBALS['mKategorieAttributSprache']);

    if (is_array($oAttribSprache_arr)) {
        // Die Standardsprache wird nicht separat übertragen und wird deshalb aus den Attributwerten gesetzt
        array_unshift($oAttribSprache_arr, (object)[
            'kAttribut' => $oAttribut->kKategorieAttribut,
            'kSprache'  => Shop::DB()->select('tsprache', 'cShopStandard', 'Y')->kSprache,
            'cName'     => $oAttribut->cName,
            'cWert'     => $oAttribut->cWert,
        ]);

        Jtllog::writeLog('Speichere Kategorieattributsprache: ' . var_export($oAttribSprache_arr, true), JTLLOG_LEVEL_DEBUG);
        DBUpdateInsert('tkategorieattributsprache', $oAttribSprache_arr, 'kAttribut', 'kSprache');
    }

    return $oAttribut->kKategorieAttribut;
}

/**
 * ToDo: Implement different updatestrategies in dependece of total and current category blocks
 * @param $nAktuell
 * @param $nGesamt
 * @return bool
 */
function setMetaLimit($nAktuell, $nGesamt)
{
    return false;
}
