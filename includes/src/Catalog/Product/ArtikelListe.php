<?php

namespace JTL\Catalog\Product;

use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Category\MenuItem;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class ArtikelListe
 * @package JTL\Catalog\Product
 */
class ArtikelListe
{
    /**
     * Array mit Artikeln
     *
     * @var Artikel[]
     */
    public array $elemente = [];

    /**
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     */
    public function __construct(private ?DbInterface $db = null, private ?JTLCacheInterface $cache = null)
    {
        $this->db    = $db ?? Shop::Container()->getDB();
        $this->cache = $cache ?? Shop::Container()->getCache();
    }

    /**
     * Holt $anzahl an Top-Angebots Artikeln in die Liste
     *
     * @param string $topneu
     * @param int    $limit wieviele Top-Angebot Artikel geholt werden sollen
     * @param int    $customerGroupID
     * @param int    $languageID
     * @return Artikel[]
     */
    public function getTopNeuArtikel($topneu, int $limit = 3, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cacheID = 'jtl_tpnw_' . (\is_string($topneu) ? $topneu : '') .
            '_' . $limit .
            '_' . $languageID .
            '_' . $customerGroupID;
        $items   = $this->cache->get($cacheID);
        if ($items === false) {
            $qry = ($topneu === 'neu')
                ? "cNeu = 'Y'"
                : "tartikel.cTopArtikel = 'Y'";
            if (!$customerGroupID) {
                $customerGroupID = Frontend::getCustomerGroup()->getID();
            }
            $items = $this->db->getObjects(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND ' . $qry . '
                    ORDER BY rand() LIMIT ' . $limit,
                ['cgid' => $customerGroupID]
            );
            $this->cache->set($cacheID, $items, [\CACHING_GROUP_CATEGORY]);
        }
        if (\is_array($items)) {
            $defaultOptions = Artikel::getDefaultOptions();
            $currency       = Frontend::getCurrency();
            $customerGroup  = CustomerGroup::getByID($customerGroupID);
            foreach ($items as $item) {
                $product = new Artikel($this->db, $customerGroup, $currency, $this->cache);
                $product->fuelleArtikel((int)$item->kArtikel, $defaultOptions, $customerGroupID, $languageID);
                $this->elemente[] = $product;
            }
        }

        return $this->elemente;
    }

