<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Product;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use JTL\Shop;
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
     * @param int   $productID
     * @param array $conf
     */
    private function checkCategoryCache(array $xml, int $productID, array $conf): void
    {
        if (!isset($xml['tartikel']['tkategorieartikel'])
            || (int)$conf['global']['kategorien_anzeigefilter'] !== \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
            || !$this->cache->isCacheGroupActive(\CACHING_GROUP_CATEGORY)
        ) {
            return;
        }
        $flush = false;
        // get list of all categories the product is currently associated with
        $currentData = $this->db->selectAll(
            'tkategorieartikel',
            'kArtikel',
            $productID,
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
            if (\in_array($categoryID, $currentCategories, true)) {
                continue;
            }
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

        if ($flush === false) {
            foreach ($currentCategories as $category) {
                // check if the product is removed from an existing category
                if (\in_array($category, $newCategoryIDs, true)) {
                    continue;
                }
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
        if ($flush === false
            && (int)$conf['global']['artikel_artikelanzeigefilter'] !== \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
        ) {
            $check         = false;
            $currentStatus = $this->db->select(
                'tartikel',
                'kArtikel',
                $productID,
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

    /**
     * @param array $products
     * @return array
     */
    private function addProduct(array $products): array
    {
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
        if (!isset($products[0]->fLieferantenlagerbestand) || $products[0]->fLieferantenlagerbestand === '') {
            $products[0]->fLieferantenlagerbestand = 0;
        }
        if (!isset($products[0]->fZulauf) || $products[0]->fZulauf === '') {
            $products[0]->fZulauf = 0;
        }
        if (!isset($products[0]->fLieferzeit) || $products[0]->fLieferzeit === '') {
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

        return $products;
    }

    /**
     * @param object|null $oldSeo
     * @param string      $newSeo
     * @param int         $productID
     */
    private function addSeo($oldSeo, $newSeo, int $productID): void
    {
        if (isset($oldSeo->cSeo)) {
            $this->checkDbeSXmlRedirect($oldSeo->cSeo, $newSeo);
        }
        $this->db->queryPrepared(
            "INSERT INTO tseo
            SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
            FROM tartikel, tsprache
            WHERE tartikel.kArtikel = :pid 
                AND tsprache.cStandard = 'Y' 
                AND tartikel.cSeo != ''",
            ['pid' => $productID],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param array $xml
     * @param array $products
     * @param int   $productID
     */
    private function addProductLocalizations(array $xml, array $products, int $productID): void
    {
        $seoData      = $this->getSeoFromDB($productID, 'kArtikel', null, 'kSprache');
        $localized    = $this->mapper->mapArray(
            $xml['tartikel'],
            'tartikelsprache',
            'mArtikelSprache'
        );
        $allLanguages = LanguageHelper::getAllLanguages(1);
        foreach ($localized as $item) {
            if (!LanguageHelper::isShopLanguage($item->kSprache, $allLanguages)) {
                continue;
            }
            if (!$item->cSeo) {
                $item->cSeo = Seo::getFlatSeoPath($item->cName);
            }
            if (!$item->cSeo) {
                $item->cSeo = $products[0]->cSeo;
            }
            if (!$item->cSeo) {
                $item->cSeo = $products[0]->cName;
            }
            $item->cSeo = Seo::checkSeo(Seo::getSeo($item->cSeo));

            $this->upsert('tartikelsprache', [$item], 'kArtikel', 'kSprache');
            $this->db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kArtikel', (int)$item->kArtikel, (int)$item->kSprache]
            );

            $seo           = new stdClass();
            $seo->cSeo     = $item->cSeo;
            $seo->cKey     = 'kArtikel';
            $seo->kKey     = $item->kArtikel;
            $seo->kSprache = $item->kSprache;
            $this->db->insert('tseo', $seo);
            // Insert into tredirect weil sich das SEO vom Artikel geändert hat
            if (isset($seoData[$item->kSprache])) {
                $this->checkDbeSXmlRedirect(
                    $seoData[$item->kSprache]->cSeo,
                    $item->cSeo
                );
            }
        }
    }

    /**
     * @param array $xml
     */
    private function addAttributes(array $xml): void
    {
        if (!isset($xml['tartikel']['tattribut']) || !\is_array($xml['tartikel']['tattribut'])) {
            return;
        }
        $attributes = $this->mapper->mapArray(
            $xml['tartikel'],
            'tattribut',
            'mAttribut'
        );
        $attrCount  = \count($attributes);
        for ($i = 0; $i < $attrCount; ++$i) {
            if ($attrCount < 2) {
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

    /**
     * @param array $xml
     */
    private function addMediaFiles(array $xml): void
    {
        if (!isset($xml['tartikel']['tmediendatei']) || !\is_array($xml['tartikel']['tmediendatei'])) {
            return;
        }
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

    /**
     * @param array $xml
     * @param array $downloadKeys
     * @param int   $productID
     */
    private function addDownloads(array $xml, array $downloadKeys, int $productID): void
    {
        if (isset($xml['tartikel']['tArtikelDownload']) && \is_array($xml['tartikel']['tArtikelDownload'])) {
            $downloads = [];
            $this->deleteDownload($productID);
            $dlData = $xml['tartikel']['tArtikelDownload']['kDownload'];
            if (\is_array($dlData)) {
                foreach ($dlData as $kDownload) {
                    $download            = new stdClass();
                    $download->kDownload = (int)$kDownload;
                    $download->kArtikel  = $productID;
                    $downloads[]         = $download;
                    if (($idx = \array_search($download->kDownload, $downloadKeys, true)) !== false) {
                        unset($downloadKeys[$idx]);
                    }
                }
            } else {
                $download            = new stdClass();
                $download->kDownload = (int)$dlData;
                $download->kArtikel  = $productID;
                $downloads[]         = $download;
                if (($idx = \array_search($download->kDownload, $downloadKeys, true)) !== false) {
                    unset($downloadKeys[$idx]);
                }
            }
            $this->upsert('tartikeldownload', $downloads, 'kArtikel', 'kDownload');
        }
        foreach ($downloadKeys as $kDownload) {
            $this->deleteDownload($productID, $kDownload);
        }
    }

    /**
     * @param array $xml
     */
    private function addUploads(array $xml): void
    {
        if (!isset($xml['tartikel']['tartikelupload']) || !\is_array($xml['tartikel']['tartikelupload'])) {
            return;
        }
        $uploads = $this->mapper->mapArray($xml['tartikel'], 'tartikelupload', 'mArtikelUpload');
        foreach ($uploads as &$upload) {
            $upload->nTyp          = 3;
            $upload->kUploadSchema = $upload->kArtikelUpload;
            $upload->kCustomID     = $upload->kArtikel;
            unset($upload->kArtikelUpload, $upload->kArtikel);
        }
        unset($upload);
        $this->upsert('tuploadschema', $uploads, 'kUploadSchema', 'kCustomID');
        $ulCount = \count($uploads);
        if ($ulCount < 2) {
            $localizedUploads = $this->mapper->mapArray(
                $xml['tartikel']['tartikelupload'],
                'tartikeluploadsprache',
                'mArtikelUploadSprache'
            );
            $this->upsert('tuploadschemasprache', $localizedUploads, 'kArtikelUpload', 'kSprache');
        } else {
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

    /**
     * @param array $xml
     */
    private function addPartList(array $xml): void
    {
        if (!isset($xml['tartikel']['tstueckliste']) || !\is_array($xml['tartikel']['tstueckliste'])) {
            return;
        }
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

    /**
     * @param array $xml
     */
    private function addConfigGroups(array $xml): void
    {
        if (!isset($xml['tartikel']['tartikelkonfiggruppe']) || !\is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
            return;
        }
        $productConfig = $this->mapper->mapArray(
            $xml['tartikel'],
            'tartikelkonfiggruppe',
            'mArtikelkonfiggruppe'
        );
        $this->upsert('tartikelkonfiggruppe', $productConfig, 'kArtikel', 'kKonfiggruppe');
    }

    /**
     * @param array $xml
     * @param int   $productID
     * @throws \Exception
     */
    private function addPrices(array $xml, int $productID): void
    {
        if (isset($xml['tartikel']['tartikelsonderpreis']['dEnde'])
            && $xml['tartikel']['tartikelsonderpreis']['dEnde'] === ''
        ) {
            $xml['tartikel']['tartikelsonderpreis']['dEnde'] = '_DBNULL_';
        }

        $this->handleNewPriceFormat($productID, $xml['tartikel']);
        $this->handlePriceHistory($productID, $xml['tartikel']);
        $this->updateXMLinDB(
            $xml['tartikel'],
            'tartikelsonderpreis',
            'mArtikelSonderpreis',
            'kArtikelSonderpreis'
        );
        if (isset($xml['tartikel']['tartikelsonderpreis']) && \is_array($xml['tartikel']['tartikelsonderpreis'])) {
            $productSpecialPrices = $this->mapper->mapArray(
                $xml['tartikel'],
                'tartikelsonderpreis',
                'mArtikelSonderpreis'
            );
            $this->updateXMLinDB(
                $xml['tartikel']['tartikelsonderpreis'],
                'tsonderpreise',
                'mSonderpreise',
                'kArtikelSonderpreis',
                'kKundengruppe'
            );
            $this->upsert('tartikelsonderpreis', $productSpecialPrices, 'kArtikelSonderpreis');
        }
    }

    /**
     * @param array $xml
     */
    private function addCharacteristics(array $xml): void
    {
        if (!isset($xml['tartikel']['teigenschaft']) || !\is_array($xml['tartikel']['teigenschaft'])) {
            return;
        }
        $characteristics = $this->mapper->mapArray($xml['tartikel'], 'teigenschaft', 'mEigenschaft');
        $aCount          = \count($characteristics);
        for ($i = 0; $i < $aCount; ++$i) {
            if ($aCount < 2) {
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
        $this->upsert('teigenschaft', $characteristics, 'kEigenschaft');
    }

    /**
     * @param array $xml
     * @param int   $productID
     */
    private function addWarehouseData(array $xml, int $productID): void
    {
        $this->db->delete('tartikelwarenlager', 'kArtikel', $productID);
        if (!isset($xml['tartikel']['tartikelwarenlager']) || !\is_array($xml['tartikel']['tartikelwarenlager'])) {
            return;
        }
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

    /**
     * @param array $xml
     */
    private function handleSQL(array $xml): void
    {
        if (isset($xml['tartikel']['SQLDEL']) && \strlen($xml['tartikel']['SQLDEL']) > 10) {
            $this->logger->debug('SQLDEL: ' . $xml['tartikel']['SQLDEL']);
            foreach (\explode("\n", $xml['tartikel']['SQLDEL']) as $sql) {
                if (\strlen($sql) <= 10) {
                    continue;
                }
                $this->db->query($sql, ReturnType::DEFAULT);
            }
        }
        if (!isset($xml['tartikel']['SQL']) || \strlen($xml['tartikel']['SQL']) <= 10) {
            return;
        }
        $this->logger->debug('SQL: ' . $xml['tartikel']['SQL']);
        foreach (\explode("\n", $xml['tartikel']['SQL']) as $sql) {
            if (\strlen($sql) <= 10) {
                continue;
            }
            $this->db->query($sql, ReturnType::DEFAULT);
        }
    }

    /**
     * @param object $product
     * @param array  $conf
     */
    private function addStockData(object $product, array $conf): void
    {
        if ((int)$product->nIstVater === 1) {
            $this->db->query(
                'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$product->kArtikel . '
                     ) AS x
                 )
                WHERE kArtikel = ' . (int)$product->kArtikel,
                ReturnType::AFFECTED_ROWS
            );
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $product->kArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        } elseif (isset($product->kVaterArtikel) && $product->kVaterArtikel > 0) {
            $this->db->query(
                'UPDATE tartikel SET fLagerbestand =
                (SELECT * FROM
                    (SELECT SUM(fLagerbestand) 
                        FROM tartikel 
                        WHERE kVaterartikel = ' . (int)$product->kVaterArtikel . '
                    ) AS x
                )
                WHERE kArtikel = ' . (int)$product->kVaterArtikel,
                ReturnType::AFFECTED_ROWS
            );
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $product->kVaterArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        }
    }

    /***
     * @param array $xml
     * @param int   $productID
     */
    private function addMinPurchaseData(array $xml, int $productID): void
    {
        $this->db->delete('tartikelabnahme', 'kArtikel', $productID);
        if (isset($xml['tartikel']['tartikelabnahme']) && \is_array($xml['tartikel']['tartikelabnahme'])) {
            $intervals = $this->mapper->mapArray($xml['tartikel'], 'tartikelabnahme', 'mArtikelAbnahme');
            $this->upsert('tartikelabnahme', $intervals, 'kArtikel', 'kKundengruppe');
        }
    }

    /**
     * @param array $xml
     * @param array $conf
     * @return array - list of product IDs to flush
     */
    private function handleInserts($xml, array $conf): array
    {
        $res       = [];
        $productID = 0;
        if (\is_array($xml['tartikel attr'])) {
            $productID = (int)$xml['tartikel attr']['kArtikel'];
        }
        if (!$productID) {
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
            $productID,
            null,
            null,
            null,
            null,
            false,
            'cSeo'
        );
        $this->checkCategoryCache($xml, $productID, $conf);
        $downloadKeys = $this->getDownloadIDs($productID);
        $this->deleteProduct($productID, true, $conf);
        $products = $this->addProduct($products);
        $this->addSeo($oldSeo, $products[0]->cSeo, $productID);
        $this->addProductLocalizations($xml, $products, $productID);
        $this->addAttributes($xml);
        $this->addMediaFiles($xml);
        $this->addDownloads($xml, $downloadKeys, $productID);
        $this->addPartList($xml);
        $this->addUploads($xml);
        $this->addMinPurchaseData($xml, $productID);
        $this->addConfigGroups($xml);
        $this->addPrices($xml, $productID);
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
        $this->addStockData($products[0], $conf);
        $this->handleSQL($xml);
        $this->addWarehouseData($xml, $productID);
        $this->addCharacteristics($xml);
        $this->addCategoryDiscounts($productID);
        $res[] = $productID;
        if (!empty($products[0]->kVaterartikel)) {
            $res[] = (int)$products[0]->kVaterartikel;
        }
        $this->sendAvailabilityMails($products[0], $conf);

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
            $productID = (int)$productID;
            $parent    = Product::getParent($productID);
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
            if ($parent > 0) {
                Artikel::beachteVarikombiMerkmalLagerbestand($parent);
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
            && (int)$conf['global']['kategorien_anzeigefilter'] === \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
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
            if ($force === true) {
                $this->deleteDownload($id);
            } else {
                $this->deleteProductDownloads($id);
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
        foreach ($this->db->selectAll('teigenschaft', 'kArtikel', $productID, 'kEigenschaft') as $attribute) {
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
        foreach ($this->db->selectAll('tattribut', 'kArtikel', $productID, 'kAttribut') as $attribute) {
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
        foreach ($this->db->selectAll('tmediendatei', 'kArtikel', $productID, 'kMedienDatei') as $mediaFile) {
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
        foreach ($this->db->selectAll('tuploadschema', 'kCustomID', $productID, 'kUploadSchema') as $upload) {
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
     * @param int $productID
     * @return int[]
     */
    private function getDownloadIDs(int $productID): array
    {
        if ($productID <= 0) {
            return [];
        }

        return map($this->db->selectAll('tartikeldownload', 'kArtikel', $productID, 'kDownload'), function ($item) {
            return (int)$item->kDownload;
        });
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
            ['productID' => $productID],
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
     * @param int $productID
     * @return array
     */
    private function addCategoryDiscounts(int $productID): array
    {
        $customerGroups     = $this->db->query(
            'SELECT kKundengruppe FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $affectedProductIDs = [];
        $this->db->delete('tartikelkategorierabatt', 'kArtikel', $productID);
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
                    'kArtikel'      => $productID,
                    'kKundengruppe' => $item->kKundengruppe,
                ],
                ReturnType::SINGLE_OBJECT
            );

            if (isset($maxDiscount->fRabatt) && $maxDiscount->fRabatt > 0) {
                $discount                = new stdClass();
                $discount->kArtikel      = $productID;
                $discount->kKundengruppe = $item->kKundengruppe;
                $discount->kKategorie    = $maxDiscount->kKategorie;
                $discount->fRabatt       = $maxDiscount->fRabatt;
                $this->db->insert('tartikelkategorierabatt', $discount);
                $affectedProductIDs[] = $productID;
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
        $configGroupIDs = map(
            $this->db->selectAll('tkonfigitem', 'kArtikel', $productID, 'kKonfiggruppe'),
            function ($item) {
                return (int)$item->kKonfiggruppe;
            }
        );
        if (\count($configGroupIDs) === 0) {
            return [];
        }

        return map(
            $this->db->query(
                'SELECT kArtikel AS id
                    FROM tartikelkonfiggruppe 
                    WHERE kKonfiggruppe IN (' . \implode(',', $configGroupIDs) . ')',
                ReturnType::ARRAY_OF_OBJECTS
            ),
            function ($item) {
                return (int)$item->id;
            }
        );
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
        $cacheTags = new Collection();
        $deps      = new Collection();
        foreach ($products as $product) {
            if (isset($product['kArtikel'])) {
                // generated by bearbeiteDeletes()
                $cacheTags->push(\CACHING_GROUP_ARTICLE . '_' . (int)$product['kArtikel']);
                if ($product['kHersteller'] > 0) {
                    $cacheTags->push(\CACHING_GROUP_MANUFACTURER . '_' . (int)$product['kHersteller']);
                }
                $cacheTags = $cacheTags->concat(map($product['categories'], function ($item) {
                    return \CACHING_GROUP_CATEGORY . '_' . (int)$item->kKategorie;
                }));
            } elseif (\is_numeric($product)) {
                // generated by bearbeiteInsert()
                $cacheTags = $cacheTags->concat(map($this->getConfigParents($product), function ($item) {
                    return \CACHING_GROUP_ARTICLE . '_' . (int)$item;
                }))->push(\CACHING_GROUP_ARTICLE . '_' . (int)$product);
                $deps->push((int)$product);
            }
        }
        // additionally get dependencies for products that were inserted
        if ($deps->count() > 0) {
            $whereIn = $deps->implode(',');
            // flush cache tags associated with the product's manufacturer ID
            $cacheTags = $cacheTags->concat(map($this->db->query(
                'SELECT DISTINCT kHersteller AS id
                FROM tartikel 
                WHERE kArtikel IN (' . $whereIn . ') 
                    AND kHersteller > 0',
                ReturnType::ARRAY_OF_OBJECTS
            ), function ($item) {
                return \CACHING_GROUP_MANUFACTURER . '_' . (int)$item->id;
            }))->concat(map($this->db->query(
                'SELECT DISTINCT kKategorie AS id
                FROM tkategorieartikel
                WHERE kArtikel IN (' . $whereIn . ')',
                ReturnType::ARRAY_OF_OBJECTS
            ), function ($item) {
                return \CACHING_GROUP_CATEGORY . '_' . (int)$item->id;
            }))->concat(map($this->db->query(
                'SELECT DISTINCT kVaterArtikel AS id
                FROM tartikel
                WHERE kArtikel IN (' . $whereIn . ')
                AND kVaterArtikel > 0',
                ReturnType::ARRAY_OF_OBJECTS
            ), function ($item) {
                return \CACHING_GROUP_ARTICLE . '_' . (int)$item->id;
            }))->concat(map($this->db->query(
                'SELECT DISTINCT kArtikel AS id
                FROM tartikel
                WHERE kVaterArtikel IN (' . $whereIn . ')
                AND kVaterArtikel > 0',
                ReturnType::ARRAY_OF_OBJECTS
            ), function ($item) {
                return \CACHING_GROUP_ARTICLE . '_' . (int)$item->id;
            }));
        }

        $cacheTags->push('jtl_mmf');
        $cacheTags = $cacheTags->unique();
        // flush product cache, category cache and cache for gibMerkmalFilterOptionen() and mega menu/category boxes
        $totalCount = $this->cache->flushTags($cacheTags->toArray());
        $end        = \microtime(true);
        $this->logger->debug(
            'Flushed a total of ' . $totalCount .
            ' keys for ' . $cacheTags->count() .
            ' tags in ' . ($end - $start) . 's'
        );
    }
}
