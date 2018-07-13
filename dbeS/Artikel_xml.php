<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$archive = null;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $articleIDs = [];
    $zipFile    = checkFile();
    $return     = 2;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Artikel - Entpacke: ' . $zipFile, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';

    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        if (Jtllog::doLog()) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $conf   = Shop::getSettings([CONF_GLOBAL]);
        foreach ($syncFiles as $i => $xmlFile) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('bearbeite: ' . $xmlFile . ' size: ' .
                    filesize($xmlFile), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (strpos($xmlFile, 'artdel.xml') !== false) {
                $articleIDs = array_merge($articleIDs, bearbeiteDeletes($xml, $conf));
            } else {
                foreach (bearbeiteInsert($xml, $conf) as $articleID) {
                    $articleIDs[] = $articleID;
                }
            }
            if ($i === 0) {
                Shop::Container()->getDB()->query(
                    "UPDATE tsuchcache
                        SET dGueltigBis = DATE_ADD(now(), INTERVAL " . SUCHCACHE_LEBENSDAUER . " MINUTE)
                        WHERE dGueltigBis IS NULL",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
        clearProductCaches($articleIDs);
    }
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $zipFile, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
}

/**
 * @param array $xml
 * @param array $conf
 * @return array - list of article IDs
 */
function bearbeiteDeletes($xml, array $conf)
{
    $res = [];
    if (!is_array($xml['del_artikel'])) {
        return $res;
    }
    if (!is_array($xml['del_artikel']['kArtikel'])) {
        $xml['del_artikel']['kArtikel'] = [$xml['del_artikel']['kArtikel']];
    }
    foreach ($xml['del_artikel']['kArtikel'] as $kArtikel) {
        $kArtikel      = (int)$kArtikel;
        $kVaterArtikel = ArtikelHelper::getParent($kArtikel);
        $nIstVater     = $kVaterArtikel > 0 ? 0 : 1;
        checkArtikelBildLoeschung($kArtikel);

        Shop::Container()->getDB()->queryPrepared(
            'DELETE teigenschaftkombiwert
                FROM teigenschaftkombiwert
                JOIN tartikel 
                    ON tartikel.kArtikel = :pid
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi',
            ['pid' => $kArtikel],
            \DB\ReturnType::AFFECTED_ROWS
        );
        $res[] = loescheArtikel($kArtikel, $nIstVater, false, $conf);
        // Lösche Artikel aus tartikelkategorierabatt
        Shop::Container()->getDB()->delete('tartikelkategorierabatt', 'kArtikel', $kArtikel);
        // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
        if ($kVaterArtikel > 0) {
            Artikel::beachteVarikombiMerkmalLagerbestand($kVaterArtikel);
        }

        executeHook(HOOK_ARTIKEL_XML_BEARBEITEDELETES, ['kArtikel' => $kArtikel]);
    }

    return $res;
}

/**
 * @param array $xml
 * @param array $conf
 * @return array - list of article IDs to flush
 */
function bearbeiteInsert($xml, array $conf)
{
    $res = [];

    $Artikel           = new stdClass();
    $Artikel->kArtikel = 0;

    if (is_array($xml['tartikel attr'])) {
        $Artikel->kArtikel = (int)$xml['tartikel attr']['kArtikel'];
    }
    if (!$Artikel->kArtikel) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('kArtikel fehlt! XML:' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }

        return $res;
    }
    if (!is_array($xml['tartikel'])) {
        return $res;
    }
    $artikel_arr = mapArray($xml, 'tartikel', $GLOBALS['mArtikel']);
    // Alten SEO-Pfad merken. Eintrag in tredirect, wenn sich der Pfad geändert hat.
    $oSeoOld       = Shop::Container()->getDB()->select(
        'tartikel',
        'kArtikel', (int)$Artikel->kArtikel,
        null, null,
        null, null,
        false,
        'cSeo'
    );
    $oSeoAssoc_arr = getSeoFromDB($Artikel->kArtikel, 'kArtikel', null, 'kSprache');
    $isParent      = isset($artikel_arr[0]->nIstVater) ? 1 : 0;

    if (isset($xml['tartikel']['tkategorieartikel'])
        && $conf['global']['kategorien_anzeigefilter'] == EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
        && Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CATEGORY)
    ) {
        $currentArticleCategories = [];
        $newArticleCategories     = [];
        $flush                    = false;
        // get list of all categories the article is currently associated with
        $currentArticleCategoriesObject = Shop::Container()->getDB()->selectAll(
            'tkategorieartikel',
            'kArtikel',
            (int)$Artikel->kArtikel,
            'kKategorie'
        );
        foreach ($currentArticleCategoriesObject as $obj) {
            $currentArticleCategories[] = (int)$obj->kKategorie;
        }
        // get list of all categories the article will be associated with after this update
        $newArticleCategoriesObject = mapArray(
            $xml['tartikel'],
            'tkategorieartikel',
            $GLOBALS['mKategorieArtikel']
        );
        foreach ($newArticleCategoriesObject as $newArticleCategory) {
            $newArticleCategories[] = (int)$newArticleCategory->kKategorie;
        }
        $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        foreach ($newArticleCategories as $newArticleCategory) {
            if (!in_array($newArticleCategory, $currentArticleCategories, true)) {
                // the article was previously not associated with this category
                $articleCount = Shop::Container()->getDB()->query(
                    "SELECT count(tkategorieartikel.kArtikel) AS count
                        FROM tkategorieartikel
                        LEFT JOIN tartikel
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel
                        WHERE tkategorieartikel.kKategorie = {$newArticleCategory} " . $stockFilter,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($articleCount->count) && (int)$articleCount->count === 0) {
                    // the category was previously empty - flush cache
                    $flush = true;
                    break;
                }
            }
        }

        if ($flush === false) {
            foreach ($currentArticleCategories as $category) {
                // check if the article is removed from an existing category
                if (!in_array($category, $newArticleCategories, true)) {
                    // check if the article was the only one in at least one of these categories
                    $articleCount = Shop::Container()->getDB()->query(
                        "SELECT count(tkategorieartikel.kArtikel) AS count
                            FROM tkategorieartikel
                            LEFT JOIN tartikel
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel
                            WHERE tkategorieartikel.kKategorie = {$category} " . $stockFilter,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (!isset($articleCount->count) || (int)$articleCount->count === 1) {
                        // the category only had this article in it - flush cache
                        $flush = true;
                        break;
                    }
                }
            }
        }
        if ($flush === false
            && $conf['global']['artikel_artikelanzeigefilter'] != EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
        ) {
            $check         = false;
            $currentStatus = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $Artikel->kArtikel,
                null, null,
                null, null,
                false,
                'cLagerBeachten, cLagerKleinerNull, fLagerbestand'
            );
            if (isset($currentStatus->cLagerBeachten)) {
                if (($currentStatus->fLagerbestand <= 0 && $xml['tartikel']['fLagerbestand'] > 0)
                    // article was not in stock before but is now - check if flush is necessary
                    || ($currentStatus->fLagerbestand > 0 && $xml['tartikel']['fLagerbestand'] <= 0)
                    // article was in stock before but is not anymore - check if flush is necessary
                    || ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
                        && $currentStatus->cLagerKleinerNull !== $xml['tartikel']['cLagerKleinerNull'])
                    // overselling status changed - check if flush is necessary
                    || ($currentStatus->cLagerBeachten !== $xml['tartikel']['cLagerBeachten']
                        && $xml['tartikel']['fLagerbestand'] <= 0)
                ) {
                    $check = true;
                }
                if ($check === true) {
                    if (is_array($newArticleCategories) && !empty($newArticleCategories)) {
                        // get count of visible articles in the article's futre categories
                        $articleCount = Shop::Container()->getDB()->query(
                            "SELECT tkategorieartikel.kKategorie, count(tkategorieartikel.kArtikel) AS count
                            FROM tkategorieartikel
                            LEFT JOIN tartikel
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel
                            WHERE tkategorieartikel.kKategorie IN (" . implode(',', $newArticleCategories) . ") " .
                            $stockFilter .
                            " GROUP BY tkategorieartikel.kKategorie",
                            \DB\ReturnType::ARRAY_OF_OBJECTS
                        );
                        foreach ($newArticleCategories as $nac) {
                            if (is_array($articleCount) && !empty($articleCount)) {
                                foreach ($articleCount as $ac) {
                                    if ($ac->kKategorie == $nac
                                        && (($currentStatus->cLagerBeachten !== 'Y' && $ac->count == 1)
                                            || ($currentStatus->cLagerBeachten === 'Y' && $ac->count == 0))
                                    ) {
                                        // there was just one product that is now sold out
                                        // or there were just sold out products and now it's not sold out anymore
                                        $flush = true;
                                        break;
                                    }
                                }
                            } else {
                                $flush = true;
                                break;
                            }
                        }
                    } else {
                        $flush = true;
                    }
                }
            }
        }

        if ($flush === true) {
            flushCategoryTreeCache();
        }
    }
    $downloadKeys = getDownloadKeys($Artikel->kArtikel);
    loescheArtikel($Artikel->kArtikel, $isParent, true, $conf);
    if ($artikel_arr[0]->kArtikel > 0) {
        if (!$artikel_arr[0]->cSeo) {
            //get seo-path from productname, but replace slashes
            $artikel_arr[0]->cSeo = getFlatSeoPath($artikel_arr[0]->cName);
        }
        $artikel_arr[0]->cSeo = getSeo($artikel_arr[0]->cSeo);
        $artikel_arr[0]->cSeo = checkSeo($artikel_arr[0]->cSeo);
        //persistente werte
        $artikel_arr[0]->dLetzteAktualisierung = 'now()';
        //mysql strict fixes
        if (isset($artikel_arr[0]->dMHD) && $artikel_arr[0]->dMHD === '') {
            $artikel_arr[0]->dMHD = '0000-00-00';
        }
        if (isset($artikel_arr[0]->dErstellt) && $artikel_arr[0]->dErstellt === '') {
            $artikel_arr[0]->dErstellt = 'now()';
        }
        if (isset($artikel_arr[0]->dZulaufDatum) && $artikel_arr[0]->dZulaufDatum === '') {
            $artikel_arr[0]->dZulaufDatum = '0000-00-00';
        } elseif (!isset($artikel_arr[0]->dZulaufDatum)) {
            $artikel_arr[0]->dZulaufDatum = '0000-00-00';
        }
        if (isset($artikel_arr[0]->dErscheinungsdatum) && $artikel_arr[0]->dErscheinungsdatum === '') {
            $artikel_arr[0]->dErscheinungsdatum = '0000-00-00';
        }
        if (isset($artikel_arr[0]->fLieferantenlagerbestand) && $artikel_arr[0]->fLieferantenlagerbestand === '') {
            $artikel_arr[0]->fLieferantenlagerbestand = 0;
        } elseif (!isset($artikel_arr[0]->fLieferantenlagerbestand)) {
            $artikel_arr[0]->fLieferantenlagerbestand = 0;
        }
        if (isset($artikel_arr[0]->fZulauf) && $artikel_arr[0]->fZulauf === '') {
            $artikel_arr[0]->fZulauf = 0;
        } elseif (!isset($artikel_arr[0]->fZulauf)) {
            $artikel_arr[0]->fZulauf = 0;
        }
        if (isset($artikel_arr[0]->fLieferzeit) && $artikel_arr[0]->fLieferzeit === '') {
            $artikel_arr[0]->fLieferzeit = 0;
        } elseif (!isset($artikel_arr[0]->fLieferzeit)) {
            $artikel_arr[0]->fLieferzeit = 0;
        }
        //temp. fix for syncing with wawi 1.0
        if (isset($artikel_arr[0]->kVPEEinheit) && is_array($artikel_arr[0]->kVPEEinheit)) {
            $artikel_arr[0]->kVPEEinheit = $artikel_arr[0]->kVPEEinheit[0];
        }

        //any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
        if (isset($artikel_arr[0]->fLagerbestand) && $artikel_arr[0]->fLagerbestand > 0) {
            $delta = Shop::Container()->getDB()->query(
                "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                        ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$artikel_arr[0]->kArtikel,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ($delta->totalquantity > 0) {
                //subtract delta from stocklevel
                $artikel_arr[0]->fLagerbestand -= $delta->totalquantity;
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog(
                        "Artikel-Sync: Lagerbestand von kArtikel {$artikel_arr[0]->kArtikel} wurde " .
                        "wegen nicht-abgeholter Bestellungen " .
                        "um {$delta->totalquantity} auf {$artikel_arr[0]->fLagerbestand} reduziert.",
                        JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml'
                    );
                }
            }
        }
        DBUpdateInsert('tartikel', $artikel_arr, 'kArtikel');
        executeHook(HOOK_ARTIKEL_XML_BEARBEITEINSERT, ['oArtikel' => $artikel_arr[0]]);
        if (isset($oSeoOld->cSeo)) {
            checkDbeSXmlRedirect($oSeoOld->cSeo, $artikel_arr[0]->cSeo);
        }
        Shop::Container()->getDB()->query(
            "INSERT INTO tseo
                SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
                FROM tartikel, tsprache
                WHERE tartikel.kArtikel = " . (int)$artikel_arr[0]->kArtikel . " 
                    AND tsprache.cStandard = 'Y' 
                    AND tartikel.cSeo != ''",
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
    $artikelsprache_arr    = mapArray($xml['tartikel'], 'tartikelsprache', $GLOBALS['mArtikelSprache']);
    $oShopSpracheAssoc_arr = Sprache::getAllLanguages(1);
    $langCount             = count($artikelsprache_arr);
    for ($i = 0; $i < $langCount; ++$i) {
        if (!Sprache::isShopLanguage($artikelsprache_arr[$i]->kSprache, $oShopSpracheAssoc_arr)) {
            continue;
        }
        if (!$artikelsprache_arr[$i]->cSeo) {
            $artikelsprache_arr[$i]->cSeo = getFlatSeoPath($artikelsprache_arr[$i]->cName);
        }
        if (!$artikelsprache_arr[$i]->cSeo) {
            $artikelsprache_arr[$i]->cSeo = $artikel_arr[0]->cSeo;
        }
        if (!$artikelsprache_arr[$i]->cSeo) {
            $artikelsprache_arr[$i]->cSeo = $artikel_arr[0]->cName;
        }
        $artikelsprache_arr[$i]->cSeo = getSeo($artikelsprache_arr[$i]->cSeo);
        $artikelsprache_arr[$i]->cSeo = checkSeo($artikelsprache_arr[$i]->cSeo);

        DBUpdateInsert('tartikelsprache', [$artikelsprache_arr[$i]], 'kArtikel', 'kSprache');
        Shop::Container()->getDB()->delete(
            'tseo',
            ['cKey', 'kKey', 'kSprache'],
            ['kArtikel', (int)$artikelsprache_arr[$i]->kArtikel, (int)$artikelsprache_arr[$i]->kSprache]
        );

        $oSeo           = new stdClass();
        $oSeo->cSeo     = $artikelsprache_arr[$i]->cSeo;
        $oSeo->cKey     = 'kArtikel';
        $oSeo->kKey     = $artikelsprache_arr[$i]->kArtikel;
        $oSeo->kSprache = $artikelsprache_arr[$i]->kSprache;
        Shop::Container()->getDB()->insert('tseo', $oSeo);
        // Insert into tredirect weil sich das SEO vom Artikel geändert hat
        if (isset($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache])) {
            checkDbeSXmlRedirect($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache]->cSeo,
                $artikelsprache_arr[$i]->cSeo);
        }
    }
    if (isset($xml['tartikel']['tattribut']) && is_array($xml['tartikel']['tattribut'])) {
        $Attribut_arr = mapArray($xml['tartikel'], 'tattribut', $GLOBALS['mAttribut']);
        $aArrCount    = count($Attribut_arr);
        for ($i = 0; $i < $aArrCount; ++$i) {
            if (count($Attribut_arr) < 2) {
                loescheAttribute($xml['tartikel']['tattribut attr']['kAttribut']);
                updateXMLinDB(
                    $xml['tartikel']['tattribut'],
                    'tattributsprache',
                    $GLOBALS['mAttributSprache'],
                    'kAttribut',
                    'kSprache'
                );
            } else {
                loescheAttribute($xml['tartikel']['tattribut'][$i . ' attr']['kAttribut']);
                updateXMLinDB(
                    $xml['tartikel']['tattribut'][$i],
                    'tattributsprache',
                    $GLOBALS['mAttributSprache'],
                    'kAttribut',
                    'kSprache'
                );
            }
        }
        DBUpdateInsert('tattribut', $Attribut_arr, 'kAttribut');
    }
    if (isset($xml['tartikel']['tmediendatei']) && is_array($xml['tartikel']['tmediendatei'])) {
        $oMediendatei_arr = mapArray($xml['tartikel'], 'tmediendatei', $GLOBALS['mMediendatei']);
        $mediaCount       = count($oMediendatei_arr);
        for ($i = 0; $i < $mediaCount; ++$i) {
            if ($mediaCount < 2) {
                loescheMediendateien($xml['tartikel']['tmediendatei attr']['kMedienDatei']);
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'],
                    'tmediendateisprache',
                    $GLOBALS['mMediendateisprache'],
                    'kMedienDatei',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'],
                    'tmediendateiattribut',
                    $GLOBALS['mMediendateiattribut'],
                    'kMedienDateiAttribut'
                );
            } else {
                loescheMediendateien($xml['tartikel']['tmediendatei'][$i . ' attr']['kMedienDatei']);
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'][$i],
                    'tmediendateisprache',
                    $GLOBALS['mMediendateisprache'],
                    'kMedienDatei',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'][$i],
                    'tmediendateiattribut',
                    $GLOBALS['mMediendateiattribut'],
                    'kMedienDateiAttribut'
                );
            }
        }
        DBUpdateInsert('tmediendatei', $oMediendatei_arr, 'kMedienDatei');
    }
    if (isset($xml['tartikel']['tArtikelDownload']) && is_array($xml['tartikel']['tArtikelDownload'])) {
        $oDownload_arr = [];
        loescheDownload($Artikel->kArtikel);
        if (isset($xml['tartikel']['tArtikelDownload']['kDownload'])
            && is_array($xml['tartikel']['tArtikelDownload']['kDownload'])
        ) {
            $kDownload_arr = $xml['tartikel']['tArtikelDownload']['kDownload'];
            foreach ($kDownload_arr as $kDownload) {
                $oArtikelDownload            = new stdClass();
                $oArtikelDownload->kDownload = (int)$kDownload;
                $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
                $oDownload_arr[]             = $oArtikelDownload;

                if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                    unset($downloadKeys[$idx]);
                }
            }
        } else {
            $oArtikelDownload            = new stdClass();
            $oArtikelDownload->kDownload = (int)$xml['tartikel']['tArtikelDownload']['kDownload'];
            $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
            $oDownload_arr[]             = $oArtikelDownload;

            if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                unset($downloadKeys[$idx]);
            }
        }

        DBUpdateInsert('tartikeldownload', $oDownload_arr, 'kArtikel', 'kDownload');
    }
    foreach ($downloadKeys as $kDownload) {
        loescheDownload($Artikel->kArtikel, $kDownload);
    }
    if (isset($xml['tartikel']['tstueckliste']) && is_array($xml['tartikel']['tstueckliste'])) {
        $oStueckliste_arr = mapArray($xml['tartikel'], 'tstueckliste', $GLOBALS['mStueckliste']);
        $cacheIDs         = [];
        if (count($oStueckliste_arr) > 0) {
            loescheStueckliste($oStueckliste_arr[0]->kStueckliste);
        }
        DBUpdateInsert('tstueckliste', $oStueckliste_arr, 'kStueckliste', 'kArtikel');
        foreach ($oStueckliste_arr as $_sl) {
            if (isset($_sl->kArtikel)) {
                $cacheIDs[] = CACHING_GROUP_ARTICLE . '_' . (int)$_sl->kArtikel;
            }
        }
        if (count($cacheIDs) > 0) {
            Shop::Cache()->flushTags($cacheIDs);
        }
    }
    if (isset($xml['tartikel']['tartikelupload']) && is_array($xml['tartikel']['tartikelupload'])) {
        $oArtikelUpload_arr = mapArray($xml['tartikel'], 'tartikelupload', $GLOBALS['mArtikelUpload']);
        foreach ($oArtikelUpload_arr as &$oArtikelUpload) {
            $oArtikelUpload->nTyp          = 3;
            $oArtikelUpload->kUploadSchema = $oArtikelUpload->kArtikelUpload;
            $oArtikelUpload->kCustomID     = $oArtikelUpload->kArtikel;
            unset($oArtikelUpload->kArtikelUpload, $oArtikelUpload->kArtikel);
        }
        unset($oArtikelUpload);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog(
                'oArtikelUpload_arr: ' . print_r($oArtikelUpload_arr, true),
                JTLLOG_LEVEL_DEBUG,
                false,
                'Artikel_xml'
            );
        }
        DBUpdateInsert('tuploadschema', $oArtikelUpload_arr, 'kUploadSchema', 'kCustomID');
        if (count($oArtikelUpload_arr) < 2) {
            $oArtikelUploadSprache_arr = mapArray(
                $xml['tartikel']['tartikelupload'],
                'tartikeluploadsprache',
                $GLOBALS['mArtikelUploadSprache']
            );
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Artikel_xml'
                );
            }
            DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
        } else {
            $ulCount = count($oArtikelUpload_arr);
            for ($i = 0; $i < $ulCount; ++$i) {
                $oArtikelUploadSprache_arr = mapArray(
                    $xml['tartikel']['tartikelupload'][$i],
                    'tartikeluploadsprache',
                    $GLOBALS['mArtikelUploadSprache']
                );
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog(
                        'oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true),
                        JTLLOG_LEVEL_DEBUG,
                        false,
                        'Artikel_xml'
                    );
                }
                DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
            }
        }
    }
    Shop::Container()->getDB()->delete('tartikelabnahme', 'kArtikel', $artikel_arr[0]->kArtikel);
    if (isset($xml['tartikel']['tartikelabnahme']) && is_array($xml['tartikel']['tartikelabnahme'])) {
        $oArtikelAbnahmeIntervalle_arr = mapArray($xml['tartikel'], 'tartikelabnahme', $GLOBALS['mArtikelAbnahme']);
        DBUpdateInsert('tartikelabnahme', $oArtikelAbnahmeIntervalle_arr, 'kArtikel', 'kKundengruppe');
    }
    if (isset($xml['tartikel']['tartikelkonfiggruppe']) && is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
        $oArtikelKonfig_arr = mapArray($xml['tartikel'], 'tartikelkonfiggruppe', $GLOBALS['mArtikelkonfiggruppe']);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog(
                'oArtikelKonfig_arr: ' . print_r($oArtikelKonfig_arr, true),
                JTLLOG_LEVEL_DEBUG,
                false,
                'Artikel_xml'
            );
        }
        DBUpdateInsert('tartikelkonfiggruppe', $oArtikelKonfig_arr, 'kArtikel', 'kKonfiggruppe');
    }
    if (isset($xml['tartikel']['tartikelsonderpreis'])) {
        updateXMLinDB(
            $xml['tartikel']['tartikelsonderpreis'],
            'tsonderpreise',
            $GLOBALS['mSonderpreise'],
            'kArtikelSonderpreis',
            'kKundengruppe'
        );
    }

    updateXMLinDB($xml['tartikel'], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

    if (isset($xml['tartikel']['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
        handleNewPriceFormat($xml['tartikel']);
    } else {
        handleOldPriceFormat(mapArray($xml['tartikel'], 'tpreise', $GLOBALS['mPreise']));
    }

    updateXMLinDB($xml['tartikel'], 'tartikelsonderpreis', $GLOBALS['mArtikelSonderpreis'], 'kArtikelSonderpreis');
    updateXMLinDB($xml['tartikel'], 'tkategorieartikel', $GLOBALS['mKategorieArtikel'], 'kKategorieArtikel');
    updateXMLinDB($xml['tartikel'], 'tartikelattribut', $GLOBALS['mArtikelAttribut'], 'kArtikelAttribut');
    updateXMLinDB($xml['tartikel'], 'tartikelsichtbarkeit', $GLOBALS['mArtikelSichtbarkeit'], 'kKundengruppe',
        'kArtikel');
    updateXMLinDB($xml['tartikel'], 'txsell', $GLOBALS['mXSell'], 'kXSell');
    updateXMLinDB($xml['tartikel'], 'tartikelmerkmal', $GLOBALS['mArtikelSichtbarkeit'], 'kMermalWert');
    if ((int)$artikel_arr[0]->nIstVater === 1) {
        Shop::Container()->getDB()->query(
            'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$artikel_arr[0]->kArtikel . '
                     ) AS x
                 )
                WHERE kArtikel = ' . (int)$artikel_arr[0]->kArtikel,
            \DB\ReturnType::AFFECTED_ROWS
        );
        Artikel::beachteVarikombiMerkmalLagerbestand(
            $artikel_arr[0]->kArtikel,
            $conf['global']['artikel_artikelanzeigefilter']
        );
    } elseif (isset($artikel_arr[0]->kVaterArtikel) && $artikel_arr[0]->kVaterArtikel > 0) {
        Shop::Container()->getDB()->query(
            "UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = " . (int)$artikel_arr[0]->kVaterArtikel . "
                    ) AS x
                )
                WHERE kArtikel = " . (int)$artikel_arr[0]->kVaterArtikel,
            \DB\ReturnType::AFFECTED_ROWS);
        // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
        Artikel::beachteVarikombiMerkmalLagerbestand(
            $artikel_arr[0]->kVaterArtikel,
            $conf['global']['artikel_artikelanzeigefilter']
        );
    }
    if (isset($xml['tartikel']['SQLDEL']) && strlen($xml['tartikel']['SQLDEL']) > 10) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('SQLDEL: ' . $xml['tartikel']['SQLDEL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }
        $cSQL_arr = explode("\n", $xml['tartikel']['SQLDEL']);
        foreach ($cSQL_arr as $cSQL) {
            if (strlen($cSQL) > 10) {
                Shop::Container()->getDB()->query($cSQL, \DB\ReturnType::AFFECTED_ROWS);
            }
        }
    }
    if (isset($xml['tartikel']['SQL']) && strlen($xml['tartikel']['SQL']) > 10) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('SQL: ' . $xml['tartikel']['SQL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }
        $cSQL_arr = explode("\n", $xml['tartikel']['SQL']);
        foreach ($cSQL_arr as $cSQL) {
            if (strlen($cSQL) > 10) {
                // Pre Wawi 0.99862 fix
                if (isset($artikel_arr[0]->kVaterArtikel)
                    && $artikel_arr[0]->kVaterArtikel > 0
                    && !isset($xml['tartikel']['SQLDEL'])
                    && strpos($cSQL, 'teigenschaftkombiwert') !== false
                ) {
                    $cDel     = substr($cSQL, strpos($cSQL, 'values ') + strlen('values '));
                    $cDel_arr = str_replace(['(', ')'], '', explode('),(', $cDel));
                    $kKey_arr = [];
                    foreach ($cDel_arr as $cDel) {
                        $kKey_arr[] = (int)substr($cDel, 0, strpos($cDel, ','));
                    }
                    Shop::Container()->getDB()->query(
                        'DELETE
                            FROM teigenschaftkombiwert 
                            WHERE kEigenschaftKombi IN (' . implode(',', $kKey_arr) . ')',
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
                Shop::Container()->getDB()->query($cSQL, \DB\ReturnType::AFFECTED_ROWS);
            }
        }
    }
    // Artikel Warenlager
    Shop::Container()->getDB()->delete('tartikelwarenlager', 'kArtikel', (int)$xml['tartikel attr']['kArtikel']);
    if (isset($xml['tartikel']['tartikelwarenlager']) && is_array($xml['tartikel']['tartikelwarenlager'])) {
        $oArtikelWarenlager_arr = mapArray($xml['tartikel'], 'tartikelwarenlager', $GLOBALS['mArtikelWarenlager']);

        foreach ($oArtikelWarenlager_arr as $oArtikelWarenlager) {
            if (isset($oArtikelWarenlager->dZulaufDatum) && $oArtikelWarenlager->dZulaufDatum === '') {
                $oArtikelWarenlager->dZulaufDatum = '0000-00-00 00:00:00';
            }
            // Prevent SQL-Exception if duplicate datasets will be sent falsely
            Shop::Container()->getDB()->queryPrepared(
                'INSERT INTO tartikelwarenlager (kArtikel, kWarenlager, fBestand, fZulauf, dZulaufDatum)
                    VALUES (:kArtikel, :kWarenlager, :fBestand, :fZulauf, :dZulaufDatum)
                    ON DUPLICATE KEY UPDATE
                        fBestand = :fBestand,
                        fZulauf = :fZulauf,
                        dZulaufDatum = :dZulaufDatum',
                [
                    'kArtikel'     => $oArtikelWarenlager->kArtikel,
                    'kWarenlager'  => $oArtikelWarenlager->kWarenlager,
                    'fBestand'     => $oArtikelWarenlager->fBestand,
                    'fZulauf'      => $oArtikelWarenlager->fZulauf,
                    'dZulaufDatum' => $oArtikelWarenlager->dZulaufDatum,
                ],
                \DB\ReturnType::QUERYSINGLE
            );
        }
    }
    $bTesteSonderpreis = false;
    if (isset($xml['tartikel']['tartikelsonderpreis']) && is_array($xml['tartikel']['tartikelsonderpreis'])) {
        $ArtikelSonderpreis_arr = mapArray(
            $xml['tartikel'],
            'tartikelsonderpreis',
            $GLOBALS['mArtikelSonderpreis']
        );
        if ($ArtikelSonderpreis_arr[0]->cAktiv === 'Y') {
            $specialPriceStart = explode('-', $ArtikelSonderpreis_arr[0]->dStart);
            if (count($specialPriceStart) > 2) {
                list($start_jahr, $start_monat, $start_tag) = $specialPriceStart;
            } else {
                $start_jahr  = null;
                $start_monat = null;
                $start_tag   = null;
            }
            $specialPriceEnd = explode('-', $ArtikelSonderpreis_arr[0]->dEnde);
            if (count($specialPriceEnd) > 2) {
                list($ende_jahr, $ende_monat, $ende_tag) = $specialPriceEnd;
            } else {
                $ende_jahr  = null;
                $ende_monat = null;
                $ende_tag   = null;
            }
            $nEndStamp   = mktime(null);
            $nStartStamp = mktime(0, 0, 0, $start_monat, $start_tag, $start_jahr);
            $nNowStamp   = time();

            if ($ende_jahr > 0) {
                $nEndStamp = mktime(0, 0, 0, $ende_monat, $ende_tag + 1, $ende_jahr);
            }
            $bTesteSonderpreis = ($nNowStamp >= $nStartStamp
                && ($nNowStamp < $nEndStamp || (int)$ArtikelSonderpreis_arr[0]->dEnde === 0)
                && ($ArtikelSonderpreis_arr[0]->nIstAnzahl === 0 || ($ArtikelSonderpreis_arr[0]->nIstAnzahl === '1'
                        && (int)$ArtikelSonderpreis_arr[0]->nAnzahl < (int)$xml['tartikel']['fLagerbestand'])));
        }
        $spCount = count($ArtikelSonderpreis_arr);
        for ($i = 0; $i < $spCount; ++$i) {
            if ($bTesteSonderpreis === true) {
                $Sonderpreise_arr = mapArray(
                    $xml['tartikel']['tartikelsonderpreis'],
                    'tsonderpreise',
                    $GLOBALS['mSonderpreise']
                );
                foreach ($Sonderpreise_arr as $Sonderpreise) {
                    setzePreisverlauf(
                        $ArtikelSonderpreis_arr[0]->kArtikel,
                        $Sonderpreise->kKundengruppe,
                        $Sonderpreise->fNettoPreis
                    );
                }
            }
            updateXMLinDB(
                $xml['tartikel']['tartikelsonderpreis'],
                'tsonderpreise',
                $GLOBALS['mSonderpreise'],
                'kArtikelSonderpreis',
                'kKundengruppe'
            );
        }
        DBUpdateInsert('tartikelsonderpreis', $ArtikelSonderpreis_arr, 'kArtikelSonderpreis');
    }
    // Preise für Preisverlauf
    // NettoPreis übertragen, falls kein Sonderpreis gesetzt wurde
    if (!($bTesteSonderpreis === true
        && isset($xml['tartikel']['tartikelsonderpreis'])
        && is_array($xml['tartikel']['tartikelsonderpreis']))
    ) {
        $oPreis_arr = mapArray($xml['tartikel'], 'tpreise', $GLOBALS['mPreise']);
        foreach ($oPreis_arr as $oPreis) {
            setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
        }
    }
    if (isset($xml['tartikel']['teigenschaft']) && is_array($xml['tartikel']['teigenschaft'])) {
        $Eigenschaft_arr = mapArray($xml['tartikel'], 'teigenschaft', $GLOBALS['mEigenschaft']);
        $eCount          = count($Eigenschaft_arr);
        for ($i = 0; $i < $eCount; ++$i) {
            if (count($Eigenschaft_arr) < 2) {
                loescheEigenschaft($xml['tartikel']['teigenschaft attr']['kEigenschaft']);
                updateXMLinDB(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftsprache',
                    $GLOBALS['mEigenschaftSprache'],
                    'kEigenschaft',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftsichtbarkeit',
                    $GLOBALS['mEigenschaftsichtbarkeit'],
                    'kEigenschaft',
                    'kKundengruppe'
                );
                $EigenschaftWert_arr = mapArray(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftwert',
                    $GLOBALS['mEigenschaftWert']
                );
                $ewCount             = count($EigenschaftWert_arr);
                for ($o = 0; $o < $ewCount; ++$o) {
                    if ($ewCount < 2) {
                        loescheEigenschaftWert($xml['tartikel']['teigenschaft']['teigenschaftwert attr']['kEigenschaftWert']);
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertsprache',
                            $GLOBALS['mEigenschaftWertSprache'],
                            'kEigenschaftWert',
                            'kSprache'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertaufpreis',
                            $GLOBALS['mEigenschaftWertAufpreis'],
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertsichtbarkeit',
                            $GLOBALS['mEigenschaftWertSichtbarkeit'],
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertabhaengigkeit',
                            $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                            'kEigenschaftWert',
                            'kEigenschaftWertZiel'
                        );
                    } else {
                        loescheEigenschaftWert($xml['tartikel']['teigenschaft']['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']);
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertsprache',
                            $GLOBALS['mEigenschaftWertSprache'],
                            'kEigenschaftWert',
                            'kSprache'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertaufpreis',
                            $GLOBALS['mEigenschaftWertAufpreis'],
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertsichtbarkeit',
                            $GLOBALS['mEigenschaftWertSichtbarkeit'],
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertabhaengigkeit',
                            $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                            'kEigenschaftWert',
                            'kEigenschaftWertZiel'
                        );
                    }
                }
                DBUpdateInsert('teigenschaftwert', $EigenschaftWert_arr, 'kEigenschaftWert');
            } else {
                //@todo: this if was added to be able to sync with wawi 1.0 - check.
                if (isset($xml['tartikel']['teigenschaft'][$i . ' attr'])) {
                    loescheEigenschaft($xml['tartikel']['teigenschaft'][$i . ' attr']['kEigenschaft']);
                }
                //@todo: this if was added to be able to sync with wawi 1.0 - check.
                if (isset($xml['tartikel']['teigenschaft'][$i])) {
                    updateXMLinDB(
                        $xml['tartikel']['teigenschaft'][$i],
                        'teigenschaftsprache',
                        $GLOBALS['mEigenschaftSprache'],
                        'kEigenschaft',
                        'kSprache'
                    );
                    updateXMLinDB(
                        $xml['tartikel']['teigenschaft'][$i],
                        'teigenschaftsichtbarkeit',
                        $GLOBALS['mEigenschaftsichtbarkeit'],
                        'kEigenschaft',
                        'kKundengruppe'
                    );
                    $EigenschaftWert_arr = mapArray(
                        $xml['tartikel']['teigenschaft'][$i],
                        'teigenschaftwert',
                        $GLOBALS['mEigenschaftWert']
                    );
                    $ewCount             = count($EigenschaftWert_arr);
                    for ($o = 0; $o < $ewCount; ++$o) {
                        if ($ewCount < 2) {
                            loescheEigenschaftWert($xml['tartikel']['teigenschaft'][$i]['teigenschaftwert attr']['kEigenschaftWert']);
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                'teigenschaftwertsprache',
                                $GLOBALS['mEigenschaftWertSprache'],
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                'teigenschaftwertaufpreis',
                                $GLOBALS['mEigenschaftWertAufpreis'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                'teigenschaftwertsichtbarkeit',
                                $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                'teigenschaftwertabhaengigkeit',
                                $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        } else {
                            loescheEigenschaftWert($xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']);
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                'teigenschaftwertsprache',
                                $GLOBALS['mEigenschaftWertSprache'],
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                'teigenschaftwertaufpreis',
                                $GLOBALS['mEigenschaftWertAufpreis'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                'teigenschaftwertsichtbarkeit',
                                $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                'teigenschaftwertabhaengigkeit', $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        }
                    }
                    DBUpdateInsert('teigenschaftwert', $EigenschaftWert_arr, 'kEigenschaftWert');
                }
            }
        }
        DBUpdateInsert('teigenschaft', $Eigenschaft_arr, 'kEigenschaft');
    }
    // Alle Shop Kundengruppen holen
    $oKundengruppe_arr = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe FROM tkundengruppe',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $res[]             = (int)$Artikel->kArtikel;
    fuelleArtikelKategorieRabatt($artikel_arr[0], $oKundengruppe_arr);
    if (!empty($artikel_arr[0]->kVaterartikel)) {
        $res[] = (int)$artikel_arr[0]->kVaterartikel;
    }
    handlePriceRange((int)$Artikel->kArtikel);

    //emailbenachrichtigung, wenn verfügbar
    versendeVerfuegbarkeitsbenachrichtigung($artikel_arr[0]);

    return $res;
}

/**
 * @param int   $kArtikel
 * @param int   $nIstVater
 * @param bool  $bForce
 * @param array $conf
 * @return array
 */
function loescheArtikel(int $kArtikel, int $nIstVater = 0, bool $bForce = false, array $conf = null)
{
    // get list of all categories the article was associated with
    $articleCategories = Shop::Container()->getDB()->selectAll(
        'tkategorieartikel',
        'kArtikel',
        $kArtikel,
        'kKategorie'
    );
    if ($bForce === false
        && isset($conf['global']['kategorien_anzeigefilter'])
        && $conf['global']['kategorien_anzeigefilter'] === '2'
    ) {
        $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        foreach ($articleCategories as $category) {
            // check if the article was the only one in at least one of these categories
            $categoryCount = Shop::Container()->getDB()->query(
                'SELECT count(tkategorieartikel.kArtikel) AS count
                    FROM tkategorieartikel
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tkategorieartikel.kArtikel
                    WHERE tkategorieartikel.kKategorie = ' . (int)$category->kKategorie . ' ' . $stockFilter,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (!isset($categoryCount->count) || (int)$categoryCount->count === 1) {
                // the category only had this article in it - flush cache
                flushCategoryTreeCache();
                break;
            }
        }
    }
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog(
            'kArtikel: ' . $kArtikel . ' - nIstVater: ' . $nIstVater,
            JTLLOG_LEVEL_DEBUG,
            false,
            'Artikel_xml loescheArtikel'
        );
    }
    if ($kArtikel > 0) {
        $manufacturerID = Shop::Container()->getDB()->queryPrepared(
            'SELECT kHersteller 
                FROM tartikel 
                WHERE kArtikel = :pid',
            ['pid' => $kArtikel],
            \DB\ReturnType::SINGLE_OBJECT
        );
        Shop::Container()->getDB()->delete('tseo', ['cKey', 'kKey'], ['kArtikel', (int)$kArtikel]);
        Shop::Container()->getDB()->delete('tartikel', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tpreise', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tpricerange', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tkategorieartikel', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tartikelsprache', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tartikelattribut', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tartikelwarenlager', 'kArtikel', $kArtikel);
        loescheArtikelAttribute($kArtikel);
        loescheArtikelEigenschaftWert($kArtikel);
        loescheArtikelEigenschaft($kArtikel);
        loescheSonderpreise($kArtikel);
        Shop::Container()->getDB()->delete('txsell', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tartikelmerkmal', 'kArtikel', $kArtikel);
        Shop::Container()->getDB()->delete('tartikelsichtbarkeit', 'kArtikel', $kArtikel);
        loescheArtikelMediendateien($kArtikel);
        if ($bForce === false) {
            loescheArtikelDownload($kArtikel);
        } else {
            loescheDownload($kArtikel, null);
        }
        loescheArtikelUpload($kArtikel);
        loescheKonfig($kArtikel);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Artikel geloescht: ' . $kArtikel, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }

        return [
            'kArtikel'    => $kArtikel,
            'categories'  => $articleCategories,
            'kHersteller' => isset($manufacturerID->kHersteller)
                ? (int)$manufacturerID->kHersteller
                : 0
        ];
    }

    return [];
}

/**
 * @param int $attributeID
 */
function loescheEigenschaft(int $attributeID)
{
    Shop::Container()->getDB()->delete('teigenschaft', 'kEigenschaft', $attributeID);
    Shop::Container()->getDB()->delete('teigenschaftsprache', 'kEigenschaft', $attributeID);
    Shop::Container()->getDB()->delete('teigenschaftsichtbarkeit', 'kEigenschaft', $attributeID);
    Shop::Container()->getDB()->delete('teigenschaftwert', 'kEigenschaft', $attributeID);
}

/**
 * @param int $kArtikel
 */
function loescheArtikelEigenschaft(int $kArtikel)
{
    $attributes = Shop::Container()->getDB()->selectAll('teigenschaft', 'kArtikel', $kArtikel, 'kEigenschaft');
    foreach ($attributes as $attribute) {
        loescheEigenschaft((int)$attribute->kEigenschaft);
    }
}

/**
 * @param int $attributeIDWert
 */
function loescheEigenschaftWert(int $attributeIDWert)
{
    Shop::Container()->getDB()->delete('teigenschaftwert', 'kEigenschaftWert', $attributeIDWert);
    Shop::Container()->getDB()->delete('teigenschaftwertaufpreis', 'kEigenschaftWert', $attributeIDWert);
    Shop::Container()->getDB()->delete('teigenschaftwertsichtbarkeit', 'kEigenschaftWert', $attributeIDWert);
    Shop::Container()->getDB()->delete('teigenschaftwertsprache', 'kEigenschaftWert', $attributeIDWert);
    Shop::Container()->getDB()->delete('teigenschaftwertabhaengigkeit', 'kEigenschaftWert', $attributeIDWert);
}

/**
 * @param int $kArtikel
 */
function loescheArtikelEigenschaftWert(int $kArtikel)
{
    $attributeValues = Shop::Container()->getDB()->queryPrepared(
        'SELECT teigenschaftwert.kEigenschaftWert
            FROM teigenschaftwert
            JOIN teigenschaft
                ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
            WHERE teigenschaft.kArtikel = :pid',
        ['pid' => $kArtikel],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($attributeValues as $attributeValue) {
        loescheEigenschaftWert((int)$attributeValue->kEigenschaftWert);
    }
}

/**
 * @param int $kAttribut
 */
function loescheAttribute(int $kAttribut)
{
    Shop::Container()->getDB()->delete('tattribut', 'kAttribut', $kAttribut);
    Shop::Container()->getDB()->delete('tattributsprache', 'kAttribut', $kAttribut);
}

/**
 * @param int $kArtikel
 */
function loescheArtikelAttribute(int $kArtikel)
{
    $attributes = Shop::Container()->getDB()->selectAll('tattribut', 'kArtikel', $kArtikel, 'kAttribut');
    foreach ($attributes as $attribute) {
        loescheAttribute((int)$attribute->kAttribut);
    }
}

/**
 * @param int $kMedienDatei
 */
function loescheMediendateien(int $kMedienDatei)
{
    Shop::Container()->getDB()->delete('tmediendatei', 'kMedienDatei', $kMedienDatei);
    Shop::Container()->getDB()->delete('tmediendateisprache', 'kMedienDatei', $kMedienDatei);
    Shop::Container()->getDB()->delete('tmediendateiattribut', 'kMedienDatei', $kMedienDatei);
}

/**
 * @param int $kArtikel
 */
function loescheArtikelMediendateien(int $kArtikel)
{
    $mediaFiles = Shop::Container()->getDB()->selectAll('tmediendatei', 'kArtikel', $kArtikel, 'kMedienDatei');
    foreach ($mediaFiles as $mediaFile) {
        loescheMediendateien((int)$mediaFile->kMedienDatei);
    }
}

/**
 * @param int $kUploadSchema
 */
function loescheUpload(int $kUploadSchema)
{
    Shop::Container()->getDB()->delete('tuploadschema', 'kUploadSchema', $kUploadSchema);
    Shop::Container()->getDB()->delete('tuploadschemasprache', 'kArtikelUpload', $kUploadSchema);
}

/**
 * @param int $kArtikel
 */
function loescheArtikelUpload(int $kArtikel)
{
    $uploads = Shop::Container()->getDB()->selectAll('tuploadschema', 'kCustomID', $kArtikel, 'kUploadSchema');
    foreach ($uploads as $upload) {
        loescheUpload((int)$upload->kUploadSchema);
    }
}

/**
 * @param int $kArtikel
 * @param int $kDownload
 */
function loescheDownload(int $kArtikel, int $kDownload = null)
{
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('loescheDownload: kArtikel:' . var_export($kArtikel, true) . '- kDownload:' .
            var_export($kDownload, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    if ($kArtikel > 0) {
        if ($kDownload > 0) {
            Shop::Container()->getDB()->delete('tartikeldownload', ['kArtikel', 'kDownload'], [$kArtikel, $kDownload]);
        } else {
            Shop::Container()->getDB()->delete('tartikeldownload', 'kArtikel', $kArtikel);
        }
    }
    if ($kDownload !== null) {
        Shop::Container()->getDB()->delete('tdownload', 'kDownload', $kDownload);
        Shop::Container()->getDB()->delete('tdownloadsprache', 'kDownload', $kDownload);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelDownload(int $kArtikel)
{
    foreach (getDownloadKeys($kArtikel) as $kDownload) {
        loescheDownload($kArtikel, $kDownload);
    }
}

/**
 * @param int $kArtikel
 */
function loescheKonfig(int $kArtikel)
{
    Shop::Container()->getDB()->delete('tartikelkonfiggruppe', 'kArtikel', $kArtikel);
}

/**
 * @param int $kStueckliste
 */
function loescheStueckliste($kStueckliste)
{
    $kStueckliste = (int)$kStueckliste;
    if ($kStueckliste > 0) {
        Shop::Container()->getDB()->delete('tstueckliste', 'kStueckliste', $kStueckliste);
    }
}

/**
 * @param int $kArtikel
 * @return int
 */
function loescheSonderpreise(int $kArtikel): int
{
    return Shop::Container()->getDB()->queryPrepared(
        'DELETE asp, sp
            FROM tartikelsonderpreis asp
            LEFT JOIN tsonderpreise sp
                ON sp.kArtikelSonderpreis = asp.kArtikelSonderpreis
            WHERE asp.kArtikel = :articleID',
        [
            'articleID' => $kArtikel,
        ],
        \DB\ReturnType::AFFECTED_ROWS
    );
}

/**
 * @param int $kArtikel
 */
function checkArtikelBildLoeschung(int $kArtikel)
{
    $oArtikelPict_arr = Shop::Container()->getDB()->selectAll(
        'tartikelpict',
        'kArtikel',
        $kArtikel,
        'kArtikelPict, kMainArtikelBild, cPfad'
    );
    // Hat der Artikel Bilder die auf eine Verknüpfung verlinken wobei der Eigentümer Artikel des Bilder gelöscht wurde
    // und nun der zu löschende Artikel die letzte Refenz darauf ist?
    foreach ($oArtikelPict_arr as $oArtikelPict) {
        deleteArticleImage($oArtikelPict, $kArtikel);
    }
    Shop::Cache()->flush('arr_article_images_' . $kArtikel);
}

/**
 * checks whether the article is a child product in any configurator
 * and returns the product IDs of parent products if yes
 *
 * @param int $kArtikel
 * @return array
 */
function getConfigParents(int $kArtikel): array
{
    $parentProductIDs = [];
    $configItems      = Shop::Container()->getDB()->selectAll('tkonfigitem', 'kArtikel', $kArtikel, 'kKonfiggruppe');
    if (!is_array($configItems) || count($configItems) === 0) {
        return $parentProductIDs;
    }
    $configGroupIDs = [];
    foreach ($configItems as $_configItem) {
        $configGroupIDs[] = (int)$_configItem->kKonfiggruppe;
    }
    $parents = Shop::Container()->getDB()->query(
        'SELECT kArtikel 
            FROM tartikelkonfiggruppe 
            WHERE kKonfiggruppe IN (' . implode(',', $configGroupIDs) . ')',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (!is_array($parents) || count($parents) === 0) {
        return $parentProductIDs;
    }
    foreach ($parents as $_parent) {
        $parentProductIDs[] = (int)$_parent->kArtikel;
    }

    return $parentProductIDs;
}

/**
 * @param  int $kArtikel
 * @return int[]
 */
function getDownloadKeys(int $kArtikel): array
{
    if ($kArtikel > 0) {
        $downloads = Shop::Container()->getDB()->selectAll('tartikeldownload', 'kArtikel', $kArtikel, 'kDownload');

        return \Functional\map($downloads, function ($item) {
            return (int)$item->kDownload;
        });
    }

    return [];
}

/**
 * clear all caches associated with a product ID
 * including manufacturers, categories, parent products
 *
 * @param array $products
 */
function clearProductCaches(array $products)
{
    $start     = microtime(true);
    $cacheTags = [];
    $deps      = [];

    foreach ($products as $product) {
        if (isset($product['kArtikel'])) {
            // generated by bearbeiteDeletes()
            $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$product['kArtikel'];
            if ($product['kHersteller'] > 0) {
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$product['kHersteller'];
            }
            foreach ($product['categories'] as $category) {
                $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
            }
        } elseif (is_numeric($product)) {
            // generated by bearbeiteInsert()
            $parentIDs = getConfigParents($product);
            foreach ($parentIDs as $parentID) {
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$parentID;
            }
            $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$product;
            $deps     [] = (int)$product;
        }
    }
    // additionally get dependencies for articles that were inserted
    if (count($deps) > 0) {
        // flush cache tags associated with the article's manufacturer ID
        $manufacturers = Shop::Container()->getDB()->query(
            'SELECT DISTINCT kHersteller 
                FROM tartikel 
                WHERE kArtikel IN (' . implode(',', $deps) . ') 
                    AND kHersteller > 0',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($manufacturers as $manufacturer) {
            $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$manufacturer->kHersteller;
        }
        // flush cache tags associated with the article's category IDs
        $categories = Shop::Container()->getDB()->query(
            'SELECT DISTINCT kKategorie
                FROM tkategorieartikel
                WHERE kArtikel IN (' . implode(',', $deps) . ')',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($categories as $category) {
            $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
        }
        // flush parent article IDs
        $parentArticles = Shop::Container()->getDB()->query(
            'SELECT DISTINCT kVaterArtikel AS id
                FROM tartikel
                WHERE kArtikel IN (' . implode(',', $deps) . ')
                AND kVaterArtikel > 0',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($parentArticles as $parentArticle) {
            $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$parentArticle->id;
        }
    }

    $cacheTags[] = 'jtl_mmf';
    $cacheTags   = array_unique($cacheTags);
    // flush article cache, category cache and cache for gibMerkmalFilterOptionen() and mega menu/category boxes
    $totalCount = Shop::Cache()->flushTags($cacheTags);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        $end = microtime(true);
        Jtllog::writeLog(
            'Flushed a total of ' . $totalCount .
            ' keys for ' . count($cacheTags) .
            ' tags in ' . ($end - $start) . 's',
            JTLLOG_LEVEL_DEBUG,
            false,
            'Artikel_xml'
        );
    }
}
