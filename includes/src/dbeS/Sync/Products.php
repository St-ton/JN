<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Product;
use JTL\Helpers\Seo;
use JTL\Shop;
use JTL\Sprache;
use stdClass;
use function Functional\flatten;
use function Functional\map;

/**
 * Class Products
 * @package JTL\dbeS\Sync
 */
final class Products extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $conf       = Shop::getSettings([\CONF_GLOBAL, \CONF_ARTIKELDETAILS]);
        $productIDs = [];
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'artdel.xml') !== false) {
                $productIDs[] = $this->handleDeletes($xml, $conf);
            } else {
                foreach ($this->handleInserts($xml, $conf) as $productID) {
                    $productIDs[] = $productID;
                }
            }
            if ($i === 0) {
                $this->db->query(
                    'UPDATE tsuchcache
                        SET dGueltigBis = DATE_ADD(NOW(), INTERVAL ' . \SUCHCACHE_LEBENSDAUER . ' MINUTE)
                        WHERE dGueltigBis IS NULL',
                    ReturnType::AFFECTED_ROWS
                );
            }
        }
        $productIDs = flatten($productIDs);
        $this->handlePriceRange($productIDs);
        $this->db->query('COMMIT', ReturnType::DEFAULT);
        $this->clearProductCaches($productIDs);

        return null;
    }

    /**
     * @param array $xml
     * @param array $conf
     * @return array - list of product IDs to flush
     */
    private function handleInserts($xml, array $conf): array
    {
        $res               = [];
        $product           = new stdClass();
        $product->kArtikel = 0;

        if (\is_array($xml['tartikel attr'])) {
            $product->kArtikel = (int)$xml['tartikel attr']['kArtikel'];
        }
        if (!$product->kArtikel) {
            $this->logger->error('kArtikel fehlt! XML:' . \print_r($xml, true));

            return $res;
        }
        if (!\is_array($xml['tartikel'])) {
            return $res;
        }
        $products = $this->mapper->mapArray($xml, 'tartikel', 'mArtikel');
        $oldSeo   = $this->db->select(
            'tartikel',
            'kArtikel',
            $product->kArtikel,
            null,
            null,
            null,
            null,
            false,
            'cSeo'
        );
        $seoData  = $this->getSeoFromDB($product->kArtikel, 'kArtikel', null, 'kSprache');
        if (isset($xml['tartikel']['tkategorieartikel'])
            && (int)$conf['global']['kategorien_anzeigefilter'] === \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
            && $this->cache->isCacheGroupActive(\CACHING_GROUP_CATEGORY)
        ) {
            $flush = false;
            // get list of all categories the product is currently associated with
            $currentData = $this->db->selectAll(
                'tkategorieartikel',
                'kArtikel',
                (int)$product->kArtikel,
                'kKategorie'
            );
            // get list of all categories the product will be associated with after this update
            $newCategoriresData = $this->mapper->mapArray(
                $xml['tartikel'],
                'tkategorieartikel',
                'mKategorieArtikel'
            );
            $newCategoryIDs     = map($newCategoriresData, function ($e) {
                return (int)$e->kKategorie;
            });
            $currentCategories  = map($currentData, function ($e) {
                return (int)$e->kKategorie;
            });
            $stockFilter        = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            foreach ($newCategoryIDs as $categoryID) {
                if (!\in_array($categoryID, $currentCategories, true)) {
                    // the product was previously not associated with this category
                    $productCount = $this->db->query(
                        'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                        FROM tkategorieartikel
                        LEFT JOIN tartikel
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel
                        WHERE tkategorieartikel.kKategorie = ' . $categoryID . ' ' . $stockFilter,
                        ReturnType::SINGLE_OBJECT
                    );
                    if (isset($productCount->cnt) && (int)$productCount->cnt === 0) {
                        // the category was previously empty - flush cache
                        $flush = true;
                        break;
                    }
                }
            }

            if ($flush === false) {
                foreach ($currentCategories as $category) {
                    // check if the product is removed from an existing category
                    if (!\in_array($category, $newCategoryIDs, true)) {
                        // check if the product was the only one in at least one of these categories
                        $productCount = $this->db->query(
                            'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                            FROM tkategorieartikel
                            LEFT JOIN tartikel
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel
                            WHERE tkategorieartikel.kKategorie = ' . $category . ' ' . $stockFilter,
                            ReturnType::SINGLE_OBJECT
                        );
                        if (!isset($productCount->cnt) || (int)$productCount->cnt === 1) {
                            // the category only had this product in it - flush cache
                            $flush = true;
                            break;
                        }
                    }
                }
            }
            if ($flush === false
                && (int)$conf['global']['artikel_artikelanzeigefilter'] !== \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
            ) {
                $check         = false;
                $currentStatus = $this->db->select(
                    'tartikel',
                    'kArtikel',
                    $product->kArtikel,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'cLagerBeachten, cLagerKleinerNull, fLagerbestand'
                );
                if (isset($currentStatus->cLagerBeachten)) {
                    if (($currentStatus->fLagerbestand <= 0 && $xml['tartikel']['fLagerbestand'] > 0)
                        // product was not in stock before but is now - check if flush is necessary
                        || ($currentStatus->fLagerbestand > 0 && $xml['tartikel']['fLagerbestand'] <= 0)
                        // product was in stock before but is not anymore - check if flush is necessary
                        || ((int)$conf['global']['artikel_artikelanzeigefilter']
                            === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
                            && $currentStatus->cLagerKleinerNull !== $xml['tartikel']['cLagerKleinerNull'])
                        // overselling status changed - check if flush is necessary
                        || ($currentStatus->cLagerBeachten !== $xml['tartikel']['cLagerBeachten']
                            && $xml['tartikel']['fLagerbestand'] <= 0)
                    ) {
                        $check = true;
                    }
                    if ($check === true) {
                        if (\is_array($newCategoryIDs) && !empty($newCategoryIDs)) {
                            // get count of visible products in the product's futre categories
                            $productCount = $this->db->query(
                                'SELECT tkategorieartikel.kKategorie, COUNT(tkategorieartikel.kArtikel) AS cnt
                                FROM tkategorieartikel
                                LEFT JOIN tartikel
                                    ON tartikel.kArtikel = tkategorieartikel.kArtikel
                                WHERE tkategorieartikel.kKategorie IN (' . \implode(',', $newCategoryIDs) . ') ' .
                                $stockFilter .
                                ' GROUP BY tkategorieartikel.kKategorie',
                                ReturnType::ARRAY_OF_OBJECTS
                            );
                            foreach ($newCategoryIDs as $nac) {
                                if (\is_array($productCount) && !empty($productCount)) {
                                    foreach ($productCount as $ac) {
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
                $this->flushCategoryTreeCache();
            }
        }
        $downloadKeys = $this->getDownloadIDs($product->kArtikel);
        $this->deleteProduct($product->kArtikel, true, $conf);
        if ($products[0]->kArtikel > 0) {
            if (!$products[0]->cSeo) {
                // get seo path from productname, but replace slashes
                $products[0]->cSeo = Seo::getFlatSeoPath($products[0]->cName);
            }
            $products[0]->cSeo = Seo::getSeo($products[0]->cSeo);
            $products[0]->cSeo = Seo::checkSeo($products[0]->cSeo);
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
            if (isset($products[0]->kVPEEinheit) && \is_array($products[0]->kVPEEinheit)) {
                $products[0]->kVPEEinheit = $products[0]->kVPEEinheit[0];
            }
            // any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
            if (isset($products[0]->fLagerbestand) && $products[0]->fLagerbestand > 0) {
                $delta = $this->db->query(
                    "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                        ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$products[0]->kArtikel,
                    ReturnType::SINGLE_OBJECT
                );
                if ($delta->totalquantity > 0) {
                    $products[0]->fLagerbestand -= $delta->totalquantity;
                    $this->logger->debug(
                        'Artikel-Sync: Lagerbestand von kArtikel ' . $products[0]->kArtikel . ' wurde ' .
                        'wegen nicht-abgeholter Bestellungen ' .
                        'um ' . $delta->totalquantity . ' auf ' . $products[0]->fLagerbestand . ' reduziert.'
                    );
                }
            }
            $this->upsert('tartikel', $products, 'kArtikel');
            \executeHook(\HOOK_ARTIKEL_XML_BEARBEITEINSERT, ['oArtikel' => $products[0]]);
            if (isset($oldSeo->cSeo)) {
                $this->checkDbeSXmlRedirect($oldSeo->cSeo, $products[0]->cSeo);
            }
            $this->db->query(
                "INSERT INTO tseo
                SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
                FROM tartikel, tsprache
                WHERE tartikel.kArtikel = " . (int)$products[0]->kArtikel . " 
                    AND tsprache.cStandard = 'Y' 
                    AND tartikel.cSeo != ''",
                ReturnType::AFFECTED_ROWS
            );
        }
        $localized    = $this->mapper->mapArray(
            $xml['tartikel'],
            'tartikelsprache',
            'mArtikelSprache'
        );
        $allLanguages = Sprache::getAllLanguages(1);
        $langCount    = \count($localized);
        for ($i = 0; $i < $langCount; ++$i) {
            if (!Sprache::isShopLanguage($localized[$i]->kSprache, $allLanguages)) {
                continue;
            }
            if (!$localized[$i]->cSeo) {
                $localized[$i]->cSeo = Seo::getFlatSeoPath($localized[$i]->cName);
            }
            if (!$localized[$i]->cSeo) {
                $localized[$i]->cSeo = $products[0]->cSeo;
            }
            if (!$localized[$i]->cSeo) {
                $localized[$i]->cSeo = $products[0]->cName;
            }
            $localized[$i]->cSeo = Seo::getSeo($localized[$i]->cSeo);
            $localized[$i]->cSeo = Seo::checkSeo($localized[$i]->cSeo);

            $this->upsert('tartikelsprache', [$localized[$i]], 'kArtikel', 'kSprache');
            $this->db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kArtikel', (int)$localized[$i]->kArtikel, (int)$localized[$i]->kSprache]
            );

            $oSeo           = new stdClass();
            $oSeo->cSeo     = $localized[$i]->cSeo;
            $oSeo->cKey     = 'kArtikel';
            $oSeo->kKey     = $localized[$i]->kArtikel;
            $oSeo->kSprache = $localized[$i]->kSprache;
            $this->db->insert('tseo', $oSeo);
            // Insert into tredirect weil sich das SEO vom Artikel geändert hat
            if (isset($seoData[$localized[$i]->kSprache])) {
                $this->checkDbeSXmlRedirect(
                    $seoData[$localized[$i]->kSprache]->cSeo,
                    $localized[$i]->cSeo
                );
            }
        }
        if (isset($xml['tartikel']['tattribut']) && \is_array($xml['tartikel']['tattribut'])) {
            $attributes = $this->mapper->mapArray(
                $xml['tartikel'],
                'tattribut',
                'mAttribut'
            );
            $attrCount  = \count($attributes);
            for ($i = 0; $i < $attrCount; ++$i) {
                if (\count($attributes) < 2) {
                    $this->deleteAttribute($xml['tartikel']['tattribut attr']['kAttribut']);
                    $this->updateXMLinDB(
                        $xml['tartikel']['tattribut'],
                        'tattributsprache',
                        'mAttributSprache',
                        'kAttribut',
                        'kSprache'
                    );
                } else {
                    $this->deleteAttribute($xml['tartikel']['tattribut'][$i . ' attr']['kAttribut']);
                    $this->updateXMLinDB(
                        $xml['tartikel']['tattribut'][$i],
                        'tattributsprache',
                        'mAttributSprache',
                        'kAttribut',
                        'kSprache'
                    );
                }
            }
            $this->upsert('tattribut', $attributes, 'kAttribut');
        }
        if (isset($xml['tartikel']['tmediendatei']) && \is_array($xml['tartikel']['tmediendatei'])) {
            $mediaFiles = $this->mapper->mapArray($xml['tartikel'], 'tmediendatei', 'mMediendatei');
            $mediaCount = \count($mediaFiles);
            for ($i = 0; $i < $mediaCount; ++$i) {
                if ($mediaCount < 2) {
                    $this->deleteMediaFile($xml['tartikel']['tmediendatei attr']['kMedienDatei']);
                    $this->updateXMLinDB(
                        $xml['tartikel']['tmediendatei'],
                        'tmediendateisprache',
                        'mMediendateisprache',
                        'kMedienDatei',
                        'kSprache'
                    );
                    $this->updateXMLinDB(
                        $xml['tartikel']['tmediendatei'],
                        'tmediendateiattribut',
                        'mMediendateiattribut',
                        'kMedienDateiAttribut'
                    );
                } else {
                    $this->deleteMediaFile($xml['tartikel']['tmediendatei'][$i . ' attr']['kMedienDatei']);
                    $this->updateXMLinDB(
                        $xml['tartikel']['tmediendatei'][$i],
                        'tmediendateisprache',
                        'mMediendateisprache',
                        'kMedienDatei',
                        'kSprache'
                    );
                    $this->updateXMLinDB(
                        $xml['tartikel']['tmediendatei'][$i],
                        'tmediendateiattribut',
                        'mMediendateiattribut',
                        'kMedienDateiAttribut'
                    );
                }
            }
            $this->upsert('tmediendatei', $mediaFiles, 'kMedienDatei');
        }
        if (isset($xml['tartikel']['tArtikelDownload']) && \is_array($xml['tartikel']['tArtikelDownload'])) {
            $downloads = [];
            $this->deleteDownload($product->kArtikel);
            $dlData = $xml['tartikel']['tArtikelDownload']['kDownload'];
            if (\is_array($dlData)) {
                foreach ($dlData as $kDownload) {
                    $download            = new stdClass();
                    $download->kDownload = (int)$kDownload;
                    $download->kArtikel  = $product->kArtikel;
                    $downloads[]         = $download;
                    if (($idx = \array_search($download->kDownload, $downloadKeys, true)) !== false) {
                        unset($downloadKeys[$idx]);
                    }
                }
            } else {
                $download            = new stdClass();
                $download->kDownload = (int)$dlData;
                $download->kArtikel  = $product->kArtikel;
                $downloads[]         = $download;
                if (($idx = \array_search($download->kDownload, $downloadKeys, true)) !== false) {
                    unset($downloadKeys[$idx]);
                }
            }
            $this->upsert('tartikeldownload', $downloads, 'kArtikel', 'kDownload');
        }
        foreach ($downloadKeys as $kDownload) {
            $this->deleteDownload($product->kArtikel, $kDownload);
        }
        if (isset($xml['tartikel']['tstueckliste']) && \is_array($xml['tartikel']['tstueckliste'])) {
            $partlists = $this->mapper->mapArray($xml['tartikel'], 'tstueckliste', 'mStueckliste');
            $cacheIDs  = [];
            if (\count($partlists) > 0) {
                $this->deletePartList((int)$partlists[0]->kStueckliste);
            }
            $this->upsert('tstueckliste', $partlists, 'kStueckliste', 'kArtikel');
            foreach ($partlists as $_sl) {
                if (isset($_sl->kArtikel)) {
                    $cacheIDs[] = \CACHING_GROUP_ARTICLE . '_' . (int)$_sl->kArtikel;
                }
            }
            if (\count($cacheIDs) > 0) {
                $this->cache->flushTags($cacheIDs);
            }
        }
        if (isset($xml['tartikel']['tartikelupload']) && \is_array($xml['tartikel']['tartikelupload'])) {
            $uploads = $this->mapper->mapArray($xml['tartikel'], 'tartikelupload', 'mArtikelUpload');
            foreach ($uploads as &$upload) {
                $upload->nTyp          = 3;
                $upload->kUploadSchema = $upload->kArtikelUpload;
                $upload->kCustomID     = $upload->kArtikel;
                unset($upload->kArtikelUpload, $upload->kArtikel);
            }
            unset($upload);
            $this->upsert('tuploadschema', $uploads, 'kUploadSchema', 'kCustomID');
            if (\count($uploads) < 2) {
                $localizedUploads = $this->mapper->mapArray(
                    $xml['tartikel']['tartikelupload'],
                    'tartikeluploadsprache',
                    'mArtikelUploadSprache'
                );
                $this->upsert('tuploadschemasprache', $localizedUploads, 'kArtikelUpload', 'kSprache');
            } else {
                $ulCount = \count($uploads);
                for ($i = 0; $i < $ulCount; ++$i) {
                    $localizedUploads = $this->mapper->mapArray(
                        $xml['tartikel']['tartikelupload'][$i],
                        'tartikeluploadsprache',
                        'mArtikelUploadSprache'
                    );
                    $this->upsert('tuploadschemasprache', $localizedUploads, 'kArtikelUpload', 'kSprache');
                }
            }
        }
        $this->db->delete('tartikelabnahme', 'kArtikel', $products[0]->kArtikel);
        if (isset($xml['tartikel']['tartikelabnahme']) && \is_array($xml['tartikel']['tartikelabnahme'])) {
            $intervals = $this->mapper->mapArray($xml['tartikel'], 'tartikelabnahme', 'mArtikelAbnahme');
            $this->upsert('tartikelabnahme', $intervals, 'kArtikel', 'kKundengruppe');
        }
        if (isset($xml['tartikel']['tartikelkonfiggruppe']) && \is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
            $productConfig = $this->mapper->mapArray(
                $xml['tartikel'],
                'tartikelkonfiggruppe',
                'mArtikelkonfiggruppe'
            );
            $this->upsert('tartikelkonfiggruppe', $productConfig, 'kArtikel', 'kKonfiggruppe');
        }
        if (isset($xml['tartikel']['tartikelsonderpreis'])) {
            if ($xml['tartikel']['tartikelsonderpreis']['dEnde'] === '') {
                $xml['tartikel']['tartikelsonderpreis']['dEnde'] = '_DBNULL_';
            }
            $this->updateXMLinDB(
                $xml['tartikel']['tartikelsonderpreis'],
                'tsonderpreise',
                'mSonderpreise',
                'kArtikelSonderpreis',
                'kKundengruppe'
            );
        }

        $this->handleNewPriceFormat($product->kArtikel, $xml['tartikel']);
        $this->updateXMLinDB(
            $xml['tartikel'],
            'tartikelsonderpreis',
            'mArtikelSonderpreis',
            'kArtikelSonderpreis'
        );
        $this->updateXMLinDB(
            $xml['tartikel'],
            'tkategorieartikel',
            'mKategorieArtikel',
            'kKategorieArtikel'
        );
        $this->updateXMLinDB(
            $xml['tartikel'],
            'tartikelattribut',
            'mArtikelAttribut',
            'kArtikelAttribut'
        );
        $this->updateXMLinDB(
            $xml['tartikel'],
            'tartikelsichtbarkeit',
            'mArtikelSichtbarkeit',
            'kKundengruppe',
            'kArtikel'
        );
        $this->updateXMLinDB($xml['tartikel'], 'txsell', 'mXSell', 'kXSell');
        $this->updateXMLinDB($xml['tartikel'], 'tartikelmerkmal', 'mArtikelSichtbarkeit', 'kMermalWert');
        if ((int)$products[0]->nIstVater === 1) {
            $this->db->query(
                'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$products[0]->kArtikel . '
                     ) AS x
                 )
                WHERE kArtikel = ' . (int)$products[0]->kArtikel,
                ReturnType::AFFECTED_ROWS
            );
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $products[0]->kArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        } elseif (isset($products[0]->kVaterArtikel) && $products[0]->kVaterArtikel > 0) {
            $this->db->query(
                'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$products[0]->kVaterArtikel . '
                    ) AS x
                )
                WHERE kArtikel = ' . (int)$products[0]->kVaterArtikel,
                ReturnType::AFFECTED_ROWS
            );
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $products[0]->kVaterArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        }
        if (isset($xml['tartikel']['SQLDEL']) && \strlen($xml['tartikel']['SQLDEL']) > 10) {
            if ($this->logger->isHandling(\JTLLOG_LEVEL_DEBUG)) {
                $this->logger->debug('SQLDEL: ' . $xml['tartikel']['SQLDEL']);
            }
            foreach (\explode("\n", $xml['tartikel']['SQLDEL']) as $cSQL) {
                if (\strlen($cSQL) > 10) {
                    $this->db->query($cSQL, ReturnType::AFFECTED_ROWS);
                }
            }
        }
        if (isset($xml['tartikel']['SQL']) && \strlen($xml['tartikel']['SQL']) > 10) {
            if ($this->logger->isHandling(\JTLLOG_LEVEL_DEBUG)) {
                $this->logger->debug('SQL: ' . $xml['tartikel']['SQL']);
            }
            foreach (\explode("\n", $xml['tartikel']['SQL']) as $cSQL) {
                if (\strlen($cSQL) > 10) {
                    // Pre Wawi 0.99862 fix
                    if (isset($products[0]->kVaterArtikel)
                        && $products[0]->kVaterArtikel > 0
                        && !isset($xml['tartikel']['SQLDEL'])
                        && \strpos($cSQL, 'teigenschaftkombiwert') !== false
                    ) {
                        $del     = \substr($cSQL, \strpos($cSQL, 'values ') + \strlen('values '));
                        $itemIDs = [];
                        foreach (\str_replace(['(', ')'], '', \explode('),(', $del)) as $del) {
                            $itemIDs[] = (int)\substr($del, 0, \strpos($del, ','));
                        }
                        $this->db->query(
                            'DELETE
                            FROM teigenschaftkombiwert 
                            WHERE kEigenschaftKombi IN (' . \implode(',', $itemIDs) . ')',
                            ReturnType::AFFECTED_ROWS
                        );
                    }
                    $this->db->query($cSQL, ReturnType::AFFECTED_ROWS);
                }
            }
        }
        // Artikel Warenlager
        $this->db->delete('tartikelwarenlager', 'kArtikel', (int)$xml['tartikel attr']['kArtikel']);
        if (isset($xml['tartikel']['tartikelwarenlager']) && \is_array($xml['tartikel']['tartikelwarenlager'])) {
            $storages = $this->mapper->mapArray($xml['tartikel'], 'tartikelwarenlager', 'mArtikelWarenlager');
            foreach ($storages as $storage) {
                if (empty($storage->dZulaufDatum)) {
                    $storage->dZulaufDatum = null;
                }
                // Prevent SQL-Exception if duplicate datasets will be sent falsely
                $this->db->queryPrepared(
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
                        'dZulaufDatum' => $storage->dZulaufDatum ?? null,
                    ],
                    ReturnType::QUERYSINGLE
                );
            }
        }
        $testSepcialPrice = false;
        if (isset($xml['tartikel']['tartikelsonderpreis']) && \is_array($xml['tartikel']['tartikelsonderpreis'])) {
            $productSpecialPrices = $this->mapper->mapArray(
                $xml['tartikel'],
                'tartikelsonderpreis',
                'mArtikelSonderpreis'
            );
            if ($productSpecialPrices[0]->cAktiv === 'Y') {
                $specialPriceStart = \explode('-', $productSpecialPrices[0]->dStart);
                if (\count($specialPriceStart) > 2) {
                    [$startYear, $startMonth, $startDay] = $specialPriceStart;
                } else {
                    $startYear  = null;
                    $startMonth = null;
                    $startDay   = null;
                }
                $specialPriceEnd = \explode('-', $productSpecialPrices[0]->dEnde);
                if (\count($specialPriceEnd) > 2) {
                    [$endYear, $endMonth, $endDay] = $specialPriceEnd;
                } else {
                    $endYear  = null;
                    $endMonth = null;
                    $endDay   = null;
                }
                $stampEnd   = \time();
                $stampStart = \mktime(0, 0, 0, $startMonth, $startDay, $startYear);
                $stampNow   = \time();

                if ($endYear > 0) {
                    $stampEnd = \mktime(0, 0, 0, $endMonth, $endDay + 1, $endYear);
                }
                $testSepcialPrice = ($stampNow >= $stampStart
                    && ($stampNow < $stampEnd || (int)$productSpecialPrices[0]->dEnde === 0)
                    && ($productSpecialPrices[0]->nIstAnzahl === 0 || ((int)$productSpecialPrices[0]->nIstAnzahl === 1
                            && (int)$productSpecialPrices[0]->nAnzahl < (int)$xml['tartikel']['fLagerbestand'])));
            }
            $spCount = \count($productSpecialPrices);
            for ($i = 0; $i < $spCount; ++$i) {
                if ($testSepcialPrice === true) {
                    $specialPrices = $this->mapper->mapArray(
                        $xml['tartikel']['tartikelsonderpreis'],
                        'tsonderpreise',
                        'mSonderpreise'
                    );
                    foreach ($specialPrices as $specialPrice) {
                        $this->setzePreisverlauf(
                            $productSpecialPrices[0]->kArtikel,
                            $specialPrice->kKundengruppe,
                            $specialPrice->fNettoPreis
                        );
                    }
                }
                $this->updateXMLinDB(
                    $xml['tartikel']['tartikelsonderpreis'],
                    'tsonderpreise',
                    'mSonderpreise',
                    'kArtikelSonderpreis',
                    'kKundengruppe'
                );
            }
            $this->upsert('tartikelsonderpreis', $productSpecialPrices, 'kArtikelSonderpreis');
        }
        // Preise für Preisverlauf
        // NettoPreis übertragen, falls kein Sonderpreis gesetzt wurde
        if (!($testSepcialPrice === true
            && isset($xml['tartikel']['tartikelsonderpreis'])
            && \is_array($xml['tartikel']['tartikelsonderpreis']))
        ) {
            $prices = $this->mapper->mapArray($xml['tartikel'], 'tpreis', 'mPreis');
            foreach ($prices as $price) {
                if ((int)$price->kKundenGruppe > 0) {
                    $nettoPrice = isset($price->tpreisdetail[0]) && (int)$price->tpreisdetail[0]['nAnzahlAb'] === 0
                        ? $price->tpreisdetail[0]['fNettoPreis']
                        : $products[0]->fStandardpreisNetto;
                    $this->setzePreisverlauf($price->kArtikel, $price->kKundenGruppe, $nettoPrice);
                }
            }
        }
        if (isset($xml['tartikel']['teigenschaft']) && \is_array($xml['tartikel']['teigenschaft'])) {
            $attributes = $this->mapper->mapArray($xml['tartikel'], 'teigenschaft', 'mEigenschaft');
            $aCount     = \count($attributes);
            for ($i = 0; $i < $aCount; ++$i) {
                if (\count($attributes) < 2) {
                    $this->deleteCharacteristic($xml['tartikel']['teigenschaft attr']['kEigenschaft']);
                    $this->updateXMLinDB(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftsprache',
                        'mEigenschaftSprache',
                        'kEigenschaft',
                        'kSprache'
                    );
                    $this->updateXMLinDB(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftsichtbarkeit',
                        'mEigenschaftsichtbarkeit',
                        'kEigenschaft',
                        'kKundengruppe'
                    );
                    $attrValues = $this->mapper->mapArray(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftwert',
                        'mEigenschaftWert'
                    );
                    $ewCount    = \count($attrValues);
                    for ($o = 0; $o < $ewCount; ++$o) {
                        if ($ewCount < 2) {
                            $this->deleteAttributeValue(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert attr']['kEigenschaftWert']
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertsprache',
                                'mEigenschaftWertSprache',
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertaufpreis',
                                'mEigenschaftWertAufpreis',
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertsichtbarkeit',
                                'mEigenschaftWertSichtbarkeit',
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertabhaengigkeit',
                                'mEigenschaftWertAbhaengigkeit',
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        } else {
                            $this->deleteAttributeValue(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertsprache',
                                'mEigenschaftWertSprache',
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertaufpreis',
                                'mEigenschaftWertAufpreis',
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertsichtbarkeit',
                                'mEigenschaftWertSichtbarkeit',
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            $this->updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertabhaengigkeit',
                                'mEigenschaftWertAbhaengigkeit',
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        }
                    }
                    $this->upsert('teigenschaftwert', $attrValues, 'kEigenschaftWert');
                } else {
                    if (isset($xml['tartikel']['teigenschaft'][$i . ' attr'])) {
                        $this->deleteCharacteristic($xml['tartikel']['teigenschaft'][$i . ' attr']['kEigenschaft']);
                    }
                    if (isset($xml['tartikel']['teigenschaft'][$i])) {
                        $current = $xml['tartikel']['teigenschaft'][$i];
                        $this->updateXMLinDB(
                            $current,
                            'teigenschaftsprache',
                            'mEigenschaftSprache',
                            'kEigenschaft',
                            'kSprache'
                        );
                        $this->updateXMLinDB(
                            $current,
                            'teigenschaftsichtbarkeit',
                            'mEigenschaftsichtbarkeit',
                            'kEigenschaft',
                            'kKundengruppe'
                        );
                        $attrValues = $this->mapper->mapArray(
                            $current,
                            'teigenschaftwert',
                            'mEigenschaftWert'
                        );
                        $ewCount    = \count($attrValues);
                        for ($o = 0; $o < $ewCount; ++$o) {
                            if ($ewCount < 2) {
                                $this->deleteAttributeValue(
                                    $current['teigenschaftwert attr']['kEigenschaftWert']
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'],
                                    'teigenschaftwertsprache',
                                    'mEigenschaftWertSprache',
                                    'kEigenschaftWert',
                                    'kSprache'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'],
                                    'teigenschaftwertaufpreis',
                                    'mEigenschaftWertAufpreis',
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'],
                                    'teigenschaftwertsichtbarkeit',
                                    'mEigenschaftWertSichtbarkeit',
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'],
                                    'teigenschaftwertabhaengigkeit',
                                    'mEigenschaftWertAbhaengigkeit',
                                    'kEigenschaftWert',
                                    'kEigenschaftWertZiel'
                                );
                            } else {
                                $this->deleteAttributeValue(
                                    $current['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'][$o],
                                    'teigenschaftwertsprache',
                                    'mEigenschaftWertSprache',
                                    'kEigenschaftWert',
                                    'kSprache'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'][$o],
                                    'teigenschaftwertaufpreis',
                                    'mEigenschaftWertAufpreis',
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'][$o],
                                    'teigenschaftwertsichtbarkeit',
                                    'mEigenschaftWertSichtbarkeit',
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                $this->updateXMLinDB(
                                    $current['teigenschaftwert'][$o],
                                    'teigenschaftwertabhaengigkeit',
                                    'mEigenschaftWertAbhaengigkeit',
                                    'kEigenschaftWert',
                                    'kEigenschaftWertZiel'
                                );
                            }
                        }
                        $this->upsert('teigenschaftwert', $attrValues, 'kEigenschaftWert');
                    }
                }
            }
            $this->upsert('teigenschaft', $attributes, 'kEigenschaft');
        }
        $customerGroups = $this->db->query(
            'SELECT kKundengruppe FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $res[]          = (int)$product->kArtikel;
        $this->addCategoryDiscounts($products[0], $customerGroups);
        if (!empty($products[0]->kVaterartikel)) {
            $res[] = (int)$products[0]->kVaterartikel;
        }
        $this->versendeVerfuegbarkeitsbenachrichtigung($products[0], $conf);

        return $res;
    }

    /**
     * @param array $xml
     * @param array $conf
     * @return array - list of product IDs
     */
    private function handleDeletes($xml, array $conf): array
    {
        $res = [];
        if (!\is_array($xml['del_artikel'])) {
            return $res;
        }
        if (!\is_array($xml['del_artikel']['kArtikel'])) {
            $xml['del_artikel']['kArtikel'] = [$xml['del_artikel']['kArtikel']];
        }
        foreach ($xml['del_artikel']['kArtikel'] as $productID) {
            $productID     = (int)$productID;
            $kVaterArtikel = Product::getParent($productID);
            $this->deleteProductImages($productID);

            $this->db->queryPrepared(
                'DELETE teigenschaftkombiwert
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                        ON tartikel.kArtikel = :pid
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi',
                ['pid' => $productID],
                ReturnType::AFFECTED_ROWS
            );
            $this->removeProductIdfromCoupons($productID);
            $res[] = $this->deleteProduct($productID, false, $conf);
            $this->db->delete('tartikelkategorierabatt', 'kArtikel', $productID);
            if ($kVaterArtikel > 0) {
                Artikel::beachteVarikombiMerkmalLagerbestand($kVaterArtikel);
            }
            \executeHook(\HOOK_ARTIKEL_XML_BEARBEITEDELETES, ['kArtikel' => $productID]);
        }

        return $res;
    }

    /**
     * @param int   $id
     * @param bool  $force
     * @param array $conf
     * @return int
     */
    private function deleteProduct(int $id, bool $force = false, array $conf = null): int
    {
        // get list of all categories the product was associated with
        $categories = $this->db->selectAll(
            'tkategorieartikel',
            'kArtikel',
            $id,
            'kKategorie'
        );
        if ($force === false
            && isset($conf['global']['kategorien_anzeigefilter'])
            && (int)$conf['global']['kategorien_anzeigefilter'] === 2
        ) {
            $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            foreach ($categories as $category) {
                // check if the product was the only one in at least one of these categories
                $categoryCount = $this->db->query(
                    'SELECT COUNT(tkategorieartikel.kArtikel) AS cnt
                        FROM tkategorieartikel
                        LEFT JOIN tartikel
                            ON tartikel.kArtikel = tkategorieartikel.kArtikel
                        WHERE tkategorieartikel.kKategorie = ' . (int)$category->kKategorie . ' ' . $stockFilter,
                    ReturnType::SINGLE_OBJECT
                );
                if (!isset($categoryCount->cnt) || (int)$categoryCount->cnt === 1) {
                    // the category only had this product in it - flush cache
                    $this->flushCategoryTreeCache();
                    break;
                }
            }
        }
        if ($id > 0) {
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kArtikel', $id]);
            $this->db->delete('tartikel', 'kArtikel', $id);
            $this->db->delete('tpricerange', 'kArtikel', $id);
            $this->db->delete('tkategorieartikel', 'kArtikel', $id);
            $this->db->delete('tartikelsprache', 'kArtikel', $id);
            $this->db->delete('tartikelattribut', 'kArtikel', $id);
            $this->db->delete('tartikelwarenlager', 'kArtikel', $id);
            $this->deleteProductAttributes($id);
            $this->deleteProductAttributeValues($id);
            $this->deleteProductCharacteristics($id);
            $this->deletePrices($id);
            $this->deleteSpecialPrices($id);
            $this->db->delete('txsell', 'kArtikel', $id);
            $this->db->delete('tartikelmerkmal', 'kArtikel', $id);
            $this->db->delete('tartikelsichtbarkeit', 'kArtikel', $id);
            $this->deleteProductMediaFiles($id);
            if ($force === false) {
                $this->deleteProductDownloads($id);
            } else {
                $this->deleteDownload($id);
            }
            $this->deleteProductUploads($id);
            $this->deleteConfigGroup($id);

            return $id;
        }

        return 0;
    }

    /**
     * @param int $productID
     */
    private function deleteProductImages(int $productID): void
    {
        $images = $this->db->selectAll(
            'tartikelpict',
            'kArtikel',
            $productID,
            'kArtikelPict, kMainArtikelBild, cPfad'
        );
        foreach ($images as $image) {
            $this->deleteProductImage($image, $productID);
        }
        $this->cache->flush('arr_article_images_' . $productID);
    }

    /**
     * @param int $id
     */
    private function deleteCharacteristic(int $id): void
    {
        $this->db->delete('teigenschaft', 'kEigenschaft', $id);
        $this->db->delete('teigenschaftsprache', 'kEigenschaft', $id);
        $this->db->delete('teigenschaftsichtbarkeit', 'kEigenschaft', $id);
        $this->db->delete('teigenschaftwert', 'kEigenschaft', $id);
    }

    /**
     * @param int $productID
     */
    private function deleteProductCharacteristics(int $productID): void
    {
        $attributes = $this->db->selectAll('teigenschaft', 'kArtikel', $productID, 'kEigenschaft');
        foreach ($attributes as $attribute) {
            $this->deleteCharacteristic((int)$attribute->kEigenschaft);
        }
    }

    /**
     * @param int $id
     */
    private function deleteAttributeValue(int $id): void
    {
        $this->db->delete('teigenschaftwert', 'kEigenschaftWert', $id);
        $this->db->delete('teigenschaftwertaufpreis', 'kEigenschaftWert', $id);
        $this->db->delete('teigenschaftwertsichtbarkeit', 'kEigenschaftWert', $id);
        $this->db->delete('teigenschaftwertsprache', 'kEigenschaftWert', $id);
        $this->db->delete('teigenschaftwertabhaengigkeit', 'kEigenschaftWert', $id);
    }

    /**
     * @param int $productID
     */
    private function deleteProductAttributeValues(int $productID): void
    {
        $attributeValues = $this->db->queryPrepared(
            'SELECT teigenschaftwert.kEigenschaftWert
            FROM teigenschaftwert
            JOIN teigenschaft
                ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
            WHERE teigenschaft.kArtikel = :pid',
            ['pid' => $productID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($attributeValues as $attributeValue) {
            $this->deleteAttributeValue((int)$attributeValue->kEigenschaftWert);
        }
    }

    /**
     * @param int $id
     */
    private function deleteAttribute(int $id): void
    {
        $this->db->delete('tattribut', 'kAttribut', $id);
        $this->db->delete('tattributsprache', 'kAttribut', $id);
    }

    /**
     * @param int $productID
     */
    private function deleteProductAttributes(int $productID): void
    {
        $attributes = $this->db->selectAll('tattribut', 'kArtikel', $productID, 'kAttribut');
        foreach ($attributes as $attribute) {
            $this->deleteAttribute((int)$attribute->kAttribut);
        }
    }

    /**
     * @param int $id
     */
    private function deleteMediaFile(int $id): void
    {
        $this->db->delete('tmediendatei', 'kMedienDatei', $id);
        $this->db->delete('tmediendateisprache', 'kMedienDatei', $id);
        $this->db->delete('tmediendateiattribut', 'kMedienDatei', $id);
    }

    /**
     * @param int $productID
     */
    private function deleteProductMediaFiles(int $productID): void
    {
        $mediaFiles = $this->db->selectAll('tmediendatei', 'kArtikel', $productID, 'kMedienDatei');
        foreach ($mediaFiles as $mediaFile) {
            $this->deleteMediaFile((int)$mediaFile->kMedienDatei);
        }
    }

    /**
     * @param int $id
     */
    private function deleteUpload(int $id): void
    {
        $this->db->delete('tuploadschema', 'kUploadSchema', $id);
        $this->db->delete('tuploadschemasprache', 'kArtikelUpload', $id);
    }

    /**
     * @param int $productID
     */
    private function deleteProductUploads(int $productID): void
    {
        $uploads = $this->db->selectAll('tuploadschema', 'kCustomID', $productID, 'kUploadSchema');
        foreach ($uploads as $upload) {
            $this->deleteUpload((int)$upload->kUploadSchema);
        }
    }

    /**
     * @param int $productID
     * @param int $downloadID
     */
    private function deleteDownload(int $productID, int $downloadID = null): void
    {
        if ($productID > 0) {
            if ($downloadID > 0) {
                $this->db->delete('tartikeldownload', ['kArtikel', 'kDownload'], [$productID, $downloadID]);
            } else {
                $this->db->delete('tartikeldownload', 'kArtikel', $productID);
            }
        }
        if ($downloadID !== null) {
            $this->db->delete('tdownload', 'kDownload', $downloadID);
            $this->db->delete('tdownloadsprache', 'kDownload', $downloadID);
        }
    }

    /**
     * @param  int $productID
     * @return int[]
     */
    private function getDownloadIDs(int $productID): array
    {
        if ($productID > 0) {
            $downloads = $this->db->selectAll('tartikeldownload', 'kArtikel', $productID, 'kDownload');

            return map($downloads, function ($item) {
                return (int)$item->kDownload;
            });
        }

        return [];
    }

    /**
     * @param int $productID
     */
    private function deleteProductDownloads(int $productID): void
    {
        foreach ($this->getDownloadIDs($productID) as $downloadID) {
            $this->deleteDownload($productID, $downloadID);
        }
    }

    /**
     * @param int $productID
     */
    private function deleteConfigGroup(int $productID): void
    {
        $this->db->delete('tartikelkonfiggruppe', 'kArtikel', $productID);
    }

    /**
     * @param int $id
     */
    private function deletePartList(int $id): void
    {
        $this->db->delete('tstueckliste', 'kStueckliste', $id);
    }

    /**
     * @param int $productID
     * @return int
     */
    private function deletePrices(int $productID): int
    {
        return $this->db->queryPrepared(
            'DELETE p, pd
                FROM tpreis p
                INNER JOIN tpreisdetail pd ON pd.kPreis = p.kPreis
                WHERE  p.kArtikel = :productID',
            [
                'productID' => $productID,
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int $productID
     * @return int
     */
    private function deleteSpecialPrices(int $productID): int
    {
        return $this->db->queryPrepared(
            'DELETE asp, sp
            FROM tartikelsonderpreis asp
            LEFT JOIN tsonderpreise sp
                ON sp.kArtikelSonderpreis = asp.kArtikelSonderpreis
            WHERE asp.kArtikel = :productID',
            ['productID' => $productID],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int $productID
     */
    private function removeProductIdfromCoupons(int $productID): void
    {
        $data = $this->db->query(
            'SELECT cArtNr FROM tartikel WHERE kArtikel = ' . $productID,
            ReturnType::SINGLE_OBJECT
        );

        if (!empty($data->cArtNr)) {
            $artNo = $data->cArtNr;
            $this->db->queryPrepared(
                "UPDATE tkupon SET cArtikel = REPLACE(cArtikel, ';" . $artNo . ";', ';') WHERE cArtikel LIKE :artno",
                ['artno' => '%;' . $artNo . ';%'],
                ReturnType::DEFAULT
            );

            $this->db->query(
                "UPDATE tkupon SET cArtikel = '' WHERE cArtikel = ';'",
                ReturnType::DEFAULT
            );
        }
    }

    /**
     * @param object $product
     * @param array  $customerGroups
     * @return array
     */
    private function addCategoryDiscounts($product, $customerGroups): array
    {
        $affectedProductIDs = [];
        $this->db->delete('tartikelkategorierabatt', 'kArtikel', (int)$product->kArtikel);
        if (!\is_array($customerGroups) || \count($customerGroups) === 0) {
            return $affectedProductIDs;
        }
        foreach ($customerGroups as $item) {
            $maxDiscount = $this->db->queryPrepared(
                'SELECT tkategoriekundengruppe.fRabatt, tkategoriekundengruppe.kKategorie
                FROM tkategoriekundengruppe
                JOIN tkategorieartikel 
                    ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategorieartikel.kArtikel = :kArtikel
                LEFT JOIN tkategoriesichtbarkeit
                    ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :kKundengruppe
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                    AND tkategoriekundengruppe.kKundengruppe = :kKundengruppe
                ORDER BY tkategoriekundengruppe.fRabatt DESC
                LIMIT 1',
                [
                    'kArtikel'      => $product->kArtikel,
                    'kKundengruppe' => $item->kKundengruppe,
                ],
                ReturnType::SINGLE_OBJECT
            );

            if (isset($maxDiscount->fRabatt) && $maxDiscount->fRabatt > 0) {
                $discount                = new stdClass();
                $discount->kArtikel      = $product->kArtikel;
                $discount->kKundengruppe = $item->kKundengruppe;
                $discount->kKategorie    = $maxDiscount->kKategorie;
                $discount->fRabatt       = $maxDiscount->fRabatt;
                $this->db->insert('tartikelkategorierabatt', $discount);
                $affectedProductIDs[] = $product->kArtikel;
            }
        }

        return $affectedProductIDs;
    }

    /**
     * checks whether the product is a child product in any configurator
     * and returns the product IDs of parent products if yes
     *
     * @param int $productID
     * @return array
     */
    private function getConfigParents(int $productID): array
    {
        $parentProductIDs = [];
        $configItems      = $this->db->selectAll('tkonfigitem', 'kArtikel', $productID, 'kKonfiggruppe');
        if (!\is_array($configItems) || \count($configItems) === 0) {
            return $parentProductIDs;
        }
        $configGroupIDs = [];
        foreach ($configItems as $_configItem) {
            $configGroupIDs[] = (int)$_configItem->kKonfiggruppe;
        }
        $parents = $this->db->query(
            'SELECT kArtikel 
            FROM tartikelkonfiggruppe 
            WHERE kKonfiggruppe IN (' . \implode(',', $configGroupIDs) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($parents) || \count($parents) === 0) {
            return $parentProductIDs;
        }
        foreach ($parents as $_parent) {
            $parentProductIDs[] = (int)$_parent->kArtikel;
        }

        return $parentProductIDs;
    }

    /**
     * flush object cache for category tree
     *
     * @return int
     */
    private function flushCategoryTreeCache(): int
    {
        return $this->cache->flushTags(['jtl_category_tree']);
    }

    /**
     * clear all caches associated with a product ID
     * including manufacturers, categories, parent products
     *
     * @param array $products
     */
    private function clearProductCaches(array $products): void
    {
        $start     = \microtime(true);
        $cacheTags = [];
        $deps      = [];
        foreach ($products as $product) {
            if (isset($product['kArtikel'])) {
                // generated by bearbeiteDeletes()
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$product['kArtikel'];
                if ($product['kHersteller'] > 0) {
                    $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . (int)$product['kHersteller'];
                }
                foreach ($product['categories'] as $category) {
                    $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
                }
            } elseif (\is_numeric($product)) {
                // generated by bearbeiteInsert()
                $parentIDs = $this->getConfigParents($product);
                foreach ($parentIDs as $parentID) {
                    $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$parentID;
                }
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$product;
                $deps     [] = (int)$product;
            }
        }
        // additionally get dependencies for products that were inserted
        if (\count($deps) > 0) {
            // flush cache tags associated with the product's manufacturer ID
            $manufacturers = $this->db->query(
                'SELECT DISTINCT kHersteller 
                FROM tartikel 
                WHERE kArtikel IN (' . \implode(',', $deps) . ') 
                    AND kHersteller > 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($manufacturers as $manufacturer) {
                $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . (int)$manufacturer->kHersteller;
            }
            // flush cache tags associated with the product's category IDs
            $categories = $this->db->query(
                'SELECT DISTINCT kKategorie
                FROM tkategorieartikel
                WHERE kArtikel IN (' . \implode(',', $deps) . ')',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($categories as $category) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
            }
            // flush parent product IDs
            $parentProducts = $this->db->query(
                'SELECT DISTINCT kVaterArtikel AS id
                FROM tartikel
                WHERE kArtikel IN (' . \implode(',', $deps) . ')
                AND kVaterArtikel > 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($parentProducts as $parentProduct) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$parentProduct->id;
            }
        }

        $cacheTags[] = 'jtl_mmf';
        $cacheTags   = \array_unique($cacheTags);
        // flush product cache, category cache and cache for gibMerkmalFilterOptionen() and mega menu/category boxes
        $totalCount = $this->cache->flushTags($cacheTags);
        $end        = \microtime(true);
        $this->logger->debug(
            'Flushed a total of ' . $totalCount .
            ' keys for ' . \count($cacheTags) .
            ' tags in ' . ($end - $start) . 's'
        );
    }
}
