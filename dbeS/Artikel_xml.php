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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $return        = 0;
            $conf = Shop::getSettings(array(CONF_GLOBAL));
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' . filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'artdel.xml') {
                    bearbeiteDeletes($xml, $conf);
                } else {
                    bearbeiteInsert($xml, $conf);
                }
                if ($i == 0) {
                    //Suchcachetimer setzen.
                    Shop::DB()->query(
                        "UPDATE tsuchcache
                            SET dGueltigBis = DATE_ADD(now(), INTERVAL " . SUCHCACHE_LEBENSDAUER . " MINUTE)
                            WHERE dGueltigBis IS NULL", 3
                    );
                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }
    } else {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}
echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
}

/**
 * @param array $xml
 * @param array $conf
 */
function bearbeiteDeletes($xml, $conf)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';

    if (is_array($xml['del_artikel']) && is_array($xml['del_artikel']['kArtikel'])) {
        foreach ($xml['del_artikel']['kArtikel'] as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            if ($kArtikel > 0) {
                $kVaterArtikel = ArtikelHelper::getParent($kArtikel);
                $nIstVater     = $kVaterArtikel > 0 ? 0 : 1;
                checkArtikelBildLoeschung($kArtikel);

                Shop::DB()->query(
                    "DELETE teigenschaftkombiwert
                        FROM teigenschaftkombiwert
                        JOIN tartikel ON tartikel.kArtikel = {$kArtikel}
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi", 4
                );
                loescheArtikel($kArtikel, $nIstVater, false, $conf);
                // Lösche Artikel aus tartikelkategorierabatt
                Shop::DB()->delete('tartikelkategorierabatt', 'kArtikel', $kArtikel);
                // Lösche Artikel aus tkategorieartikelgesamt
                Shop::DB()->delete('tkategorieartikelgesamt', 'kArtikel', $kArtikel);
                // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
                if ($kVaterArtikel > 0) {
                    Artikel::beachteVarikombiMerkmalLagerbestand($kVaterArtikel);
                }

                executeHook(HOOK_ARTIKEL_XML_BEARBEITEDELETES, array('kArtikel' => $kArtikel));
            }
        }
    } else {
        if (is_array($xml['del_artikel']) && intval($xml['del_artikel']['kArtikel']) > 0) {
            $kVaterArtikel = ArtikelHelper::getParent(intval($xml['del_artikel']['kArtikel']));
            $nIstVater     = $kVaterArtikel > 0 ? 0 : 1;
            checkArtikelBildLoeschung(intval($xml['del_artikel']['kArtikel']));
            Shop::DB()->query(
                "DELETE teigenschaftkombiwert
                    FROM teigenschaftkombiwert
                    JOIN tartikel ON tartikel.kArtikel = " . intval($xml['del_artikel']['kArtikel']) . "
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi", 4
            );

            loescheArtikel(intval($xml['del_artikel']['kArtikel']), $nIstVater, false, $conf);
            // Lösche Artikel aus tartikelkategorierabatt
            Shop::DB()->delete('tartikelkategorierabatt', 'kArtikel', (int)$xml['del_artikel']['kArtikel']);
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            if ($kVaterArtikel > 0) {
                Artikel::beachteVarikombiMerkmalLagerbestand($kVaterArtikel);
            }

            executeHook(HOOK_ARTIKEL_XML_BEARBEITEDELETES, array('kArtikel' => $xml['del_artikel']['kArtikel']));
        }
    }
}

/**
 * @param array $xml
 * @param array $conf
 */
