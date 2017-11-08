<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ArtikelListe
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
    public function getTopNeuArtikel($topneu, $anzahl = 3, $kKundengruppe = 0, $kSprache = 0)
    {
        $this->elemente = [];
        if (!Session::CustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $kKundengruppe = (int)$kKundengruppe;
        $kSprache      = (int)$kSprache;
        $anzahl        = (int)$anzahl;
        $cacheID       = 'jtl_tpnw_' . (is_string($topneu) ? $topneu : '') .
            '_' . $anzahl .
            '_' . $kSprache .
            '_' . $kKundengruppe;
        $objArr        = Shop::Cache()->get($cacheID);
        if ($objArr === false) {
            $qry = ($topneu === 'neu')
                ? "cNeu = 'Y'"
                : "tartikel.cTopArtikel = 'Y'";
            if (!$kKundengruppe) {
                $kKundengruppe = Session::CustomerGroup()->getID();
            }
            $objArr = Shop::DB()->query(
                "SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND $qry
                    ORDER BY rand() LIMIT " . $anzahl, 2
            );
            Shop::Cache()->set($cacheID, $objArr, [CACHING_GROUP_CATEGORY]);
        }
        if (is_array($objArr)) {
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
    public function getArtikelFromKategorie($kKategorie, $limitStart, $limitAnzahl, $order, $kKundengruppe = 0, $kSprache = 0)
    {
        $this->elemente = [];
        if (!$kKategorie || !Session::CustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = Session::CustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $kKategorie    = (int)$kKategorie;
        $kKundengruppe = (int)$kKundengruppe;
        $kSprache      = (int)$kSprache;
        $limitAnzahl   = (int)$limitAnzahl;
        $limitStart    = (int)$limitStart;
        $cacheID       = 'jtl_top_' . md5($kKategorie . $limitStart . $limitAnzahl . $kKundengruppe . $kSprache);
        if (($res = Shop::Cache()->get($cacheID)) !== false) {
            $this->elemente = $res;
        } else {
            $hstSQL = '';
            if (Shop::getProductFilter() !== null && Shop::getProductFilter()->hasManufacturer()) {
                $hstSQL = ' AND tartikel.kHersteller = ' . Shop::getProductFilter()->getManufacturer()->getValue() . ' ';
            }
            $lagerfilter = gibLagerfilter();
            $objArr      = Shop::DB()->query(
                "SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                    " . Preise::getPriceJoinSql($kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        $hstSQL
                        AND tkategorieartikel.kKategorie = $kKategorie
                        $lagerfilter
                    ORDER BY $order, nSort
                    LIMIT $limitStart, $limitAnzahl
                    ", 2
            );
            if (is_array($objArr)) {
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($objArr as $obj) {
                    $artikel = new Artikel();
                    $artikel->fuelleArtikel($obj->kArtikel, $defaultOptions);
                    $this->elemente[] = $artikel;
                }
                Shop::Cache()->set(
                    $cacheID,
                    $this->elemente,
                    [CACHING_GROUP_CATEGORY, CACHING_GROUP_CATEGORY . '_' . $kKategorie]
                );
            }
        }

        return $this->elemente;
    }

    /**
     * @param array $kArtikel_arr
     * @param int   $start
     * @param int   $maxAnzahl
     * @return Artikel[]
     */
    public function getArtikelByKeys($kArtikel_arr, $start, $maxAnzahl)
    {
        $this->elemente = [];
        if (!Session::CustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cnt            = count($kArtikel_arr);
        $anz            = 0;
        $defaultOptions = Artikel::getDefaultOptions();
        for ($i = (int)$start; $i < $cnt; $i++) {
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
    public function holeTopArtikel($katListe)
    {
        $arr_kKategorie = [];
        if (!empty($katListe->elemente)) {
            foreach ($katListe->elemente as $i => $kategorie) {
                $arr_kKategorie[] = (int)$kategorie->kKategorie;
                if (isset($kategorie->Unterkategorien) && is_array($kategorie->Unterkategorien)) {
                    foreach ($kategorie->Unterkategorien as $kategorie_lvl2) {
                        $arr_kKategorie[] = (int)$kategorie_lvl2->kKategorie;
                    }
                }
            }
        }
        $cacheID = 'hTA_' . md5(json_encode($arr_kKategorie));
        $objArr  = Shop::Cache()->get($cacheID);
        if ($objArr === false && count($arr_kKategorie) > 0) {
            if (!Session::CustomerGroup()->mayViewCategories()) {
                return $this->elemente;
            }
            $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
            $kKundengruppe = Session::CustomerGroup()->getID();
            $cLimitSql     = isset($Einstellungen['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$Einstellungen['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            //top-Artikel
            $lagerfilter = gibLagerfilter();
            $objArr      = Shop::DB()->query("
                SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                    " . Preise::getPriceJoinSql($kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.cTopArtikel = 'Y'
                        AND (tkategorieartikel.kKategorie IN (" . implode(', ', $arr_kKategorie) . "))
                        $lagerfilter
                    ORDER BY rand()
                    {$cLimitSql}
                    ", 2
            );
            $cacheTags = [CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION];
            foreach ($arr_kKategorie as $category) {
                $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . $category;
            }
            Shop::Cache()->set($cacheID, $objArr, $cacheTags);
        }
        if (is_array($objArr)) {
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
     * @param Kategorieliste    $katListe
     * @param ArtikelListe|null $topArtikelliste
     * @return Artikel[]
     */
    public function holeBestsellerArtikel($katListe, $topArtikelliste = null)
    {
        $arr_kKategorie = [];
        if (isset($katListe->elemente) && is_array($katListe->elemente)) {
            foreach ($katListe->elemente as $i => $kategorie) {
                $arr_kKategorie[] = (int)$kategorie->kKategorie;
                if (isset($kategorie->Unterkategorien) && is_array($kategorie->Unterkategorien)) {
                    foreach ($kategorie->Unterkategorien as $kategorie_lvl2) {
                        $arr_kKategorie[] = (int)$kategorie_lvl2->kKategorie;
                    }
                }
            }
        }
        $cacheID = 'hBsA_' . md5(json_encode($arr_kKategorie) . json_encode($topArtikelliste));
        $objArr  = Shop::Cache()->get($cacheID);
        if ($objArr === false && count($arr_kKategorie) > 0) {
            if (!Session::CustomerGroup()->mayViewCategories()) {
                return $this->elemente;
            }
            $kKundengruppe = Session::CustomerGroup()->getID();
            //top artikel nicht nochmal in den bestsellen vorkommen lassen
            $sql_artikelExclude = '';
            if (isset($topArtikelliste->elemente) && is_array($topArtikelliste->elemente)) {
                foreach ($topArtikelliste->elemente as $ele) {
                    if ($ele->kArtikel > 0) {
                        $sql_artikelExclude .= ' AND tartikel.kArtikel != ' . (int)$ele->kArtikel;
                    }
                }
            }
            $conf        = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
            $cLimitSql   = isset($conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            $lagerfilter = gibLagerfilter();
            $objArr      = Shop::DB()->query("
                SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                    " . Preise::getPriceJoinSql($kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        " . $sql_artikelExclude . "
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kArtikel = tbestseller.kArtikel
                        AND (tkategorieartikel.kKategorie IN (" . implode(', ', $arr_kKategorie) . "))
                        $lagerfilter
                    ORDER BY tbestseller.fAnzahl DESC
                    {$cLimitSql}
                    ", 2
            );
            $cacheTags = [CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION];
            foreach ($arr_kKategorie as $category) {
                $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . $category;
            }
            Shop::Cache()->set($cacheID, $objArr, $cacheTags);
        }
        if (is_array($objArr)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($objArr as $obj) {
                $this->elemente[] = (new Artikel())->fuelleArtikel($obj->kArtikel, $defaultOptions);
            }
        }

        return $this->elemente;
    }
}
