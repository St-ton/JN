<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Product;
use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$archive = null;
$zipFile = $_FILES['data']['tmp_name'];
$logger  = Shop::Container()->getLogService()->withName('dbeS');
if (auth()) {
    $articleIDs = [];
    $zipFile    = checkFile();
    $return     = 2;
    $unzipPath  = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    $db         = Shop::Container()->getDB();
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        $logger->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $conf   = Shop::getSettings([CONF_GLOBAL]);
        $db->query('START TRANSACTION', \DB\ReturnType::DEFAULT);
        foreach ($syncFiles as $i => $xmlFile) {
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
                    'UPDATE tsuchcache
                        SET dGueltigBis = DATE_ADD(NOW(), INTERVAL ' . SUCHCACHE_LEBENSDAUER . ' MINUTE)
                        WHERE dGueltigBis IS NULL',
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
        }
        handlePriceRange($articleIDs);
        $db->query('COMMIT', \DB\ReturnType::DEFAULT);
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
        clearProductCaches($articleIDs);
    }
}

echo $return;

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
    $db = Shop::Container()->getDB();
    foreach ($xml['del_artikel']['kArtikel'] as $kArtikel) {
        $kArtikel      = (int)$kArtikel;
        $kVaterArtikel = Product::getParent($kArtikel);
        $nIstVater     = $kVaterArtikel > 0 ? 0 : 1;
        checkArtikelBildLoeschung($kArtikel);

        $db->queryPrepared(
            'DELETE teigenschaftkombiwert
                FROM teigenschaftkombiwert
                JOIN tartikel 
                    ON tartikel.kArtikel = :pid
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi',
            ['pid' => $kArtikel],
            \DB\ReturnType::AFFECTED_ROWS
        );
        removeProductIdfromCoupons($kArtikel);
        $res[] = loescheArtikel($kArtikel, false, $conf);
        // Lösche Artikel aus tartikelkategorierabatt
        $db->delete('tartikelkategorierabatt', 'kArtikel', $kArtikel);
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
    $res               = [];
    $logger            = Shop::Container()->getLogService()->withName('dbeS');
    $Artikel           = new stdClass();
    $Artikel->kArtikel = 0;

    if (is_array($xml['tartikel attr'])) {
        $Artikel->kArtikel = (int)$xml['tartikel attr']['kArtikel'];
    }
    if (!$Artikel->kArtikel) {
        $logger->error('kArtikel fehlt! XML:' . print_r($xml, true));

        return $res;
    }
    if (!is_array($xml['tartikel'])) {
        return $res;
    }
    $db       = Shop::Container()->getDB();
    $products = mapArray($xml, 'tartikel', Mapper::getMapping('mArtikel'));
    $oSeoOld  = $db->select(
        'tartikel',
        'kArtikel',
        (int)$Artikel->kArtikel,
        null,
        null,
        null,
        null,
        false,
        'cSeo'
    );
    $seoData  = getSeoFromDB($Artikel->kArtikel, 'kArtikel', null, 'kSprache');
    if (isset($xml['tartikel']['tkategorieartikel'])
        && (int)$conf['global']['kategorien_anzeigefilter'] === EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
        && Shop::Container()->getCache()->isCacheGroupActive(CACHING_GROUP_CATEGORY)
    ) {
        $currentArticleCategories = [];
        $newArticleCategories     = [];
        $flush                    = false;
        // get list of all categories the article is currently associated with
        $currentArticleCategoriesObject = $db->selectAll(
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
            Mapper::getMapping('mKategorieArtikel')
        );
        foreach ($newArticleCategoriesObject as $newArticleCategory) {
            $newArticleCategories[] = (int)$newArticleCategory->kKategorie;
        }
        $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        foreach ($newArticleCategories as $newArticleCategory) {
            if (!in_array($newArticleCategory, $currentArticleCategories, true)) {
                // the article was previously not associated with this category
                $articleCount = $db->query(
                    'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                        FROM tkategorieartikel
                        LEFT JOIN tartikel
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel
                        WHERE tkategorieartikel.kKategorie = ' . $newArticleCategory . ' ' . $stockFilter,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($articleCount->cnt) && (int)$articleCount->cnt === 0) {
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
                    $articleCount = $db->query(
                        'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                            FROM tkategorieartikel
                            LEFT JOIN tartikel
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel
                            WHERE tkategorieartikel.kKategorie = ' . $category . ' ' . $stockFilter,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (!isset($articleCount->cnt) || (int)$articleCount->cnt === 1) {
                        // the category only had this article in it - flush cache
                        $flush = true;
                        break;
                    }
                }
            }
        }
        if ($flush === false
            && (int)$conf['global']['artikel_artikelanzeigefilter'] !== EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
        ) {
            $check         = false;
            $currentStatus = $db->select(
                'tartikel',
                'kArtikel',
                $Artikel->kArtikel,
                null,
                null,
                null,
                null,
                false,
                'cLagerBeachten, cLagerKleinerNull, fLagerbestand'
            );
            if (isset($currentStatus->cLagerBeachten)) {
                if (($currentStatus->fLagerbestand <= 0 && $xml['tartikel']['fLagerbestand'] > 0)
                    // article was not in stock before but is now - check if flush is necessary
                    || ($currentStatus->fLagerbestand > 0 && $xml['tartikel']['fLagerbestand'] <= 0)
                    // article was in stock before but is not anymore - check if flush is necessary
                    || ((int)$conf['global']['artikel_artikelanzeigefilter']
                        === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
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
                        $articleCount = $db->query(
                            'SELECT tkategorieartikel.kKategorie, COUNT(tkategorieartikel.kArtikel) AS cnt
                                FROM tkategorieartikel
                                LEFT JOIN tartikel
                                    ON tartikel.kArtikel = tkategorieartikel.kArtikel
                                WHERE tkategorieartikel.kKategorie IN (' . implode(',', $newArticleCategories) . ') ' .
                                $stockFilter .
                                ' GROUP BY tkategorieartikel.kKategorie',
                            \DB\ReturnType::ARRAY_OF_OBJECTS
                        );
                        foreach ($newArticleCategories as $nac) {
                            if (is_array($articleCount) && !empty($articleCount)) {
                                foreach ($articleCount as $ac) {
                                    if ($ac->kKategorie == $nac
                                        && (($currentStatus->cLagerBeachten !== 'Y' && (int)$ac->cnt === 1)
                                            || ($currentStatus->cLagerBeachten === 'Y' && (int)$ac->cnt === 0))
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
    loescheArtikel($Artikel->kArtikel, true, $conf);
    if ($products[0]->kArtikel > 0) {
        if (!$products[0]->cSeo) {
            // get seo path from productname, but replace slashes
            $products[0]->cSeo = \JTL\SeoHelper::getFlatSeoPath($products[0]->cName);
        }
        $products[0]->cSeo = \JTL\SeoHelper::getSeo($products[0]->cSeo);
        $products[0]->cSeo = \JTL\SeoHelper::checkSeo($products[0]->cSeo);
        // persistente werte
        $products[0]->dLetzteAktualisierung = 'NOW()';
        // mysql strict fixes
        if (empty($products[0]->dMHD)) {
            $products[0]->dMHD = '_DBNULL_';
        }
        if (isset($products[0]->dErstellt) && $products[0]->dErstellt === '') {
            $products[0]->dErstellt = 'NOW()';
        }
        if (empty($products[0]->dZulaufDatum)) {
            $products[0]->dZulaufDatum = '_DBNULL_';
        }
        if (empty($products[0]->dErscheinungsdatum)) {
            $products[0]->dErscheinungsdatum = '_DBNULL_';
        }
        if (isset($products[0]->fLieferantenlagerbestand) && $products[0]->fLieferantenlagerbestand === '') {
            $products[0]->fLieferantenlagerbestand = 0;
        } elseif (!isset($products[0]->fLieferantenlagerbestand)) {
            $products[0]->fLieferantenlagerbestand = 0;
        }
        if (isset($products[0]->fZulauf) && $products[0]->fZulauf === '') {
            $products[0]->fZulauf = 0;
        } elseif (!isset($products[0]->fZulauf)) {
            $products[0]->fZulauf = 0;
        }
        if (isset($products[0]->fLieferzeit) && $products[0]->fLieferzeit === '') {
            $products[0]->fLieferzeit = 0;
        } elseif (!isset($products[0]->fLieferzeit)) {
            $products[0]->fLieferzeit = 0;
        }
        // temp. fix for syncing with wawi 1.0
        if (isset($products[0]->kVPEEinheit) && is_array($products[0]->kVPEEinheit)) {
            $products[0]->kVPEEinheit = $products[0]->kVPEEinheit[0];
        }
        // any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
        if (isset($products[0]->fLagerbestand) && $products[0]->fLagerbestand > 0) {
            $delta = $db->query(
                "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                        ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$products[0]->kArtikel,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ($delta->totalquantity > 0) {
                //subtract delta from stocklevel
                $products[0]->fLagerbestand -= $delta->totalquantity;
                if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
                    $logger->debug(
                        'Artikel-Sync: Lagerbestand von kArtikel ' . $products[0]->kArtikel . ' wurde ' .
                        'wegen nicht-abgeholter Bestellungen ' .
                        'um ' . $delta->totalquantity . ' auf ' . $products[0]->fLagerbestand . ' reduziert.'
                    );
                }
            }
        }
        DBUpdateInsert('tartikel', $products, 'kArtikel');
        executeHook(HOOK_ARTIKEL_XML_BEARBEITEINSERT, ['oArtikel' => $products[0]]);
        if (isset($oSeoOld->cSeo)) {
            checkDbeSXmlRedirect($oSeoOld->cSeo, $products[0]->cSeo);
        }
        $db->query(
            "INSERT INTO tseo
                SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
                FROM tartikel, tsprache
                WHERE tartikel.kArtikel = " . (int)$products[0]->kArtikel . " 
                    AND tsprache.cStandard = 'Y' 
                    AND tartikel.cSeo != ''",
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
    $localized    = mapArray(
        $xml['tartikel'],
        'tartikelsprache',
        Mapper::getMapping('mArtikelSprache')
    );
    $allLanguages = Sprache::getAllLanguages(1);
    $langCount    = count($localized);
    for ($i = 0; $i < $langCount; ++$i) {
        if (!Sprache::isShopLanguage($localized[$i]->kSprache, $allLanguages)) {
            continue;
        }
        if (!$localized[$i]->cSeo) {
            $localized[$i]->cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$i]->cName);
        }
        if (!$localized[$i]->cSeo) {
            $localized[$i]->cSeo = $products[0]->cSeo;
        }
        if (!$localized[$i]->cSeo) {
            $localized[$i]->cSeo = $products[0]->cName;
        }
        $localized[$i]->cSeo = \JTL\SeoHelper::getSeo($localized[$i]->cSeo);
        $localized[$i]->cSeo = \JTL\SeoHelper::checkSeo($localized[$i]->cSeo);

        DBUpdateInsert('tartikelsprache', [$localized[$i]], 'kArtikel', 'kSprache');
        $db->delete(
            'tseo',
            ['cKey', 'kKey', 'kSprache'],
            ['kArtikel', (int)$localized[$i]->kArtikel, (int)$localized[$i]->kSprache]
        );

        $oSeo           = new stdClass();
        $oSeo->cSeo     = $localized[$i]->cSeo;
        $oSeo->cKey     = 'kArtikel';
        $oSeo->kKey     = $localized[$i]->kArtikel;
        $oSeo->kSprache = $localized[$i]->kSprache;
        $db->insert('tseo', $oSeo);
        // Insert into tredirect weil sich das SEO vom Artikel geändert hat
        if (isset($seoData[$localized[$i]->kSprache])) {
            checkDbeSXmlRedirect(
                $seoData[$localized[$i]->kSprache]->cSeo,
                $localized[$i]->cSeo
            );
        }
    }
    if (isset($xml['tartikel']['tattribut']) && is_array($xml['tartikel']['tattribut'])) {
        $attributes = mapArray(
            $xml['tartikel'],
            'tattribut',
            Mapper::getMapping('mAttribut')
        );
        $attrCount  = count($attributes);
        for ($i = 0; $i < $attrCount; ++$i) {
            if (count($attributes) < 2) {
                loescheAttribute($xml['tartikel']['tattribut attr']['kAttribut']);
                updateXMLinDB(
                    $xml['tartikel']['tattribut'],
                    'tattributsprache',
                    Mapper::getMapping('mAttributSprache'),
                    'kAttribut',
                    'kSprache'
                );
            } else {
                loescheAttribute($xml['tartikel']['tattribut'][$i . ' attr']['kAttribut']);
                updateXMLinDB(
                    $xml['tartikel']['tattribut'][$i],
                    'tattributsprache',
                    Mapper::getMapping('mAttributSprache'),
                    'kAttribut',
                    'kSprache'
                );
            }
        }
        DBUpdateInsert('tattribut', $attributes, 'kAttribut');
    }
    if (isset($xml['tartikel']['tmediendatei']) && is_array($xml['tartikel']['tmediendatei'])) {
        $mediaFiles = mapArray($xml['tartikel'], 'tmediendatei', Mapper::getMapping('mMediendatei'));
        $mediaCount = count($mediaFiles);
        for ($i = 0; $i < $mediaCount; ++$i) {
            if ($mediaCount < 2) {
                loescheMediendateien($xml['tartikel']['tmediendatei attr']['kMedienDatei']);
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'],
                    'tmediendateisprache',
                    Mapper::getMapping('mMediendateisprache'),
                    'kMedienDatei',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'],
                    'tmediendateiattribut',
                    Mapper::getMapping('mMediendateiattribut'),
                    'kMedienDateiAttribut'
                );
            } else {
                loescheMediendateien($xml['tartikel']['tmediendatei'][$i . ' attr']['kMedienDatei']);
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'][$i],
                    'tmediendateisprache',
                    Mapper::getMapping('mMediendateisprache'),
                    'kMedienDatei',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['tmediendatei'][$i],
                    'tmediendateiattribut',
                    Mapper::getMapping('mMediendateiattribut'),
                    'kMedienDateiAttribut'
                );
            }
        }
        DBUpdateInsert('tmediendatei', $mediaFiles, 'kMedienDatei');
    }
    if (isset($xml['tartikel']['tArtikelDownload']) && is_array($xml['tartikel']['tArtikelDownload'])) {
        $downloads = [];
        loescheDownload($Artikel->kArtikel);
        if (isset($xml['tartikel']['tArtikelDownload']['kDownload'])
            && is_array($xml['tartikel']['tArtikelDownload']['kDownload'])
        ) {
            foreach ($xml['tartikel']['tArtikelDownload']['kDownload'] as $kDownload) {
                $oArtikelDownload            = new stdClass();
                $oArtikelDownload->kDownload = (int)$kDownload;
                $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
                $downloads[]                 = $oArtikelDownload;

                if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                    unset($downloadKeys[$idx]);
                }
            }
        } else {
            $oArtikelDownload            = new stdClass();
            $oArtikelDownload->kDownload = (int)$xml['tartikel']['tArtikelDownload']['kDownload'];
            $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
            $downloads[]             = $oArtikelDownload;

            if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                unset($downloadKeys[$idx]);
            }
        }

        DBUpdateInsert('tartikeldownload', $downloads, 'kArtikel', 'kDownload');
    }
    foreach ($downloadKeys as $kDownload) {
        loescheDownload($Artikel->kArtikel, $kDownload);
    }
    if (isset($xml['tartikel']['tstueckliste']) && is_array($xml['tartikel']['tstueckliste'])) {
        $partlists = mapArray($xml['tartikel'], 'tstueckliste', Mapper::getMapping('mStueckliste'));
        $cacheIDs  = [];
        if (count($partlists) > 0) {
            loescheStueckliste($partlists[0]->kStueckliste);
        }
        DBUpdateInsert('tstueckliste', $partlists, 'kStueckliste', 'kArtikel');
        foreach ($partlists as $_sl) {
            if (isset($_sl->kArtikel)) {
                $cacheIDs[] = CACHING_GROUP_ARTICLE . '_' . (int)$_sl->kArtikel;
            }
        }
        if (count($cacheIDs) > 0) {
            Shop::Container()->getCache()->flushTags($cacheIDs);
        }
    }
    if (isset($xml['tartikel']['tartikelupload']) && is_array($xml['tartikel']['tartikelupload'])) {
        $uploads = mapArray($xml['tartikel'], 'tartikelupload', Mapper::getMapping('mArtikelUpload'));
        foreach ($uploads as &$upload) {
            $upload->nTyp          = 3;
            $upload->kUploadSchema = $upload->kArtikelUpload;
            $upload->kCustomID     = $upload->kArtikel;
            unset($upload->kArtikelUpload, $upload->kArtikel);
        }
        unset($upload);
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('oArtikelUpload_arr: ' . print_r($uploads, true));
        }
        DBUpdateInsert('tuploadschema', $uploads, 'kUploadSchema', 'kCustomID');
        if (count($uploads) < 2) {
            $localizedUploads = mapArray(
                $xml['tartikel']['tartikelupload'],
                'tartikeluploadsprache',
                Mapper::getMapping('mArtikelUploadSprache')
            );
            if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
                $logger->debug('oArtikelUploadSprache_arr: ' . print_r($localizedUploads, true));
            }
            DBUpdateInsert('tuploadschemasprache', $localizedUploads, 'kArtikelUpload', 'kSprache');
        } else {
            $ulCount = count($uploads);
            for ($i = 0; $i < $ulCount; ++$i) {
                $localizedUploads = mapArray(
                    $xml['tartikel']['tartikelupload'][$i],
                    'tartikeluploadsprache',
                    Mapper::getMapping('mArtikelUploadSprache')
                );
                if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
                    $logger->debug('oArtikelUploadSprache_arr: ' . print_r($localizedUploads, true));
                }
                DBUpdateInsert('tuploadschemasprache', $localizedUploads, 'kArtikelUpload', 'kSprache');
            }
        }
    }
    $db->delete('tartikelabnahme', 'kArtikel', $products[0]->kArtikel);
    if (isset($xml['tartikel']['tartikelabnahme']) && is_array($xml['tartikel']['tartikelabnahme'])) {
        $intervals = mapArray($xml['tartikel'], 'tartikelabnahme', Mapper::getMapping('mArtikelAbnahme'));
        DBUpdateInsert('tartikelabnahme', $intervals, 'kArtikel', 'kKundengruppe');
    }
    if (isset($xml['tartikel']['tartikelkonfiggruppe']) && is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
        $productConfig = mapArray(
            $xml['tartikel'],
            'tartikelkonfiggruppe',
            Mapper::getMapping('mArtikelkonfiggruppe')
        );
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('oArtikelKonfig_arr: ' . print_r($productConfig, true));
        }
        DBUpdateInsert('tartikelkonfiggruppe', $productConfig, 'kArtikel', 'kKonfiggruppe');
    }
    if (isset($xml['tartikel']['tartikelsonderpreis'])) {
        if ($xml['tartikel']['tartikelsonderpreis']['dEnde'] === '') {
            $xml['tartikel']['tartikelsonderpreis']['dEnde'] = '_DBNULL_';
        }
        updateXMLinDB(
            $xml['tartikel']['tartikelsonderpreis'],
            'tsonderpreise',
            Mapper::getMapping('mSonderpreise'),
            'kArtikelSonderpreis',
            'kKundengruppe'
        );
    }

    updateXMLinDB($xml['tartikel'], 'tpreise', Mapper::getMapping('mPreise'), 'kKundengruppe', 'kArtikel');

    if (isset($xml['tartikel']['tpreis'])) {
        handleNewPriceFormat($xml['tartikel']);
    } else {
        handleOldPriceFormat(mapArray($xml['tartikel'], 'tpreise', Mapper::getMapping('mPreise')));
    }

    updateXMLinDB(
        $xml['tartikel'],
        'tartikelsonderpreis',
        Mapper::getMapping('mArtikelSonderpreis'),
        'kArtikelSonderpreis'
    );
    updateXMLinDB(
        $xml['tartikel'],
        'tkategorieartikel',
        Mapper::getMapping('mKategorieArtikel'),
        'kKategorieArtikel'
    );
    updateXMLinDB(
        $xml['tartikel'],
        'tartikelattribut',
        Mapper::getMapping('mArtikelAttribut'),
        'kArtikelAttribut'
    );
    updateXMLinDB(
        $xml['tartikel'],
        'tartikelsichtbarkeit',
        Mapper::getMapping('mArtikelSichtbarkeit'),
        'kKundengruppe',
        'kArtikel'
    );
    updateXMLinDB($xml['tartikel'], 'txsell', Mapper::getMapping('mXSell'), 'kXSell');
    updateXMLinDB($xml['tartikel'], 'tartikelmerkmal', Mapper::getMapping('mArtikelSichtbarkeit'), 'kMermalWert');
    if ((int)$products[0]->nIstVater === 1) {
        $db->query(
            'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$products[0]->kArtikel . '
                     ) AS x
                 )
                WHERE kArtikel = ' . (int)$products[0]->kArtikel,
            \DB\ReturnType::AFFECTED_ROWS
        );
        Artikel::beachteVarikombiMerkmalLagerbestand(
            $products[0]->kArtikel,
            $conf['global']['artikel_artikelanzeigefilter']
        );
    } elseif (isset($products[0]->kVaterArtikel) && $products[0]->kVaterArtikel > 0) {
        $db->query(
            'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$products[0]->kVaterArtikel . '
                    ) AS x
                )
                WHERE kArtikel = ' . (int)$products[0]->kVaterArtikel,
            \DB\ReturnType::AFFECTED_ROWS
        );
        // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
        Artikel::beachteVarikombiMerkmalLagerbestand(
            $products[0]->kVaterArtikel,
            $conf['global']['artikel_artikelanzeigefilter']
        );
    }
    if (isset($xml['tartikel']['SQLDEL']) && strlen($xml['tartikel']['SQLDEL']) > 10) {
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('SQLDEL: ' . $xml['tartikel']['SQLDEL']);
        }
        foreach (explode("\n", $xml['tartikel']['SQLDEL']) as $cSQL) {
            if (strlen($cSQL) > 10) {
                $db->query($cSQL, \DB\ReturnType::AFFECTED_ROWS);
            }
        }
    }
    if (isset($xml['tartikel']['SQL']) && strlen($xml['tartikel']['SQL']) > 10) {
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('SQL: ' . $xml['tartikel']['SQL']);
        }
        foreach (explode("\n", $xml['tartikel']['SQL']) as $cSQL) {
            if (strlen($cSQL) > 10) {
                // Pre Wawi 0.99862 fix
                if (isset($products[0]->kVaterArtikel)
                    && $products[0]->kVaterArtikel > 0
                    && !isset($xml['tartikel']['SQLDEL'])
                    && strpos($cSQL, 'teigenschaftkombiwert') !== false
                ) {
                    $cDel     = substr($cSQL, strpos($cSQL, 'values ') + strlen('values '));
                    $cDel_arr = str_replace(['(', ')'], '', explode('),(', $cDel));
                    $kKey_arr = [];
                    foreach ($cDel_arr as $cDel) {
                        $kKey_arr[] = (int)substr($cDel, 0, strpos($cDel, ','));
                    }
                    $db->query(
                        'DELETE
                            FROM teigenschaftkombiwert 
                            WHERE kEigenschaftKombi IN (' . implode(',', $kKey_arr) . ')',
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
                $db->query($cSQL, \DB\ReturnType::AFFECTED_ROWS);
            }
        }
    }
    // Artikel Warenlager
    $db->delete('tartikelwarenlager', 'kArtikel', (int)$xml['tartikel attr']['kArtikel']);
    if (isset($xml['tartikel']['tartikelwarenlager']) && is_array($xml['tartikel']['tartikelwarenlager'])) {
        $storages = mapArray($xml['tartikel'], 'tartikelwarenlager', Mapper::getMapping('mArtikelWarenlager'));
        foreach ($storages as $storage) {
            if (empty($storage->dZulaufDatum)) {
                $storage->dZulaufDatum = null;
            }
            // Prevent SQL-Exception if duplicate datasets will be sent falsely
            $db->queryPrepared(
                'INSERT INTO tartikelwarenlager (kArtikel, kWarenlager, fBestand, fZulauf, dZulaufDatum)
                    VALUES (:kArtikel, :kWarenlager, :fBestand, :fZulauf, :dZulaufDatum)
                    ON DUPLICATE KEY UPDATE
                        fBestand = :fBestand,
                        fZulauf = :fZulauf,
                        dZulaufDatum = :dZulaufDatum',
                [
                    'kArtikel'     => $storage->kArtikel,
                    'kWarenlager'  => $storage->kWarenlager,
                    'fBestand'     => $storage->fBestand,
                    'fZulauf'      => $storage->fZulauf,
                    'dZulaufDatum' => $storage->dZulaufDatum ?? '_DBNULL_',
                ],
                \DB\ReturnType::QUERYSINGLE
            );
        }
    }
    $testSepcialPrice = false;
    if (isset($xml['tartikel']['tartikelsonderpreis']) && is_array($xml['tartikel']['tartikelsonderpreis'])) {
        $productSpecialPrices = mapArray(
            $xml['tartikel'],
            'tartikelsonderpreis',
            Mapper::getMapping('mArtikelSonderpreis')
        );
        if ($productSpecialPrices[0]->cAktiv === 'Y') {
            $specialPriceStart = explode('-', $productSpecialPrices[0]->dStart);
            if (count($specialPriceStart) > 2) {
                [$start_jahr, $start_monat, $start_tag] = $specialPriceStart;
            } else {
                $start_jahr  = null;
                $start_monat = null;
                $start_tag   = null;
            }
            $specialPriceEnd = explode('-', $productSpecialPrices[0]->dEnde);
            if (count($specialPriceEnd) > 2) {
                [$ende_jahr, $ende_monat, $ende_tag] = $specialPriceEnd;
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
            $testSepcialPrice = ($nNowStamp >= $nStartStamp
                && ($nNowStamp < $nEndStamp || (int)$productSpecialPrices[0]->dEnde === 0)
                && ($productSpecialPrices[0]->nIstAnzahl === 0 || ($productSpecialPrices[0]->nIstAnzahl === '1'
                        && (int)$productSpecialPrices[0]->nAnzahl < (int)$xml['tartikel']['fLagerbestand'])));
        }
        $spCount = count($productSpecialPrices);
        for ($i = 0; $i < $spCount; ++$i) {
            if ($testSepcialPrice === true) {
                $specialPrices = mapArray(
                    $xml['tartikel']['tartikelsonderpreis'],
                    'tsonderpreise',
                    Mapper::getMapping('mSonderpreise')
                );
                foreach ($specialPrices as $specialPrice) {
                    setzePreisverlauf(
                        $productSpecialPrices[0]->kArtikel,
                        $specialPrice->kKundengruppe,
                        $specialPrice->fNettoPreis
                    );
                }
            }
            updateXMLinDB(
                $xml['tartikel']['tartikelsonderpreis'],
                'tsonderpreise',
                Mapper::getMapping('mSonderpreise'),
                'kArtikelSonderpreis',
                'kKundengruppe'
            );
        }
        DBUpdateInsert('tartikelsonderpreis', $productSpecialPrices, 'kArtikelSonderpreis');
    }
    // Preise für Preisverlauf
    // NettoPreis übertragen, falls kein Sonderpreis gesetzt wurde
    if (!($testSepcialPrice === true
        && isset($xml['tartikel']['tartikelsonderpreis'])
        && is_array($xml['tartikel']['tartikelsonderpreis']))
    ) {
        $prices = mapArray($xml['tartikel'], 'tpreise', Mapper::getMapping('mPreise'));
        foreach ($prices as $price) {
            setzePreisverlauf($price->kArtikel, $price->kKundengruppe, $price->fVKNetto);
        }
    }
    if (isset($xml['tartikel']['teigenschaft']) && is_array($xml['tartikel']['teigenschaft'])) {
        $attributes = mapArray($xml['tartikel'], 'teigenschaft', Mapper::getMapping('mEigenschaft'));
        $eCount     = count($attributes);
        for ($i = 0; $i < $eCount; ++$i) {
            if (count($attributes) < 2) {
                loescheEigenschaft($xml['tartikel']['teigenschaft attr']['kEigenschaft']);
                updateXMLinDB(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftsprache',
                    Mapper::getMapping('mEigenschaftSprache'),
                    'kEigenschaft',
                    'kSprache'
                );
                updateXMLinDB(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftsichtbarkeit',
                    Mapper::getMapping('mEigenschaftsichtbarkeit'),
                    'kEigenschaft',
                    'kKundengruppe'
                );
                $attrValues = mapArray(
                    $xml['tartikel']['teigenschaft'],
                    'teigenschaftwert',
                    Mapper::getMapping('mEigenschaftWert')
                );
                $ewCount    = count($attrValues);
                for ($o = 0; $o < $ewCount; ++$o) {
                    if ($ewCount < 2) {
                        loescheEigenschaftWert(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert attr']['kEigenschaftWert']
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertsprache',
                            Mapper::getMapping('mEigenschaftWertSprache'),
                            'kEigenschaftWert',
                            'kSprache'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertaufpreis',
                            Mapper::getMapping('mEigenschaftWertAufpreis'),
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertsichtbarkeit',
                            Mapper::getMapping('mEigenschaftWertSichtbarkeit'),
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                            'teigenschaftwertabhaengigkeit',
                            Mapper::getMapping('mEigenschaftWertAbhaengigkeit'),
                            'kEigenschaftWert',
                            'kEigenschaftWertZiel'
                        );
                    } else {
                        loescheEigenschaftWert(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertsprache',
                            Mapper::getMapping('mEigenschaftWertSprache'),
                            'kEigenschaftWert',
                            'kSprache'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertaufpreis',
                            Mapper::getMapping('mEigenschaftWertAufpreis'),
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertsichtbarkeit',
                            Mapper::getMapping('mEigenschaftWertSichtbarkeit'),
                            'kEigenschaftWert',
                            'kKundengruppe'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                            'teigenschaftwertabhaengigkeit',
                            Mapper::getMapping('mEigenschaftWertAbhaengigkeit'),
                            'kEigenschaftWert',
                            'kEigenschaftWertZiel'
                        );
                    }
                }
                DBUpdateInsert('teigenschaftwert', $attrValues, 'kEigenschaftWert');
            } else {
                //@todo: this if was added to be able to sync with wawi 1.0 - check.
                if (isset($xml['tartikel']['teigenschaft'][$i . ' attr'])) {
                    loescheEigenschaft($xml['tartikel']['teigenschaft'][$i . ' attr']['kEigenschaft']);
                }
                //@todo: this if was added to be able to sync with wawi 1.0 - check.
                if (isset($xml['tartikel']['teigenschaft'][$i])) {
                    $current = $xml['tartikel']['teigenschaft'][$i];
                    updateXMLinDB(
                        $current,
                        'teigenschaftsprache',
                        Mapper::getMapping('mEigenschaftSprache'),
                        'kEigenschaft',
                        'kSprache'
                    );
                    updateXMLinDB(
                        $current,
                        'teigenschaftsichtbarkeit',
                        Mapper::getMapping('mEigenschaftsichtbarkeit'),
                        'kEigenschaft',
                        'kKundengruppe'
                    );
                    $attrValues = mapArray(
                        $current,
                        'teigenschaftwert',
                        Mapper::getMapping('mEigenschaftWert')
                    );
                    $ewCount    = count($attrValues);
                    for ($o = 0; $o < $ewCount; ++$o) {
                        if ($ewCount < 2) {
                            loescheEigenschaftWert(
                                $current['teigenschaftwert attr']['kEigenschaftWert']
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'],
                                'teigenschaftwertsprache',
                                Mapper::getMapping('mEigenschaftWertSprache'),
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'],
                                'teigenschaftwertaufpreis',
                                Mapper::getMapping('mEigenschaftWertAufpreis'),
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'],
                                'teigenschaftwertsichtbarkeit',
                                Mapper::getMapping('mEigenschaftWertSichtbarkeit'),
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'],
                                'teigenschaftwertabhaengigkeit',
                                Mapper::getMapping('mEigenschaftWertAbhaengigkeit'),
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        } else {
                            loescheEigenschaftWert($current['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']);
                            updateXMLinDB(
                                $current['teigenschaftwert'][$o],
                                'teigenschaftwertsprache',
                                Mapper::getMapping('mEigenschaftWertSprache'),
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'][$o],
                                'teigenschaftwertaufpreis',
                                Mapper::getMapping('mEigenschaftWertAufpreis'),
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'][$o],
                                'teigenschaftwertsichtbarkeit',
                                Mapper::getMapping('mEigenschaftWertSichtbarkeit'),
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $current['teigenschaftwert'][$o],
                                'teigenschaftwertabhaengigkeit',
                                Mapper::getMapping('mEigenschaftWertAbhaengigkeit'),
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        }
                    }
                    DBUpdateInsert('teigenschaftwert', $attrValues, 'kEigenschaftWert');
                }
            }
        }
        DBUpdateInsert('teigenschaft', $attributes, 'kEigenschaft');
    }
    $customerGroups = $db->query(
        'SELECT kKundengruppe FROM tkundengruppe',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $res[]             = (int)$Artikel->kArtikel;
    fuelleArtikelKategorieRabatt($products[0], $customerGroups);
    if (!empty($products[0]->kVaterartikel)) {
        $res[] = (int)$products[0]->kVaterartikel;
    }
    versendeVerfuegbarkeitsbenachrichtigung($products[0]);

    return $res;
}

/**
 * @param int $kArtikel
 */
function removeProductIdfromCoupons(int $kArtikel)
{
    $product = Shop::Container()->getDB()->select(
        'tartikel',
        'kArtikel',
        $kArtikel,
        null,
        null,
        null,
        null,
        false,
        'cArtNr'
    );

    if (isset($product->cArtNr) && is_string($product->cArtNr) && strlen($product->cArtNr) > 0) {
        $cArtNr = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $kArtikel,
            null,
            null,
            null,
            null,
            false,
            'cArtNr'
        )->cArtNr;

        Shop::Container()->getDB()->query(
            "UPDATE tkupon SET cArtikel = REPLACE(cArtikel, ';$cArtNr;', ';') WHERE cArtikel LIKE '%;$cArtNr;%'",
            \DB\ReturnType::DEFAULT
        );

        Shop::Container()->getDB()->query(
            "UPDATE tkupon SET cArtikel = '' WHERE cArtikel = ';'",
            \DB\ReturnType::DEFAULT
        );
    }
}

/**
 * @param int   $kArtikel
 * @param bool  $bForce
 * @param array $conf
 * @return array
 */
function loescheArtikel(int $kArtikel, bool $bForce = false, array $conf = null)
{
    $db = Shop::Container()->getDB();
    // get list of all categories the article was associated with
    $articleCategories = $db->selectAll(
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
            $categoryCount = $db->query(
                'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                    FROM tkategorieartikel
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tkategorieartikel.kArtikel
                    WHERE tkategorieartikel.kKategorie = ' . (int)$category->kKategorie . ' ' . $stockFilter,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (!isset($categoryCount->cnt) || (int)$categoryCount->cnt === 1) {
                // the category only had this article in it - flush cache
                flushCategoryTreeCache();
                break;
            }
        }
    }
    if ($kArtikel > 0) {
        $manufacturerID = $db->queryPrepared(
            'SELECT kHersteller 
                FROM tartikel 
                WHERE kArtikel = :pid',
            ['pid' => $kArtikel],
            \DB\ReturnType::SINGLE_OBJECT
        );
        $db->delete('tseo', ['cKey', 'kKey'], ['kArtikel', $kArtikel]);
        $db->delete('tartikel', 'kArtikel', $kArtikel);
        $db->delete('tpreise', 'kArtikel', $kArtikel);
        $db->delete('tpricerange', 'kArtikel', $kArtikel);
        $db->delete('tkategorieartikel', 'kArtikel', $kArtikel);
        $db->delete('tartikelsprache', 'kArtikel', $kArtikel);
        $db->delete('tartikelattribut', 'kArtikel', $kArtikel);
        $db->delete('tartikelwarenlager', 'kArtikel', $kArtikel);
        loescheArtikelAttribute($kArtikel);
        loescheArtikelEigenschaftWert($kArtikel);
        loescheArtikelEigenschaft($kArtikel);
        loescheSonderpreise($kArtikel);
        $db->delete('txsell', 'kArtikel', $kArtikel);
        $db->delete('tartikelmerkmal', 'kArtikel', $kArtikel);
        $db->delete('tartikelsichtbarkeit', 'kArtikel', $kArtikel);
        loescheArtikelMediendateien($kArtikel);
        if ($bForce === false) {
            loescheArtikelDownload($kArtikel);
        } else {
            loescheDownload($kArtikel, null);
        }
        loescheArtikelUpload($kArtikel);
        loescheKonfig($kArtikel);

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
    $db = Shop::Container()->getDB();
    $db->delete('teigenschaft', 'kEigenschaft', $attributeID);
    $db->delete('teigenschaftsprache', 'kEigenschaft', $attributeID);
    $db->delete('teigenschaftsichtbarkeit', 'kEigenschaft', $attributeID);
    $db->delete('teigenschaftwert', 'kEigenschaft', $attributeID);
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
    $db = Shop::Container()->getDB();
    $db->delete('teigenschaftwert', 'kEigenschaftWert', $attributeIDWert);
    $db->delete('teigenschaftwertaufpreis', 'kEigenschaftWert', $attributeIDWert);
    $db->delete('teigenschaftwertsichtbarkeit', 'kEigenschaftWert', $attributeIDWert);
    $db->delete('teigenschaftwertsprache', 'kEigenschaftWert', $attributeIDWert);
    $db->delete('teigenschaftwertabhaengigkeit', 'kEigenschaftWert', $attributeIDWert);
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
 * @param int $productID
 */
function checkArtikelBildLoeschung(int $productID)
{
    $images = Shop::Container()->getDB()->selectAll(
        'tartikelpict',
        'kArtikel',
        $productID,
        'kArtikelPict, kMainArtikelBild, cPfad'
    );
    foreach ($images as $image) {
        deleteArticleImage($image, $productID);
    }
    Shop::Container()->getCache()->flush('arr_article_images_' . $productID);
}

/**
 * checks whether the article is a child product in any configurator
 * and returns the product IDs of parent products if yes
 *
 * @param int $productID
 * @return array
 */
function getConfigParents(int $productID): array
{
    $parentProductIDs = [];
    $configItems      = Shop::Container()->getDB()->selectAll('tkonfigitem', 'kArtikel', $productID, 'kKonfiggruppe');
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
 * @param  int $productID
 * @return int[]
 */
function getDownloadKeys(int $productID): array
{
    if ($productID > 0) {
        $downloads = Shop::Container()->getDB()->selectAll('tartikeldownload', 'kArtikel', $productID, 'kDownload');

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
function clearProductCaches($products)
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
        $parentProducts = Shop::Container()->getDB()->query(
            'SELECT DISTINCT kVaterArtikel AS id
                FROM tartikel
                WHERE kArtikel IN (' . implode(',', $deps) . ')
                AND kVaterArtikel > 0',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($parentProducts as $parentProduct) {
            $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$parentProduct->id;
        }
    }

    $cacheTags[] = 'jtl_mmf';
    $cacheTags   = array_unique($cacheTags);
    // flush article cache, category cache and cache for gibMerkmalFilterOptionen() and mega menu/category boxes
    $totalCount = Shop::Container()->getCache()->flushTags($cacheTags);
    $end        = microtime(true);
    Shop::Container()->getLogService()->debug(
        'Flushed a total of ' . $totalCount .
        ' keys for ' . count($cacheTags) .
        ' tags in ' . ($end - $start) . 's'
    );
}