    /**
     * Holt (max) $anzahl an Artikeln aus der angegebenen Kategorie in die Liste
     *
     * @param int    $categoryID - Kategorie Key
     * @param int    $limitStart
     * @param int    $limitAnzahl - wieviele Artikel geholt werden sollen. Sind nicht genug in der entsprechenden
     *                            Kategorie enthalten, wird die Maximalanzahl geholt.
     * @param string $order
     * @param int    $customerGroupID
     * @param int    $languageID
     * @return Artikel[]
     */
    public function getArtikelFromKategorie(
        int    $categoryID,
        int    $limitStart,
        int    $limitAnzahl,
        string $order,
        int    $customerGroupID = 0,
        int    $languageID = 0
    ): array {
        $this->elemente = [];
        if (!$categoryID || !Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $cacheID = 'jtl_top_' . \md5($categoryID . $limitStart . $limitAnzahl . $customerGroupID . $languageID);
        if (($res = $this->cache->get($cacheID)) !== false) {
            $this->elemente = $res;
        } else {
            $productFilter = Shop::getProductFilter();
            $conditionSQL  = '';
            if ($productFilter->hasManufacturer()) {
                $conditionSQL = ' AND tartikel.kHersteller = ' . $productFilter->getManufacturer()->getValue() . ' ';
            }
            $stockFilterSQL = $productFilter->getFilterSQL()->getStockFilterSQL();
            $items          = $this->db->getObjects(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid ' .
                    Preise::getPriceJoinSql($customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel ' . $conditionSQL . ' 
                        AND tkategorieartikel.kKategorie = :cid ' . $stockFilterSQL . '
                    ORDER BY ' . $order . ', nSort
                    LIMIT :lmts, :lmte',
                [
                    'cgid' => $customerGroupID,
                    'cid'  => $categoryID,
                    'lmts' => $limitStart,
                    'lmte' => $limitAnzahl
                ]
            );
            $defaultOptions = Artikel::getDefaultOptions();
            $currency       = Frontend::getCurrency();
            $customerGroup  = CustomerGroup::getByID($customerGroupID);
            foreach ($items as $item) {
                $product = new Artikel($this->db, $customerGroup, $currency, $this->cache);
                $product->fuelleArtikel((int)$item->kArtikel, $defaultOptions, $customerGroupID, $languageID);
                if ($product->kArtikel > 0) {
                    $this->elemente[] = $product;
                }
            }
            $this->cache->set(
                $cacheID,
                $this->elemente,
                [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_CATEGORY . '_' . $categoryID]
            );
        }

        return $this->elemente;
    }

    /**
     * @param array $productIDs
     * @param int   $start
     * @param int   $limit
     * @return Artikel[]
     */
    public function getArtikelByKeys(array $productIDs, int $start, int $limit): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cnt             = \count($productIDs);
        $total           = 0;
        $defaultOptions  = Artikel::getDefaultOptions();
        $languageID      = Shop::getLanguageID();
        $customerGroup   = Frontend::getCustomerGroup();
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        $currency        = Frontend::getCurrency();
        for ($i = $start; $i < $cnt; $i++) {
            $product = new Artikel($this->db, $customerGroup, $currency, $this->cache);
            $product->fuelleArtikel($productIDs[$i], $defaultOptions, $customerGroupID, $languageID);
            if ($product->kArtikel > 0) {
                ++$total;
                $this->elemente[] = $product;
            }
            if ($total >= $limit) {
                break;
            }
        }
        $this->elemente = Product::separateByAvailability($this->elemente, true);

        return $this->elemente;
    }

    /**
     * @param KategorieListe $categoryList
     * @return Artikel[]
     */
    public function holeTopArtikel($categoryList): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        $i           = 0;
        if (!empty($categoryList->elemente)) {
            foreach ($categoryList->elemente as $category) {
                /** @var MenuItem $category */
                $categoryIDs[] = $category->getID();
                if (++$i > \PRODUCT_LIST_CATEGORY_LIMIT) {
                    break;
                }
                if ($category->hasChildren()) {
                    foreach ($category->getChildren() as $level2) {
                        /** @var MenuItem $level2 */
                        $categoryIDs[] = $level2->getID();
                        if (++$i > \PRODUCT_LIST_CATEGORY_LIMIT) {
                            break;
                        }
                    }
                }
            }
        }
        $cacheID         = 'hTA_' . \md5(\json_encode($categoryIDs));
        $items           = $this->cache->get($cacheID);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($items === false && \count($categoryIDs) > 0) {
            $conf           = Shop::getSettingValue(\CONF_ARTIKELUEBERSICHT, 'artikelubersicht_topbest_anzahl');
            $limitSql       = 'LIMIT ' . (int)($conf ?? 6);
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $items          = $this->db->getObjects(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid '
                    . Preise::getPriceJoinSql($customerGroupID) . " 
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.cTopArtikel = 'Y'
                        AND (tkategorieartikel.kKategorie IN (" . \implode(', ', $categoryIDs) . ')) ' .
                        $stockFilterSQL . '  ORDER BY rand() ' . $limitSql,
                ['cgid' => $customerGroupID]
            );
            $cacheTags      = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            $this->cache->set($cacheID, $items, $cacheTags);
        }
        if ($items === false) {
            return $this->elemente;
        }
        $defaultOptions = Artikel::getDefaultOptions();
        $languageID     = Shop::getLanguageID();
        $currency       = Frontend::getCurrency();
        $customerGroup  = CustomerGroup::getByID($customerGroupID);
        foreach ($items as $obj) {
            $product = new Artikel($this->db, $customerGroup, $currency, $this->cache);
            $product->fuelleArtikel((int)$obj->kArtikel, $defaultOptions, $customerGroupID, $languageID);
            if ($product->kArtikel > 0) {
                $this->elemente[] = $product;
            }
        }

        return $this->elemente;
    }

    /**
     * @param Kategorieliste    $categoryList
     * @param ArtikelListe|null $topProductsList
     * @return Artikel[]
     */
    public function holeBestsellerArtikel($categoryList, $topProductsList = null): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        if (GeneralObject::isCountable('elemente', $categoryList)) {
            $i = 0;
            foreach ($categoryList->elemente as $category) {
                /** @var MenuItem $category */
                $categoryIDs[] = $category->getID();
                if (++$i > \PRODUCT_LIST_CATEGORY_LIMIT) {
                    break;
                }
                if ($category->hasChildren()) {
                    foreach ($category->getChildren() as $level2) {
                        /** @var MenuItem $level2 */
                        $categoryIDs[] = $level2->getID();
                        if (++$i > \PRODUCT_LIST_CATEGORY_LIMIT) {
                            break;
                        }
                    }
                }
            }
        }
        $keys = null;
        if ($topProductsList instanceof self) {
            $keys = map($topProductsList->elemente, static function ($e) {
                return $e->cacheID ?? 0;
            });
        }
        $cacheID         = 'hBsA_' . \md5(\json_encode($categoryIDs) . \json_encode($keys));
        $items           = $this->cache->get($cacheID);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($items === false && \count($categoryIDs) > 0) {
            // top artikel nicht nochmal in den bestsellen vorkommen lassen
            $excludes = '';
            if (GeneralObject::isCountable('elemente', $topProductsList)) {
                $exclude  = map($topProductsList->elemente, static function ($e) {
                    return (int)$e->kArtikel;
                });
                $excludes = \count($exclude) > 0
                    ? ' AND tartikel.kArtikel NOT IN (' . \implode(',', $exclude) . ') '
                    : '';
            }
            $conf           = Shop::getSettingValue(\CONF_ARTIKELUEBERSICHT, 'artikelubersicht_topbest_anzahl');
            $limitSQL       = 'LIMIT ' . (int)($conf ?? 6);
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $items          = $this->db->getObjects(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid ' . Preise::getPriceJoinSql($customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL' . $excludes . '
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kArtikel = tbestseller.kArtikel
                        AND (tkategorieartikel.kKategorie IN (' . \implode(', ', $categoryIDs) . '))
                        AND tbestseller.isBestseller = 1 ' .
                        $stockFilterSQL . '
                    ORDER BY tbestseller.fAnzahl DESC ' . $limitSQL,
                ['cgid' => $customerGroupID]
            );
            $cacheTags      = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            $this->cache->set($cacheID, $items, $cacheTags);
        }
        if (\is_array($items)) {
            $defaultOptions = Artikel::getDefaultOptions();
            $languageID     = Shop::getLanguageID();
            $currency       = Frontend::getCurrency();
            $customerGroup  = CustomerGroup::getByID($customerGroupID);
            foreach ($items as $item) {
                $product = new Artikel($this->db, $customerGroup, $currency, $this->cache);
                $product->fuelleArtikel((int)$item->kArtikel, $defaultOptions, $customerGroupID, $languageID);
                if ($product->kArtikel > 0) {
                    $this->elemente[] = $product;
                }
            }
        }

        return $this->elemente;
    }
}
