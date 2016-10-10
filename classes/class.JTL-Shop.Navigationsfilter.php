<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Navigationsfilter
 */
class Navigationsfilter
{
    /**
     * @var array
     */
    public $oSprache_arr;

    /**
     * @var FilterKategorie
     */
    public $Kategorie;

    /**
     * @var FilterKategorieFilter
     */
    public $KategorieFilter;

    /**
     * @var FilterHersteller
     */
    public $Hersteller;

    /**
     * @var FilterHerstellerFilter
     */
    public $HerstellerFilter;

    /**
     * @var FilterMerkmal
     */
    public $MerkmalWert;

    /**
     * @var FilterSearch
     */
    public $Suchanfrage;

    /**
     * @var FilterSearch[]
     */
    public $SuchFilter = [];

    /**
     * @var FilterTagFilter[]
     */
    public $TagFilter = [];

    /**
     * @var FilterMerkmalFilter[]
     */
    public $MerkmalFilter = [];

    /**
     * @var FilterSearchSpecial[]
     */
    public $SuchspecialFilter = [];

    /**
     * @var FilterRating
     */
    public $BewertungFilter;

    /**
     * @var FilterPriceRange
     */
    public $PreisspannenFilter;

    /**
     * @var FilterTag
     */
    public $Tag;

    /**
     * @var FilterNews
     */
    public $News;

    /**
     * @var FilterNewsOverview
     */
    public $NewsMonat;

    /**
     * @var FilterNewsCategory
     */
    public $NewsKategorie;

    /**
     * @var FilterSearchSpecial
     */
    public $Suchspecial;

    /**
     * @var object
     */
    public $Suche;

    /**
     * @var object
     */
    public $EchteSuche;

    /**
     * @var int
     */
    public $nAnzahlProSeite = 0;

    /**
     * @var int
     */
    public $nAnzahlFilter = 0;

    /**
     * @var int
     */
    public $nSeite = 0;

