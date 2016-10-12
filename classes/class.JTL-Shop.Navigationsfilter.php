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
     * @var string
     */
    public $cBrotNaviName = '';

    /**
     * @var array
     */
    private $articleKeys = [];
    /**
     * @var array
     */
    private $conf;
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
     * @var FilterSearch
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
        $this->conf = Shop::getSettings([CONF_ARTIKELUEBERSICHT, CONF_NAVIGATIONSFILTER, CONF_BOXEN, CONF_GLOBAL]);
        $this->initBaseStates();
    }

    /**
     * @param bool $byType
     * @return array
     */
    public function getActiveFilters($byType = false)
    {
        if ($byType) {
            $filters = ['mm' => [], 'ssf' => [], 'tf' => [], 'sf' => [], 'hf' => []];
        } else {
            $filters = [];
        }
        if ($this->HerstellerFilter->isInitialized()) {
            if ($byType) {
                $filters['hf'][] = $this->HerstellerFilter;
            } else {
                $filters[] = $this->HerstellerFilter;
            }
        }
        if ($this->PreisspannenFilter->isInitialized()) {
            if ($byType) {
                $filters['pf'][] = $this->PreisspannenFilter;
            } else {
                $filters[] = $this->PreisspannenFilter;
            }
        }
        foreach ($this->MerkmalFilter as $filter) {
            if ($filter->isInitialized()) {
                if ($byType) {
                    $filters['mm'][] = $filter;
                } else {
                    $filters[] = $filter;
                }
            }
        }
        foreach ($this->SuchspecialFilter as $filter) {
            if ($filter->isInitialized()) {
                if ($byType) {
                    $filters['ssf'][] = $filter;
                } else {
                    $filters[] = $filter;
                }
            }
        }
        foreach ($this->TagFilter as $filter) {
            if ($filter->isInitialized()) {
                if ($byType) {
                    $filters['tf'][] = $filter;
                } else {
                    $filters[] = $filter;
                }
            }
        }
        foreach ($this->SuchFilter as $filter) {
            if ($filter->isInitialized()) {
                if ($byType) {
                    $filters['sf'][] = $filter;
                } else {
                    $filters[] = $filter;
                }
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

        $this->Suchanfrage = new FilterSearchQuery();

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

        $this->Suche = new FilterSearch();


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
            $this->Suchanfrage = (new FilterSearchQuery())->init($params['kSuchanfrage'], $this->oSprache_arr);
            $oSuchanfrage      = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->Suche = (new FilterSearch())->init($params['kSuchanfrage'], $this->oSprache_arr);
                $this->Suche->cSuche = $oSuchanfrage->cSuche;
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

            $this->Suche = (new FilterSearch())->init($params['kSuchanfrage'], $this->oSprache_arr);
            $this->Suche->cSuche = $params['cSuche'];
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

    /**
     * @return stdClass
     */
    private function getOrder()
    {
        $Artikelsortierung = $this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
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

    /**
     * @return int
     */
    private function getArticlesPerPageLimit()
    {
        if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
            return (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        }
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            return (int)$_SESSION['ArtikelProSeite'];
        }

        return ($this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0)
            ? (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']
            : 20;
    }


    /**
     * @return stdClass|string
     */
    public function getProductKeys()
    {

        // $nArtikelProSeite auf max. ARTICLES_PER_PAGE_HARD_LIMIT beschränken
        $nArtikelProSeite = min($this->getArticlesPerPageLimit(), ARTICLES_PER_PAGE_HARD_LIMIT);
        $nLimitN          = ($this->nSeite - 1) * $nArtikelProSeite;

        $oSuchergebnisse                    = new stdClass();
        $oSuchergebnisse->Artikel           = new ArtikelListe();
        $oSuchergebnisse->MerkmalFilter     = [];
        $oSuchergebnisse->Herstellerauswahl = [];
        $oSuchergebnisse->Tags              = [];
        $oSuchergebnisse->Bewertung         = [];
        $oSuchergebnisse->Preisspanne       = [];
        $oSuchergebnisse->Suchspecial       = [];
        $oSuchergebnisse->SuchFilter        = [];

        $order = $this->getOrder();

        $state = $this->getCurrentStateData();
        $state->joins[] = "\n#current order join \n" . $order->join;

        $query = $this->getBaseQuery(['tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);

        $oArtikelKey_arr = Shop::DB()->query(
            $query, 2
        );

        return $oArtikelKey_arr;
    }

    /**
     * @return stdClass
     */
    public function getProducts()
    {
        $oSuchergebnisse                         = new stdClass();
        $oSuchergebnisse->Artikel                = new stdClass();
        $oSuchergebnisse->Artikel->elemente      = [];
        $oArtikelOptionen                        = new stdClass();
        $oArtikelOptionen->nMerkmale             = 1;
        $oArtikelOptionen->nKategorie            = 1;
        $oArtikelOptionen->nAttribute            = 1;
        $oArtikelOptionen->nArtikelAttribute     = 1;
        $oArtikelOptionen->nVariationKombiKinder = 1;
        $oArtikelOptionen->nWarenlager           = 1;

        $nArtikelProSeite = $this->getArticlesPerPageLimit();
        $nLimitN          = ($this->nSeite - 1) * $nArtikelProSeite;
        // 50 nach links und 50 nach rechts für Artikeldetails blättern rausholen
        $nLimitNBlaetter = $nLimitN;
        if ($nLimitNBlaetter >= 50) {
            $nLimitNBlaetter -= 50;
        } elseif ($nLimitNBlaetter < 50) {
            $nLimitNBlaetter = 0;
        }

        $nArtikelProSeiteBlaetter = max(100, $nArtikelProSeite + 50);
        $offsetEnd                = $nArtikelProSeiteBlaetter - $nLimitNBlaetter;

        $keys = $this->getProductKeys();
        foreach (array_slice($keys, $nLimitNBlaetter, $offsetEnd) as $i => $oArtikelKey) {
            $nLaufLimitN = $i + $nLimitNBlaetter;
            if ($nLaufLimitN >= $nLimitN && $nLaufLimitN < $nLimitN + $nArtikelProSeite) {
                $oArtikel = new Artikel();
                //$oArtikelOptionen->nVariationDetailPreis = 1;
                $oArtikel->fuelleArtikel($oArtikelKey->kArtikel, $oArtikelOptionen);
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($oArtikel->nIstVater == 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                }
                $oSuchergebnisse->Artikel->elemente[] = $oArtikel;
            }
        }
        $oSuchergebnisse->GesamtanzahlArtikel = count($keys);

        if (!empty($this->Suche->cSuche)) {
            suchanfragenSpeichern($this->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel);
            $this->Suche->kSuchanfrage = gibSuchanfrageKey($this->Suche->cSuche, Shop::getLanguage());
        }

        $nLimitN = $nArtikelProSeite * ($this->nSeite - 1);

        $oSuchergebnisse->ArtikelVon = $nLimitN + 1;
        $oSuchergebnisse->ArtikelBis = min($nLimitN + $nArtikelProSeite, $oSuchergebnisse->GesamtanzahlArtikel);

        $oSuchergebnisse->Seitenzahlen                = new stdClass();
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $this->nSeite;
        $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $nArtikelProSeite);
        $oSuchergebnisse->Seitenzahlen->minSeite      = min(intval($oSuchergebnisse->Seitenzahlen->AktuelleSeite - (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'] / 2), 0);
        $oSuchergebnisse->Seitenzahlen->maxSeite      = max($oSuchergebnisse->Seitenzahlen->MaxSeiten,
            $oSuchergebnisse->Seitenzahlen->minSeite + (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'] - 1);
        if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
            $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
        }

        return $oSuchergebnisse;
    }

    /**
     * @return stdClass
     */
    private function getCurrentStateData()
    {
        $data = new stdClass();
        $data->having = [];
        $data->joins = [];
        $data->joins[] = "\n#current state join \n" . $this->getActiveState()->getSQLJoin();
        $data->conditions[] = "\n#condition for current state \n" . $this->getActiveState()->getSQLCondition();
        foreach ($this->getActiveFilters(true) as $type => $filter) {
            $count = count($filter);
            if ($count > 1) {
                $singleConditions = [];
                /** @var AbstractFilter $item */
                foreach ($filter as $idx => $item) {
                    if ($idx === 0) {
                        $data->joins[] = "\n#join from filter " . $type . "\n" . $item->getSQLJoin();
                        if ($item->getType() === AbstractFilter::FILTER_TYPE_AND) {
                            //filters that decrease the total amount of articles must have a "HAVING" clause
                            $data->having[] = 'HAVING COUNT(' . $item->getTableName() . '.' . $item->getPrimaryKeyRow() . ') = ' . $count;
                        }
                    }
                    $singleConditions[] = $item->getSQLCondition();
                }
                $data->conditions[] = $singleConditions;
            } elseif ($count === 1)  {
                $data->joins[] = "\n#join from filter " . $type . "\n" . $filter[0]->getSQLJoin();
                $data->conditions[] = "\n#condition from filter " . $type . "\n" . $filter[0]->getSQLCondition();
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getSearchSpecialFilterOptions()
    {
        $oSuchspecialFilterDB_arr = array();
        if ($this->conf['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y') {
            for ($i = 1; $i < 7; $i++) {
                $state = $this->getCurrentStateData();
                switch ($i) {
                    case SEARCHSPECIALS_BESTSELLER:
                        $nAnzahl = ($this->conf['global']['global_bestseller_minanzahl'] > 0)
                            ? (int)$this->conf['global']['global_bestseller_minanzahl']
                            : 100;
                        $state->joins[]  = 'JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel';
                        $state->conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;
                        break;
                    case SEARCHSPECIALS_SPECIALOFFERS:
                        if (!$this->PreisspannenFilter->isInitialized()) {
                            $state->joins[] = "JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                            JOIN tsonderpreise ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis";
                            $tsonderpreise = 'tsonderpreise';
                        } else {
                            $tsonderpreise = 'tsonderpreise';//'tspgspqf';
                        }
                        $state->conditions[] = "tartikelsonderpreis.cAktiv = 'Y' AND tartikelsonderpreis.dStart <= now()";
                        $state->conditions[] = "(tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')";
                        $state->conditions[] = $tsonderpreise . ".kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
                        break;
                    case SEARCHSPECIALS_NEWPRODUCTS:
                        $alter_tage = ($this->conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                            ? (int)$this->conf['boxen']['box_neuimsortiment_alter_tage']
                            : 30;
                        $state->conditions[] = "tartikel.cNeu='Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt";
                        break;
                    case SEARCHSPECIALS_TOPOFFERS:
                        $state->conditions[] = 'tartikel.cTopArtikel = "Y"';
                        break;
                    case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                        $state->conditions[] = 'now() < tartikel.dErscheinungsdatum';
                        break;
                    case SEARCHSPECIALS_TOPREVIEWS:
                        if (!$this->BewertungFilter->isInitialized()) {
                            $state->joins[] = "JOIN tartikelext ON tartikelext.kArtikel = tartikel.kArtikel";
                        }
                        $state->conditions[] = "ROUND(tartikelext.fDurchschnittsBewertung) >= " . (int)$this->conf['boxen']['boxen_topbewertet_minsterne'];
                        break;
                }
                $qry = $this->getBaseQuery(['tartikel.kArtikel'], $state->joins, $state->conditions, $state->having);
                $oSuchspecialFilterDB = Shop::DB()->query(
                    $qry, 2
                );
                $oSuchspecial          = new stdClass();
                $oSuchspecial->nAnzahl = count($oSuchspecialFilterDB);
                $oSuchspecial->kKey    = $i;

                $oZusatzFilter                          = new stdClass();
                $oZusatzFilter->SuchspecialFilter       = new stdClass();
                $oZusatzFilter->SuchspecialFilter->kKey = $i;
                $oSuchspecial->cURL                     = gibNaviURL($this, false, $oZusatzFilter);
                $oSuchspecialFilterDB_arr[$i]           = $oSuchspecial;
            }
        }

        return $oSuchspecialFilterDB_arr;
    }

    /**
     * @return array
     */
    public function getManufacturerFilterOptions()
    {
        $oHerstellerFilterDB_arr = array();
        if ($this->conf['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            //it's actually stupid to filter by manufacturer if we already got a manufacturer filter active...
//            if ($this->HerstellerFilter->isInitialized()) {
//                $filter              = new stdClass();
//                $filter->cSeo        = $this->HerstellerFilter->getSeo();
//                $filter->kHersteller = $this->HerstellerFilter->getID();
//                $filter->cName       = $this->HerstellerFilter->getName();
//
//                return $filter;
//            }
            $order = $this->getOrder();

            $state = $this->getCurrentStateData();
            $state->joins[] = "\n#current order join \n" . $order->join;
            $state->joins[] = "JOIN thersteller ON tartikel.kHersteller = thersteller.kHersteller";

            $query = $this->getBaseQuery(['thersteller.kHersteller', 'thersteller.cName', 'thersteller.nSortNr', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "
            SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM
                (" . $query . "
                ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . Shop::getLanguage() . "
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName";

            $oHerstellerFilterDB_arr = Shop::DB()->query($query, 2);
            //baue URL
            $oZusatzFilter = new stdClass();
            $count         = count($oHerstellerFilterDB_arr);
            for ($i = 0; $i < $count; ++$i) {
                $oHerstellerFilterDB_arr[$i]->kHersteller = (int)$oHerstellerFilterDB_arr[$i]->kHersteller;
                $oHerstellerFilterDB_arr[$i]->nAnzahl = (int)$oHerstellerFilterDB_arr[$i]->nAnzahl;
                $oHerstellerFilterDB_arr[$i]->nSortNr = (int)$oHerstellerFilterDB_arr[$i]->nSortNr;

                $oZusatzFilter->HerstellerFilter = new stdClass();
                $oZusatzFilter->HerstellerFilter->kHersteller = (int)$oHerstellerFilterDB_arr[$i]->kHersteller;
                $oZusatzFilter->HerstellerFilter->cSeo        = $oHerstellerFilterDB_arr[$i]->cSeo;

                $oHerstellerFilterDB_arr[$i]->cURL            = gibNaviURL($this, true, $oZusatzFilter);
            }
            unset($oZusatzFilter);
        }

        return $oHerstellerFilterDB_arr;
    }

    /**
     * @return array
     */
    public function getRatingFilterOptions()
    {
        $oBewertungFilter_arr = array();
        if ($this->conf['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
            $order = $this->getOrder();
            $state = $this->getCurrentStateData();
            $state->joins[] = "\n#current order join \n" . $order->join;
            $state->joins[] = "JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel";

            $query = $this->getBaseQuery(['ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "
            SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                FROM
                (" . $query . "
                ) AS ssMerkmal
                    GROUP BY ssMerkmal.nSterne
                    ORDER BY ssMerkmal.nSterne DESC";


            $oBewertungFilterDB_arr = Shop::DB()->query($query, 2);
            if (is_array($oBewertungFilterDB_arr) && count($oBewertungFilterDB_arr) > 0) {
                $nSummeSterne = 0;
                foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                    $nSummeSterne += $oBewertungFilterDB->nAnzahl;
                    $oBewertung          = new stdClass();
                    $oBewertung->nStern  = $oBewertungFilterDB->nSterne;
                    $oBewertung->nAnzahl = $nSummeSterne;
                    //baue URL
                    if (!isset($oZusatzFilter)) {
                        $oZusatzFilter                  = new stdClass();
                        $oZusatzFilter->BewertungFilter = new stdClass();
                    }
                    $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                    $oBewertung->cURL                        = gibNaviURL($this, true, $oZusatzFilter);
                    $oBewertungFilter_arr[]                  = $oBewertung;
                }
            }
        }

        return $oBewertungFilter_arr;
    }

    /**
     * @return array
     */
    public function getTagFilterOptions()
    {
        $oTagFilter_arr = array();
        if ($this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $order = $this->getOrder();
            $state = $this->getCurrentStateData();
            $state->joins[] = "\n#current order join \n" . $order->join;
            $state->joins[] = "JOIN ttagartikel ON ttagartikel.kArtikel = tartikel.kArtikel";
            $state->joins[] = "JOIN ttag ON ttagartikel.kTag = ttag.kTag";

            $state->conditions[] = "ttag.nAktiv = 1";
            $state->conditions[] = "ttag.kSprache = " . Shop::getLanguage();
            $query = $this->getBaseQuery(['ttag.kTag', 'ttag.cName', 'ttagartikel.nAnzahlTagging', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['ttag.kTag', 'tartikel.kArtikel']);

            $query = "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                FROM
                (" . $query . ") AS ssMerkmal
            LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                AND tseo.cKey = 'kTag'
                AND tseo.kSprache = " . Shop::getLanguage() . "
            GROUP BY ssMerkmal.kTag
            ORDER BY nAnzahl DESC LIMIT 0 , " . (int)$this->conf['navigationsfilter']['tagfilter_max_anzeige'];
            $oTagFilterDB_arr = Shop::DB()->query($query, 2);

            if (is_array($oTagFilterDB_arr)) {
                foreach ($oTagFilterDB_arr as $oTagFilterDB) {
                    $oTagFilter = new stdClass();
                    if (!isset($oZusatzFilter)) {
                        $oZusatzFilter = new stdClass();
                    }
                    if (!isset($oZusatzFilter->TagFilter)) {
                        $oZusatzFilter->TagFilter = new stdClass();
                    }
                    //baue URL
                    $oZusatzFilter->TagFilter->kTag = $oTagFilterDB->kTag;
                    $oTagFilter->cURL               = gibNaviURL($this, true, $oZusatzFilter);
                    $oTagFilter->kTag               = $oTagFilterDB->kTag;
                    $oTagFilter->cName              = $oTagFilterDB->cName;
                    $oTagFilter->nAnzahl            = $oTagFilterDB->nAnzahl;
                    $oTagFilter->nAnzahlTagging     = $oTagFilterDB->nAnzahlTagging;

                    $oTagFilter_arr[] = $oTagFilter;
                }
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oTagFilter_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oTagFilter_arr[0]->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / 9;
            }
            foreach ($oTagFilter_arr as $i => $oTagwolke) {
                if ($oTagwolke->kTag > 0) {
                    if ($nPrioStep < 1) {
                        $oTagFilter_arr[$i]->Klasse = rand(1, 10);
                    } else {
                        $oTagFilter_arr[$i]->Klasse = round(($oTagwolke->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / $nPrioStep) + 1;
                    }
                }
            }
        }

        return $oTagFilter_arr;
    }

    /**
     * @param array $oMerkmalauswahl_arr
     * @param int   $kMerkmal
     * @return int
     */
    private function getAttributePosition($oMerkmalauswahl_arr, $kMerkmal)
    {
        if (is_array($oMerkmalauswahl_arr)) {
            foreach ($oMerkmalauswahl_arr as $i => $oMerkmalauswahl) {
                if ($oMerkmalauswahl->kMerkmal == $kMerkmal) {
                    return $i;
                }
            }
        }

        return -1;
    }

    /**
     * @param null|Kategorie $oAktuelleKategorie
     * @param bool           $bForce
     * @return array
     */
    public function getAttributeFilterOptions($oAktuelleKategorie = null, $bForce = false)
    {
        $oMerkmalFilter_arr          = array();
        $cKatAttribMerkmalFilter_arr = array();
        if (isset($this->conf['navigationsfilter']['merkmalfilter_verwenden']) && $this->conf['navigationsfilter']['merkmalfilter_verwenden'] !== 'N' || $bForce) {
            // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
            if ($this->KategorieFilter->isInitialized()) {
                if (isset($oAktuelleKategorie->categoryFunctionAttributes) && is_array($oAktuelleKategorie->categoryFunctionAttributes) && count($oAktuelleKategorie->categoryFunctionAttributes) > 0) {
                    if (!empty($oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])) {
                        $cKatAttribMerkmalFilter_arr = explode(';', $oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]);
                    }
                }
            }

            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $state->joins[] = "\n#current order join \n" . $order->join;

            $select = 'tmerkmal.cName';
            if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                $select = "tmerkmalsprache.cName";
                $state->joins[]   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                    AND tmerkmalsprache.kSprache = " . Shop::getLanguage();
            }
            if (!$this->MerkmalWert->isInitialized() && count($this->MerkmalFilter) === 0) {
                $state->joins[] = "JOIN tartikelmerkmal ON tartikel.kArtikel = tartikelmerkmal.kArtikel";
            }
            $state->joins[] = "JOIN tmerkmalwert ON tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert";
                $state->joins[] = "JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                    AND tmerkmalwertsprache.kSprache = " . (int)Shop::$kSprache;
            $state->joins[] = "JOIN tmerkmal ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal";

            $query = $this->getBaseQuery(['tartikelmerkmal.kMerkmal', 'tartikelmerkmal.kMerkmalWert', 'tmerkmalwert.cBildPfad AS cMMWBildPfad',
                                          'tmerkmalwertsprache.cWert', 'tmerkmal.nSort AS nSortMerkmal', 'tmerkmalwert.nSort', 'tmerkmal.cTyp',
                                          'tmerkmal.cBildPfad AS cMMBildPfad', $select], $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']);

            $query =
                "SELECT tseo.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
                FROM
                ("
                    . $query .
            ") AS ssMerkmal
            LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kMerkmalWert
                AND tseo.cKey = 'kMerkmalWert'
                AND tseo.kSprache = " . (int)Shop::$kSprache . "
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";

            $oMerkmalFilterDB_arr = Shop::DB()->query($query, 2);

            if (is_array($oMerkmalFilterDB_arr) && count($oMerkmalFilterDB_arr) > 0) {
                foreach ($oMerkmalFilterDB_arr as $i => $oMerkmalFilterDB) {
                    $nPos          = $this->getAttributePosition($oMerkmalFilter_arr, $oMerkmalFilterDB->kMerkmal);
                    $oMerkmalWerte = new stdClass();
                    if ($this->MerkmalWert->getID() == $oMerkmalFilterDB->kMerkmalWert || (checkMerkmalWertVorhanden($this->MerkmalWert, $oMerkmalFilterDB->kMerkmalWert))) {
                        $oMerkmalWerte->nAktiv = 1;
                    } else {
                        $oMerkmalWerte->nAktiv = 0;
                    }
                    $oMerkmalWerte->kMerkmalWert = $oMerkmalFilterDB->kMerkmalWert;
                    $oMerkmalWerte->cWert        = $oMerkmalFilterDB->cWert;
                    $oMerkmalWerte->nAnzahl      = $oMerkmalFilterDB->nAnzahl;

                    if (strlen($oMerkmalFilterDB->cMMWBildPfad) > 0) {
                        $oMerkmalWerte->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalFilterDB->cMMWBildPfad;
                        $oMerkmalWerte->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalFilterDB->cMMWBildPfad;
                    } else {
                        $oMerkmalWerte->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                        $oMerkmalWerte->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    }
                    //baue URL
                    $oZusatzFilter = new stdClass();
                    $oZusatzFilter->MerkmalFilter = new stdClass();
                    $oZusatzFilter->MerkmalFilter->kMerkmalWert = $oMerkmalFilterDB->kMerkmalWert;
                    $oZusatzFilter->MerkmalFilter->cSeo         = $oMerkmalFilterDB->cSeo;
                    $oMerkmalWerte->cURL                        = gibNaviURL($this, true, $oZusatzFilter);

                    //hack for #4815
                    if ($oMerkmalWerte->nAktiv === 1 && isset($oZusatzFilter->MerkmalFilter->cSeo)) {
                        //remove '__attrY' from '<url>attrX__attrY'
                        $newURL = str_replace('__' . $oZusatzFilter->MerkmalFilter->cSeo, '', $oMerkmalWerte->cURL);
                        //remove 'attrY__' from '<url>attrY__attrX'
                        $newURL              = str_replace($oZusatzFilter->MerkmalFilter->cSeo . '__', '', $newURL);
                        $oMerkmalWerte->cURL = $newURL;
                    }
                    $oMerkmal           = new stdClass();
                    $oMerkmal->cName    = $oMerkmalFilterDB->cName;
                    $oMerkmal->cTyp     = $oMerkmalFilterDB->cTyp;
                    $oMerkmal->kMerkmal = $oMerkmalFilterDB->kMerkmal;
                    if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                        $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                        $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                    } else {
                        $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    }
                    $oMerkmal->oMerkmalWerte_arr = array();
                    if ($nPos >= 0) {
                        $oMerkmalFilter_arr[$nPos]->oMerkmalWerte_arr[] = $oMerkmalWerte;
                    } else {
                        //#533 Anzahl max Merkmale erreicht?
                        if (isset($this->conf['navigationsfilter']['merkmalfilter_maxmerkmale']) &&
                            $this->conf['navigationsfilter']['merkmalfilter_maxmerkmale'] > 0 &&
                            count($oMerkmalFilter_arr) >= $this->conf['navigationsfilter']['merkmalfilter_maxmerkmale']
                        ) {
                            continue;
                        }
                        $oMerkmal->oMerkmalWerte_arr[] = $oMerkmalWerte;
                        $oMerkmalFilter_arr[]          = $oMerkmal;
                    }
                }
            }
            //Filter durchgehen und die Merkmalwerte raustun, die zuviel sind und deren Anzahl am geringsten ist.
            foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
                //#534 Anzahl max Merkmalwerte erreicht?
                if (isset($this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) && $this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte'] > 0) {
                    while (count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr) > $this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) {
                        $nMinAnzahl = 999999;
                        $nIndex     = -1;
                        $count      = count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr);
                        for ($l = 0; $l < $count; $l++) {
                            if ($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl < $nMinAnzahl) {
                                $nMinAnzahl = $oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl;
                                $nIndex     = $l;
                            }
                        }
                        if ($nIndex >= 0) {
                            unset($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$nIndex]);
                            $oMerkmalFilter_arr[$o]->oMerkmalWerte_arr = array_merge($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr);
                        }
                    }
                }
            }
            // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen rauswerfen
            if (count($cKatAttribMerkmalFilter_arr) > 0) {
                $nKatFilter = count($oMerkmalFilter_arr);
                for ($i = 0; $i < $nKatFilter; $i++) {
                    if (!in_array($oMerkmalFilter_arr[$i]->cName, $cKatAttribMerkmalFilter_arr)) {
                        unset($oMerkmalFilter_arr[$i]);
                    }
                }
                $oMerkmalFilter_arr = array_merge($oMerkmalFilter_arr);
            }
            //Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
            foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
                $bAlleNumerisch = true;
                $count          = count($oMerkmalFilter->oMerkmalWerte_arr);
                for ($i = 0; $i < $count; $i++) {
                    if (!is_numeric($oMerkmalFilter->oMerkmalWerte_arr[$i]->cWert)) {
                        $bAlleNumerisch = false;
                        break;
                    }
                }
                if ($bAlleNumerisch) {
                    usort($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr, 'sortierMerkmalWerteNumerisch');
                }
            }
        }

        return $oMerkmalFilter_arr;
    }

    /**
     * @param $FilterSQL
     * @param $oSuchergebnisse
     * @return array
     */
    public function getPriceRangeFilterOptions($FilterSQL, $oSuchergebnisse)
    {
        $oPreisspanne_arr = array();

        // Prüfe ob es nur einen Artikel in der Artikelübersicht gibt, falls ja und es ist noch kein Preisspannenfilter gesetzt
        // dürfen keine Preisspannenfilter angezeigt werden
        if ($oSuchergebnisse->GesamtanzahlArtikel == 1 && !$this->PreisspannenFilter->isInitialized()) {
            return $oPreisspanne_arr;
        }
        if ($this->conf['navigationsfilter']['preisspannenfilter_benutzen'] !== 'N') {
            $cPreisspannenJOIN = "LEFT JOIN tartikelkategorierabatt ON tartikelkategorierabatt.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                                    AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                                LEFT JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                    AND tartikelsonderpreis.cAktiv='Y'
                                    AND tartikelsonderpreis.dStart <= now()
                                    AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                                LEFT JOIN tsonderpreise ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                    AND tsonderpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;

            // Automatisch
            if ($this->conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
                // Finde den höchsten und kleinsten Steuersatz
                if (is_array($_SESSION['Steuersatz']) && $_SESSION['Kundengruppe']->nNettoPreise === '0') {
                    $fSteuersatz_arr = array();
                    foreach ($_SESSION['Steuersatz'] as $fSteuersatz) {
                        $fSteuersatz_arr[] = $fSteuersatz;
                    }
                    $fSteuersatzMax = count($fSteuersatz_arr) ? max($fSteuersatz_arr) : 0;
                    $fSteuersatzMin = count($fSteuersatz_arr) ? min($fSteuersatz_arr) : 0;
                } elseif ($_SESSION['Kundengruppe']->nNettoPreise > 0) {
                    $fSteuersatzMax = 0.0;
                    $fSteuersatzMin = 0.0;
                }
                $fKundenrabatt = 0.0;
                if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
                    $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
                }
                $oPreisspannenFilterMaxMin = Shop::DB()->query(
                    "SELECT max(ssMerkmal.fMax) AS fMax, min(ssMerkmal.fMin) AS fMin
                    FROM (
                        SELECT ROUND(
                            LEAST(
                                (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . ") *
                                ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                                IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatzMax . ") / 100), 2) AS fMax,
                     ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . ") *
                     ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                     IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatzMin . ") / 100), 2) AS fMin
                    FROM tartikel
                    JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                        AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    " . $FilterSQL->oHerstellerFilterSQL->cJoin . "

                    " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                    " . $FilterSQL->oSuchFilterSQL->cJoin . "
                    " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                    " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                    " . $FilterSQL->oTagFilterSQL->cJoin . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "

                    " . $cPreisspannenJOIN . "

                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kVaterArtikel = 0
                        " . gibLagerfilter() . "
                        " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                        " . $FilterSQL->oSuchFilterSQL->cWhere . "
                        " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                        " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                        " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                        " . $FilterSQL->oTagFilterSQL->cWhere . "
                        " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                        " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                    GROUP BY tartikel.kArtikel
                    " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                ) AS ssMerkmal
                ", 1);
                if (isset($oPreisspannenFilterMaxMin->fMax) && $oPreisspannenFilterMaxMin->fMax > 0) {
                    // Berechnet Max, Min, Step, Anzahl, Diff und liefert diese Werte in einem Objekt
                    $oPreis = berechneMaxMinStep($oPreisspannenFilterMaxMin->fMax * $_SESSION['Waehrung']->fFaktor, $oPreisspannenFilterMaxMin->fMin * $_SESSION['Waehrung']->fFaktor);
                    // Begrenzung der Preisspannen bei zu großen Preisdifferenzen
                    if ($oPreis->nAnzahlSpannen > 20) {
                        $oPreis->nAnzahlSpannen = 20;
                    }
                    $cSelectSQL = '';
                    for ($i = 0; $i < $oPreis->nAnzahlSpannen; $i++) {
                        if ($i > 0) {
                            $cSelectSQL .= ', ';
                        }
                        $cSelectSQL .= " SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                    }
                    $oPreisspannenFilterDB = Shop::DB()->query(
                        "SELECT " . $cSelectSQL . "
                        FROM
                        (
                            SELECT " . berechnePreisspannenSQL($oPreis) . "
                            FROM tartikel
                            JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                            " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                            " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                            " . $FilterSQL->oSuchFilterSQL->cJoin . "
                            " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                            " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                            " . $FilterSQL->oTagFilterSQL->cJoin . "
                            " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "

                            " . $cPreisspannenJOIN . "

                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tartikel.kVaterArtikel = 0
                                " . gibLagerfilter() . "
                                " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                                " . $FilterSQL->oSuchFilterSQL->cWhere . "
                                " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                                " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                                " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                                " . $FilterSQL->oTagFilterSQL->cWhere . "
                                " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                                " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                            GROUP BY tartikel.kArtikel
                            " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                        ) AS ssMerkmal
                        ", 1
                    );

                    $nPreisspannenAnzahl_arr   = (is_bool($oPreisspannenFilterDB)) ? null : get_object_vars($oPreisspannenFilterDB);
                    $oPreisspannenFilterDB_arr = array();
                    for ($i = 0; $i < $oPreis->nAnzahlSpannen; $i++) {
                        if ($i == 0) {
                            $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - 0);
                        } else {
                            $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                        }
                    }
                    $nPreisMax      = $oPreis->fMaxPreis;
                    $nPreisMin      = $oPreis->fMinPreis;
                    $nStep          = $oPreis->fStep;
                    $nAnzahlSpannen = $oPreis->nAnzahlSpannen;
                    for ($i = 0; $i < $nAnzahlSpannen; $i++) {
                        $oPreisspannenFilter       = new stdClass();
                        $oPreisspannenFilter->nVon = ($nPreisMin + $i * $nStep);
                        $oPreisspannenFilter->nBis = ($nPreisMin + ($i + 1) * $nStep);
                        if ($oPreisspannenFilter->nBis > $nPreisMax) {
                            if ($oPreisspannenFilter->nVon >= $nPreisMax) {
                                $oPreisspannenFilter->nVon = ($nPreisMin + ($i - 1) * $nStep);
                            }

                            if ($oPreisspannenFilter->nBis > $nPreisMax) {
                                $oPreisspannenFilter->nBis = $nPreisMax;
                            }
                        }
                        // Localize Preise
                        $oPreisspannenFilter->cVonLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nVon);
                        $oPreisspannenFilter->cBisLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nBis);
                        $oPreisspannenFilter->nAnzahlArtikel = $oPreisspannenFilterDB_arr[$i];
                        //baue URL
                        if (!isset($oZusatzFilter)) {
                            $oZusatzFilter = new stdClass();
                        }
                        if (!isset($oZusatzFilter->PreisspannenFilter)) {
                            $oZusatzFilter->PreisspannenFilter = new stdClass();
                        }
                        $oZusatzFilter->PreisspannenFilter->fVon = $oPreisspannenFilter->nVon;
                        $oZusatzFilter->PreisspannenFilter->fBis = $oPreisspannenFilter->nBis;
                        $oPreisspannenFilter->cURL               = gibNaviURL($this, true, $oZusatzFilter);
                        $oPreisspanne_arr[]                      = $oPreisspannenFilter;
                    }
                }
            } else {
                $oPreisspannenfilter_arr = Shop::DB()->query("SELECT * FROM tpreisspannenfilter", 2);
                if (is_array($oPreisspannenfilter_arr) && count($oPreisspannenfilter_arr) > 0) {
                    // Berechnet Max, Min, Step, Anzahl, Diff
                    $oPreis = berechneMaxMinStep(
                        $oPreisspannenfilter_arr[count($oPreisspannenfilter_arr) - 1]->nBis * $_SESSION['Waehrung']->fFaktor,
                        $oPreisspannenfilter_arr[0]->nVon * $_SESSION['Waehrung']->fFaktor
                    );
                    if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                        $res = array();
//                        Shop::Cache()->set($cacheID, $res, array(CACHING_GROUP_CATEGORY));

                        return $res;
                    }
                    $cSelectSQL = '';
                    $count      = count($oPreisspannenfilter_arr);
                    for ($i = 0; $i < $count; $i++) {
                        if ($i > 0) {
                            $cSelectSQL .= ', ';
                        }
                        $cSelectSQL .= "SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                    }

                    $oPreisspannenFilterDB = Shop::DB()->query(
                        "SELECT " . $cSelectSQL . "
                        FROM
                        (
                            SELECT " . berechnePreisspannenSQL($oPreis, $oPreisspannenfilter_arr) . "
                            FROM tartikel
                            JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                            " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                            " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                            " . $FilterSQL->oSuchFilterSQL->cJoin . "
                            " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                            " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                            " . $FilterSQL->oTagFilterSQL->cJoin . "
                            " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "

                            " . $cPreisspannenJOIN . "

                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tartikel.kVaterArtikel = 0
                                " . gibLagerfilter() . "
                                " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                                " . $FilterSQL->oSuchFilterSQL->cWhere . "
                                " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                                " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                                " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                                " . $FilterSQL->oTagFilterSQL->cWhere . "
                                " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                                " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                            GROUP BY tartikel.kArtikel
                            " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                        ) AS ssMerkmal
                        ", 1
                    );
                    $nPreisspannenAnzahl_arr   = get_object_vars($oPreisspannenFilterDB);
                    $oPreisspannenFilterDB_arr = array();
                    if (is_array($nPreisspannenAnzahl_arr)) {
                        $count = count($nPreisspannenAnzahl_arr);
                        for ($i = 0; $i < $count; $i++) {
                            if ($i === 0) {
                                $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - 0);
                            } else {
                                $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                            }
                        }
                    }
                    foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                        $oPreisspannenfilterTMP                 = new stdClass();
                        $oPreisspannenfilterTMP->nVon           = $oPreisspannenfilter->nVon;
                        $oPreisspannenfilterTMP->nBis           = $oPreisspannenfilter->nBis;
                        $oPreisspannenfilterTMP->nAnzahlArtikel = $oPreisspannenFilterDB_arr[$i];
                        // Localize Preise
                        $oPreisspannenfilterTMP->cVonLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nVon);
                        $oPreisspannenfilterTMP->cBisLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nBis);
                        //baue URL
                        $oZusatzFilter                           = new stdClass();
                        $oZusatzFilter->PreisspannenFilter       = new stdClass();
                        $oZusatzFilter->PreisspannenFilter->fVon = $oPreisspannenfilterTMP->nVon;
                        $oZusatzFilter->PreisspannenFilter->fBis = $oPreisspannenfilterTMP->nBis;
                        $oPreisspannenfilterTMP->cURL            = gibNaviURL($this, true, $oZusatzFilter);
                        $oPreisspanne_arr[]                      = $oPreisspannenfilterTMP;
                    }
                }
            }
        }
        // Preisspannen ohne Artikel ausblenden (falls im Backend eingestellt)
        if ($this->conf['navigationsfilter']['preisspannenfilter_spannen_ausblenden'] === 'Y') {
            if (count($oPreisspanne_arr) > 0) {
                $oPreisspanneTMP_arr = array();
                foreach ($oPreisspanne_arr as $oPreisspanne) {
                    if ($oPreisspanne->nAnzahlArtikel > 0) {
                        $oPreisspanneTMP_arr[] = $oPreisspanne;
                    }
                }
                $oPreisspanne_arr = $oPreisspanneTMP_arr;
            }
        }

        return $oPreisspanne_arr;
    }

    /**
     * @return array
     */
    public function getSearchFilterOptions()
    {
        $oSuchFilterDB_arr = array();
        if ($this->conf['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $nLimit = (isset($this->conf['navigationsfilter']['suchtrefferfilter_anzahl'])
                && intval($this->conf['navigationsfilter']['suchtrefferfilter_anzahl']) > 0) ? " LIMIT " . (int)$this->conf['navigationsfilter']['suchtrefferfilter_anzahl'] : '';


            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $state->joins[] = "\n#current order join \n" . $order->join;

            $state->joins[] = "JOIN tsuchcachetreffer ON tartikel.kArtikel = tsuchcachetreffer.kArtikel";
            $state->joins[] = "JOIN tsuchcache ON tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache";
            $state->joins[] = "JOIN tsuchanfrage ON tsuchanfrage.cSuche = tsuchcache.cSuche AND tsuchanfrage.kSprache = " . Shop::getLanguage();

            $state->conditions[] = "tsuchanfrage.nAktiv = 1";

            $query = $this->getBaseQuery(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);

            $query = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM
                ("
                . $query .
                ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;

            $oSuchFilterDB_arr = Shop::DB()->query($query, 2);

            $kSuchanfrage_arr = array();
            if ($this->Suche->kSuchanfrage > 0) {
                $kSuchanfrage_arr[] = (int)$this->Suche->kSuchanfrage;
            }
            if (count($this->SuchFilter) > 0) {
                foreach ($this->SuchFilter as $oSuchFilter) {
                    if (isset($oSuchFilter->kSuchanfrage)) {
                        $kSuchanfrage_arr[] = (int)$oSuchFilter->kSuchanfrage;
                    }
                }
            }
            // Werfe bereits gesetzte Filter aus dem Ergebnis Array
            $nCount = count($oSuchFilterDB_arr);
            for ($j = 0; $j < $nCount; $j++) {
                $count = count($kSuchanfrage_arr);
                for ($i = 0; $i < $count; $i++) {
                    if ($oSuchFilterDB_arr[$j]->kSuchanfrage == $kSuchanfrage_arr[$i]) {
                        unset($oSuchFilterDB_arr[$j]);
                        break;
                    }
                }
            }
            if (is_array($oSuchFilterDB_arr)) {
                $oSuchFilterDB_arr = array_merge($oSuchFilterDB_arr);
            }
            //baue URL
            $count = count($oSuchFilterDB_arr);
            for ($i = 0; $i < $count; $i++) {
                $oZusatzFilter = new stdClass();
                $oZusatzFilter->SuchFilter = new stdClass();
                $oZusatzFilter->SuchFilter->kSuchanfrage = (int)$oSuchFilterDB_arr[$i]->kSuchanfrage;
                $oSuchFilterDB_arr[$i]->cURL             = gibNaviURL($this, true, $oZusatzFilter);
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oSuchFilterDB_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oSuchFilterDB_arr[0]->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($oSuchFilterDB_arr as $i => $oSuchFilterDB) {
                $oSuchFilterDB_arr[$i]->Klasse = rand(1, 10);
                if (isset($oSuchFilterDB->kSuchCache) && $oSuchFilterDB->kSuchCache > 0 && $nPrioStep >= 0) {
                    $oSuchFilterDB_arr[$i]->Klasse = round(($oSuchFilterDB->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / $nPrioStep) + 1;
                }
            }
        }

        return $oSuchFilterDB_arr;
    }
    
    /**
     * @return array
     */
    public function getCategoryFilterOptions()
    {
        $oKategorieFilterDB_arr = array();
        if ($this->conf['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
            if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) {
                $oKundengruppe                           = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
                $kKundengruppe = $oKundengruppe->kKundengruppe;
            } else {
                $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            }
            $kSprache = Shop::getLanguage();
            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $state->joins[] = "\n#current order join \n" . $order->join;

            // Kategoriefilter anzeige
            if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && (!$this->Kategorie->isInitialized())) {
                $kKatFilter        = ($this->KategorieFilter->isInitialized()) ? '' : " AND tkategorieartikelgesamt.kOberKategorie = 0";
                $state->joins[] = "JOIN tkategorieartikelgesamt ON tartikel.kArtikel = tkategorieartikelgesamt.kArtikel " . $kKatFilter;
                $state->joins[] = "JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie";
            } else {
                if (!$this->Kategorie->isInitialized()) {
                    $state->joins[] = "JOIN tkategorieartikel ON tartikel.kArtikel = tkategorieartikel.kArtikel";
                }
                $state->joins[] = "JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie";
            }

            // nicht Standardsprache? Dann hole Namen nicht aus tkategorie sondern aus tkategoriesprache
            $cSQLKategorieSprache          = new stdClass();
            $cSQLKategorieSprache->cJOIN   = '';
            $select = ['tkategorie.kKategorie', 'tkategorie.nSort'];
            if (!standardspracheAktiv()) {
                $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
                $state->joins[]   = "JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie AND tkategoriesprache.kSprache = " . Shop::getLanguage();
            } else {
                $select[] = "tkategorie.cName";
            }

            $query = $this->getBaseQuery($select, $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['tkategorie.kKategorie', 'tartikel.kArtikel']);
            $query =  "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM
                (" . $query . "
            ) AS ssMerkmal
            LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                AND tseo.cKey = 'kKategorie'
                AND tseo.kSprache = " . Shop::getLanguage() . "
            GROUP BY ssMerkmal.kKategorie
            ORDER BY ssMerkmal.nSort, ssMerkmal.cName";
            $oKategorieFilterDB_arr = Shop::DB()->query($query, 2);
            //baue URL
            $count = (is_array($oKategorieFilterDB_arr)) ? count($oKategorieFilterDB_arr) : 0;
            for ($i = 0; $i < $count; ++$i) {
                // Anzeigen als KategoriePfad
                if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'KP') {
                    $oKategorie                        = new Kategorie($oKategorieFilterDB_arr[$i]->kKategorie);
                    $oKategorieFilterDB_arr[$i]->cName = gibKategoriepfad($oKategorie, $kKundengruppe, $kSprache);
                }
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                if (!isset($oZusatzFilter->KategorieFilter)) {
                    $oZusatzFilter->KategorieFilter = new stdClass();
                }
                $oZusatzFilter->KategorieFilter->kKategorie = $oKategorieFilterDB_arr[$i]->kKategorie;
                $oZusatzFilter->KategorieFilter->cSeo       = $oKategorieFilterDB_arr[$i]->cSeo;
                $oKategorieFilterDB_arr[$i]->cURL           = gibNaviURL($this, true, $oZusatzFilter);
                $oKategorieFilterDB_arr[$i]->nAnzahl = (int)$oKategorieFilterDB_arr[$i]->nAnzahl;
                $oKategorieFilterDB_arr[$i]->kKategorie = (int)$oKategorieFilterDB_arr[$i]->kKategorie;
                $oKategorieFilterDB_arr[$i]->nSort = (int)$oKategorieFilterDB_arr[$i]->nSort;
            }
            //neue Sortierung
            if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'KP') {
                usort($oKategorieFilterDB_arr, 'sortierKategoriepfade');
            }
        }

        return $oKategorieFilterDB_arr;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $this->cBrotNaviName =  '';
        if ($this->Kategorie->isInitialized()) {
            $this->cBrotNaviName = $this->Kategorie->getName();
        }
        if ($this->Hersteller->isInitialized()) {
            $this->cBrotNaviName = $this->Hersteller->getName();
        }
        if ($this->MerkmalWert->isInitialized()) {
            $this->cBrotNaviName = $this->MerkmalWert->getName();
        }
        if ($this->Tag->isInitialized()) {
            $this->cBrotNaviName = $this->Tag->getName();
        }
        if ($this->Suchspecial->isInitialized()) {
            $this->cBrotNaviName = $this->Suchspecial->getName();
        }
        if ($this->Suche->isInitialized()) {
            $this->cBrotNaviName = $this->Suche->getName();
        }
        if ($this->Kategorie->isInitialized()) {
            return $this->cBrotNaviName;
        }
        if ($this->Hersteller->isInitialized()) {
            return Shop::Lang()->get('productsFrom', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->MerkmalWert->isInitialized()) {
            return Shop::Lang()->get('productsWith', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Tag->isInitialized()) {
            return Shop::Lang()->get('showAllProductsTaggedWith', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Suchspecial->isInitialized()) {
            return $this->cBrotNaviName;
        }
        if (!empty($this->Suche->cSuche)) {
            return Shop::Lang()->get('for', 'global') . ' ' . $this->cBrotNaviName;
        }

        return '';
    }
    
    /**
     * @param array  $select
     * @param array  $joins
     * @param array  $conditions
     * @param array  $having
     * @param string $order
     * @param string $limit
     * @param array  $groupBy
     * @return string
     */
    private function getBaseQuery($select = ['tartikel.kArtikel'], $joins, $conditions, $having = [], $order = '', $limit = '', $groupBy = ['tartikel.kArtikel'])
    {
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        $conditions    = implode(' AND ', array_map(function ($a) {
            return (is_string($a))
                ? ($a)
                : ('(' . implode(' OR ', $a) . ')');
        }, $conditions));
        $joins         = implode("\n", $joins);
        $having        = implode(' AND ', $having);
        if (!empty($limit)) {
            $limit = ' LIMIT ' . $limit;
        }
        if (!empty($order)) {
            $order = "ORDER BY " . $order;
        }
        if (!empty($conditions)) {
            $conditions = ' AND ' . $conditions;
        }
        if (!empty($groupBy)) {
            $groupBy = "GROUP BY " . implode(', ', $groupBy);
        }

        $query = "SELECT " . implode(', ', $select) . "
            FROM tartikel " . $joins . "
            #default group visibility
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            #default conditions
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                #stock filter
                " . gibLagerfilter() .
                $conditions . "
            #default group by
            " . $groupBy . "
            " . $having . "
            #order by
            " . $order . "
            #limit sql
            " . $limit;

        return $query;
    }
}
