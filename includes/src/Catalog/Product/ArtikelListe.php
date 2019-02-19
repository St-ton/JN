<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use function Functional\map;
use JTL\DB\ReturnType;
use JTL\Catalog\Category\KategorieListe;
use JTL\Session\Frontend;
use JTL\Shop;

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
    public $elemente = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Holt $anzahl an Top-Angebots Artikeln in die Liste
     *
     * @param string $topneu
     * @param int    $anzahl wieviele Top-Angebot Artikel geholt werden sollen
     * @param int    $kKundengruppe
     * @param int    $kSprache
     * @return Artikel[]
     */
    public function getTopNeuArtikel($topneu, int $anzahl = 3, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cacheID = 'jtl_tpnw_' . (\is_string($topneu) ? $topneu : '') .
            '_' . $anzahl .
            '_' . $kSprache .
            '_' . $kKundengruppe;
        $objArr  = Shop::Container()->getCache()->get($cacheID);
        if ($objArr === false) {
            $qry = ($topneu === 'neu')
                ? "cNeu = 'Y'"
                : "tartikel.cTopArtikel = 'Y'";
            if (!$kKundengruppe) {
                $kKundengruppe = Frontend::getCustomerGroup()->getID();
            }
            $objArr = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND ' . $qry . '
                    ORDER BY rand() LIMIT ' . $anzahl,
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $objArr, [\CACHING_GROUP_CATEGORY]);
        }
        if (\is_array($objArr)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($objArr as $obj) {
                $artikel = new Artikel();
                $artikel->fuelleArtikel($obj->kArtikel, $defaultOptions);
                $this->elemente[] = $artikel;
            }
        }

        return $this->elemente;
    }

    /**
     * Holt (max) $anzahl an Artikeln aus der angegebenen Kategorie in die Liste
     *
     * @param int    $kKategorie  Kategorie Key
     * @param int    $limitStart
     * @param int    $limitAnzahl - wieviele Artikel geholt werden sollen. Sind nicht genug in der entsprechenden
     *                            Kategorie enthalten, wird die Maximalanzahl geholt.
     * @param string $order
     * @param int    $kKundengruppe
     * @param int    $kSprache
     * @return Artikel[]
     */
    public function getArtikelFromKategorie(
        int $kKategorie,
        int $limitStart,
        int $limitAnzahl,
        string $order,
        int $kKundengruppe = 0,
        int $kSprache = 0
    ): array {
        $this->elemente = [];
        if (!$kKategorie || !Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $cacheID = 'jtl_top_' . \md5($kKategorie . $limitStart . $limitAnzahl . $kKundengruppe . $kSprache);
        if (($res = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->elemente = $res;
        } else {
            $hstSQL = '';
            if (Shop::getProductFilter() !== null && Shop::getProductFilter()->hasManufacturer()) {
                $hstSQL = ' AND tartikel.kHersteller = ' .
                    Shop::getProductFilter()->getManufacturer()->getValue() . ' ';
            }
            $lagerfilter    = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $objArr         = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . ' ' .
                    Preise::getPriceJoinSql($kKundengruppe) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel ' . $hstSQL . ' 
                        AND tkategorieartikel.kKategorie = ' . $kKategorie . ' ' . $lagerfilter . '
                    ORDER BY $order, nSort
                    LIMIT ' . $limitStart . ', ' . $limitAnzahl,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($objArr as $obj) {
                $artikel = new Artikel();
                $artikel->fuelleArtikel($obj->kArtikel, $defaultOptions);
                $this->elemente[] = $artikel;
            }
            Shop::Container()->getCache()->set(
                $cacheID,
                $this->elemente,
                [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_CATEGORY . '_' . $kKategorie]
            );
        }

        return $this->elemente;
    }

    /**
     * @param array $kArtikel_arr
     * @param int   $start
     * @param int   $maxAnzahl
     * @return Artikel[]
     */
    public function getArtikelByKeys(array $kArtikel_arr, int $start, int $maxAnzahl): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cnt            = \count($kArtikel_arr);
        $anz            = 0;
        $defaultOptions = Artikel::getDefaultOptions();
        for ($i = $start; $i < $cnt; $i++) {
            $artikel = new Artikel();
            $artikel->fuelleArtikel($kArtikel_arr[$i], $defaultOptions);
            if (!empty($artikel->kArtikel) && $artikel->kArtikel > 0) {
                ++$anz;
                $this->elemente[] = $artikel;
            }
            if ($anz >= $maxAnzahl) {
                break;
            }
        }

        return $this->elemente;
    }

    /**
     * @param KategorieListe $katListe
     * @return Artikel[]
     */
    public function holeTopArtikel($katListe): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        if (!empty($katListe->elemente)) {
            foreach ($katListe->elemente as $i => $kategorie) {
                $categoryIDs[] = (int)$kategorie->kKategorie;
                if (isset($kategorie->Unterkategorien) && \is_array($kategorie->Unterkategorien)) {
                    foreach ($kategorie->Unterkategorien as $kategorie_lvl2) {
                        $categoryIDs[] = (int)$kategorie_lvl2->kKategorie;
                    }
                }
            }
        }
        $cacheID = 'hTA_' . \md5(\json_encode($categoryIDs));
        $objArr  = Shop::Container()->getCache()->get($cacheID);
        if ($objArr === false && \count($categoryIDs) > 0) {
            $conf          = Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
            $cLimitSql     = isset($conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            $lagerfilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $objArr        = Shop::Container()->getDB()->query(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . ' ' .
                    Preise::getPriceJoinSql($kKundengruppe) . " 
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.cTopArtikel = 'Y'
                        AND (tkategorieartikel.kKategorie IN (" . \implode(', ', $categoryIDs) . ')) ' .
                        $lagerfilter . '  ORDER BY rand() ' . $cLimitSql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $cacheTags     = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $category) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $category;
            }
            Shop::Container()->getCache()->set($cacheID, $objArr, $cacheTags);
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($objArr as $obj) {
            $artikel = new Artikel();
            $artikel->fuelleArtikel((int)$obj->kArtikel, $defaultOptions);
            $this->elemente[] = $artikel;
        }

        return $this->elemente;
    }

    /**
     * @param Kategorieliste    $katListe
     * @param ArtikelListe|null $topArtikelliste
     * @return Artikel[]
     */
    public function holeBestsellerArtikel($katListe, $topArtikelliste = null): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        if (isset($katListe->elemente) && \is_array($katListe->elemente)) {
            foreach ($katListe->elemente as $i => $kategorie) {
                $categoryIDs[] = (int)$kategorie->kKategorie;
                if (isset($kategorie->Unterkategorien) && \is_array($kategorie->Unterkategorien)) {
                    foreach ($kategorie->Unterkategorien as $kategorie_lvl2) {
                        $categoryIDs[] = (int)$kategorie_lvl2->kKategorie;
                    }
                }
            }
        }
        $keys = null;
        if ($topArtikelliste instanceof self) {
            $keys = map($topArtikelliste->elemente, function ($e) {
                return $e->cacheID ?? 0;
            });
        }
        $cacheID = 'hBsA_' . \md5(\json_encode($categoryIDs) . \json_encode($keys));
        $objArr  = Shop::Container()->getCache()->get($cacheID);
        if ($objArr === false && \count($categoryIDs) > 0) {
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
            // top artikel nicht nochmal in den bestsellen vorkommen lassen
            $excludes = '';
            if (isset($topArtikelliste->elemente) && \is_array($topArtikelliste->elemente)) {
                $exclude            = map($topArtikelliste->elemente, function ($e) {
                    return (int)$e->kArtikel;
                });
                $excludes = \count($exclude) > 0
                    ? ' AND tartikel.kArtikel NOT IN (' . \implode(',', $exclude) . ') '
                    : '';
            }
            $conf        = Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
            $cLimitSql   = isset($conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            $lagerfilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $objArr      = Shop::Container()->getDB()->query(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . ' ' .
                    Preise::getPriceJoinSql($kKundengruppe) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL' . $excludes . '
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kArtikel = tbestseller.kArtikel
                        AND (tkategorieartikel.kKategorie IN (' . \implode(', ', $categoryIDs) . ')) ' .
                        $lagerfilter . '
                    ORDER BY tbestseller.fAnzahl DESC ' . $cLimitSql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $cacheTags   = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $category) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $category;
            }
            Shop::Container()->getCache()->set($cacheID, $objArr, $cacheTags);
        }
        if (\is_array($objArr)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($objArr as $obj) {
                $this->elemente[] = (new Artikel())->fuelleArtikel((int)$obj->kArtikel, $defaultOptions);
            }
        }

        return $this->elemente;
    }
}