    /**
     * @var int
     */
    public $nSortierung = 0;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->oSprache_arr = Shop::Lang()->getLangArray();
        $this->initBaseStates();
    }

    /**
     * @return array
     */
    public function getActiveFilters()
    {
        $filters = [];
        if ($this->HerstellerFilter->isInitialized()) {
            $filters[] = $this->HerstellerFilter;
        }
        foreach ($this->MerkmalFilter as $filter) {
            if ($filter->isInitialized()) {
                $filters[] = $filter;
            }
        }
        foreach ($this->SuchspecialFilter as $filter) {
            if ($filter->isInitialized()) {
                $filters[] = $filter;
            }
        }
        foreach ($this->TagFilter as $filter) {
            if ($filter->isInitialized()) {
                $filters[] = $filter;
            }
        }
        foreach ($this->SuchFilter as $filter) {
            if ($filter->isInitialized()) {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    /**
     * @return FilterHersteller|FilterKategorie|FilterMerkmal|FilterSearch|null
     */
    public function getActiveState()
    {
        if ($this->Hersteller->isInitialized()) {
            return $this->Hersteller;
        }
        if ($this->Kategorie->isInitialized()) {
            return $this->Kategorie;
        }
        if ($this->MerkmalWert->isInitialized()) {
            return $this->MerkmalWert;
        }
        if ($this->Suchanfrage->isInitialized()) {
            return $this->Suchanfrage;
        }

        return null;
    }

    /**
     * @return $this
     */
    private function initBaseStates()
    {
        $this->Kategorie       = new FilterKategorie();
        $this->KategorieFilter = new FilterKategorieFilter();

        $this->HerstellerFilter = new FilterHersteller();
        $this->Hersteller       = new FilterHersteller();

        $this->Suchanfrage = new FilterSearch();

        $this->MerkmalWert = new FilterMerkmal();

        $this->Tag = new FilterTag();

        $this->News = new FilterNews();

        $this->NewsMonat = new FilterNewsOverview();

        $this->NewsKategorie = new FilterNewsCategory();

        $this->Suchspecial = new FilterSearchSpecial();

        $this->MerkmalFilter     = [];
        $this->SuchFilter        = [];
        $this->TagFilter         = [];
        $this->SuchspecialFilter = [];

        $this->BewertungFilter = new FilterRating();

        $this->PreisspannenFilter = new FilterPriceRange();

        $this->Suche = new stdClass();
        $this->Suche->cSuche = '';
        $this->Suche->kSuchanfrage = 0;


        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates($params)
    {
        $count = 0;
        if ($params['kKategorie'] > 0) {
            $this->Kategorie = (new FilterKategorie())->init($params['kKategorie'], $this->oSprache_arr);
        }
        if ($params['kKategorieFilter'] > 0) {
            $this->KategorieFilter = (new FilterKategorieFilter())->init($params['kKategorieFilter'], $this->oSprache_arr);
            ++$count;
        }
        if ($params['kHersteller'] > 0) {
            $this->Hersteller = (new FilterHersteller())->init($params['kHersteller'], $this->oSprache_arr);
        }
        if ($params['kHerstellerFilter'] > 0) {
            $this->HerstellerFilter = (new FilterHerstellerFilter())->init($params['kHerstellerFilter'], $this->oSprache_arr);
            ++$count;
        }
        if ($params['kSuchanfrage'] > 0) {
            $this->Suchanfrage = (new FilterSearch())->init($params['kSuchanfrage'], $this->oSprache_arr);
            $oSuchanfrage      = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->Suche->kSuchanfrage = $params['kSuchanfrage'];
                $this->Suche->cSuche       = $oSuchanfrage->cSuche;
            }
        }
        if ($params['kMerkmalWert'] > 0) {
            $this->MerkmalWert = (new FilterMerkmal())->init($params['kMerkmalWert'], $this->oSprache_arr);
        }
        if (count($params['MerkmalFilter_arr']) > 0) {
            foreach ($params['MerkmalFilter_arr'] as $mmf) {
                $this->MerkmalFilter[] = (new FilterMerkmalFilter())->init($mmf, $this->oSprache_arr);
            }
            ++$count;
        }
        if ($params['kTag'] > 0) {
            $this->Tag = (new FilterTag())->init($params['kTag'], $this->oSprache_arr);
        }
        if (count($params['TagFilter_arr']) > 0) {
            foreach ($params['TagFilter_arr'] as $tf) {
                $this->TagFilter[] = (new FilterTagFilter())->init($tf, $this->oSprache_arr);
            }
            ++$count;
        }
        if ($params['kNews'] > 0) {
            $this->News = (new FilterNews())->init($params['kNews'], $this->oSprache_arr);
        }
        if ($params['kNewsMonatsUebersicht'] > 0) {
            $this->NewsMonat = (new FilterNewsOverview())->init($params['kNewsMonatsUebersicht'], $this->oSprache_arr);
        }
        if ($params['kNewsKategorie'] > 0) {
            $this->NewsKategorie = (new FilterNewsCategory())->init($params['kNewsKategorie'], $this->oSprache_arr);
        }
        if ($params['kSuchspecial'] > 0) {
            $this->Suchspecial = (new FilterSearchSpecial())->init($params['kSuchspecial'], $this->oSprache_arr);
        }
        if ($params['kSuchspecialFilter'] > 0) {
            $this->SuchspecialFilter = (new FilterSearchSpecial())->init($params['kSuchspecialFilter'], $this->oSprache_arr);
            ++$count;
        }

        if (count($params['SuchFilter_arr']) > 0) {
            //@todo - same as suchfilter?
            foreach ($params['SuchFilter_arr'] as $sf) {
                $this->SuchFilter[] = (new FilterSearch())->init($sf, $this->oSprache_arr);
            }
            ++$count;
        }

        if ($params['nBewertungSterneFilter'] > 0) {
            $this->BewertungFilter = (new FilterRating())->init($params['nBewertungSterneFilter'], []);
        }
        if (strlen($params['cPreisspannenFilter']) > 0) {
            $this->PreisspannenFilter = (new FilterPriceRange())->init($params['cPreisspannenFilter'], []);
            ++$count;
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] > 0) {
            $this->nAnzahlProSeite = (int)$params['nArtikelProSeite'];
        }
        if (strlen($params['cSuche']) > 0) {
            $params['cSuche']         = StringHandler::filterXSS($params['cSuche']);
            $this->Suche              = new stdClass();
            $this->Suche->cSuche      = $params['cSuche'];
            $this->EchteSuche         = new stdClass();
            $this->EchteSuche->cSuche = $params['cSuche'];
        }
        if (!empty($this->Suche->cSuche)) {
            //@todo?
            $this->Suche->kSuchCache = bearbeiteSuchCache($this);
        }
        $this->nSeite = verifyGPCDataInteger('seite');
        if (!$this->nSeite) {
            $this->nSeite = 1;
        }
        $this->nAnzahlFilter = $count;

        return $this->validate();
    }

    /**
     * @return bool
     */
    public function hasManufacturerFilter()
    {
        return $this->HerstellerFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasManufacturer()
    {
        return $this->Hersteller->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasCategory()
    {
        return $this->Kategorie->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasCategoryFilter()
    {
        return $this->KategorieFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSearchFilter()
    {
        return $this->Suche->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasTag()
    {
        return $this->Tag->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasTagFilter()
    {
        return count($this->TagFilter) > 0;
    }

    /**
     * @return bool
     */
    public function hasSuchanfrage()
    {
        return $this->Suchanfrage->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNews()
    {
        return $this->News->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNewsOverview()
    {
        return $this->NewsMonat->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNewsCategory()
    {
        return $this->NewsKategorie->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasMerkmalWert()
    {
        return $this->MerkmalWert->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSearchSpecial()
    {
        return $this->Suchspecial->isInitialized();
    }


    /**
     * @return $this
     */
    public function validate()
    {
        if ($this->nAnzahlFilter > 0) {
            if (!$this->hasManufacturer() && !$this->hasCategory() &&
                !$this->hasTag() && !$this->hasSuchanfrage() && !$this->hasNews() &&
                !$this->hasNewsOverview() && !$this->hasNewsCategory() &&
                !isset($this->Suche->cSuche) && !$this->hasMerkmalWert() && !$this->hasSearchSpecial()) {
                //we have a manufacturer filter that doesn't filter anything
                if ($this->HerstellerFilter->getSeo(Shop::getLanguage()) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->HerstellerFilter->getSeo(Shop::getLanguage()));
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if ($this->KategorieFilter->getSeo(Shop::getLanguage()) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->KategorieFilter->getSeo(Shop::getLanguage()));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() && $this->Hersteller->getSeo(Shop::getLanguage()) !== null) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Hersteller->getSeo(Shop::getLanguage()));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() && $this->Kategorie->getSeo(Shop::getLanguage()) !== null) {
                //we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Kategorie->getSeo(Shop::getLanguage()));
                exit();
            }
        }

        return $this;
    }

    private function getOrder()
    {
        $conf              = Shop::getSettings(array(CONF_ARTIKELUEBERSICHT));
        $Artikelsortierung = $conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        $sort = new stdClass();
        $sort->join = '';
        if (isset($_SESSION['Usersortierung'])) {
            $Artikelsortierung          = mappeUsersortierung($_SESSION['Usersortierung']);
            $_SESSION['Usersortierung'] = $Artikelsortierung;
        }
        if (isset($this->nSortierung) && $this->nSortierung > 0 && (int)$_SESSION['Usersortierung'] === 100) {
            $Artikelsortierung = $this->nSortierung;
        }
        $sort->orderBy = 'tartikel.nSort, tartikel.cName';
        switch (intval($Artikelsortierung)) {
            case SEARCH_SORT_STANDARD:
                if ($this->Kategorie->kKategorie > 0) {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                } elseif ($this->Suche->kSuchCache > 0 && isset($_SESSION['Usersortierung']) && (int)$_SESSION['Usersortierung'] === 100) {
                    $sort->orderBy = 'tsuchcachetreffer.nSort';
                } else {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                }
                break;
            case SEARCH_SORT_NAME_ASC:
                $sort->orderBy = 'tartikel.cName';
                break;
            case SEARCH_SORT_NAME_DESC:
                $sort->orderBy = 'tartikel.cName DESC';
                break;
            case SEARCH_SORT_PRICE_ASC:
                $sort->orderBy = 'tpreise.fVKNetto, tartikel.cName';
                $sort->coin = 'JOIN tpreise ON tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe;
                break;
            case SEARCH_SORT_PRICE_DESC:
                $sort->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
                $sort->coin = 'JOIN tpreise ON tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe;
                break;
            case SEARCH_SORT_EAN:
                $sort->orderBy = 'tartikel.cBarcode, tartikel.cName';
                break;
            case SEARCH_SORT_NEWEST_FIRST:
                $sort->orderBy = 'tartikel.dErstellt DESC, tartikel.cName';
                break;
            case SEARCH_SORT_PRODUCTNO:
                $sort->orderBy = 'tartikel.cArtNr, tartikel.cName';
                break;
            case SEARCH_SORT_AVAILABILITY:
                $sort->orderBy = 'tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName';
                break;
            case SEARCH_SORT_WEIGHT:
                $sort->orderBy = 'tartikel.fGewicht, tartikel.cName';
                break;
            case SEARCH_SORT_DATEOFISSUE:
                $sort->orderBy = 'tartikel.dErscheinungsdatum DESC, tartikel.cName';
                break;
            case SEARCH_SORT_BESTSELLER:
                $sort->orderBy = 'tbestseller.fAnzahl DESC, tartikel.cName';
                $sort->join = 'LEFT JOIN tbestseller ON tartikel.kArtikel = tbestseller.kArtikel';
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
                $sort->join = 'LEFT JOIN tbewertung ON tbewertung.kArtikel = tartikel.kArtikel';
                break;
            default:
                break;
        }

        return $sort;
    }

    public function getProducts()
    {
        // Artikelanzahl pro Seite
        $nArtikelProSeite = 20;
        $conf             = Shop::getSettings(array(CONF_ARTIKELUEBERSICHT));
        if (intval($conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) > 0) {
            $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'];
        }
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            $nArtikelProSeite = (int)$_SESSION['ArtikelProSeite'];
        }
        if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
            $nArtikelProSeite = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        }
        // $nArtikelProSeite auf max. ARTICLES_PER_PAGE_HARD_LIMIT beschränken
        $nArtikelProSeite = min($nArtikelProSeite, ARTICLES_PER_PAGE_HARD_LIMIT);
        $nLimitN          = ($this->nSeite - 1) * $nArtikelProSeite;

        //@todo
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;

        $oSuchergebnisse                    = new stdClass();
        $oSuchergebnisse->Artikel           = new ArtikelListe();
        $oSuchergebnisse->MerkmalFilter     = [];
        $oSuchergebnisse->Herstellerauswahl = [];
        $oSuchergebnisse->Tags              = [];
        $oSuchergebnisse->Bewertung         = [];
        $oSuchergebnisse->Preisspanne       = [];
        $oSuchergebnisse->Suchspecial       = [];
        $oSuchergebnisse->SuchFilter        = [];

//        baueArtikelAnzahl($FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
//        $oSuchergebnisse->Artikel->elemente = gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, false, $oSuchergebnisse);



        // 50 nach links und 50 nach rechts für Artikeldetails blättern rausholen
        $nLimitNBlaetter = $nLimitN;
        if ($nLimitNBlaetter >= 50) {
            $nLimitNBlaetter -= 50;
        } elseif ($nLimitNBlaetter < 50) {
            $nLimitNBlaetter = 0;
        }
        // Immer 100 Artikel rausholen, damit wir in den Artikeldetails auch vernünftig blättern können
        $nArtikelProSeiteBlaetter = max(100, $nArtikelProSeite + 50);
        $cLimitSQL = " LIMIT " . $nLimitNBlaetter . ", " . $nArtikelProSeiteBlaetter;


        $order = $this->getOrder();
//        if ($bExtern) {
//            $cLimitSQL = " LIMIT " . $nArtikelProSeite;
//        }

        $joins = "\n#current state join \n" . $this->getActiveState()->getSQLJoin() .
            "\n#current order join \n" . $order->join;
        $conditions = "\n#condition for current state \n" . ' AND ' . $this->getActiveState()->getSQLCondition();

        foreach ($this->getActiveFilters() as $filter) {
            $name = get_class($filter);
            Shop::dbg($filter, false, 'active filter:');
            Shop::dbg($filter->getSQLJoin(), false, 'join:');
            $joins .= "\n#join from filter " . $name . "\n" . $filter->getSQLJoin() . ' ';
            $conditions .= "\n#condition from filter " . $name . "\n" . ' AND ' . $filter->getSQLCondition() . ' ';
        }

        $query = "SELECT tartikel.kArtikel
            FROM tartikel " . $joins . "
            #default group visibility
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            #default conditions
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                #stock filter
                " . gibLagerfilter() . $conditions . "
            #default group by
            GROUP BY tartikel.kArtikel
            #order by
            ORDER BY " . $order->orderBy . " 
            #limit sql
            " . $cLimitSQL;

        Shop::dbg($query, false, 'new query:');
        return $query;

        $oArtikelKey_arr = Shop::DB()->query(
            $query, 2,1
        );


        return $oSuchergebnisse;
    }
}
