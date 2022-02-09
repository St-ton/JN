<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Category\MenuItem;
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
     * @var array
     */
    public array $elemente = [];

    /**
     * @var int
     */
    private int $languageID;

    /**
     * @var int
     */
    private int $customerGroupID;

    /**
     * @param int|null $languageID
     * @param int|null $customerGroupID
     */
    public function __construct(?int $languageID = null, ?int $customerGroupID = null)
    {
        $this->languageID      = $languageID ?? Shop::getLanguageID();
        $this->customerGroupID = $customerGroupID ?? Frontend::getCustomer()->getGroupID();
    }

    /**
     * Holt $anzahl an Top-Angebots Artikeln in die Liste
     *
     * @param string|null $topneu
     * @param int    $limit wieviele Top-Angebot Artikel geholt werden sollen
     * @return Artikel[]
     */
    public function getTopNeuArtikel(?string $topneu = null, int $limit = 3): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if ($topneu !== 'neu') {
            $topneu = '';
        }
        $cacheID = 'jtl_gtna_' . $topneu
            . '_' . $limit
            . '_' . $this->languageID
            . '_' . $this->customerGroupID;
        $items   = Shop::Container()->getCache()->get($cacheID);
        $db      = Shop::Container()->getDB();
        if ($items === false) {
            $qry   = ($topneu === 'neu')
                ? "cNeu = 'Y'"
                : "tartikel.cTopArtikel = 'Y'";
            $items = $db->getInts(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND ' . $qry . '
                    ORDER BY rand() LIMIT ' . $limit,
                'kArtikel',
                ['cgid' => $this->customerGroupID]
            );
            Shop::Container()->getCache()->set($cacheID, $items, [\CACHING_GROUP_CATEGORY]);
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($items as $id) {
            $product = new Artikel($db);
            $product->fuelleArtikel($id, $defaultOptions, $this->customerGroupID, $this->languageID);
            $this->elemente[] = $product;
        }

        return $this->elemente;
    }

    /**
     * Holt (max) $anzahl an Artikeln aus der angegebenen Kategorie in die Liste
     *
     * @param int    $categoryID Kategorie Key
     * @param int    $limitStart
     * @param int    $count - wieviele Artikel geholt werden sollen. Sind nicht genug in der entsprechenden
     *                            Kategorie enthalten, wird die Maximalanzahl geholt.
     * @param string $order
     * @return Artikel[]
     */
    public function getArtikelFromKategorie(int $categoryID, int $limitStart, int $count, string $order): array
    {
        $this->elemente = [];
        if (!$categoryID || !Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cacheID = 'jtl_gafk_'
            . \md5($categoryID . $limitStart . $count . $this->customerGroupID . $this->languageID);
        if (($res = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->elemente = $res;
        } else {
            $db            = Shop::Container()->getDB();
            $productFilter = Shop::getProductFilter();
            $conditionSQL  = '';
            if ($productFilter->hasManufacturer()) {
                $conditionSQL = ' AND tartikel.kHersteller = ' . $productFilter->getManufacturer()->getValue() . ' ';
            }
            $stockFilterSQL = $productFilter->getFilterSQL()->getStockFilterSQL();
            $items          = $db->getInts(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid'
                        . Preise::getPriceJoinSql($this->customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel ' . $conditionSQL . ' 
                        AND tkategorieartikel.kKategorie = :cid ' . $stockFilterSQL . '
                    ORDER BY ' . $order . ', nSort
                    LIMIT :lmts, :lmte',
                'kArtikel',
                [
                    'cgid' => $this->customerGroupID,
                    'cid'  => $categoryID,
                    'lmts' => $limitStart,
                    'lmte' => $count
                ]
            );
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($items as $id) {
                $product = new Artikel($db);
                $product->fuelleArtikel($id, $defaultOptions, $this->customerGroupID, $this->languageID);
                if ($product->kArtikel > 0) {
                    $this->elemente[] = $product;
                }
            }
            Shop::Container()->getCache()->set(
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
        $cnt            = \count($productIDs);
        $total          = 0;
        $defaultOptions = Artikel::getDefaultOptions();
        $db             = Shop::Container()->getDB();
        for ($i = $start; $i < $cnt; $i++) {
            $product = new Artikel($db);
            $product->fuelleArtikel($productIDs[$i], $defaultOptions, $this->customerGroupID, $this->languageID);
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
    public function holeTopArtikel(KategorieListe $categoryList): array
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
        $cacheID = 'hTA_' . \md5(\json_encode($categoryIDs));
        $items   = Shop::Container()->getCache()->get($cacheID);
        $db      = Shop::Container()->getDB();
        if ($items === false && \count($categoryIDs) > 0) {
            $conf           = Shop::getSettingValue(\CONF_ARTIKELUEBERSICHT, 'artikelubersicht_topbest_anzahl');
            $limitSql       = 'LIMIT ' . (int)($conf ?? 6);
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $items          = $db->getInts(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid '
                        . Preise::getPriceJoinSql($this->customerGroupID) . " 
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.cTopArtikel = 'Y'
                        AND (tkategorieartikel.kKategorie IN (" . \implode(', ', $categoryIDs) . ')) ' .
                $stockFilterSQL . '  ORDER BY rand() ' . $limitSql,
                'kArtikel',
                ['cgid' => $this->customerGroupID]
            );
            $cacheTags      = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            Shop::Container()->getCache()->set($cacheID, $items, $cacheTags);
        }
        if ($items === false) {
            return $this->elemente;
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($items as $id) {
            $product = new Artikel($db);
            $product->fuelleArtikel($id, $defaultOptions, $this->customerGroupID, $this->languageID);
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
    public function holeBestsellerArtikel(KategorieListe $categoryList, ?ArtikelListe $topProductsList = null): array
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
        $cacheID = 'hBsA_' . \md5(\json_encode($categoryIDs) . \json_encode($keys));
        $items   = Shop::Container()->getCache()->get($cacheID);
        $db      = Shop::Container()->getDB();
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
            $items          = $db->getInts(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid '
                        . Preise::getPriceJoinSql($this->customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL' . $excludes . '
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kArtikel = tbestseller.kArtikel
                        AND (tkategorieartikel.kKategorie IN (' . \implode(', ', $categoryIDs) . ')) ' .
                $stockFilterSQL . '
                    ORDER BY tbestseller.fAnzahl DESC ' . $limitSQL,
                'kArtikel',
                ['cgid' => $this->customerGroupID]
            );
            $cacheTags      = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            Shop::Container()->getCache()->set($cacheID, $items, $cacheTags);
        }
        if (\is_array($items)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($items as $id) {
                $product = new Artikel($db);
                $product->fuelleArtikel($id, $defaultOptions, $this->customerGroupID, $this->languageID);
                if ($product->kArtikel > 0) {
                    $this->elemente[] = $product;
                }
            }
        }

        return $this->elemente;
    }
}