function bearbeiteInsert($xml, array $conf)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';

    $Artikel           = new stdClass();
    $Artikel->kArtikel = 0;

    if (is_array($xml['tartikel attr'])) {
        $Artikel->kArtikel = intval($xml['tartikel attr']['kArtikel']);
    }
    if (!$Artikel->kArtikel) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('kArtikel fehlt! XML:' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }

        return;
    }
    if (is_array($xml['tartikel'])) {
        $artikel_arr = mapArray($xml, 'tartikel', $GLOBALS['mArtikel']);
        // Alten SEO-Pfad merken. Eintrag in tredirect, wenn sich der Pfad geändert hat. 
        $oSeoOld       = Shop::DB()->query("SELECT cSeo FROM tartikel WHERE kArtikel = " . (int)$Artikel->kArtikel, 1);
        $oSeoAssoc_arr = getSeoFromDB($Artikel->kArtikel, 'kArtikel', null, 'kSprache');
        $isParent      = (isset($artikel_arr[0]->nIstVater)) ? 1 : 0;

        if (isset($xml['tartikel']['tkategorieartikel']) && $conf['global']['kategorien_anzeigefilter'] == EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE && Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CATEGORY)) {
            $currentArticleCategories = array();
            $newArticleCategories     = array();
            $flush                    = false;
            // get list of all categories the article is currently associated with
            $currentArticleCategoriesObject = Shop::DB()->query("SELECT kKategorie FROM tkategorieartikel WHERE kArtikel = " . $Artikel->kArtikel, 2);
            foreach ($currentArticleCategoriesObject as $obj) {
                $currentArticleCategories[] = (int)$obj->kKategorie;
            }
            // get list of all categories the article will be associated with after this update
            $newArticleCategoriesObject = mapArray($xml['tartikel'], 'tkategorieartikel', $GLOBALS['mKategorieArtikel']);
            foreach ($newArticleCategoriesObject as $newArticleCategory) {
                $newArticleCategories[] = (int)$newArticleCategory->kKategorie;
            }
            foreach ($newArticleCategories as $newArticleCategory) {
                if (!in_array($newArticleCategory, $currentArticleCategories)) {
                    // the article was previously not associated with this category
                    $articleCount = Shop::DB()->query("
                        SELECT count(tkategorieartikel.kArtikel) AS count 
                          FROM tkategorieartikel 
                          LEFT JOIN tartikel 
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel 
                            WHERE tkategorieartikel.kKategorie = " . $newArticleCategory . " " . gibLagerfilter(), 1
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
                    if (!in_array($category, $newArticleCategories)) {
                        // check if the article was the only one in at least one of these categories
                        $articleCount = Shop::DB()->query("
                            SELECT count(tkategorieartikel.kArtikel) AS count 
                              FROM tkategorieartikel 
                              LEFT JOIN tartikel 
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel 
                                WHERE tkategorieartikel.kKategorie = " . $category . " " . gibLagerfilter(), 1
                        );
                        if (!isset($articleCount->count) || (int)$articleCount->count === 1) {
                            // the category only had this article in it - flush cache
                            $flush = true;
                            break;
                        }
                    }
                }
            }
            if ($flush === false && $conf['global']['artikel_artikelanzeigefilter'] != EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE) {
                $check         = false;
                $currentStatus = Shop::DB()->query("SELECT cLagerBeachten, cLagerKleinerNull, fLagerbestand FROM tartikel WHERE kArtikel = " . $Artikel->kArtikel, 1);
                if (isset($currentStatus->cLagerBeachten)) {
                    if ($currentStatus->fLagerbestand <= 0 && $xml['tartikel']['fLagerbestand'] > 0) {
                        // article was not in stock before but is now - check if flush is necessary
                        $check = true;
                    } elseif ($currentStatus->fLagerbestand > 0 && $xml['tartikel']['fLagerbestand'] <= 0) {
                        // article was in stock before but is not anymore - check if flush is necessary
                        $check = true;
                    } elseif ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL && $currentStatus->cLagerKleinerNull !== $xml['tartikel']['cLagerKleinerNull']) {
                        // overselling status changed - check if flush is necessary
                        $check = true;
                    } elseif ($currentStatus->cLagerBeachten !== $xml['tartikel']['cLagerBeachten'] && $xml['tartikel']['fLagerbestand'] <= 0) {
                        $check = true;
                    }
                    if ($check === true) {
                        // get count of visible articles in the article's futre categories
                        $articleCount = Shop::DB()->query("
                            SELECT tkategorieartikel.kKategorie, count(tkategorieartikel.kArtikel) AS count 
                              FROM tkategorieartikel 
                              LEFT JOIN tartikel 
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel 
                                WHERE tkategorieartikel.kKategorie IN (" . implode(',', $newArticleCategories) . ") " . gibLagerfilter() . " GROUP BY tkategorieartikel.kKategorie", 2
                        );
                        if (is_array($newArticleCategories) && !empty($newArticleCategories)) {
                            foreach ($newArticleCategories as $nac) {
                                if (is_array($articleCount) && !empty($articleCount)) {
                                    foreach ($articleCount as $ac) {
                                        if ($ac->kKategorie == $nac) {
                                            if (($currentStatus->cLagerBeachten !== 'Y' && $ac->count == 1) || ($currentStatus->cLagerBeachten === 'Y' && $ac->count == 0)) {
                                                // there was just one product that is now sold out or there were just sold out products and now it's not sold out anymore
                                                $flush = true;
                                                break;
                                            }
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
                $delta = Shop::DB()->query("
                  SELECT SUM(pos.nAnzahl) AS totalquantity 
                    FROM tbestellung b 
                      JOIN twarenkorbpos pos 
                        ON pos.kWarenkorb = b.kWarenkorb 
                        WHERE b.cAbgeholt = 'N' 
                          AND pos.kArtikel = " . (int)$artikel_arr[0]->kArtikel, 1
                );
                if($delta->totalquantity > 0) {
                    $artikel_arr[0]->fLagerbestand = $artikel_arr[0]->fLagerbestand - $delta->totalquantity; //subtract delta from stocklevel
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog("Artikel-Sync: Lagerbestand von kArtikel {$artikel_arr[0]->kArtikel} wurde wegen nicht-abgeholter Bestellungen um {$delta->totalquantity} reduziert auf {$artikel_arr[0]->fLagerbestand}." , JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                    }
                }
            }
            
            DBUpdateInsert('tartikel', $artikel_arr, 'kArtikel');
            executeHook(HOOK_ARTIKEL_XML_BEARBEITEINSERT, array('oArtikel' => $artikel_arr[0]));
            // Insert into tredirect weil sich das SEO vom Artikel geändert hat
            if (isset($oSeoOld->cSeo)) {
                checkDbeSXmlRedirect($oSeoOld->cSeo, $artikel_arr[0]->cSeo);
            }
            //insert in tseo
            Shop::DB()->query(
                "INSERT INTO tseo
                    SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
                    FROM tartikel, tsprache
                    WHERE tartikel.kArtikel = " . (int)$artikel_arr[0]->kArtikel . " 
                        AND tsprache.cStandard = 'Y' 
                        AND tartikel.cSeo != ''", 4
            );
        }
        //Artikelsprache
        $artikelsprache_arr = mapArray($xml['tartikel'], 'tartikelsprache', $GLOBALS['mArtikelSprache']);
        if (is_array($artikelsprache_arr)) {
            $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            $langCount             = count($artikelsprache_arr);
            for ($i = 0; $i < $langCount; $i++) {
                // Sprachen die nicht im Shop vorhanden sind überspringen
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
                DBUpdateInsert('tartikelsprache', array($artikelsprache_arr[$i]), 'kArtikel', 'kSprache');
                Shop::DB()->query(
                    "DELETE FROM tseo
                        WHERE cKey = 'kArtikel'
                            AND kKey = " . (int)$artikelsprache_arr[$i]->kArtikel . "
                            AND kSprache = " . (int)$artikelsprache_arr[$i]->kSprache, 4
                );

                $oSeo           = new stdClass();
                $oSeo->cSeo     = $artikelsprache_arr[$i]->cSeo;
                $oSeo->cKey     = 'kArtikel';
                $oSeo->kKey     = $artikelsprache_arr[$i]->kArtikel;
                $oSeo->kSprache = $artikelsprache_arr[$i]->kSprache;
                Shop::DB()->insert('tseo', $oSeo);
                // Insert into tredirect weil sich das SEO vom Artikel geändert hat
                if (isset($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache])) {
                    checkDbeSXmlRedirect($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache]->cSeo, $artikelsprache_arr[$i]->cSeo);
                }
            }
        }
        //Attribute
        if (isset($xml['tartikel']['tattribut']) && is_array($xml['tartikel']['tattribut'])) {
            $Attribut_arr = mapArray($xml['tartikel'], 'tattribut', $GLOBALS['mAttribut']);
            $aArrCount    = count($Attribut_arr);
            for ($i = 0; $i < $aArrCount; $i++) {
                if (count($Attribut_arr) < 2) {
                    loescheAttribute($xml['tartikel']['tattribut attr']['kAttribut']);
                    updateXMLinDB($xml['tartikel']['tattribut'], 'tattributsprache', $GLOBALS['mAttributSprache'], 'kAttribut', 'kSprache');
                } else {
                    loescheAttribute($xml['tartikel']['tattribut'][$i . ' attr']['kAttribut']);
                    updateXMLinDB($xml['tartikel']['tattribut'][$i], 'tattributsprache', $GLOBALS['mAttributSprache'], 'kAttribut', 'kSprache');
                }
            }
            DBUpdateInsert('tattribut', $Attribut_arr, 'kAttribut');
        }
        //Medienmodul
        if (isset($xml['tartikel']['tmediendatei']) && is_array($xml['tartikel']['tmediendatei'])) {
            $oMediendatei_arr = mapArray($xml['tartikel'], 'tmediendatei', $GLOBALS['mMediendatei']);
            $mediaCount       = count($oMediendatei_arr);
            for ($i = 0; $i < $mediaCount; $i++) {
                if ($mediaCount < 2) {
                    loescheMediendateien($xml['tartikel']['tmediendatei attr']['kMedienDatei']);
                    updateXMLinDB($xml['tartikel']['tmediendatei'], 'tmediendateisprache', $GLOBALS['mMediendateisprache'], 'kMedienDatei', 'kSprache');
                    updateXMLinDB($xml['tartikel']['tmediendatei'], 'tmediendateiattribut', $GLOBALS['mMediendateiattribut'], 'kMedienDateiAttribut');
                } else {
                    loescheMediendateien($xml['tartikel']['tmediendatei'][$i . ' attr']['kMedienDatei']);
                    updateXMLinDB($xml['tartikel']['tmediendatei'][$i], 'tmediendateisprache', $GLOBALS['mMediendateisprache'], 'kMedienDatei', 'kSprache');
                    updateXMLinDB($xml['tartikel']['tmediendatei'][$i], 'tmediendateiattribut', $GLOBALS['mMediendateiattribut'], 'kMedienDateiAttribut');
                }
            }
            DBUpdateInsert('tmediendatei', $oMediendatei_arr, 'kMedienDatei');
        }
        //Downloadmodul
        if (isset($xml['tartikel']['tArtikelDownload']) && is_array($xml['tartikel']['tArtikelDownload'])) {
            $oDownload_arr = array();
            if (isset($xml['tartikel']['tArtikelDownload']['kDownload']) && is_array($xml['tartikel']['tArtikelDownload']['kDownload'])) {
                $kDownload_arr = $xml['tartikel']['tArtikelDownload']['kDownload'];
                foreach ($kDownload_arr as $kDownload) {
                    $oArtikelDownload            = new stdClass();
                    $oArtikelDownload->kDownload = (int)$kDownload;
                    $oArtikelDownload->kArtikel  = (int)$Artikel->kArtikel;
                    $oDownload_arr[]             = $oArtikelDownload;

                    loescheDownload($oArtikelDownload->kArtikel, $oArtikelDownload->kDownload);
                }
            } else {
                $oArtikelDownload            = new stdClass();
                $oArtikelDownload->kDownload = intval($xml['tartikel']['tArtikelDownload']['kDownload']);
                $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
                $oDownload_arr[]             = $oArtikelDownload;

                loescheDownload($oArtikelDownload->kArtikel, $oArtikelDownload->kDownload);
            }
            DBUpdateInsert('tartikeldownload', $oDownload_arr, 'kArtikel', 'kDownload');
        }
        // Stückliste
        if (isset($xml['tartikel']['tstueckliste']) && is_array($xml['tartikel']['tstueckliste'])) {
            $oStueckliste_arr = mapArray($xml['tartikel'], 'tstueckliste', $GLOBALS['mStueckliste']);
            $cacheIDs         = array();
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
        // Uploads
        if (isset($xml['tartikel']['tartikelupload']) && is_array($xml['tartikel']['tartikelupload'])) {
            $oArtikelUpload_arr = mapArray($xml['tartikel'], 'tartikelupload', $GLOBALS['mArtikelUpload']);
            foreach ($oArtikelUpload_arr as &$oArtikelUpload) {
                $oArtikelUpload->nTyp          = 3;
                $oArtikelUpload->kUploadSchema = $oArtikelUpload->kArtikelUpload;
                $oArtikelUpload->kCustomID     = $oArtikelUpload->kArtikel;

                unset($oArtikelUpload->kArtikelUpload);
                unset($oArtikelUpload->kArtikel);
            }
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('oArtikelUpload_arr: ' . print_r($oArtikelUpload_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            DBUpdateInsert('tuploadschema', $oArtikelUpload_arr, 'kUploadSchema', 'kCustomID');

            // Upload-Sprachen
            if (count($oArtikelUpload_arr) < 2) {
                $oArtikelUploadSprache_arr = mapArray($xml['tartikel']['tartikelupload'], 'tartikeluploadsprache', $GLOBALS['mArtikelUploadSprache']);
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                }
                DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
            } else {
                $ulCount = count($oArtikelUpload_arr);
                for ($i = 0; $i < $ulCount; $i++) {
                    $oArtikelUploadSprache_arr = mapArray($xml['tartikel']['tartikelupload'][$i], 'tartikeluploadsprache', $GLOBALS['mArtikelUploadSprache']);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                    }
                    DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
                }
            }
        }
        // abnahmeintervalle
        Shop::DB()->delete('tartikelabnahme', 'kArtikel', $artikel_arr[0]->kArtikel);
        if (isset($xml['tartikel']['tartikelabnahme']) && is_array($xml['tartikel']['tartikelabnahme'])) {
            $oArtikelAbnahmeIntervalle_arr = mapArray($xml['tartikel'], 'tartikelabnahme', $GLOBALS['mArtikelAbnahme']);
            DBUpdateInsert('tartikelabnahme', $oArtikelAbnahmeIntervalle_arr, 'kArtikel', 'kKundengruppe');
        }
        // Konfig
        loescheKonfig($Artikel->kArtikel);
        clearProductCaches($Artikel->kArtikel);
        if (isset($xml['tartikel']['tartikelkonfiggruppe']) && is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
            $oArtikelKonfig_arr = mapArray($xml['tartikel'], 'tartikelkonfiggruppe', $GLOBALS['mArtikelkonfiggruppe']);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('oArtikelKonfig_arr: ' . print_r($oArtikelKonfig_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            DBUpdateInsert('tartikelkonfiggruppe', $oArtikelKonfig_arr, 'kArtikel', 'kKonfiggruppe');
        }

        // Sonderpreise
        Shop::DB()->delete('tsonderpreise', 'kArtikelSonderpreis', $artikel_arr[0]->kArtikel);
        if (isset($xml['tartikel']['tartikelsonderpreis'])) {
            updateXMLinDB($xml['tartikel']['tartikelsonderpreis'], 'tsonderpreise', $GLOBALS['mSonderpreise'], 'kArtikelSonderpreis', 'kKundengruppe');
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
        updateXMLinDB($xml['tartikel'], 'tartikelsichtbarkeit', $GLOBALS['mArtikelSichtbarkeit'], 'kKundengruppe', 'kArtikel');
        updateXMLinDB($xml['tartikel'], 'txsell', $GLOBALS['mXSell'], 'kXSell');
        updateXMLinDB($xml['tartikel'], 'tartikelmerkmal', $GLOBALS['mArtikelSichtbarkeit'], 'kMermalWert');
        if ($artikel_arr[0]->nIstVater == 1) {
            //Lagerbestand-Update: Lagerbestand des Vaterartikels berechnet sich aus der Summe der Kindartikel-Lagerbestände
            Shop::DB()->query("
                UPDATE tartikel SET fLagerbestand =
                    (SELECT * FROM
                        (SELECT SUM(fLagerbestand) FROM tartikel WHERE kVaterartikel = " . (int)$artikel_arr[0]->kArtikel . ") AS x)
                    WHERE kArtikel = " . (int)$artikel_arr[0]->kArtikel, 3
            );
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand($artikel_arr[0]->kArtikel, $conf['global']['artikel_artikelanzeigefilter']);
        } elseif (isset($artikel_arr[0]->kVaterArtikel) && $artikel_arr[0]->kVaterArtikel > 0) {
            //Lagerbestand-Update: Lagerbestand des Vaterartikels berechnet sich aus der Summe der Kindartikel-Lagerbestände
            Shop::DB()->query("
                UPDATE tartikel SET fLagerbestand =
                    (SELECT * FROM
                        (SELECT SUM(fLagerbestand) FROM tartikel WHERE kVaterartikel = " . (int)$artikel_arr[0]->kVaterArtikel . ") AS x)
                    WHERE kArtikel = " . (int)$artikel_arr[0]->kVaterArtikel, 3);
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand($artikel_arr[0]->kVaterArtikel, $conf['global']['artikel_artikelanzeigefilter']);
            
        }
        // SQL DEL
        if (isset($xml['tartikel']['SQLDEL']) && strlen($xml['tartikel']['SQLDEL']) > 10) { // teigenschaftkombiwert sqls absetzen
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('SQLDEL: ' . $xml['tartikel']['SQLDEL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $cSQL_arr = explode("\n", $xml['tartikel']['SQLDEL']);
            foreach ($cSQL_arr as $cSQL) {
                if (strlen($cSQL) > 10) {
                    Shop::DB()->query($cSQL, 4);
                }
            }
        }
        // SQL
        if (isset($xml['tartikel']['SQL']) && strlen($xml['tartikel']['SQL']) > 10) { //teigenschaftkombiwert sqls absetzen
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('SQL: ' . $xml['tartikel']['SQL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $cSQL_arr = explode("\n", $xml['tartikel']['SQL']);
            foreach ($cSQL_arr as $cSQL) {
                if (strlen($cSQL) > 10) {
                    // Pre Wawi 0.99862 fix
                    if (!isset($xml['tartikel']['SQLDEL']) && strpos($cSQL, 'teigenschaftkombiwert') !== false && isset($artikel_arr[0]->kVaterArtikel) && $artikel_arr[0]->kVaterArtikel > 0) {
                        $cDel     = substr($cSQL, strpos($cSQL, 'values ') + strlen('values '));
                        $cDel_arr = str_replace(array('(', ')'), '', explode('),(', $cDel));
                        $kKey_arr = array();
                        foreach ($cDel_arr as $cDel) {
                            $kKey_arr[] = (int)substr($cDel, 0, strpos($cDel, ","));
                        }
                        Shop::DB()->query("DELETE FROM teigenschaftkombiwert WHERE kEigenschaftKombi IN (" . implode(',', $kKey_arr) . ")", 4);
                    }

                    Shop::DB()->query($cSQL, 4);
                }
            }
        }
        // tkategoriegesamt füllen
        fuelleKategorieGesamt(mapArray($xml['tartikel'], 'tkategorieartikel', $GLOBALS['mKategorieArtikel']));
        // Preise für Preisverlauf
        $oPreis_arr = mapArray($xml['tartikel'], 'tpreise', $GLOBALS['mPreise']);
        foreach ($oPreis_arr as $oPreis) {
            setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
        }
        // Artikel Warenlager
        if (isset($xml['tartikel']['tartikelwarenlager']) && is_array($xml['tartikel']['tartikelwarenlager'])) {
            $oArtikelWarenlager_arr = mapArray($xml['tartikel'], 'tartikelwarenlager', $GLOBALS['mArtikelWarenlager']);
            foreach ($oArtikelWarenlager_arr as $_oawl) {
                if (isset($_oawl->dZulaufDatum) && $_oawl->dZulaufDatum === '') {
                    $_oawl->dZulaufDatum = '0000-00-00 00:00:00';
                }
            }
            DBUpdateInsert('tartikelwarenlager', $oArtikelWarenlager_arr, 'kArtikel', 'kWarenlager');
        }
        if (isset($xml['tartikel']['tartikelsonderpreis']) && is_array($xml['tartikel']['tartikelsonderpreis'])) {
            $ArtikelSonderpreis_arr = mapArray($xml['tartikel'], 'tartikelsonderpreis', $GLOBALS['mArtikelSonderpreis']);
            $bTesteSonderpreis      = false;
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
                if ($nNowStamp >= $nStartStamp && ($nNowStamp < $nEndStamp || intval($ArtikelSonderpreis_arr[0]->dEnde) === 0)) {
                    $bTesteSonderpreis = true;
                }
            }
            $spCount = count($ArtikelSonderpreis_arr);
            for ($i = 0; $i < $spCount; $i++) {
                $Sonderpreise_arr = mapArray($xml['tartikel']['tartikelsonderpreis'], 'tsonderpreise', $GLOBALS['mSonderpreise']);
                if ($bTesteSonderpreis == true) {
                    foreach ($Sonderpreise_arr as $Sonderpreise) {
                        setzePreisverlauf($ArtikelSonderpreis_arr[0]->kArtikel, $Sonderpreise->kKundengruppe, $Sonderpreise->fNettoPreis);
                    }
                }
                updateXMLinDB($xml['tartikel']['tartikelsonderpreis'], 'tsonderpreise', $GLOBALS['mSonderpreise'], 'kArtikelSonderpreis', 'kKundengruppe');
            }
            DBUpdateInsert('tartikelsonderpreis', $ArtikelSonderpreis_arr, 'kArtikelSonderpreis');
        }
        if (isset($xml['tartikel']['teigenschaft']) && is_array($xml['tartikel']['teigenschaft'])) {
            $Eigenschaft_arr = mapArray($xml['tartikel'], 'teigenschaft', $GLOBALS['mEigenschaft']);
            $eCount          = count($Eigenschaft_arr);
            for ($i = 0; $i < $eCount; $i++) {
                if (count($Eigenschaft_arr) < 2) {
                    loescheEigenschaft($xml['tartikel']['teigenschaft attr']['kEigenschaft']);
                    updateXMLinDB($xml['tartikel']['teigenschaft'], 'teigenschaftsprache', $GLOBALS['mEigenschaftSprache'], 'kEigenschaft', 'kSprache');
                    updateXMLinDB($xml['tartikel']['teigenschaft'], 'teigenschaftsichtbarkeit', $GLOBALS['mEigenschaftsichtbarkeit'], 'kEigenschaft', 'kKundengruppe');
                    $EigenschaftWert_arr = mapArray($xml['tartikel']['teigenschaft'], 'teigenschaftwert', $GLOBALS['mEigenschaftWert']);
                    $ewCount             = count($EigenschaftWert_arr);
                    for ($o = 0; $o < $ewCount; $o++) {
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
                        updateXMLinDB($xml['tartikel']['teigenschaft'][$i], 'teigenschaftsprache', $GLOBALS['mEigenschaftSprache'], 'kEigenschaft', 'kSprache');
                        updateXMLinDB($xml['tartikel']['teigenschaft'][$i], 'teigenschaftsichtbarkeit', $GLOBALS['mEigenschaftsichtbarkeit'], 'kEigenschaft', 'kKundengruppe');
                        $EigenschaftWert_arr = mapArray($xml['tartikel']['teigenschaft'][$i], 'teigenschaftwert', $GLOBALS['mEigenschaftWert']);
                        $ewCount             = count($EigenschaftWert_arr);
                        for ($o = 0; $o < $ewCount; $o++) {
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
        $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);
        fuelleArtikelKategorieRabatt($artikel_arr[0], $oKundengruppe_arr);
        //emailbenachrichtigung, wenn verfügbar
        versendeVerfuegbarkeitsbenachrichtigung($artikel_arr[0]);
    }
}

/**
 * @param int   $kArtikel
 * @param int   $nIstVater
 * @param bool  $bForce
 * @param array $conf
 */
function loescheArtikel($kArtikel, $nIstVater = 0, $bForce = false, $conf = null)
{
    $kArtikel = (int)$kArtikel;
    if ($bForce === false && isset($conf['global']['kategorien_anzeigefilter']) && $conf['global']['kategorien_anzeigefilter'] === '2') {
        // get list of all categories the article was associated with
        $articleCategories = Shop::DB()->query("SELECT kKategorie FROM tkategorieartikel WHERE kArtikel = " . $kArtikel, 2);
        foreach ($articleCategories as $category) {
            // check if the article was the only one in at least one of these categories
            $categoryCount = Shop::DB()->query("
                        SELECT count(tkategorieartikel.kArtikel) AS count 
                          FROM tkategorieartikel 
                          LEFT JOIN tartikel 
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel 
                            WHERE tkategorieartikel.kKategorie = " . (int)$category->kKategorie . " " . gibLagerfilter(), 1
            );
            if (!isset($categoryCount->count) || (int)$categoryCount->count === 1) {
                // the category only had this article in it - flush cache
                flushCategoryTreeCache();
                break;
            }
        }
    }
    clearProductCaches($kArtikel);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('kArtikel: ' . $kArtikel . ' - nIstVater: ' . $nIstVater, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml loescheArtikel');
    }
    if ($kArtikel > 0) {
        Shop::DB()->query("DELETE FROM tseo WHERE kKey = " . $kArtikel . " AND cKey = 'kArtikel'", 4);
        Shop::DB()->query("DELETE FROM tartikel WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tpreise WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikelsonderpreis WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tkategorieartikel WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikelsprache WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikelattribut WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tattribut WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM teigenschaft WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM txsell WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikelmerkmal WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikelsichtbarkeit WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tmediendatei WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tartikeldownload WHERE kArtikel = " . $kArtikel, 4);
        Shop::DB()->query("DELETE FROM tuploadschemasprache WHERE kArtikelUpload IN (SELECT kUploadSchema FROM tuploadschema WHERE kCustomID='{$kArtikel}' AND nTyp = 3)", 4);
        Shop::DB()->query("DELETE FROM tuploadschema WHERE kCustomID='{$kArtikel}' AND nTyp = 3", 4);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Artikel geloescht: ' . $kArtikel, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }
    }
}

/**
 * @param int $kEigenschaft
 */
function loescheEigenschaft($kEigenschaft)
{
    $kEigenschaft = (int)$kEigenschaft;
    if ($kEigenschaft > 0) {
        Shop::DB()->query("DELETE FROM teigenschaft WHERE kEigenschaft = " . $kEigenschaft, 4);
        Shop::DB()->query("DELETE FROM teigenschaftsprache WHERE kEigenschaft = " . $kEigenschaft, 4);
        Shop::DB()->query("DELETE FROM teigenschaftsichtbarkeit WHERE kEigenschaft = " . $kEigenschaft, 4);
        Shop::DB()->query("DELETE FROM teigenschaftwert WHERE kEigenschaft = " . $kEigenschaft, 4);
    }
}

/**
 * @param int $kEigenschaftWert
 */
function loescheEigenschaftWert($kEigenschaftWert)
{
    $kEigenschaftWert = (int)$kEigenschaftWert;
    if ($kEigenschaftWert > 0) {
        Shop::DB()->query("DELETE FROM teigenschaftwert WHERE kEigenschaftWert = " . $kEigenschaftWert, 4);
        Shop::DB()->query("DELETE FROM teigenschaftwertaufpreis WHERE kEigenschaftWert = " . $kEigenschaftWert, 4);
        Shop::DB()->query("DELETE FROM teigenschaftwertsichtbarkeit WHERE kEigenschaftWert = " . $kEigenschaftWert, 4);
        Shop::DB()->query("DELETE FROM teigenschaftwertsprache WHERE kEigenschaftWert = " . $kEigenschaftWert, 4);
        Shop::DB()->query("DELETE FROM teigenschaftwertabhaengigkeit WHERE kEigenschaftWert = " . $kEigenschaftWert, 4);
    }
}

/**
 * @param int $kAttribut
 */
function loescheAttribute($kAttribut)
{
    $kAttribut = (int)$kAttribut;
    if ($kAttribut > 0) {
        Shop::DB()->query("DELETE FROM tattribut WHERE kAttribut = " . $kAttribut, 4);
        Shop::DB()->query("DELETE FROM tattributsprache WHERE kAttribut = " . $kAttribut, 4);
    }
}

/**
 * @param int $kMedienDatei
 */
function loescheMediendateien($kMedienDatei)
{
    $kMedienDatei = (int)$kMedienDatei;
    if ($kMedienDatei > 0) {
        Shop::DB()->query("DELETE FROM tmediendateisprache WHERE kMedienDatei = " . $kMedienDatei, 4);
        Shop::DB()->query("DELETE FROM tmediendateiattribut WHERE kMedienDatei = " . $kMedienDatei, 4);
    }
}

/**
 * @param int $kUploadSchema
 */
function loescheUpload($kUploadSchema)
{
    $kUploadSchema = (int)$kUploadSchema;
    if ($kUploadSchema > 0) {
        Shop::DB()->query("DELETE FROM tuploadschema WHERE kUploadSchema = " . $kUploadSchema, 4);
        Shop::DB()->query("DELETE FROM tuploadschemasprache WHERE kArtikelUpload = " . $kUploadSchema, 4);
    }
}

/**
 * @param int $kArtikel
 */
function loescheKonfig($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        Shop::DB()->query("DELETE FROM tartikelkonfiggruppe WHERE kArtikel = " . $kArtikel, 4);
    }
}

/**
 * @param int $kArtikel
 * @param int $kDownload
 */
function loescheDownload($kArtikel, $kDownload)
{
    $kArtikel  = (int)$kArtikel;
    $kDownload = (int)$kDownload;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('loescheDownload: kArtikel:' . var_export($kArtikel, true) . '- kDownload:' . var_export($kDownload, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    if ($kArtikel > 0 && $kDownload > 0) {
        Shop::DB()->query("DELETE FROM tartikeldownload WHERE kArtikel = " . $kArtikel . " AND kDownload = " . $kDownload, 4);
    }
}

/**
 * @param int $kStueckliste
 */
function loescheStueckliste($kStueckliste)
{
    $kStueckliste = (int)$kStueckliste;
    if ($kStueckliste > 0) {
        Shop::DB()->query("DELETE FROM tstueckliste WHERE kStueckliste = " . $kStueckliste, 4);
    }
}

/**
 * @param array $oKategorieArtikel_arr
 */
function fuelleKategorieGesamt($oKategorieArtikel_arr)
{
    if (is_array($oKategorieArtikel_arr) && count($oKategorieArtikel_arr) > 0) {
        $isPageCacheEnabled = Shop::Cache()->isPageCacheEnabled();
        // Laufe jeden KategorieArtikel Eintrag durch
        foreach ($oKategorieArtikel_arr as $oKategorieArtikel) {
            // Lösche aktuellen KategorieArtikel
            Shop::DB()->query("DELETE FROM tkategorieartikelgesamt WHERE kArtikel = " . (int)$oKategorieArtikel->kArtikel, 3);
            $oOberKategorie_arr = array();
            // Hole die Kategorie vom aktuellen KategorieArtikel
            $oKategorie = Shop::DB()->query("SELECT * FROM tkategorie WHERE kKategorie = " . (int)$oKategorieArtikel->kKategorie, 1);

            $oOberKategorie_arr[] = $oKategorie;
            Shop::Cache()->flushTags(array(CACHING_GROUP_CATEGORY . '_' . $oKategorieArtikel->kKategorie));
            if ($isPageCacheEnabled === true) {
                if (!isset($smarty)) {
                    $smarty = Shop::Smarty();
                }
                $smarty->clearCache(null, 'jtlc|category|cid' . $oKategorieArtikel->kKategorie);
            }

            // Laufe solange bis es keine OberKategorie mehr zum aktuellen KategorieArtikel gibt
            // Falls es zum aktuellen KategorieArtikel keine OberKategorie gibt, wird die schleife nicht betreten
            while (isset($oKategorie->kOberKategorie) && $oKategorie->kOberKategorie > 0) {
                // Hole OberKategorie
                $oKategorie = Shop::DB()->query("SELECT * FROM tkategorie WHERE kKategorie = " . (int)$oKategorie->kOberKategorie, 1);
                if (isset($oKategorie->kKategorie)) {
                    $oOberKategorie_arr[] = $oKategorie;
                }
            }

            $oOberKategorie_arr = array_reverse($oOberKategorie_arr);  // Dreh das Array um, damit wir an Array[0] auch das Level 0 haben

            if (count($oOberKategorie_arr) > 0) {
                // Speicher den kompletten Kategoriepfad zum aktuellen KategorieArtikel nach Level sortiert in die Datenbank
                foreach ($oOberKategorie_arr as $i => $oOberKategorie) {
                    $oKategorieArtikelGesamt                 = new stdClass();
                    $oKategorieArtikelGesamt->kArtikel       = (int)$oKategorieArtikel->kArtikel;
                    $oKategorieArtikelGesamt->kOberKategorie = (int)$oOberKategorie->kOberKategorie;
                    $oKategorieArtikelGesamt->kKategorie     = (int)$oOberKategorie->kKategorie;
                    $oKategorieArtikelGesamt->nLevel         = $i;

                    Shop::DB()->insert('tkategorieartikelgesamt', $oKategorieArtikelGesamt);
                    Shop::Cache()->flushTags(array(CACHING_GROUP_CATEGORY . '_' . $oKategorieArtikelGesamt->kKategorie));
                    if ($isPageCacheEnabled === true) {
                        if (!isset($smarty)) {
                            $smarty = Shop::Smarty();
                        }
                        $smarty->clearCache(null, 'jtlc|category|cid' . $oKategorieArtikelGesamt->kKategorie);
                    }
                }
            }
        }
    }
}

/**
 * @param int $kArtikel
 */
function checkArtikelBildLoeschung($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $oArtikelPict_arr = Shop::DB()->query(
            "SELECT kArtikelPict, kMainArtikelBild, cPfad
                FROM tartikelpict
                WHERE kArtikel = " . $kArtikel, 2
        );
        // Besitzt der zu löschende Artikel Bilder?
        if (isset($oArtikelPict_arr) && count($oArtikelPict_arr) > 0) {
            // Hat der Artikel Bilder die auf eine Verknüpfung verlinken wobei der Eigentümer Artikel des Bilder gelöscht wurde
            // und nun der zu löschende Artikel die letzte Refenz darauf ist?
            foreach ($oArtikelPict_arr as $oArtikelPict) {
                deleteArticleImage($oArtikelPict, $kArtikel);
            }
            //flush article images cache
            Shop::Cache()->flush('arr_article_images_' . $kArtikel);
        }
    }
}

/**
 * checks wether the article is a child product in any configurator
 * and returns the product IDs of parent products if yes
 *
 * @param int $kArtikel
 * @return array
 */
function getConfigParents($kArtikel)
{
    $kArtikel         = (int)$kArtikel;
    $parentProductIDs = array();
    $configItems      = Shop::DB()->query("SELECT kKonfiggruppe FROM tkonfigitem WHERE kArtikel = " . $kArtikel, 2);
    if (!is_array($configItems) || count($configItems) === 0) {
        return $parentProductIDs;
    }
    $configGroupIDs = array();
    foreach ($configItems as $_configItem) {
        $configGroupIDs[] = (int)$_configItem->kKonfiggruppe;
    }
    $parents = Shop::DB()->query("SELECT kArtikel FROM tartikelkonfiggruppe WHERE kKonfiggruppe IN (" . implode(',', $configGroupIDs) . ")", 2);
    if (!is_array($parents) || count($parents) === 0) {
        return $parentProductIDs;
    }
    foreach ($parents as $_parent) {
        $parentProductIDs[] = (int)$_parent->kArtikel;
    }

    return $parentProductIDs;
}

/**
 * clear all caches associated with a product ID
 * including manufacturers, categories, parent products
 *
 * @param int $kArtikel
 */
function clearProductCaches($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    //flush config parents cache
    $parentIDs = getConfigParents($kArtikel);
    foreach ($parentIDs as $_parentID) {
        Shop::Cache()->flushTags(array(CACHING_GROUP_ARTICLE . '_' . $_parentID));
    }
    //flush article cache
    Shop::Cache()->flushTags(array(CACHING_GROUP_ARTICLE . '_' . $kArtikel));
    if (Shop::Cache()->isPageCacheEnabled()) {
        if (!isset($smarty)) {
            $smarty = Shop::Smarty();
        }
        $smarty->clearCache(null, 'jtlc|article|aid' . $kArtikel);
    }
    //flush cache tags associated with the article's manufacturer ID
    $oArticleManufacturer = Shop::DB()->query("SELECT kHersteller FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
    if (isset($oArticleManufacturer->kHersteller) && intval($oArticleManufacturer->kHersteller) > 0) {
        Shop::Cache()->flushTags(array(CACHING_GROUP_MANUFACTURER . '_' . $oArticleManufacturer->kHersteller));
    }
    //flush cache tags associated with the article's category IDs
    $oArticleCategories = Shop::DB()->query("SELECT * FROM tkategorieartikel WHERE kArtikel = " . $kArtikel, 2);
    if (is_array($oArticleCategories)) {
        foreach ($oArticleCategories as $_articleCategory) {
            Shop::Cache()->flushTags(array(CACHING_GROUP_CATEGORY . '_' . (int)$_articleCategory->kKategorie));
            if (Shop::Cache()->isPageCacheEnabled()) {
                if (!isset($smarty)) {
                    $smarty = Shop::Smarty();
                }
                $smarty->clearCache(null, 'jtlc|category|cid' . (int)$_articleCategory->kKategorie);
            }
        }
    }
    //clear cache for gibMerkmalFilterOptionen() and mega menu/category boxes
    Shop::Cache()->flushTags(array('jtl_mmf'));
}
