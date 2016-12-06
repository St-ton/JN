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
     * @var FilterSearchSpecialFilter
     */
    public $SuchspecialFilter;

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
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->oSprache_arr = Shop::Lang()->getLangArray();
        $this->conf         = Shop::getSettings([
            CONF_ARTIKELUEBERSICHT,
            CONF_NAVIGATIONSFILTER,
            CONF_BOXEN,
            CONF_GLOBAL,
            CONF_SUCHSPECIAL
        ]);
        $this->languageID   = Shop::getLanguage();
        if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $oKundengruppe = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
            $this->customerGroupID = (int)$oKundengruppe->kKundengruppe;
        } else {
            $this->customerGroupID = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        }
        $this->initBaseStates();
    }

    /**
     * @return int
     */
    public function getCustomerGroupID()
    {
        return (int)$this->customerGroupID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCustomerGroupID($id)
    {
        $this->customerGroupID = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageID()
    {
        return (int)$this->languageID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setLanguageID($id)
    {
        $this->languageID = (int)$id;

        return $this;
    }
    /**
     * @param bool $byType
     * @return array
     */
    public function getActiveFilters($byType = false)
    {
        if ($byType) {
            $filters = ['mm' => [], 'ssf' => [], 'tf' => [], 'sf' => [], 'hf' => [], 'bf' => []];
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
        if ($this->BewertungFilter->isInitialized()) {
            if ($byType) {
                $filters['bf'][] = $this->BewertungFilter;
            } else {
                $filters[] = $this->BewertungFilter;
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
//        foreach ($this->SuchspecialFilter as $filter) {
//            if ($filter->isInitialized()) {
//                if ($byType) {
//                    $filters['ssf'][] = $filter;
//                } else {
//                    $filters[] = $filter;
//                }
//            }
//        }
        if ($this->SuchspecialFilter->isInitialized()) {
            if ($byType) {
                $filters['ssf'][] = $this->SuchspecialFilter;
            } else {
                $filters[] = $this->SuchspecialFilter;
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
     * @return FilterHersteller|FilterKategorie|FilterMerkmal|FilterSearch|FilterSearchSpecial|null
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
        if ($this->Suchspecial->isInitialized()) {
            return $this->Suchspecial;
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

        $this->SuchspecialFilter = new FilterSearchSpecialFilter();

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
            $this->SuchspecialFilter = (new FilterSearchSpecialFilter())->init($params['kSuchspecialFilter'], $this->oSprache_arr);
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
            ++$count;
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
            $params['cSuche'] = StringHandler::filterXSS($params['cSuche']);

            $this->Suche              = (new FilterSearch())->init($params['kSuchanfrage'], $this->oSprache_arr);
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
     * @return int
     * @todo: update value when adding filters
     */
    public function getFilterCount()
    {
        return $this->nAnzahlFilter;
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
                if ($this->HerstellerFilter->getSeo($this->languageID) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->HerstellerFilter->getSeo($this->languageID));
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if ($this->KategorieFilter->getSeo($this->languageID) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->KategorieFilter->getSeo($this->languageID));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() && $this->Hersteller->getSeo($this->languageID) !== null) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Hersteller->getSeo($this->languageID));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() && $this->Kategorie->getSeo($this->languageID) !== null) {
                //we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Kategorie->getSeo($this->languageID));
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
        $sort              = new stdClass();
        $sort->join        = new FilterJoin();
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
                } elseif ($this->Suche->isInitialized() && isset($_SESSION['Usersortierung']) && (int)$_SESSION['Usersortierung'] === 100) {
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
                $sort->join->setComment('join from SORT by price ASC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = ' . $this->customerGroupID);
                break;
            case SEARCH_SORT_PRICE_DESC:
                $sort->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
                $sort->join    = new FilterJoin();
                $sort->join->setComment('join from SORT by price DESC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = ' . $this->customerGroupID);
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
                $sort->join    = new FilterJoin();
                $sort->join->setComment('join from SORT by bestseller')
                           ->setType('LEFT JOIN')
                           ->setTable('tbestseller')
                           ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
                $sort->join    = new FilterJoin();
                $sort->join->setComment('join from SORT by rating')
                           ->setType('LEFT JOIN')
                           ->setTable('tbewertung')
                           ->setOn('tbewertung.kArtikel = tartikel.kArtikel');
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
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            return (int)$_SESSION['ArtikelProSeite'];
        }
        if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
            return (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        }

        return ($this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0)
            ? (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']
            : 20;
    }

    /**
     * @return string
     */
    private function getStorageFilter()
    {
        $filterSQL = '';
        if ((int)$this->conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') OR tartikel.cLagerVariation = 'Y')";
        } elseif ((int)$this->conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') OR tartikel.cLagerKleinerNull = 'Y' OR tartikel.cLagerVariation = 'Y')";
        }
        executeHook(HOOK_STOCK_FILTER, [
            'conf'      => (int)$this->conf['global']['artikel_artikelanzeigefilter'],
            'filterSQL' => &$filterSQL
        ]);

        return $filterSQL;
    }

    /**
     * @return stdClass|string
     */
    public function getProductKeys()
    {
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

        $state->joins[] = $order->join;

        $query = $this->getBaseQuery(['tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);

        return Shop::DB()->query($query, 2);
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
            $this->Suche->kSuchanfrage = gibSuchanfrageKey($this->Suche->cSuche, $this->languageID);
        }

        $nLimitN = $nArtikelProSeite * ($this->nSeite - 1);

        $oSuchergebnisse->ArtikelVon = $nLimitN + 1;
        $oSuchergebnisse->ArtikelBis = min($nLimitN + $nArtikelProSeite, $oSuchergebnisse->GesamtanzahlArtikel);

        $oSuchergebnisse->Seitenzahlen                = new stdClass();
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $this->nSeite;
        $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $nArtikelProSeite);
        $oSuchergebnisse->Seitenzahlen->minSeite      = min(intval($oSuchergebnisse->Seitenzahlen->AktuelleSeite - (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'] / 2), 0);
        $oSuchergebnisse->Seitenzahlen->maxSeite      = max($oSuchergebnisse->Seitenzahlen->MaxSeiten, $oSuchergebnisse->Seitenzahlen->minSeite + (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'] - 1);
        if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
            $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
        }

        return $oSuchergebnisse;
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return stdClass
     */
    private function getCurrentStateData($ignore = null)
    {
        $state              = $this->getActiveState();
        $data               = new stdClass();
        $data->having       = [];
        $data->joins        = $state->getSQLJoin();
        $data->conditions   = [];

        $stateCondition = $state->getSQLCondition();
        if (!empty($stateCondition)) {
            $data->conditions[] = $stateCondition;
        }

        foreach ($this->getActiveFilters(true) as $type => $filter) {
            $count = count($filter);
            if ($count > 1) {
                $singleConditions = [];
                /** @var AbstractFilter $item */
                foreach ($filter as $idx => $item) {
                    if ($ignore === null || get_class($item) !== $ignore) {
                        if ($idx === 0) {
                            foreach ($item->getSQLJoin() as $filterJoin) {
                                $data->joins[] = $filterJoin;
                            }
                            if ($item->getType() === AbstractFilter::FILTER_TYPE_AND) {
                                //filters that decrease the total amount of articles must have a "HAVING" clause
                                $data->having[] = 'HAVING COUNT(' . $item->getTableName() . '.' . $item->getPrimaryKeyRow() . ') = ' . $count;
                            }
                        }
                        $singleConditions[] = $item->getSQLCondition();
                    }
                }
                if (!empty($singleConditions)) {
                    $data->conditions[] = $singleConditions;
                }
            } elseif ($count === 1)  {
                if ($ignore === null || get_class($filter[0]) !== $ignore) {
                    foreach ($filter[0]->getSQLJoin() as $filterJoin) {
                        $data->joins[] = $filterJoin;
                    }
                    $data->conditions[] = "\n#condition from filter " . $type . "\n" . $filter[0]->getSQLCondition();
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getSearchSpecialFilterOptions()
    {
        $oSuchspecialFilterDB_arr = [];
        if ($this->conf['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y') {
            for ($i = 1; $i < 7; ++$i) {
                $state = $this->getCurrentStateData();
                switch ($i) {
                    case SEARCHSPECIALS_BESTSELLER:
                        $nAnzahl = ($this->conf['global']['global_bestseller_minanzahl'] > 0)
                            ? (int)$this->conf['global']['global_bestseller_minanzahl']
                            : 100;

                        $join = new FilterJoin();
                        $join->setComment('join from getSearchSpecialFilterOptions bestseller')
                             ->setType('JOIN')
                             ->setTable('tbestseller')
                             ->setOn('tbestseller.kArtikel = tartikel.kArtikel');
                        $state->joins[] = $join;

                        $state->conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;
                        break;
                    case SEARCHSPECIALS_SPECIALOFFERS:
                        if (!$this->PreisspannenFilter->isInitialized()) {
                            $join = new FilterJoin();
                            $join->setComment('join1 from getSearchSpecialFilterOptions special offer')
                                 ->setType('JOIN')
                                 ->setTable('tartikelsonderpreis')
                                 ->setOn('tartikelsonderpreis.kArtikel = tartikel.kArtikel');
                            $state->joins[] = $join;

                            $join = new FilterJoin();
                            $join->setComment('join2 from getSearchSpecialFilterOptions special offer')
                                 ->setType('JOIN')
                                 ->setTable('tsonderpreise')
                                 ->setOn('tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis');
                            $state->joins[] = $join;
                            $tsonderpreise = 'tsonderpreise';
                        } else {
                            $tsonderpreise = 'tsonderpreise';//'tspgspqf';
                        }
                        $state->conditions[] = "tartikelsonderpreis.cAktiv = 'Y' AND tartikelsonderpreis.dStart <= now()";
                        $state->conditions[] = "(tartikelsonderpreis.dEnde >= CuRDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')";
                        $state->conditions[] = $tsonderpreise . ".kKundengruppe = " . $this->customerGroupID;
                        break;
                    case SEARCHSPECIALS_NEWPRODUCTS:
                        $alter_tage = ($this->conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                            ? (int)$this->conf['boxen']['box_neuimsortiment_alter_tage']
                            : 30;
                        $state->conditions[] = "tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt";
                        break;
                    case SEARCHSPECIALS_TOPOFFERS:
                        $state->conditions[] = 'tartikel.cTopArtikel = "Y"';
                        break;
                    case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                        $state->conditions[] = 'now() < tartikel.dErscheinungsdatum';
                        break;
                    case SEARCHSPECIALS_TOPREVIEWS:
                        if (!$this->BewertungFilter->isInitialized()) {
                            $join = new FilterJoin();
                            $join->setComment('join from getSearchSpecialFilterOptions top reviews')
                                 ->setType('JOIN')
                                 ->setTable('tartikelext')
                                 ->setOn('tartikelext.kArtikel = tartikel.kArtikel');
                            $state->joins[] = $join;
                        }
                        $state->conditions[] = "ROUND(tartikelext.fDurchschnittsBewertung) >= " . (int)$this->conf['boxen']['boxen_topbewertet_minsterne'];
                        break;
                }
                $qry = $this->getBaseQuery(['tartikel.kArtikel'], $state->joins, $state->conditions, $state->having);

                $oSuchspecialFilterDB  = Shop::DB()->query($qry, 2);
                $oSuchspecial          = new stdClass();
                $oSuchspecial->nAnzahl = count($oSuchspecialFilterDB);
                $oSuchspecial->kKey    = $i;

                $oZusatzFilter                          = new stdClass();
                $oZusatzFilter->SuchspecialFilter       = new stdClass();
                $oZusatzFilter->SuchspecialFilter->kKey = $i;
                $oSuchspecial->cURL                     = $this->getURL(true, $oZusatzFilter);
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
        $oHerstellerFilterDB_arr = [];
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
            $join  = new FilterJoin();
            $join->setComment('join from manufacturerFilterOptions')
                 ->setType('JOIN')
                 ->setTable('thersteller')
                 ->setOn('tartikel.kHersteller = thersteller.kHersteller');

            $state->joins[] = $order->join;
            $state->joins[] = $join;

            $query = $this->getBaseQuery(['thersteller.kHersteller', 'thersteller.cName', 'thersteller.nSortNr', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "
            SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM
                (" . $query . "
                ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $this->languageID . "
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

                $oHerstellerFilterDB_arr[$i]->cURL            = $this->getURL(true, $oZusatzFilter);
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
        $oBewertungFilter_arr = [];
        if ($this->conf['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $join = new FilterJoin();
            $join->setComment('join from getRatingFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tartikelext')
                 ->setOn('tartikel.kArtikel = tartikelext.kArtikel');

            $state->joins[] = $order->join;
            $state->joins[] = $join;

            $query = $this->getBaseQuery(['ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                        FROM (" . $query . " ) AS ssMerkmal
                        GROUP BY ssMerkmal.nSterne
                        ORDER BY ssMerkmal.nSterne DESC";

            $oBewertungFilterDB_arr = Shop::DB()->query($query, 2);
            if (is_array($oBewertungFilterDB_arr)) {
                $nSummeSterne = 0;
                foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                    $nSummeSterne += (int)$oBewertungFilterDB->nAnzahl;
                    $oBewertung          = new stdClass();
                    $oBewertung->nStern  = (int)$oBewertungFilterDB->nSterne;
                    $oBewertung->nAnzahl = $nSummeSterne;
                    //baue URL
                    if (!isset($oZusatzFilter)) {
                        $oZusatzFilter                  = new stdClass();
                        $oZusatzFilter->BewertungFilter = new stdClass();
                    }
                    $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                    $oBewertung->cURL                        = $this->getURL(true, $oZusatzFilter);
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
        $oTagFilter_arr = [];
        if ($this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $joinedTables = [];
            $order        = $this->getOrder();
            $state        = $this->getCurrentStateData();

            $join = new FilterJoin();
            $join->setComment('join1 from getTagFilterOptions')
                 ->setType('JOIN')
                 ->setTable('ttagartikel')
                 ->setOn('ttagartikel.kArtikel = tartikel.kArtikel');

            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from getTagFilterOptions')
                 ->setType('JOIN')
                 ->setTable('ttag')
                 ->setOn('ttagartikel.kTag = ttag.kTag');

            $state->joins[] = $join;
            $state->joins[] = $order->join;

            //remove duplicate joins
            foreach ($state->joins as $i => $stateJoin) {
                if (!in_array($stateJoin->getTable(), $joinedTables)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }

            $state->conditions[] = "ttag.nAktiv = 1";
            $state->conditions[] = "ttag.kSprache = " . $this->languageID;
            $query = $this->getBaseQuery(['ttag.kTag', 'ttag.cName', 'ttagartikel.nAnzahlTagging', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['ttag.kTag', 'tartikel.kArtikel']);

            $query = "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                    FROM (" . $query . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                    AND tseo.cKey = 'kTag'
                    AND tseo.kSprache = " . $this->languageID . "
                GROUP BY ssMerkmal.kTag
                ORDER BY nAnzahl DESC LIMIT 0, " . (int)$this->conf['navigationsfilter']['tagfilter_max_anzeige'];
            $oTagFilterDB_arr = Shop::DB()->query($query, 2);
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
                $oTagFilter->cURL               = $this->getURL(true, $oZusatzFilter);
                $oTagFilter->kTag               = $oTagFilterDB->kTag;
                $oTagFilter->cName              = $oTagFilterDB->cName;
                $oTagFilter->nAnzahl            = $oTagFilterDB->nAnzahl;
                $oTagFilter->nAnzahlTagging     = $oTagFilterDB->nAnzahlTagging;

                $oTagFilter_arr[] = $oTagFilter;
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oTagFilter_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oTagFilter_arr[0]->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / 9;
            }
            foreach ($oTagFilter_arr as $i => $oTagwolke) {
                if ($oTagwolke->kTag > 0) {
                    $oTagFilter_arr[$i]->Klasse = ($nPrioStep < 1)
                        ? rand(1, 10)
                        : round(($oTagwolke->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / $nPrioStep) + 1;
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
        $oMerkmalFilter_arr          = [];
        $cKatAttribMerkmalFilter_arr = [];
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
            $state = $this->getCurrentStateData('FilterMerkmalFilter');
            $state->joins[] = $order->join;

            $select = 'tmerkmal.cName';
            if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                $select = "tmerkmalsprache.cName";
                $join   = new FilterJoin();
                $join->setComment('join1 from getAttributeFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tmerkmalsprache')
                     ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal AND tmerkmalsprache.kSprache = ' . $this->languageID);
                $state->joins[] = $join;
            }
            if (true||!$this->MerkmalWert->isInitialized() && count($this->MerkmalFilter) === 0) {
                $join = new FilterJoin();
                $join->setComment('join2 from getAttributeFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tartikelmerkmal')
                     ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel');
                $state->joins[] = $join;
            }
            $join = new FilterJoin();
            $join->setComment('join3 from getAttributeFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tmerkmalwert')
                 ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join4 from getAttributeFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tmerkmalwertsprache')
                 ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert AND tmerkmalwertsprache.kSprache = ' . $this->languageID);
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join5 from getAttributeFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tmerkmal')
                 ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal');
            $state->joins[] = $join;

            if (count($this->MerkmalFilter) > 0) {
                $join = new FilterJoin();
                $activeFilterIDs = [];
                foreach ($this->MerkmalFilter as $filter) {
                    $activeFilterIDs[] = $filter->getID();
                }
                $join->setComment('join6 from getAttributeFilterOptions')
                     ->setType('JOIN')
                     ->setTable('(
                                SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeFilterIDs) . ' )
                                    GROUP BY kArtikel
                                    HAVING count(*) = ' . count($activeFilterIDs) . '
                                    ) AS ssj1')
                     ->setOn('tartikel.kArtikel = ssj1.kArtikel');
                $state->joins[] = $join;
            }

            $query = $this->getBaseQuery([
                    'tartikelmerkmal.kMerkmal',
                    'tartikelmerkmal.kMerkmalWert',
                    'tmerkmalwert.cBildPfad AS cMMWBildPfad',
                    'tmerkmalwertsprache.cWert',
                    'tmerkmal.nSort AS nSortMerkmal',
                    'tmerkmalwert.nSort',
                    'tmerkmal.cTyp',
                    'tmerkmal.cBildPfad AS cMMBildPfad',
                    $select
                ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
                '',
                ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']);

            $query = "SELECT tseo.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kMerkmalWert
                    AND tseo.cKey = 'kMerkmalWert'
                    AND tseo.kSprache = " . $this->languageID . "
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";

            $oMerkmalFilterDB_arr = Shop::DB()->query($query, 2);

            if (is_array($oMerkmalFilterDB_arr)) {
                foreach ($oMerkmalFilterDB_arr as $i => $oMerkmalFilterDB) {
                    $nPos          = $this->getAttributePosition($oMerkmalFilter_arr, (int)$oMerkmalFilterDB->kMerkmal);
                    $oMerkmalWerte = new stdClass();

                    $oMerkmalWerte->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                    $oMerkmalWerte->cWert        = $oMerkmalFilterDB->cWert;
                    $oMerkmalWerte->nAnzahl      = (int)$oMerkmalFilterDB->nAnzahl;
                    $oMerkmalWerte->nAktiv       = ($this->MerkmalWert->getID() === $oMerkmalWerte->kMerkmalWert || ($this->attributeValueIsActive($oMerkmalWerte->kMerkmalWert)))
                        ? 1
                        : 0;

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
                    $oZusatzFilter->MerkmalFilter->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                    $oZusatzFilter->MerkmalFilter->cSeo         = $oMerkmalFilterDB->cSeo;
                    $oMerkmalWerte->cURL                        = $this->getURL(true, $oZusatzFilter);

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
                    $oMerkmal->kMerkmal = (int)$oMerkmalFilterDB->kMerkmal;
                    if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                        $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                        $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                    } else {
                        $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    }
                    $oMerkmal->oMerkmalWerte_arr = [];
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
            //Filter durchgehen und die Merkmalwerte entfernen, die zuviel sind und deren Anzahl am geringsten ist.
            foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
                //#534 Anzahl max Merkmalwerte erreicht?
                if (isset($this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) && $this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte'] > 0) {
                    while (count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr) > $this->conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) {
                        $nMinAnzahl = 999999;
                        $nIndex     = -1;
                        $count      = count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr);
                        for ($l = 0; $l < $count; ++$l) {
                            if ($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl < $nMinAnzahl) {
                                $nMinAnzahl = (int)$oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl;
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
            // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen entfernen
            if (count($cKatAttribMerkmalFilter_arr) > 0) {
                $nKatFilter = count($oMerkmalFilter_arr);
                for ($i = 0; $i < $nKatFilter; ++$i) {
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
                for ($i = 0; $i < $count; ++$i) {
                    if (!is_numeric($oMerkmalFilter->oMerkmalWerte_arr[$i]->cWert)) {
                        $bAlleNumerisch = false;
                        break;
                    }
                }
                if ($bAlleNumerisch) {
                    usort($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr, function($a, $b) {
                        return ($a == $b)
                            ? 0
                            : (($a->cWert < $b->cWert)
                                ? -1
                                : 1
                            );
                    });
                }
            }
        }

        return $oMerkmalFilter_arr;
    }

    /**
     * @param object      $oPreis
     * @param object      $currency
     * @param array|null  $oPreisspannenfilter_arr
     * @return string
     */
    private function getPriceRangeSQL($oPreis, $currency, $oPreisspannenfilter_arr = null)
    {
        $cSQL          = '';
        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }
        // Wenn Option vorhanden, dann nur Spannen anzeigen, in denen Artikel vorhanden sind
        if ($this->conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
//            $nPreisMax = $oPreis->fMaxPreis;
            $nPreisMin = $oPreis->fMinPreis;
            $nStep     = $oPreis->fStep;
            $oPreisspannenfilter_arr = [];
            for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                $fakePriceRange = new stdClass();
                $fakePriceRange->nBis = ($nPreisMin + ($i + 1) * $nStep);
                $oPreisspannenfilter_arr[$i] = $fakePriceRange;
            }
        }

        if (is_array($oPreisspannenfilter_arr)) {
            foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                $cSQL .= "COUNT(DISTINCT 
                    IF(";

                $nBis = $oPreisspannenfilter->nBis;
                // Finde den höchsten und kleinsten Steuersatz
                if (is_array($_SESSION['Steuersatz']) && intval($_SESSION['Kundengruppe']->nNettoPreise) === 0) {
                    $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
                    foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                        $fSteuersatz = floatval($_SESSION['Steuersatz'][$nSteuersatzKeys]);
                        $cSQL .= "IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor . ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                            $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                            $currency->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100)
                        , 2),";
                    }
                    $cSQL .= "0";
                    $count = count($nSteuersatzKeys_arr);
                    for ($x = 0; $x < $count; $x++) {
                        $cSQL .= ")";
                    }
                } elseif ($_SESSION['Kundengruppe']->nNettoPreise > 0) {
                    $cSQL .= "ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor . ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                        $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $currency->fFaktor . "))), 2)";
                }

                $cSQL .= " < " . $nBis . ", tartikel.kArtikel, NULL)
                    ) AS anz" . $i . ", ";
            }
            $cSQL = substr($cSQL, 0, strlen($cSQL) - 2);
        }

        return $cSQL;
    }

    /**
     * @param int $productCount
     * @return array
     */
    public function getPriceRangeFilterOptions($productCount)
    {
        $oPreisspanne_arr = [];

        // Prüfe ob es nur einen Artikel in der Artikelübersicht gibt, falls ja und es ist noch kein Preisspannenfilter gesetzt
        // dürfen keine Preisspannenfilter angezeigt werden
        if ($this->conf['navigationsfilter']['preisspannenfilter_benutzen'] === 'N' || ($productCount === 1 && !$this->PreisspannenFilter->isInitialized())) {
            return $oPreisspanne_arr;
        }
        $currency = (isset($_SESSION['Waehrung']))
            ? $_SESSION['Waehrung']
            : null;
        if (!isset($currency->kWaehrung)) {
            $currency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }

        $order = $this->getOrder();
        $state = $this->getCurrentStateData();

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tartikelkategorierabatt')
             ->setOn("tartikelkategorierabatt.kKundengruppe = " . $this->customerGroupID . " AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel");
        $state->joins[] = $join;

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tartikelsonderpreis')
             ->setOn("tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= now()
                        AND (tartikelsonderpreis.dEnde >= CURDATe() OR tartikelsonderpreis.dEnde = '0000-00-00')");
        $state->joins[] = $join;

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tsonderpreise')
             ->setOn("tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis AND tsonderpreise.kKundengruppe = " . $this->customerGroupID);
        $state->joins[] = $join;

        $state->joins[] = $order->join;

        // Automatisch
        if ($this->conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
            $join = new FilterJoin();
            $join->setComment('join1 from getPriceRangeFilterOptions')
                 ->setTable('tpreise')
                 ->setType('JOIN')
                 ->setOn('tpreise.kArtikel = tartikel.kArtikel AND tpreise.kKundengruppe = ' . $this->customerGroupID);
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from getPriceRangeFilterOptions')
                 ->setTable('tartikelsichtbarkeit')
                 ->setType('LEFT JOIN')
                 ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel AND tartikelsichtbarkeit.kKundengruppe = ' . $this->customerGroupID);
            $state->joins[] = $join;

            //remove duplicate joins
            $joinedTables = [];
            foreach ($state->joins as $i => $stateJoin) {
                if (is_string($stateJoin)){
                    throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
                }
                if (!in_array($stateJoin->getTable(), $joinedTables)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }
            // Finde den höchsten und kleinsten Steuersatz
            if (is_array($_SESSION['Steuersatz']) && $_SESSION['Kundengruppe']->nNettoPreise === '0') {
                $fSteuersatz_arr = [];
                foreach ($_SESSION['Steuersatz'] as $fSteuersatz) {
                    $fSteuersatz_arr[] = $fSteuersatz;
                }
                $fSteuersatzMax = count($fSteuersatz_arr) ? max($fSteuersatz_arr) : 0;
                $fSteuersatzMin = count($fSteuersatz_arr) ? min($fSteuersatz_arr) : 0;
            } elseif ($_SESSION['Kundengruppe']->nNettoPreise > 0) {
                $fSteuersatzMax = 0.0;
                $fSteuersatzMin = 0.0;
            }
            $fKundenrabatt = (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0)
                ? $_SESSION['Kunde']->fRabatt
                : 0.0;
            $state->conditions    = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('(' . implode(' OR ', $a) . ')');
            }, $state->conditions));
            if (!empty($state->conditions)) {
                $state->conditions = ' AND ' . $state->conditions;
            }
            $state->having = implode(' AND ', $state->having);
            $state->joins  = implode("\n", $state->joins);
            $qry = "SELECT max(ssMerkmal.fMax) AS fMax, min(ssMerkmal.fMin) AS fMin
                FROM (
                    SELECT ROUND(
                        LEAST(
                            (tpreise.fVKNetto * " . $currency->fFaktor . ") *
                            ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                            IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $currency->fFaktor . "))) * ((100 + " . $fSteuersatzMax . ") / 100), 2) AS fMax,
                 ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor . ") *
                 ((100 - greatest(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                 IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $currency->fFaktor . "))) * ((100 + " . $fSteuersatzMin . ") / 100), 2) AS fMin
                FROM tartikel
                " . $state->joins . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . $this->getStorageFilter() . "
                    " . $state->conditions . "
                GROUP BY tartikel.kArtikel
                " . $state->having . "
            ) AS ssMerkmal";
            $oPreisspannenFilterMaxMin = Shop::DB()->query($qry, 1);
            if (isset($oPreisspannenFilterMaxMin->fMax) && $oPreisspannenFilterMaxMin->fMax > 0) {
                // Berechnet Max, Min, Step, Anzahl, Diff und liefert diese Werte in einem Objekt
                $oPreis = berechneMaxMinStep($oPreisspannenFilterMaxMin->fMax * $currency->fFaktor, $oPreisspannenFilterMaxMin->fMin * $currency->fFaktor);
                // Begrenzung der Preisspannen bei zu großen Preisdifferenzen
                $oPreis->nAnzahlSpannen = min(20, (int)$oPreis->nAnzahlSpannen);
                $cSelectSQL = '';
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= " SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                }
                $qry = "SELECT " . $cSelectSQL . "
                    FROM
                    (
                        SELECT " . $this->getPriceRangeSQL($oPreis, $currency) . "
                        FROM tartikel " .
                    $state->joins . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = 0
                            " . $this->getStorageFilter() . "
                            " . $state->conditions . "
                        GROUP BY tartikel.kArtikel
                        " . $state->having . "
                    ) AS ssMerkmal
                    ";
                $oPreisspannenFilterDB = Shop::DB()->query($qry, 1);

                $nPreisspannenAnzahl_arr   = (is_object($oPreisspannenFilterDB)) ? get_object_vars($oPreisspannenFilterDB) : null;
                $oPreisspannenFilterDB_arr = [];
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    $sub = ($i === 0)
                        ? 0
                        : ($nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                    $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $sub);
                }
                $nPreisMax      = $oPreis->fMaxPreis;
                $nPreisMin      = $oPreis->fMinPreis;
                $nStep          = $oPreis->fStep;
                $nAnzahlSpannen = (int)$oPreis->nAnzahlSpannen;
                for ($i = 0; $i < $nAnzahlSpannen; ++$i) {
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
                    $oPreisspannenFilter->cVonLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nVon, $currency);
                    $oPreisspannenFilter->cBisLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nBis, $currency);
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
                    $oPreisspannenFilter->cURL               = $this->getURL(true, $oZusatzFilter);
                    $oPreisspanne_arr[]                      = $oPreisspannenFilter;
                }
            }
        } else {
            $oPreisspannenfilter_arr = Shop::DB()->query("SELECT * FROM tpreisspannenfilter", 2);
            if (is_array($oPreisspannenfilter_arr) && count($oPreisspannenfilter_arr) > 0) {
                // Berechnet Max, Min, Step, Anzahl, Diff
                $oPreis = berechneMaxMinStep(
                    $oPreisspannenfilter_arr[count($oPreisspannenfilter_arr) - 1]->nBis * $currency->fFaktor,
                    $oPreisspannenfilter_arr[0]->nVon * $currency->fFaktor
                );
                if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                    $res = [];
//                    Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY]);

                    return $res;
                }
                $cSelectSQL = '';
                $count      = count($oPreisspannenfilter_arr);
                for ($i = 0; $i < $count; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= "SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                }

                $oPreisspannenFilterDB = Shop::DB()->query(
                    "SELECT " . $cSelectSQL . "
                        FROM
                        (
                            SELECT " . $this->getPriceRangeSQL($oPreis, $currency, $oPreisspannenfilter_arr) . "
                                FROM tartikel " .
                                $state->joins . "
                                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                    AND tartikel.kVaterArtikel = 0
                                    " . $this->getStorageFilter() . "
                                    " . $state->where . "
                                GROUP BY tartikel.kArtikel
                                " . $state->having . "
                        ) AS ssMerkmal
                    ", 1
                );
                $nPreisspannenAnzahl_arr   = get_object_vars($oPreisspannenFilterDB);
                $oPreisspannenFilterDB_arr = [];
                if (is_array($nPreisspannenAnzahl_arr)) {
                    $count = count($nPreisspannenAnzahl_arr);
                    for ($i = 0; $i < $count; ++$i) {
                        $sub = ($i === 0)
                            ? 0
                            : ($nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                        $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $sub);
                    }
                }
                foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                    $oPreisspannenfilterTMP                 = new stdClass();
                    $oPreisspannenfilterTMP->nVon           = $oPreisspannenfilter->nVon;
                    $oPreisspannenfilterTMP->nBis           = $oPreisspannenfilter->nBis;
                    $oPreisspannenfilterTMP->nAnzahlArtikel = (int)$oPreisspannenFilterDB_arr[$i];
                    // Localize Preise
                    $oPreisspannenfilterTMP->cVonLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nVon, $currency);
                    $oPreisspannenfilterTMP->cBisLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nBis, $currency);
                    //baue URL
                    $oZusatzFilter                           = new stdClass();
                    $oZusatzFilter->PreisspannenFilter       = new stdClass();
                    $oZusatzFilter->PreisspannenFilter->fVon = $oPreisspannenfilterTMP->nVon;
                    $oZusatzFilter->PreisspannenFilter->fBis = $oPreisspannenfilterTMP->nBis;
                    $oPreisspannenfilterTMP->cURL            = $this->getURL(true, $oZusatzFilter);
                    $oPreisspanne_arr[]                      = $oPreisspannenfilterTMP;
                }
            }
        }
        // Preisspannen ohne Artikel ausblenden (falls im Backend eingestellt)
        if ($this->conf['navigationsfilter']['preisspannenfilter_spannen_ausblenden'] === 'Y') {
            if (count($oPreisspanne_arr) > 0) {
                $oPreisspanneTMP_arr = [];
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
        $oSuchFilterDB_arr = [];
        if ($this->conf['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $nLimit = (isset($this->conf['navigationsfilter']['suchtrefferfilter_anzahl']) && (int)$this->conf['navigationsfilter']['suchtrefferfilter_anzahl'] > 0)
                ? " LIMIT " . (int)$this->conf['navigationsfilter']['suchtrefferfilter_anzahl']
                : '';

            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $state->joins[] = $order->join;
            $join           = new FilterJoin();
            $join->setComment('join1 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcachetreffer')
                 ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcache')
                 ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join3 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchanfrage')
                 ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche AND tsuchanfrage.kSprache = ' . $this->languageID);
            $state->joins[] = $join;

            $state->conditions[] = "tsuchanfrage.nAktiv = 1";

            $query = $this->getBaseQuery(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);

            $query = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;

            $oSuchFilterDB_arr = Shop::DB()->query($query, 2);

            $kSuchanfrage_arr = [];
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
            $count  = count($kSuchanfrage_arr);
            for ($j = 0; $j < $nCount; ++$j) {
                for ($i = 0; $i < $count; ++$i) {
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
            for ($i = 0; $i < $count; ++$i) {
                $oZusatzFilter = new stdClass();
                $oZusatzFilter->SuchFilter = new stdClass();
                $oZusatzFilter->SuchFilter->kSuchanfrage = (int)$oSuchFilterDB_arr[$i]->kSuchanfrage;
                $oSuchFilterDB_arr[$i]->cURL             = $this->getURL(true, $oZusatzFilter);
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
        $oKategorieFilterDB_arr = [];
        if ($this->conf['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
            $order = $this->getOrder();
            $state = $this->getCurrentStateData();

            $state->joins[] = $order->join;

            // Kategoriefilter anzeige
            if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && (!$this->Kategorie->isInitialized())) {
                $kKatFilter = ($this->KategorieFilter->isInitialized()) ? '' : " AND tkategorieartikelgesamt.kOberKategorie = 0";

                $join = new FilterJoin();
                $join->setComment('join1 from getCategoryFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tkategorieartikelgesamt')
                     ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' . $kKatFilter);
                $state->joins[] = $join;

                $join = new FilterJoin();
                $join->setComment('join2 from getCategoryFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tkategorie')
                     ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie');
                $state->joins[] = $join;

            } else {
                if (!$this->Kategorie->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setComment('join3 from getCategoryFilterOptions')
                         ->setType('JOIN')
                         ->setTable('tkategorieartikel')
                         ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');
                    $state->joins[] = $join;
                }
                $join = new FilterJoin();
                $join->setComment('join4 from getCategoryFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tkategorie')
                     ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie');
                $state->joins[] = $join;
            }

            // nicht Standardsprache? Dann hole Namen nicht aus tkategorie sondern aus tkategoriesprache
            $cSQLKategorieSprache        = new stdClass();
            $cSQLKategorieSprache->cJOIN = '';
            $select                      = ['tkategorie.kKategorie', 'tkategorie.nSort'];
            if (!standardspracheAktiv()) {
                $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
                $join     = new FilterJoin();
                $join->setComment('join5 from getCategoryFilterOptions')
                     ->setType('JOIN')
                     ->setTable('tkategoriesprache')
                     ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie AND tkategoriesprache.kSprache = ' . $this->languageID);
                $state->joins[] = $join;
            } else {
                $select[] = "tkategorie.cName";
            }

            $query = $this->getBaseQuery($select, $state->joins, $state->conditions, $state->having, $order->orderBy, '', ['tkategorie.kKategorie', 'tartikel.kArtikel']);
            $query =  "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM (" . $query . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = " .$this->languageID . "
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName";
            $oKategorieFilterDB_arr = Shop::DB()->query($query, 2);
            //baue URL
            $count                          = (is_array($oKategorieFilterDB_arr)) ? count($oKategorieFilterDB_arr) : 0;
            $oZusatzFilter                  = new stdClass();
            $oZusatzFilter->KategorieFilter = new stdClass();
            for ($i = 0; $i < $count; ++$i) {
                // Anzeigen als KategoriePfad
                if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'KP') {
                    $oKategorie                        = new Kategorie($oKategorieFilterDB_arr[$i]->kKategorie);
                    $oKategorieFilterDB_arr[$i]->cName = gibKategoriepfad($oKategorie, $this->customerGroupID, $this->languageID);
                }
                $oZusatzFilter->KategorieFilter->kKategorie = (int)$oKategorieFilterDB_arr[$i]->kKategorie;
                $oZusatzFilter->KategorieFilter->cSeo       = $oKategorieFilterDB_arr[$i]->cSeo;
                $oKategorieFilterDB_arr[$i]->cURL           = $this->getURL(true, $oZusatzFilter);
                $oKategorieFilterDB_arr[$i]->nAnzahl        = (int)$oKategorieFilterDB_arr[$i]->nAnzahl;
                $oKategorieFilterDB_arr[$i]->kKategorie     = (int)$oKategorieFilterDB_arr[$i]->kKategorie;
                $oKategorieFilterDB_arr[$i]->nSort          = (int)$oKategorieFilterDB_arr[$i]->nSort;
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
     * @throws InvalidArgumentException
     */
    private function getBaseQuery($select = ['tartikel.kArtikel'], $joins, $conditions, $having = [], $order = '', $limit = '', $groupBy = ['tartikel.kArtikel'])
    {
        $join = new FilterJoin();
        $join->setComment('article visiblity join from getBaseQuery')
             ->setType('LEFT JOIN')
             ->setTable('tartikelsichtbarkeit')
             ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel AND tartikelsichtbarkeit.kKundengruppe = ' . $this->customerGroupID);
        $joins[] = $join;
        //remove duplicate joins
        $joinedTables = [];
        foreach ($joins as $i => $stateJoin) {
            if (is_string($stateJoin)){
                throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
            }
            if (!in_array($stateJoin->getTable(), $joinedTables)) {
                $joinedTables[] = $stateJoin->getTable();
            } else {
                unset($joins[$i]);
            }
        }
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
            #default conditions
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                #stock filter
                " . $this->getStorageFilter() .
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

    /**
     * @param null|Kategorie $currentCategory
     */
    public function setUserSort($currentCategory = null)
    {
        // Der User möchte die Standardsortierung wiederherstellen
        if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') === 100) {
            unset($_SESSION['Usersortierung']);
            unset($_SESSION['nUsersortierungWahl']);
            unset($_SESSION['UsersortierungVorSuche']);
        }
        // Wenn noch keine Sortierung gewählt wurde => setze Standard-Sortierung aus Option
        if (!isset($_SESSION['Usersortierung']) && isset($this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl']) && isset($this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'])) {
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        // Eine Suche wurde ausgeführt und die Suche wird auf die Suchtreffersuche eingestellt
        if (isset($this->Suche->kSuchCache) && $this->Suche->kSuchCache > 0 && !isset($_SESSION['nUsersortierungWahl'])) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // Kategorie Funktionsattribut
        if (!empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $_SESSION['Usersortierung'] = mappeUsersortierung($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]);
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && intval($_SESSION['UsersortierungVorSuche']) > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->Suchspecial->isInitialized()) {
            // Gibt die Suchspecial$this->conf als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = gibSuchspecialEinstellungMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            if (count($oSuchspecialEinstellung_arr) > 0 && isset($oSuchspecialEinstellung_arr[$this->Suchspecial->getID()]) && $oSuchspecialEinstellung_arr[$this->Suchspecial->getID()] !== -1) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$this->Suchspecial->getID()];
            }
        }
        // Der User hat expliziet eine Sortierung eingestellt
        if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') !== 100) {
            $_SESSION['Usersortierung']         = verifyGPCDataInteger('Sortierung');
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
            setFsession(0, $_SESSION['Usersortierung'], 0);
        }
    }

    /**
     * @param bool $bSeo
     * @param object $oZusatzFilter
     * @param bool   $bCanonical
     * @return string
     */
    private function getURL($bSeo = true, $oZusatzFilter, $bCanonical = false)
    {
        $cSEOURL  = Shop::getURL() . '/';
        // Gibt es zu der Suche bereits eine Suchanfrage?
        if (!empty($this->Suche->cSuche)) {
            $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'cSuche', Shop::DB()->escape($this->Suche->cSuche), 'kSprache', $this->languageID, 'nAktiv', 1, false, 'kSuchanfrage');
            if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
                // Hole alle aktiven Sprachen
                $oSprache_arr = $this->oSprache_arr;
                $bSprache     = (is_array($oSprache_arr) && count($oSprache_arr) > 0);
                $oSeo_arr     = Shop::DB()->selectAll('tseo', ['cKey', 'kKey'], ['kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage], 'cSeo, kSprache',  'kSprache');
                if ($bSprache) {
                    foreach ($oSprache_arr as $oSprache) {
                        $this->Suchanfrage->cSeo[$oSprache->kSprache] = '';
                        if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                            foreach ($oSeo_arr as $oSeo) {
                                if ($oSprache->kSprache == $oSeo->kSprache) {
                                    $this->Suchanfrage->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                                }
                            }
                        }
                    }
                }

                $this->Suchanfrage->kSuchanfrage = (int)$oSuchanfrage->kSuchanfrage;
            }
        }
        // Falls Sort, Artikelanz, Preis, Bewertung oder Tag Filter gesetzt wurde


        if ($this->PreisspannenFilter->isInitialized() ||
            ($this->BewertungFilter->isInitialized()) ||
            (isset($this->SuchFilter->kSuchanfrage) && $this->SuchFilter->kSuchanfrage > 0) ||
            (is_array($this->SuchFilter) && count($this->SuchFilter) > 0) && (!isset($this->EchteSuche->cSuche) || strlen($this->EchteSuche->cSuche) === 0) ||
            ((isset($this->TagFilter) && count($this->TagFilter) > 0) || $this->SuchspecialFilter->isInitialized()) ||
            (isset($oZusatzFilter->PreisspannenFilter->fVon) && isset($oZusatzFilter->PreisspannenFilter->fBis) &&
                $oZusatzFilter->PreisspannenFilter->fVon >= 0 && $oZusatzFilter->PreisspannenFilter->fBis > 0) ||
            (isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) ||
            (isset($oZusatzFilter->BewertungFilter->nSterne) && $oZusatzFilter->BewertungFilter->nSterne > 0) ||
            (isset($oZusatzFilter->TagFilter->kTag) && $oZusatzFilter->TagFilter->kTag > 0) ||
            (!isset($this->Suchanfrage->kSuchanfrage) && (isset($this->Suche->cSuche) && strlen($this->Suche->cSuche) > 0) ||
                (isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) ||
                (isset($oZusatzFilter->SuchFilter->kSuchanfrage) && $oZusatzFilter->SuchFilter->kSuchanfrage > 0))
        ) {
            $bSeo = false;
        }
        $cURL = $cSEOURL . 'index.php?';
        // Mainwords
        if ($this->Kategorie->isInitialized()) {
            if (strlen($this->Kategorie->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            } else {
                $cSEOURL .= $this->Kategorie->getSeo($this->languageID);
            }
            $cURL .= 'k=' . $this->Kategorie->getID();
        } elseif ($this->Hersteller->isInitialized()) {
            $cSEOURL .= $this->Hersteller->getSeo($this->languageID);
            if ($bSeo && strlen($this->Hersteller->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'h=' . $this->Hersteller->getID();
        } elseif ($this->Suchanfrage->isInitialized()) {
            $cSEOURL .= $this->Suchanfrage->getSeo($this->languageID);
            if ($bSeo && strlen($this->Suchanfrage->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'l=' . $this->Suchanfrage->getID();
        } elseif ($this->MerkmalWert->isInitialized()) {
            $cSEOURL .= $this->MerkmalWert->getSeo($this->languageID);
            if ($bSeo && strlen($this->MerkmalWert->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'm=' . $this->MerkmalWert->getID();
        } elseif ($this->Tag->isInitialized()) {
            $cSEOURL .= $this->Tag->getSeo($this->languageID);
            if ($bSeo && strlen($this->Tag->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 't=' . $this->Tag->getID();
        } elseif ($this->Suchspecial->isInitialized()) {
            $cSEOURL .= $this->Suchspecial->getSeo($this->languageID);
            if ($bSeo && strlen($this->Suchspecial->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'q=' . $this->Suchspecial->getID();
        } elseif ($this->News->isInitialized()) {
            $cSEOURL .= $this->News->getSeo($this->languageID);
            if ($bSeo && strlen($this->News->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'n=' . $this->News->getID();
        } elseif ($this->NewsMonat->isInitialized()) {
            $cSEOURL .= $this->NewsMonat->getSeo($this->languageID);
            if ($bSeo && strlen($this->NewsMonat->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'nm=' . $this->NewsMonat->getID();
        } elseif ($this->NewsKategorie->isInitialized()) {
            $cSEOURL .= $this->NewsKategorie->getSeo($this->languageID);
            if ($bSeo && strlen($this->NewsKategorie->getSeo($this->languageID)) === 0) {
                $bSeo = false;
            }
            $cURL .= 'nk=' . $this->NewsKategorie->getID();
        }
        if ((isset($this->EchteSuche->cSuche) && strlen($this->EchteSuche->cSuche) > 0) &&
            (!isset($this->Suchanfrage->kSuchanfrage) || intval($this->Suchanfrage->kSuchanfrage) === 0)
        ) {
            $bSeo = false;
            $cURL .= 'suche=' . urlencode($this->EchteSuche->cSuche);
        }
        // Filter
        // Kategorie
        if (!$bCanonical) {
            if ($this->KategorieFilter->isInitialized() && (!$this->Kategorie->isInitialized() || $this->Kategorie->getID() !== $this->KategorieFilter->getID()) ) {
                if (!isset($oZusatzFilter->FilterLoesen->Kategorie) || !$oZusatzFilter->FilterLoesen->Kategorie) {
                    if (strlen($this->KategorieFilter->getSeo($this->languageID)) === 0) {
                        $bSeo = false;
                    }
                    if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && !empty($oZusatzFilter->KategorieFilter->kKategorie)) {
                        if (!empty($oZusatzFilter->KategorieFilter->cSeo)) {
                            $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
                        } else {
                            $cSEOURL .= SEP_KAT . $this->KategorieFilter->getSeo($this->languageID);
                        }
                        $cURL .= '&amp;kf=' . $oZusatzFilter->KategorieFilter->kKategorie;
                    } else {
                        $cSEOURL .= SEP_KAT . $this->KategorieFilter->getSeo($this->languageID);
                        $cURL .= '&amp;kf=' . $this->KategorieFilter->getID();
                    }
                }
            } elseif ((isset($oZusatzFilter->KategorieFilter->kKategorie) && $oZusatzFilter->KategorieFilter->kKategorie > 0) &&
                (!$this->Kategorie->isInitialized() || $this->Kategorie->getID() !== $oZusatzFilter->KategorieFilter->kKategorie)
            ) {
                $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
                $cURL .= '&amp;kf=' . $oZusatzFilter->KategorieFilter->kKategorie;
            }
            // Hersteller
            if ($this->HerstellerFilter->isInitialized() && (!$this->Hersteller->isInitialized() || $this->Hersteller->getID() !== $this->HerstellerFilter->getID())) {
                if (empty($oZusatzFilter->FilterLoesen->Hersteller)) {
                    $cSEOURL .= SEP_HST . $this->HerstellerFilter->getSeo($this->languageID);
                    if ($bSeo && strlen($this->HerstellerFilter->getSeo($this->languageID)) === 0) {
                        $bSeo = false;
                    }
                    $cURL .= '&amp;hf=' . $this->HerstellerFilter->getID();
                }
            } elseif (!empty($oZusatzFilter->HerstellerFilter->kHersteller) && (!$this->Hersteller->isInitialized() || $this->Hersteller->getID() !== $oZusatzFilter->HerstellerFilter->kHersteller)) {
                $cSEOURL .= SEP_HST . $oZusatzFilter->HerstellerFilter->cSeo;
                $cURL .= '&amp;hf=' . $oZusatzFilter->HerstellerFilter->kHersteller;
            }
            // Suche
            $nLetzterSuchFilter   = 1;
            $bZusatzSuchEnthalten = false;
            $oSuchanfrage_arr     = [];
            if (isset($this->SuchFilter) && is_array($this->SuchFilter) && count($this->SuchFilter) > 0) {
                foreach ($this->SuchFilter as $i => $oSuchFilter) {
                    if (isset($oSuchFilter->kSuchanfrage) && $oSuchFilter->kSuchanfrage > 0) {
                        if (isset($oZusatzFilter->FilterLoesen->SuchFilter) && $oZusatzFilter->FilterLoesen->SuchFilter != $oSuchFilter->kSuchanfrage) {
                            $bSeo = false;
                            if ($oSuchFilter->kSuchanfrage != $this->Suche->kSuchanfrage) {
                                $oSuchanfrage_arr[$i]->kSuchanfrage = $oSuchFilter->kSuchanfrage;
                            }
                            ++$nLetzterSuchFilter;
                            if ($oSuchFilter->kSuchanfrage == $oZusatzFilter->SuchFilter->kSuchanfrage) {
                                $bZusatzSuchEnthalten = true;
                            }
                        }
                    }
                }
            }
            // Zusatz SuchFilter
            if (!empty($oZusatzFilter->SuchFilter->kSuchanfrage) && !$bZusatzSuchEnthalten) {
                $nPos = count($oSuchanfrage_arr);
                if (!isset($oSuchanfrage_arr[$nPos])) {
                    $oSuchanfrage_arr[$nPos] = new stdClass();
                }
                $oSuchanfrage_arr[$nPos]->kSuchanfrage = $oZusatzFilter->SuchFilter->kSuchanfrage;
            }
            // Baue SuchFilter-URL
            $oSuchanfrage_arr = sortiereFilter($oSuchanfrage_arr, 'kSuchanfrage');
            if (is_array($oSuchanfrage_arr) && count($oSuchanfrage_arr) > 0) {
                foreach ($oSuchanfrage_arr as $i => $oSuchanfrage) {
                    $cURL .= '&amp;sf' . ($i + 1) . '=' . (int)$oSuchanfrage->kSuchanfrage;
                }
            }
            // Merkmale
            $nLetzterMerkmalFilter   = 1;
            $bZusatzMerkmalEnthalten = false;
            $oMerkmalWert_arr        = [];
            foreach ($this->MerkmalFilter as $i => $oMerkmalFilter) {
                if ($oMerkmalFilter->isInitialized() > 0) {
                    if ((!isset($oZusatzFilter->FilterLoesen->Merkmale)) || $oZusatzFilter->FilterLoesen->Merkmale != $oMerkmalFilter->kMerkmal) {
                        if ((!isset($oZusatzFilter->FilterLoesen->MerkmalWert) && isset($oMerkmalFilter->kMerkmalWert)) ||
                            $oZusatzFilter->FilterLoesen->MerkmalWert != $oMerkmalFilter->kMerkmalWert) {
                            if (strlen($oMerkmalFilter->cSeo[$this->languageID]) === 0) {
                                $bSeo = false;
                            }
                            $oMerkmalWert_arr[$i]               = new stdClass();
                            $oMerkmalWert_arr[$i]->kMerkmalWert = (int)$oMerkmalFilter->kMerkmalWert;
                            $oMerkmalWert_arr[$i]->cSeo         = $oMerkmalFilter->cSeo[$this->languageID];
                            ++$nLetzterMerkmalFilter;
                            if (isset($oMerkmalFilter->kMerkmalWert) && isset($oZusatzFilter->MerkmalFilter->kMerkmalWert) && $oMerkmalFilter->kMerkmalWert == $oZusatzFilter->MerkmalFilter->kMerkmalWert) {
                                $bZusatzMerkmalEnthalten = true;
                            }
                        }
                    }
                }
            }
            // Zusatz MerkmalFilter
            if (isset($oZusatzFilter->MerkmalFilter->kMerkmalWert) && $oZusatzFilter->MerkmalFilter->kMerkmalWert > 0 && !$bZusatzMerkmalEnthalten) {
                $nPos                                  = count($oMerkmalWert_arr);
                $oMerkmalWert_arr[$nPos]               = new stdClass();
                $oMerkmalWert_arr[$nPos]->kMerkmalWert = (int)$oZusatzFilter->MerkmalFilter->kMerkmalWert;
                $oMerkmalWert_arr[$nPos]->cSeo         = $oZusatzFilter->MerkmalFilter->cSeo;
            }
            // Baue MerkmalFilter URL
            $oMerkmalWert_arr = sortiereFilter($oMerkmalWert_arr, 'kMerkmalWert');
            if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
                foreach ($oMerkmalWert_arr as $i => $oMerkmalWert) {
                    $cSEOURL .= SEP_MERKMAL . $oMerkmalWert->cSeo;
                    $cURL .= '&amp;mf' . ($i + 1) . '=' . (int)$oMerkmalWert->kMerkmalWert;
                }
            }
            // Preisspannen
            if (isset($this->PreisspannenFilter->fVon) && $this->PreisspannenFilter->fVon >= 0 &&
                isset($this->PreisspannenFilter->fBis) && $this->PreisspannenFilter->fBis > 0 &&
                !isset($oZusatzFilter->FilterLoesen->Preisspannen)
            ) {
                $cURL .= '&amp;pf=' . $this->PreisspannenFilter->fVon . '_' . $this->PreisspannenFilter->fBis;
            } elseif (isset($oZusatzFilter->PreisspannenFilter->fVon) && $oZusatzFilter->PreisspannenFilter->fVon >= 0 &&
                isset($oZusatzFilter->PreisspannenFilter->fBis) && $oZusatzFilter->PreisspannenFilter->fBis > 0
            ) {
                $cURL .= '&amp;pf=' . $oZusatzFilter->PreisspannenFilter->fVon . '_' . $oZusatzFilter->PreisspannenFilter->fBis;
            }
            // Bewertung
            if (isset($this->BewertungFilter->nSterne) && $this->BewertungFilter->nSterne > 0 &&
                !isset($oZusatzFilter->FilterLoesen->Bewertungen) && !isset($oZusatzFilter->BewertungFilter->nSterne)
            ) {
                $cURL .= '&amp;bf=' . $this->BewertungFilter->getID();
            } elseif (isset($oZusatzFilter->BewertungFilter->nSterne) && $oZusatzFilter->BewertungFilter->nSterne > 0) {
                $cURL .= '&amp;bf=' . $oZusatzFilter->BewertungFilter->nSterne;
            }
            // Tag
            $nLetzterTagFilter   = 1;
            $bZusatzTagEnthalten = false;
            $oTag_arr            = [];
            if (!isset($oZusatzFilter->FilterLoesen->Tags)) {
                if (isset($this->TagFilter) && is_array($this->TagFilter)) {
                    foreach ($this->TagFilter as $i => $oTagFilter) {
                        if ($oTagFilter->kTag > 0) {
                            if (!isset($oTag_arr[$i])) {
                                $oTag_arr[$i] = new stdClass();
                            }
                            $oTag_arr[$i]->kTag = (int)$oTagFilter->kTag;
                            ++$nLetzterTagFilter;
                            if (isset($oZusatzFilter->TagFilter->kTag) && $oTagFilter->kTag == $oZusatzFilter->TagFilter->kTag) {
                                $bZusatzTagEnthalten = true;
                            }
                        }
                    }
                }
            }
            // Zusatz Tagfilter
            if (isset($oZusatzFilter->TagFilter->kTag) && $oZusatzFilter->TagFilter->kTag > 0 && !$bZusatzTagEnthalten) {
                //$cURL .= "&amp;tf" . $nLetzterTagFilter . "=" . $oZusatzFilter->TagFilter->kTag;
                $nPos = count($oTag_arr);
                if (!isset($oTag_arr[$nPos])) {
                    $oTag_arr[$nPos] = new stdClass();
                }
                $oTag_arr[$nPos]->kTag = (int)$oZusatzFilter->TagFilter->kTag;
            }
            // Baue TagFilter URL
            $oTag_arr = sortiereFilter($oTag_arr, 'kTag');
            if (is_array($oTag_arr) && count($oTag_arr) > 0) {
                foreach ($oTag_arr as $i => $oTag) {
                    $cURL .= '&amp;tf' . ($i + 1) . '=' . (int)$oTag->kTag;
                }
            }
            // Suchspecialfilter
            if ($this->SuchspecialFilter->isInitialized() && (!$this->Suchspecial->isInitialized() || $this->Suchspecial->getID() !== $this->SuchspecialFilter->getID()) ) {
                if (!isset($oZusatzFilter->FilterLoesen->Suchspecials) || !$oZusatzFilter->FilterLoesen->Suchspecials) {
                    $cSEOURL .= $this->SuchspecialFilter->getSeo($this->languageID);
                    if ($bSeo && strlen($this->SuchspecialFilter->getSeo($this->languageID)) === 0) {
                        $bSeo = false;
                    }
                    $cURL .= '&amp;qf=' . $this->SuchspecialFilter->getID();
                }
            } elseif ((isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) &&
                (!$this->Suchspecial->isInitialized() || $this->Suchspecial->getID() !== $oZusatzFilter->SuchspecialFilter->kKey)
            ) {
                $cURL .= '&amp;qf=' . $oZusatzFilter->SuchspecialFilter->kKey;
            }
        }

        if (strlen($cSEOURL) > 254) {
            $bSeo = false;
        }

        if ($bSeo) {
            return $cSEOURL;
        }
        if ($this->languageID != Shop::$kSprache) {
            //@todo@todo: this will probably never happen..?
            $cISOSprache = '';
            if (isset($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0) {
                foreach ($_SESSION['Sprachen'] as $i => $oSprache) {
                    if ($oSprache->kSprache == $this->languageID) {
                        $cISOSprache = $oSprache->cISO;
                    }
                }
            }

            return $cURL . '&amp;lang=' . $cISOSprache;
        }

        return $cURL;
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    private function attributeValueIsActive($kMerkmalWert)
    {
        foreach ($this->MerkmalFilter as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->getID() === $kMerkmalWert) {
                return true;
            }
        }

        return false;
    }
}
