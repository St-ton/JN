<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';

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
    private $conf;
    /**
     * @var array
     */
    public $oSprache_arr;

    /**
     * @var FilterCategory
     */
    public $Kategorie;

    /**
     * @var FilterCategoryFilter
     */
    public $KategorieFilter;

    /**
     * @var FilterManufacturer
     */
    public $Hersteller;

    /**
     * @var FilterManufacturerFilter
     */
    public $HerstellerFilter;

    /**
     * @var FilterAttribute
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
     * @var FilterAttributeFilter[]
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
    public $nSeite = 1;

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
     * @var array
     */
    private $params = [];

    /**
     * @var IFilter[]
     */
    private $filters = [];

    /**
     * @var IFilter[]
     */
    private $activeFilters = [];

    /**
     * @var null
     */
    private $baseState = null;

    /**
     * @var stdClass
     */
    public $URL;

    /**
     * @var FilterTagFilter
     */
    public $tagFilterCompat;

    /**
     * @var FilterAttributeFilter
     */
    public $attributeFilterCompat;

    /**
     * @var FilterSearch
     */
    public $searchFilterCompat;

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
            CONF_SUCHSPECIAL,
            CONF_METAANGABEN
        ]);
        $this->languageID   = Shop::getLanguage();
        if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $oKundengruppe         = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
            $this->customerGroupID = (int)$oKundengruppe->kKundengruppe;
        } else {
            $this->customerGroupID = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        }
        $this->initBaseStates();

        $urls                          = new stdClass();
        $urls->cAllePreisspannen       = '';
        $urls->cAlleBewertungen        = '';
        $urls->cAlleTags               = '';
        $urls->cAlleSuchspecials       = '';
        $urls->cAlleErscheinungsdatums = '';
        $urls->cAlleKategorien         = '';
        $urls->cAlleHersteller         = '';
        $urls->cAlleMerkmale           = [];
        $urls->cAlleMerkmalWerte       = [];
        $urls->cAlleSuchFilter         = [];
        $urls->cNoFilter               = null;

        $this->URL = $urls;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->conf;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID()
    {
        return $this->customerGroupID;
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
        return $this->languageID;
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
     * @return FilterManufacturer|FilterCategory|FilterAttribute|FilterSearch|FilterSearchSpecial|FilterDummyState
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
        if ($this->Suche->isInitialized()) {
            return $this->Suche;
        }

        return new FilterDummyState();
    }

    /**
     * @return $this
     */
    private function initBaseStates()
    {
        $languageID      = $this->getLanguageID();
        $customerGroupID = $this->getCustomerGroupID();
        $config          = $this->getConfig();

        $this->Kategorie       = new FilterCategory($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->KategorieFilter = new FilterCategoryFilter($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->HerstellerFilter = new FilterManufacturerFilter($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->Hersteller       = new FilterManufacturer($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suchanfrage = new FilterSearchQuery($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->MerkmalWert = new FilterAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Tag = new FilterTag($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->News = new FilterNews($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->NewsMonat = new FilterNewsOverview($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->NewsKategorie = new FilterNewsCategory($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suchspecial = new FilterSearchSpecial($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->MerkmalFilter = [];
        $this->SuchFilter    = [];
        $this->TagFilter     = [];

        $this->SuchspecialFilter = new FilterSearchSpecialFilter($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->BewertungFilter = new FilterRating($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->PreisspannenFilter = new FilterPriceRange($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->tagFilterCompat       = new FilterTagFilter($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->attributeFilterCompat = new FilterAttributeFilter($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->searchFilterCompat    = new FilterSearch($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suche = new FilterSearch($languageID, $customerGroupID, $config, $this->oSprache_arr);
        executeHook(HOOK_NAVIGATIONSFILTER_INIT, ['navifilter' => $this]);

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates(&$params)
    {
        $this->params    = $params;
        $languageID      = $this->getLanguageID();
        $customerGroupID = $this->getCustomerGroupID();
        $config          = $this->getConfig();
        if ($params['kKategorie'] > 0) {
            $this->Kategorie->init($params['kKategorie']);
            $this->baseState = $this->Kategorie;
        }
        if ($params['kKategorieFilter'] > 0) {
            $this->addActiveFilter($this->KategorieFilter, $params['kKategorieFilter']);
        }
        if ($params['kHersteller'] > 0) {
            $this->Hersteller->init($params['kHersteller']);
            $this->baseState = $this->Hersteller;
        }
        if ($params['kHerstellerFilter'] > 0) {
            $this->addActiveFilter($this->HerstellerFilter, $params['kHerstellerFilter']);
        }
        if ($params['kSuchanfrage'] > 0) {
            $this->Suchanfrage->init($params['kSuchanfrage']);
            $this->baseState = $this->Suchanfrage;
            $oSuchanfrage    = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->Suche->init($params['kSuchanfrage']);
                $this->Suche->cSuche = $oSuchanfrage->cSuche;
            }
        }
        if ($params['kMerkmalWert'] > 0) {
            $this->MerkmalWert = (new FilterAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr))->init($params['kMerkmalWert']);
            $this->baseState   = $this->MerkmalWert;
        }
        if (count($params['MerkmalFilter_arr']) > 0) {
            foreach ($params['MerkmalFilter_arr'] as $mmf) {
                $this->MerkmalFilter[] = $this->addActiveFilter(new FilterAttributeFilter(), $mmf);
            }
        }
        if ($params['kTag'] > 0) {
            $this->Tag->init($params['kTag']);
            $this->baseState = $this->Tag;
        }
        if (count($params['TagFilter_arr']) > 0) {
            foreach ($params['TagFilter_arr'] as $tf) {
                $this->TagFilter[] = $this->addActiveFilter(new FilterTagFilter(), $tf);
            }
        }
        if ($params['kNews'] > 0) {
            $this->News->init($params['kNews']);
        }
        if ($params['kNewsMonatsUebersicht'] > 0) {
            $this->NewsMonat->init($params['kNewsMonatsUebersicht']);
        }
        if ($params['kNewsKategorie'] > 0) {
            $this->NewsKategorie->init($params['kNewsKategorie']);
        }
        if ($params['kSuchspecial'] > 0) {
            $this->Suchspecial->init($params['kSuchspecial']);
            $this->baseState = $this->Suchspecial;
        }
        if ($params['kSuchspecialFilter'] > 0) {
            $this->addActiveFilter($this->SuchspecialFilter, $params['kSuchspecialFilter']);
        }

        if (count($params['SuchFilter_arr']) > 0) {
            //@todo - same as suchfilter?
            foreach ($params['SuchFilter_arr'] as $sf) {
                $this->SuchFilter[] = $this->addActiveFilter(new FilterSearch(), $sf);
            }
        }

        if ($params['nBewertungSterneFilter'] > 0) {
            $this->addActiveFilter($this->BewertungFilter, $params['nBewertungSterneFilter']);
        }
        if (strlen($params['cPreisspannenFilter']) > 0) {
            $this->addActiveFilter($this->PreisspannenFilter, $params['cPreisspannenFilter']);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] > 0) {
            $this->nAnzahlProSeite = (int)$params['nArtikelProSeite'];
        }
        if (strlen($params['cSuche']) > 0) {
            $params['cSuche'] = StringHandler::filterXSS($params['cSuche']);
            $this->Suche->init($params['kSuchanfrage']);
            $this->Suche->cSuche      = $params['cSuche'];
            $this->EchteSuche         = new stdClass();
            $this->EchteSuche->cSuche = $params['cSuche'];
        }
        if (!empty($this->Suche->cSuche)) {
            //@todo?
            $this->Suche->kSuchCache = bearbeiteSuchCache($this);
            $this->baseState         = $this->Suche;
        }
        $this->nSeite = max(1, verifyGPCDataInteger('seite'));
        foreach ($this->filters as $filter) {
            //auto init custom filters
            if ($filter->isCustom()) {
                $filterParam = $filter->getUrlParam();
                if (isset($_GET[$filterParam])) {
                    if (
                        ($filter->getType() === AbstractFilter::FILTER_TYPE_AND &&
                            (verifyGPCDataInteger($filterParam) > 0 || verifyGPDataString($filterParam) !== '')) ||
                        ($filter->getType() === AbstractFilter::FILTER_TYPE_OR && is_array($_GET[$filterParam]))
                    ) {
                        if (is_array($_GET[$filterParam])) {
                            $filterValue = [];
                            foreach ($_GET[$filterParam] as $idx => $param) {
                                $filterValue[$idx] = Shop::DB()->realEscape($param);
                            }
                        } else {
                            $filterValue = Shop::DB()->realEscape($_GET[$filterParam]);
                        }
                        $this->addActiveFilter($filter, $filterValue);
                        $params[$filterParam] = $filterValue;
                    }
                }
            }
        }
        executeHook(HOOK_NAVIGATIONSFILTER_INIT_FILTER, ['navifilter' => $this, 'params' => $params]);

        return $this->validate();
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function registerFilter(IFilter $filter)
    {
        $filter->setData($this->getLanguageID(), $this->getCustomerGroupID(), $this->getConfig(), $this->oSprache_arr);
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param string $filterName
     * @return IFilter
     * @throws Exception
     */
    public function registerFilterByClassName($filterName)
    {
        $filter = null;
        if (class_exists($filterName)) {
            /** @var IFilter $filter */
            $filter = new $filterName($this->getLanguageID(), $this->getCustomerGroupID(), $this->getConfig());
            $filter->setClassName($filterName);
            $this->filters[] = $filter;
        } else {
            throw new Exception('Cannot register filter class ' . $filterName);
        }

        return $filter;
    }

    /**
     * @param IFilter $filter
     * @param mixed   $filterValue - shortcut to set active value (same as calling init($filterValue)
     * @return IFilter
     */
    public function addActiveFilter(IFilter $filter, $filterValue)
    {
        $filter->setData($this->getLanguageID(), $this->getCustomerGroupID(), $this->getConfig(), $this->oSprache_arr)
               ->init($filterValue);
        $this->activeFilters[] = $filter;
        ++$this->nAnzahlFilter;

        return $filter;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function enableFilter(IFilter $filter)
    {
        foreach ($this->filters as $idx => $registeredFilter) {
            if ($filter->getName() === $registeredFilter->getName()) {
                $this->filters[$idx] = $filter;
            }
        }
        $this->activeFilters[] = $filter;
        ++$this->nAnzahlFilter;

        return $this;
    }

    /**
     * @param string $filterClassName
     * @return int|null
     */
    public function getFilterValue($filterClassName)
    {
        foreach ($this->activeFilters as $filter) {
            if ($filterClassName === $filter->getClassName()) {
                return $filter->getValue();
            }
        }

        return null;
    }

    /**
     * @param string $filterName
     * @return bool
     */
    public function hasFilter($filterName) {
        foreach ($this->activeFilters as $filter) {
            if ($filterName === $filter->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filterClassName
     * @return IFilter|null
     */
    public function getFilterByClassName($filterClassName)
    {
        foreach ($this->filters as $filter) {
            if ($filter->getClassName() === $filterClassName) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @return IFilter[]
     */
    public function getAvailableFilters()
    {
        return $this->filters;
    }

    /**
     * @return int
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
    public function hasAttributeValue()
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
                !isset($this->Suche->cSuche) && !$this->hasAttributeValue() && !$this->hasSearchSpecial()
            ) {
                //we have a manufacturer filter that doesn't filter anything
                if ($this->HerstellerFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->HerstellerFilter->getSeo($this->getLanguageID()));
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if ($this->KategorieFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $this->KategorieFilter->getSeo($this->getLanguageID()));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() && $this->Hersteller->getSeo($this->getLanguageID()) !== null) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Hersteller->getSeo($this->getLanguageID()));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() && $this->Kategorie->getSeo($this->getLanguageID()) !== null) {
                //we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $this->Kategorie->getSeo($this->getLanguageID()));
                exit();
            }
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getOrder()
    {
        $Artikelsortierung = $this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        $sort              = new stdClass();
        $sort->join        = new FilterJoin();
        if (isset($_SESSION['Usersortierung'])) {
            $Artikelsortierung          = $this->mapUserSorting($_SESSION['Usersortierung']);
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
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID());
                break;
            case SEARCH_SORT_PRICE_DESC:
                $sort->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
                $sort->join    = new FilterJoin();
                $sort->join->setComment('join from SORT by price DESC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID());
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
            $limit = (int)$_SESSION['ArtikelProSeite'];
        } elseif ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
            $limit = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = (($max = $this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) > 0)
                ? (int)$max
                : 20;
        }

        return min($limit, ARTICLES_PER_PAGE_HARD_LIMIT);
    }

    /**
     * @return string
     */
    public function getStorageFilter()
    {
        $filterSQL  = '';
        $filterType = (int)$this->conf['global']['artikel_artikelanzeigefilter'];
        if ($filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerVariation = 'Y')";
        } elseif ($filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerKleinerNull = 'Y' OR tartikel.cLagerVariation = 'Y')";
        }
        executeHook(HOOK_STOCK_FILTER, [
            'conf'      => $filterType,
            'filterSQL' => &$filterSQL
        ]);

        return $filterSQL;
    }

    /**
     * @return array
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

        $order          = $this->getOrder();
        $state          = $this->getCurrentStateData();
        $state->joins[] = $order->join;

        $query = $this->getBaseQuery(['tartikel.kArtikel'], $state->joins, $state->conditions, $state->having, $order->orderBy);

        return Shop::DB()->query($query, 2);
    }

    /**
     * @param bool $forProductListing
     * @return stdClass
     */
    public function getProducts($forProductListing = true)
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
            $this->Suche->kSuchanfrage = gibSuchanfrageKey($this->Suche->cSuche, $this->getLanguageID());
        }

        $nLimitN = $nArtikelProSeite * ($this->nSeite - 1);
        $max     = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];

        $oSuchergebnisse->ArtikelVon = $nLimitN + 1;
        $oSuchergebnisse->ArtikelBis = min($nLimitN + $nArtikelProSeite, $oSuchergebnisse->GesamtanzahlArtikel);

        $oSuchergebnisse->Seitenzahlen                = new stdClass();
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $this->nSeite;
        $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $nArtikelProSeite);
        $oSuchergebnisse->Seitenzahlen->minSeite      = min(intval($oSuchergebnisse->Seitenzahlen->AktuelleSeite - $max / 2), 0);
        $oSuchergebnisse->Seitenzahlen->maxSeite      = max($oSuchergebnisse->Seitenzahlen->MaxSeiten,
            $oSuchergebnisse->Seitenzahlen->minSeite + $max - 1);
        if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
            $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
        }

        return ($forProductListing === true) ? $oSuchergebnisse : $oSuchergebnisse->Artikel->elemente;
    }

    /**
     * @param bool $byType
     * @return array|IFilter[]
     */
    public function getActiveFilters($byType = false)
    {
        $filters = ($byType !== false)
            ? ['kf' => [], 'mm' => [], 'ssf' => [], 'tf' => [], 'sf' => [], 'hf' => [], 'bf' => [], 'custom' => [], 'misc' => []]
            : [];
        foreach ($this->activeFilters as $activeFilter) {
            //get custom filters
            if ($activeFilter->isCustom()) {
                if ($byType) {
                    $filters['custom'][] = $activeFilter;
                } else {
                    $filters[] = $activeFilter;
                }
            } else {
                //get build-in filters
                $found = false;
                if ($this->KategorieFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['kf'][] = $this->KategorieFilter;
                    } else {
                        $filters[] = $this->KategorieFilter;
                    }
                } elseif ($this->HerstellerFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['hf'][] = $this->HerstellerFilter;
                    } else {
                        $filters[] = $this->HerstellerFilter;
                    }
                } elseif ($this->BewertungFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['bf'][] = $this->BewertungFilter;
                    } else {
                        $filters[] = $this->BewertungFilter;
                    }
                } elseif ($this->PreisspannenFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['pf'][] = $this->PreisspannenFilter;
                    } else {
                        $filters[] = $this->PreisspannenFilter;
                    }
                } elseif ($this->SuchspecialFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['ssf'][] = $this->SuchspecialFilter;
                    } else {
                        $filters[] = $this->SuchspecialFilter;
                    }
                }
                foreach ($this->MerkmalFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['mm'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->TagFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['tf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->SuchFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['sf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                //get built-in filters that were manually set
                if ($found === false) {
                    if ($byType) {
                        $filters['misc'][] = $activeFilter;
                    } else {
                        $filters[] = $activeFilter;
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return stdClass
     */
    public function getCurrentStateData($ignore = null)
    {
        $state            = $this->getActiveState();
        $stateCondition   = $state->getSQLCondition();
        $stateJoin        = $state->getSQLJoin();
        $data             = new stdClass();
        $data->having     = [];
        $data->conditions = [];
        $data->joins      = (is_array($stateJoin))
            ? $stateJoin
            : [$stateJoin];
        if (!empty($stateCondition)) {
            $data->conditions[] = $stateCondition;
        }
        foreach ($this->getActiveFilters(true) as $type => $filter) {
            $count = count($filter);
            if ($count > 1) {
                $singleConditions = [];
                /** @var AbstractFilter $item */
                foreach ($filter as $idx => $item) {
                    if ($ignore === null || $item->getClassName() !== $ignore) {
                        if ($idx === 0) {
                            $itemJoin = $item->getSQLJoin();
                            if (is_array($itemJoin)) {
                                foreach ($item->getSQLJoin() as $filterJoin) {
                                    $data->joins[] = $filterJoin;
                                }
                            } else {
                                $data->joins[] = $itemJoin;
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
            } elseif ($count === 1) {
                /** @var array(AbstractFilter) $filter */
                if ($ignore === null || $filter[0]->getClassName() !== $ignore) {
                    $itemJoin = $filter[0]->getSQLJoin();
                    if (is_array($itemJoin)) {
                        foreach ($itemJoin as $filterJoin) {
                            $data->joins[] = $filterJoin;
                        }
                    } else {
                        $data->joins[] = $itemJoin;
                    }

                    $data->conditions[] = "\n#condition from filter " . $type . "\n" . $filter[0]->getSQLCondition();
                }
            }
        }

        return $data;
    }

    /**
     * @param object         $oSuchergebnisse
     * @param null|Kategorie $AktuelleKategorie
     * @return mixed
     */
    public function setFilterOptions($oSuchergebnisse, $AktuelleKategorie = null)
    {
        $oSuchergebnisse->Herstellerauswahl = $this->HerstellerFilter->getOptions();
        $oSuchergebnisse->Bewertung         = $this->BewertungFilter->getOptions();
        $oSuchergebnisse->Tags              = $this->Tag->getOptions();

        if ($this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
            $oTags_arr = [];
            foreach ($oSuchergebnisse->Tags as $key => $oTags) {
                $oTags_arr[$key]       = $oTags;
                $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
            }
            $oSuchergebnisse->TagsJSON = Boxen::gibJSONString($oTags_arr);

        }
        $oSuchergebnisse->MerkmalFilter    = $this->attributeFilterCompat->getOptions([
            'oAktuelleKategorie' => $AktuelleKategorie,
            'bForce'             => function_exists('starteAuswahlAssistent')
        ]);
        $oSuchergebnisse->Preisspanne      = $this->PreisspannenFilter->getOptions($oSuchergebnisse->GesamtanzahlArtikel);
        $oSuchergebnisse->Kategorieauswahl = $this->KategorieFilter->getOptions();
        $oSuchergebnisse->SuchFilter       = $this->searchFilterCompat->getOptions();
        $oSuchergebnisse->SuchFilterJSON   = [];

        foreach ($oSuchergebnisse->SuchFilter as $key => $oSuchfilter) {
            $oSuchergebnisse->SuchFilterJSON[$key]       = $oSuchfilter;
            $oSuchergebnisse->SuchFilterJSON[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
        }
        $oSuchergebnisse->SuchFilterJSON = Boxen::gibJSONString($oSuchergebnisse->SuchFilterJSON);


        $oSuchergebnisse->Suchspecialauswahl = (!$this->params['kSuchspecial'] && !$this->params['kSuchspecialFilter'])
            ? $this->SuchspecialFilter->getOptions()
            : null;

        $oSuchergebnisse->customFilters = [];
        foreach($this->filters as $filter) {
            $filterObject                     = new stdClass();
            $filterObject->cClassname         = $filter->getClassName();
            $filterObject->cName              = $filter->getName();
            $filterObject->value              = $filter->getValue();
            $filterObject->filterOptions      = $filter->getOptions();
            $oSuchergebnisse->customFilters[] = $filterObject;
        }

        return $oSuchergebnisse;
    }

    /**
     * @param array $oMerkmalauswahl_arr
     * @param int   $kMerkmal
     * @return int
     */
    public function getAttributePosition($oMerkmalauswahl_arr, $kMerkmal)
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
     * @return string
     */
    public function getHeader()
    {
        $this->cBrotNaviName = '';
        if ($this->Kategorie->isInitialized()) {
            $this->cBrotNaviName = $this->Kategorie->getName();
        } elseif ($this->Hersteller->isInitialized()) {
            $this->cBrotNaviName = $this->Hersteller->getName();
        } elseif ($this->MerkmalWert->isInitialized()) {
            $this->cBrotNaviName = $this->MerkmalWert->getName();
        } elseif ($this->Tag->isInitialized()) {
            $this->cBrotNaviName = $this->Tag->getName();
        } elseif ($this->Suchspecial->isInitialized()) {
            $this->cBrotNaviName = $this->Suchspecial->getName();
        } elseif ($this->Suche->isInitialized()) {
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
    public function getBaseQuery(
        $select = ['tartikel.kArtikel'],
        $joins,
        $conditions,
        $having = [],
        $order = '',
        $limit = '',
        $groupBy = ['tartikel.kArtikel'],
        $or = false
    ) {
        $join = new FilterJoin();
        $join->setComment('article visiblity join from getBaseQuery')
             ->setType('LEFT JOIN')
             ->setTable('tartikelsichtbarkeit')
             ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID());
        $joins[] = $join;
        //remove duplicate joins
        $joinedTables = [];
        foreach ($joins as $i => $stateJoin) {
            if (is_string($stateJoin)) {
                throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
            }
            if (!in_array($stateJoin->getTable(), $joinedTables)) {
                $joinedTables[] = $stateJoin->getTable();
            } else {
                unset($joins[$i]);
            }
        }
        if ($or === true) {
            //testing
            $conditions = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('NOT(' . implode(' OR ', $a) . ')');
            }, $conditions));
        } else {
            $conditions = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('(' . implode(' OR ', $a) . ')');
            }, $conditions));
        }

        $joins      = implode("\n", $joins);
        $having     = implode(' AND ', $having);
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
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
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
            $_SESSION['Usersortierung'] = $this->mapUserSorting($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]);
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && intval($_SESSION['UsersortierungVorSuche']) > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->Suchspecial->isInitialized()) {
            // Gibt die Suchspecials als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = gibSuchspecialEinstellungMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            if (count($oSuchspecialEinstellung_arr) > 0 && isset($oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()]) && $oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()] !== -1) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()];
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
     * @param bool   $bSeo
     * @param object $oZusatzFilter
     * @param bool   $bCanonical
     * @param bool   $debug
     * @return string
     */
    public function getURL($bSeo = true, $oZusatzFilter, $bCanonical = false, $debug = false)
    {
        $cSEOURL = Shop::getURL() . '/';
        if ($debug === true) {
            Shop::dbg($bSeo, false, 'bseo0:');
        }
        // Gibt es zu der Suche bereits eine Suchanfrage?
        if (!empty($this->Suche->cSuche)) {
            $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'cSuche', Shop::DB()->escape($this->Suche->cSuche),
                'kSprache', $this->getLanguageID(), 'nAktiv', 1, false, 'kSuchanfrage');
            if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
                // Hole alle aktiven Sprachen
                $oSprache_arr = $this->oSprache_arr;
                $bSprache     = (is_array($oSprache_arr) && count($oSprache_arr) > 0);
                $oSeo_arr     = Shop::DB()->selectAll('tseo', ['cKey', 'kKey'],
                    ['kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage], 'cSeo, kSprache', 'kSprache');
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
            if (strlen($this->Kategorie->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            } else {
                $cSEOURL .= $this->Kategorie->getSeo($this->getLanguageID());
            }
            $cURL .= 'k=' . $this->Kategorie->getValue();
        } elseif ($this->Hersteller->isInitialized()) {
            $cSEOURL .= $this->Hersteller->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->Hersteller->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'h=' . $this->Hersteller->getValue();
        } elseif ($this->Suchanfrage->isInitialized()) {
            $cSEOURL .= $this->Suchanfrage->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->Suchanfrage->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'l=' . $this->Suchanfrage->getValue();
        } elseif ($this->MerkmalWert->isInitialized()) {
            $cSEOURL .= $this->MerkmalWert->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->MerkmalWert->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'm=' . $this->MerkmalWert->getValue();
        } elseif ($this->Tag->isInitialized()) {
            $cSEOURL .= $this->Tag->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->Tag->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 't=' . $this->Tag->getValue();
        } elseif ($this->Suchspecial->isInitialized()) {
            $cSEOURL .= $this->Suchspecial->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->Suchspecial->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'q=' . $this->Suchspecial->getValue();
        } elseif ($this->News->isInitialized()) {
            $cSEOURL .= $this->News->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->News->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'n=' . $this->News->getValue();
        } elseif ($this->NewsMonat->isInitialized()) {
            $cSEOURL .= $this->NewsMonat->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->NewsMonat->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'nm=' . $this->NewsMonat->getValue();
        } elseif ($this->NewsKategorie->isInitialized()) {
            $cSEOURL .= $this->NewsKategorie->getSeo($this->getLanguageID());
            if ($bSeo && strlen($this->NewsKategorie->getSeo($this->getLanguageID())) === 0) {
                $bSeo = false;
            }
            $cURL .= 'nk=' . $this->NewsKategorie->getValue();
        }
        if ((isset($this->EchteSuche->cSuche) && strlen($this->EchteSuche->cSuche) > 0) &&
            (!isset($this->Suchanfrage->kSuchanfrage) || intval($this->Suchanfrage->kSuchanfrage) === 0)
        ) {
            $bSeo = false;
            $cURL .= 'suche=' . urlencode($this->EchteSuche->cSuche);
        }
        if ($debug === true) {
            Shop::dbg($bSeo, false, 'bseo after:');
        }
        // Filter
        // Kategorie
        if (!$bCanonical) {
            if ($this->KategorieFilter->isInitialized() && (!$this->Kategorie->isInitialized() || $this->Kategorie->getValue() !== $this->KategorieFilter->getValue())) {
                if (!isset($oZusatzFilter->FilterLoesen->Kategorie) || !$oZusatzFilter->FilterLoesen->Kategorie) {
                    if (strlen($this->KategorieFilter->getSeo($this->getLanguageID())) === 0) {
                        $bSeo = false;
                    }
                    if ($this->conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && !empty($oZusatzFilter->KategorieFilter->kKategorie)) {
                        if (!empty($oZusatzFilter->KategorieFilter->cSeo)) {
                            $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
                        } else {
                            $cSEOURL .= SEP_KAT . $this->KategorieFilter->getSeo($this->getLanguageID());
                        }
                        $cURL .= '&kf=' . $oZusatzFilter->KategorieFilter->kKategorie;
                    } else {
                        $cSEOURL .= SEP_KAT . $this->KategorieFilter->getSeo($this->getLanguageID());
                        $cURL .= '&kf=' . $this->KategorieFilter->getValue();
                    }
                }
            } elseif ((isset($oZusatzFilter->KategorieFilter->kKategorie) && $oZusatzFilter->KategorieFilter->kKategorie > 0) &&
                (!$this->Kategorie->isInitialized() || $this->Kategorie->getValue() !== $oZusatzFilter->KategorieFilter->kKategorie)
            ) {
                $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
                $cURL .= '&kf=' . $oZusatzFilter->KategorieFilter->kKategorie;
            }
            // Hersteller
            if ($this->HerstellerFilter->isInitialized() && (!$this->Hersteller->isInitialized() || $this->Hersteller->getValue() !== $this->HerstellerFilter->getValue())) {
                if (empty($oZusatzFilter->FilterLoesen->Hersteller)) {
                    $cSEOURL .= SEP_HST . $this->HerstellerFilter->getSeo($this->getLanguageID());
                    if ($bSeo && strlen($this->HerstellerFilter->getSeo($this->getLanguageID())) === 0) {
                        $bSeo = false;
                    }
                    $cURL .= '&hf=' . $this->HerstellerFilter->getValue();
                }
            } elseif (!empty($oZusatzFilter->HerstellerFilter->kHersteller) && (!$this->Hersteller->isInitialized() || $this->Hersteller->getValue() !== $oZusatzFilter->HerstellerFilter->kHersteller)) {
                $cSEOURL .= SEP_HST . $oZusatzFilter->HerstellerFilter->cSeo;
                $cURL .= '&hf=' . $oZusatzFilter->HerstellerFilter->kHersteller;
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
                    $cURL .= '&sf' . ($i + 1) . '=' . (int)$oSuchanfrage->kSuchanfrage;
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
                            $oZusatzFilter->FilterLoesen->MerkmalWert != $oMerkmalFilter->kMerkmalWert
                        ) {
                            if (strlen($oMerkmalFilter->cSeo[$this->getLanguageID()]) === 0) {
                                $bSeo = false;
                            }
                            $oMerkmalWert_arr[$i]               = new stdClass();
                            $oMerkmalWert_arr[$i]->kMerkmalWert = (int)$oMerkmalFilter->kMerkmalWert;
                            $oMerkmalWert_arr[$i]->cSeo         = $oMerkmalFilter->cSeo[$this->getLanguageID()];
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
                    $cURL .= '&mf' . ($i + 1) . '=' . (int)$oMerkmalWert->kMerkmalWert;
                }
            }
            // Preisspannen
            if (isset($this->PreisspannenFilter->fVon) && $this->PreisspannenFilter->fVon >= 0 &&
                isset($this->PreisspannenFilter->fBis) && $this->PreisspannenFilter->fBis > 0 &&
                !isset($oZusatzFilter->FilterLoesen->Preisspannen)
            ) {
                $cURL .= '&pf=' . $this->PreisspannenFilter->fVon . '_' . $this->PreisspannenFilter->fBis;
            } elseif (isset($oZusatzFilter->PreisspannenFilter->fVon) && $oZusatzFilter->PreisspannenFilter->fVon >= 0 &&
                isset($oZusatzFilter->PreisspannenFilter->fBis) && $oZusatzFilter->PreisspannenFilter->fBis > 0
            ) {
                $cURL .= '&pf=' . $oZusatzFilter->PreisspannenFilter->fVon . '_' . $oZusatzFilter->PreisspannenFilter->fBis;
            }
            // Bewertung
            if (isset($this->BewertungFilter->nSterne) && $this->BewertungFilter->nSterne > 0 &&
                !isset($oZusatzFilter->FilterLoesen->Bewertungen) && !isset($oZusatzFilter->BewertungFilter->nSterne)
            ) {
                $cURL .= '&bf=' . $this->BewertungFilter->getValue();
            } elseif (isset($oZusatzFilter->BewertungFilter->nSterne) && $oZusatzFilter->BewertungFilter->nSterne > 0) {
                $cURL .= '&bf=' . $oZusatzFilter->BewertungFilter->nSterne;
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
                //$cURL .= "&tf" . $nLetzterTagFilter . "=" . $oZusatzFilter->TagFilter->kTag;
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
                    $cURL .= '&tf' . ($i + 1) . '=' . (int)$oTag->kTag;
                }
            }
            // Suchspecialfilter
            if ((isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) &&
                (!$this->Suchspecial->isInitialized() || $this->Suchspecial->getValue() !== $oZusatzFilter->SuchspecialFilter->kKey)
            ) {
                $cURL .= '&qf=' . $oZusatzFilter->SuchspecialFilter->kKey;
            } elseif ($this->SuchspecialFilter->isInitialized() && (!$this->Suchspecial->isInitialized() || $this->Suchspecial->getValue() !== $this->SuchspecialFilter->getValue())) {
                if (!isset($oZusatzFilter->FilterLoesen->Suchspecials) || !$oZusatzFilter->FilterLoesen->Suchspecials) {
                    $cSEOURL .= $this->SuchspecialFilter->getSeo($this->getLanguageID());
                    if ($bSeo && strlen($this->SuchspecialFilter->getSeo($this->getLanguageID())) === 0) {
                        $bSeo = false;
                    }
                    $cURL .= '&qf=' . $this->SuchspecialFilter->getValue();
                }
            }
        }

        if (strlen($cSEOURL) > 254) {
            $bSeo = false;
        }
        if ($debug === true) {
            Shop::dbg($oZusatzFilter, false, '$oZusatzFilter:');
        }
        if (isset($oZusatzFilter->mValue) && isset($oZusatzFilter->nType) && isset($oZusatzFilter->cParam)) {
            $bSeo = false;
            //custom filter
            if ($debug === true) {
                Shop::dbg($cURL, false, 'zusatz curl before:');
            }
            $cURL .= '&' . $oZusatzFilter->cParam .
                (($oZusatzFilter->nType === AbstractFilter::FILTER_TYPE_OR)
                    ? '[]'
                    : '')
                . '=' . $oZusatzFilter->mValue;
            if ($debug === true) {
                Shop::dbg($cURL, false, 'zusatz curl after:');
            }
        }
        foreach ($this->getActiveFilters() as $filter) {
            if ($filter->isCustom()) {
                $className = $filter->getClassName();
                if ((!isset($oZusatzFilter->FilterLoesen->Kategorie) || $oZusatzFilter->FilterLoesen->Kategorie !== true) && //cAlleKategorien
                    $oZusatzFilter !== null && //cNoFilter
                    !isset($oZusatzFilter->FilterLoesen->$className)
                ) {
                    //@todo: custom filters do not  support SEO URLs yet
                    $bSeo = false;
                }
                if (!isset($oZusatzFilter->FilterLoesen->$className) || $oZusatzFilter->FilterLoesen->$className > 0) {
                    $param  = $filter->getUrlParam();
                    $values = $filter->getValue();
                    if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                        $ignore = (isset($oZusatzFilter->FilterLoesen->$className) && $oZusatzFilter->FilterLoesen->$className !== true)
                            ? $oZusatzFilter->FilterLoesen->$className
                            : null;
                        if ($ignore !== null && count($values) > 1) {
                            $bSeo = false;
                        }
                        foreach ($values as $value) {
                            if ($value !== $ignore) {
                                $cURL .= '&' . $param . '[]' . '=' . $value;
                            }
                        }
                    } else {
                        $cURL .= '&' . $param . '=' . $values;
                    }
                }
            }
        }
        if ($debug === true) {
            Shop::dbg($bSeo, false, 'bseo final:');
            Shop::dbg($cSEOURL, false, '$cSEOURL final:');
            Shop::dbg($cURL, false, '$cURL final:');
        }

        if ($bSeo) {
            return $cSEOURL;
        }
        if ($this->getLanguageID() != Shop::getLanguage()) {
            //@todo@todo: this will probably never happen..?
            $cISOSprache = '';
            if (isset($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0) {
                foreach ($_SESSION['Sprachen'] as $i => $oSprache) {
                    if ($oSprache->kSprache == $this->getLanguageID()) {
                        $cISOSprache = $oSprache->cISO;
                    }
                }
            }

            return urlencode($cURL . '&lang=' . $cISOSprache);
        }

        return $cURL;
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    public function attributeValueIsActive($kMerkmalWert)
    {
        foreach ($this->MerkmalFilter as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->getValue() === $kMerkmalWert) {
                return true;
            }
        }

        return false;
    }

    /**
     * Die Usersortierung kann entweder ein Integer sein oder via Kategorieattribut ein String
     *
     * @param int|string $sort
     * @return int
     */
    public function mapUserSorting($sort)
    {
        // Ist die Usersortierung ein Integer => Return direkt den Integer
        preg_match('/[0-9]+/', $sort, $cTreffer_arr);
        if (isset($cTreffer_arr[0]) && strlen($sort) === strlen($cTreffer_arr[0])) {
            return $sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (strtolower($sort)) {
            case SEARCH_SORT_CRITERION_NAME:
                return SEARCH_SORT_NAME_ASC;
                break;

            case SEARCH_SORT_CRITERION_NAME_ASC:
                return SEARCH_SORT_NAME_ASC;
                break;

            case SEARCH_SORT_CRITERION_NAME_DESC:
                return SEARCH_SORT_NAME_DESC;
                break;

            case SEARCH_SORT_CRITERION_PRODUCTNO:
                return SEARCH_SORT_PRODUCTNO;
                break;

            case SEARCH_SORT_CRITERION_AVAILABILITY:
                return SEARCH_SORT_AVAILABILITY;
                break;

            case SEARCH_SORT_CRITERION_WEIGHT:
                return SEARCH_SORT_WEIGHT;
                break;

            case SEARCH_SORT_CRITERION_PRICE:
                return SEARCH_SORT_PRICE_ASC;
                break;

            case SEARCH_SORT_CRITERION_PRICE_ASC:
                return SEARCH_SORT_PRICE_ASC;
                break;

            case SEARCH_SORT_CRITERION_PRICE_DESC:
                return SEARCH_SORT_PRICE_DESC;
                break;

            case SEARCH_SORT_CRITERION_EAN:
                return SEARCH_SORT_EAN;
                break;

            case SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return SEARCH_SORT_NEWEST_FIRST;
                break;

            case SEARCH_SORT_CRITERION_DATEOFISSUE:
                return SEARCH_SORT_DATEOFISSUE;
                break;

            case SEARCH_SORT_CRITERION_BESTSELLER:
                return SEARCH_SORT_BESTSELLER;
                break;

            case SEARCH_SORT_CRITERION_RATING:
                return SEARCH_SORT_RATING;

            default:
                return SEARCH_SORT_STANDARD;
                break;
        }
    }

    /**
     * @param string $cTitle
     * @return string
     */
    public function truncateMetaTitle($cTitle)
    {
        return (($length = $this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0)
            ? substr($cTitle, 0, (int)$length)
            : $cTitle;
    }

    /**
     * @param object         $oMeta
     * @param object         $oSuchergebnisse
     * @param array          $GlobaleMetaAngaben_arr
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaTitle($oMeta, $oSuchergebnisse, $GlobaleMetaAngaben_arr, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $append = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        // Pruefen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaTitle) > 0) {
            $oMeta->cMetaTitle = strip_tags($oMeta->cMetaTitle);
            // Globalen Meta Title anhaengen
            if ($append === true && !empty($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title)) {
                return $this->truncateMetaTitle($oMeta->cMetaTitle . ' ' . $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title);
            }

            return $this->truncateMetaTitle($oMeta->cMetaTitle);
        }
        // Set Default Titles
        $cMetaTitle = $this->getMetaStart($oSuchergebnisse);
        $cMetaTitle = str_replace('"', "'", $cMetaTitle);
        $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        // Kategorieattribute koennen Standard-Titles ueberschreiben
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cTitleTag) && strlen($oKategorie->cTitleTag) > 0) {
                // meta title via new method
                $cMetaTitle = strip_tags($oKategorie->cTitleTag);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->categoryAttributes['meta_title']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
                $cMetaTitle = strip_tags($oKategorie->categoryAttributes['meta_title']->cWert);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->KategorieAttribute['meta_title'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cMetaTitle = strip_tags($oKategorie->KategorieAttribute['meta_title']);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            }
        }
        // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
        if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
            $cMetaTitle .= ', ' . Shop::Lang()->get('page',
                    'global') . " {$oSuchergebnisse->Seitenzahlen->AktuelleSeite}";
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title)) {
            $cMetaTitle .= ' - ' . $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title;
        }

        return $this->truncateMetaTitle($cMetaTitle);
    }

    /**
     * @param object         $oMeta
     * @param array          $oArtikel_arr
     * @param object         $oSuchergebnisse
     * @param array          $GlobaleMetaAngaben_arr
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $GlobaleMetaAngaben_arr, $oKategorie = null ) {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaDescription) > 0) {
            $oMeta->cMetaDescription = strip_tags($oMeta->cMetaDescription);

            return truncateMetaDescription($oMeta->cMetaDescription);
        }
        // Kategorieattribut?
        $cKatDescription = '';
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cMetaDescription) && strlen($oKategorie->cMetaDescription) > 0) {
                // meta description via new method
                $cKatDescription = strip_tags($oKategorie->cMetaDescription);

                return truncateMetaDescription($cKatDescription);
            }
            if (!empty($oKategorie->categoryAttributes['meta_description']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
                $cKatDescription = strip_tags($oKategorie->categoryAttributes['meta_description']->cWert);

                return truncateMetaDescription($cKatDescription);
            }
            if (!empty($oKategorie->KategorieAttribute['meta_description'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cKatDescription = strip_tags($oKategorie->KategorieAttribute['meta_description']);

                return truncateMetaDescription($cKatDescription);
            }
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (isset($oKategorie->cBeschreibung) && strlen($oKategorie->cBeschreibung) > 0) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $oKategorie->cBeschreibung));
            } elseif ($oKategorie->bUnterKategorien) { // Hat die aktuelle Kategorie Unterkategorien?
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);

                if (isset($oKategorieListe->elemente) && is_array($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                            if ($i > 0) {
                                $cKatDescription .= ', ' . strip_tags($oUnterkat->cName);
                            } else {
                                $cKatDescription .= strip_tags($oUnterkat->cName);
                            }
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription  = str_replace('"', '', $cKatDescription);
                $cKatDescription  = StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                $cMetaDescription = (isset($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) && strlen($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) > 0)
                    ? trim(strip_tags($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) . " " . $cKatDescription)
                    : trim($cKatDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 && $oSuchergebnisse->ArtikelVon > 0 && $oSuchergebnisse->ArtikelBis > 0) {
                    $cMetaDescription .= ', ' . Shop::Lang()->get('products',
                            'global') . " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
                }

                return truncateMetaDescription($cMetaDescription);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $cMetaDescription = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr);
            $nCount = 12;
            if (count($oArtikel_arr) < $nCount) {
                $nCount = count($oArtikel_arr);
            }
            $cArtikelName = '';
            for ($i = 0; $i < $nCount; ++$i) {
                if ($i > 0) {
                    $cArtikelName .= ' - ' . $oArtikel_arr[$i]->cName;
                } else {
                    $cArtikelName .= $oArtikel_arr[$i]->cName;
                }
            }
            $cArtikelName = str_replace('"', '', $cArtikelName);
            $cArtikelName = StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

            if (isset($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) && strlen($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) > 0) {
                $cMetaDescription = $this->getMetaStart($oSuchergebnisse) . ': ' . $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix . ' ' . $cArtikelName;
            } else {
                $cMetaDescription = $this->getMetaStart($oSuchergebnisse) . ': ' . $cArtikelName;
            }
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 && $oSuchergebnisse->ArtikelVon > 0 && $oSuchergebnisse->ArtikelBis > 0) {
                $cMetaDescription .= ', ' . Shop::Lang()->get('products',
                        'global') . " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
            }
        }

        return truncateMetaDescription(strip_tags($cMetaDescription));
    }

    /**
     * @param object         $oMeta
     * @param array          $oArtikel_arr
     * @param Kategorie|null $oKategorie
     * @return mixed|string
     */
    public function getMetaKeywords($oMeta, $oArtikel_arr, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaKeywords) > 0) {
            $oMeta->cMetaKeywords = strip_tags($oMeta->cMetaKeywords);

            return $oMeta->cMetaKeywords;
        }
        // Kategorieattribut?
        $cKatKeywords = '';
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cMetaKeywords) && strlen($oKategorie->cMetaKeywords) > 0) {
                // meta keywords via new method
                $cKatKeywords = strip_tags($oKategorie->cMetaKeywords);

                return $cKatKeywords;
            }
            if (!empty($oKategorie->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                $cKatKeywords = strip_tags($oKategorie->categoryAttributes['meta_keywords']->cWert);

                return $cKatKeywords;
            }
            if (!empty($oKategorie->KategorieAttribute['meta_keywords'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cKatKeywords = strip_tags($oKategorie->KategorieAttribute['meta_keywords']);

                return $cKatKeywords;
            }
        }
        // Keine eingestellten Metas vorhanden => baue Standard Metas
        $cMetaKeywords = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr); // Shuffle alle Artikel
            $nCount = 6;
            if (count($oArtikel_arr) < $nCount) {
                $nCount = count($oArtikel_arr);
            }
            $cArtikelName          = '';
            $excludes              = holeExcludedKeywords();
            $oExcludesKeywords_arr = (isset($excludes[$_SESSION['cISOSprache']]->cKeywords))
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = gibExcludesKeywordsReplace($oArtikel_arr[$i]->cName,
                    $oExcludesKeywords_arr); // Filter nicht erlaubte Keywords
                if (strpos($cExcArtikelName, ' ') !== false) {
                    // Wenn der Dateiname aus mehreren Wörtern besteht
                    $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                    $cSubName        = '';
                    if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                        foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                            if (strlen($cSubNameTMP) > 2) {
                                $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                                if ($j > 0) {
                                    $cSubName .= ', ' . $cSubNameTMP;
                                } else {
                                    $cSubName .= $cSubNameTMP;
                                }
                            }
                        }
                    }
                    $cArtikelName .= $cSubName;
                } elseif ($i > 0) {
                    $cArtikelName .= ', ' . $oArtikel_arr[$i]->cName;
                } else {
                    $cArtikelName .= $oArtikel_arr[$i]->cName;
                }
            }
            $cMetaKeywords = $cArtikelName;
            // Prüfe doppelte Einträge und lösche diese
            $cMetaKeywordsUnique_arr = [];
            $cMeta_arr               = explode(', ', $cMetaKeywords);
            if (is_array($cMeta_arr) && count($cMeta_arr) > 1) {
                foreach ($cMeta_arr as $cMeta) {
                    if (!in_array($cMeta, $cMetaKeywordsUnique_arr)) {
                        $cMetaKeywordsUnique_arr[] = $cMeta;
                    }
                }
                $cMetaKeywords = implode(', ', $cMetaKeywordsUnique_arr);
            }
        } elseif (!empty($oKategorie->kKategorie)) {
            // Hat die aktuelle Kategorie Unterkategorien?
            if ($oKategorie->bUnterKategorien) {
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);
                if (isset($oKategorieListe->elemente) && is_array($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                            if ($i > 0) {
                                $cKatKeywords .= ', ' . $oUnterkat->cName;
                            } else {
                                $cKatKeywords .= $oUnterkat->cName;
                            }
                        }
                    }
                }
            } elseif (isset($oKategorie->cBeschreibung) && strlen($oKategorie->cBeschreibung) > 0) { // Hat die aktuelle Kategorie eine Beschreibung?
                $cKatKeywords = $oKategorie->cBeschreibung;
            }
            $cKatKeywords  = str_replace('"', '', $cKatKeywords);
            $cMetaKeywords = $cKatKeywords;

            return strip_tags($cMetaKeywords);
        }
        $cMetaKeywords = str_replace('"', '', $cMetaKeywords);
        $cMetaKeywords = StringHandler::htmlentitydecode($cMetaKeywords, ENT_NOQUOTES);

        return strip_tags($cMetaKeywords);
    }

    /**
     * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
     *
     * @param object $oSuchergebnisse
     * @return string
     */
    public function getMetaStart($oSuchergebnisse)
    {
        $cMetaTitle = '';

        // MerkmalWert
        if ($this->MerkmalWert->isInitialized()) {
            $cMetaTitle .= $this->MerkmalWert->getName();
        } elseif ($this->Kategorie->isInitialized()) { // Kategorie
            $cMetaTitle .= $this->Kategorie->getName();
        } elseif ($this->Hersteller->isInitialized()) { // Hersteller
            $cMetaTitle .= $this->Hersteller->getName();
        } elseif ($this->Tag->isInitialized()) { // Tag
            $cMetaTitle .= $this->Tag->getName();
        } elseif ($this->Suche->isInitialized()) { // Suchebegriff
            $cMetaTitle .= $this->Suche->cSuche;
            //@todo: does this work?
            //$cMetaTitle .= $this->Suche->getName();
        } elseif ($this->Suchspecial->isInitialized()) { // Suchspecial
            $cMetaTitle .= $this->Suchspecial->getName();
        }
        // Kategoriefilter
        if ($this->KategorieFilter->isInitialized()) {
            $cMetaTitle .= ' ' . $this->KategorieFilter->getName();
        }
        // Herstellerfilter
        if ($this->HerstellerFilter->isInitialized() && !empty($oSuchergebnisse->Herstellerauswahl[0]->cName)) {
            $cMetaTitle .= ' ' . $this->HerstellerFilter->getName();
        }
        // Tagfilter
        if (is_array($this->TagFilter) && count($this->TagFilter) > 0 && isset($this->TagFilter[0]->cName)) {
            $cMetaTitle .= ' ' . $this->TagFilter[0]->cName;
        }
        // Suchbegrifffilter
        if (is_array($this->SuchFilter) && count($this->SuchFilter) > 0) {
            foreach ($this->SuchFilter as $i => $oSuchFilter) {
                if (isset($oSuchFilter->cName)) {
                    $cMetaTitle .= ' ' . $oSuchFilter->cName;
                }
            }
        }
        // Suchspecialfilter
        if ($this->SuchspecialFilter->isInitialized()) {
            switch ($this->SuchspecialFilter->getValue()) {
                case SEARCHSPECIALS_BESTSELLER:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('bestsellers', 'global');
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('specialOffers', 'global');
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('newProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topOffers', 'global');
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('upcomingProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topReviews', 'global');
                    break;

                default:
                    break;
            }
        }
        // MerkmalWertfilter
        if (is_array($this->MerkmalFilter) && count($this->MerkmalFilter) > 0) {
            foreach ($this->MerkmalFilter as $oMerkmalFilter) {
                if (isset($oMerkmalFilter->cName)) {
                    $cMetaTitle .= ' ' . $oMerkmalFilter->cName;
                }
            }
        }

        return ltrim($cMetaTitle);
    }

    /**
     * @param bool   $bSeo
     * @param object $oSuchergebnisse
     */
    public function createUnsetFilterURLs($bSeo, $oSuchergebnisse)
    {
        if ($this->SuchspecialFilter->isInitialized()) {
            $bSeo = false;
        }
        // URLs bauen, die Filter lösen
        $oZusatzFilter                          = new stdClass();
        $oZusatzFilter->FilterLoesen            = new stdClass();
        $oZusatzFilter->FilterLoesen->Kategorie = true;

        $this->URL->cAlleKategorien = $this->getURL($bSeo, $oZusatzFilter, false, false);

        $oZusatzFilter->FilterLoesen             = new stdClass();
        $oZusatzFilter->FilterLoesen->Hersteller = true;

        $this->URL->cAlleHersteller = $this->getURL($bSeo, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen = new stdClass();

        foreach ($this->MerkmalFilter as $oMerkmal) {
            if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
                $oZusatzFilter->FilterLoesen->Merkmale         = $oMerkmal->kMerkmal;
                $this->URL->cAlleMerkmale[$oMerkmal->kMerkmal] = $this->getURL($bSeo, $oZusatzFilter);
            }
            $oZusatzFilter->FilterLoesen->MerkmalWert              = $oMerkmal->kMerkmalWert;
            $this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = $this->getURL($bSeo, $oZusatzFilter);
        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->MerkmalWert->isInitialized() && !isset($this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()])) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace($this->MerkmalWert->getSeo(Shop::getLanguage()) . '__', '',
                $this->URL->cAlleKategorien);
            if ($_mmwSeo !== $this->URL->cAlleKategorien) {
                $this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()] = $_mmwSeo;
            }
        }

        $oZusatzFilter->FilterLoesen               = new stdClass();
        $oZusatzFilter->FilterLoesen->Preisspannen = true;

        $this->URL->cAllePreisspannen = $this->getURL($bSeo, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen              = new stdClass();
        $oZusatzFilter->FilterLoesen->Bewertungen = true;

        $this->URL->cAlleBewertungen = $this->getURL($bSeo, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen       = new stdClass();
        $oZusatzFilter->FilterLoesen->Tags = true;

        $this->URL->cAlleTags = $this->getURL($bSeo, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen               = new stdClass();
        $oZusatzFilter->FilterLoesen->Suchspecials = true;

        $this->URL->cAlleSuchspecials = $this->getURL($bSeo, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen                    = new stdClass();
        $oZusatzFilter->FilterLoesen->Erscheinungsdatum = true;

        $this->URL->cAlleErscheinungsdatums = $this->getURL(false, $oZusatzFilter);

        $oZusatzFilter->FilterLoesen = new stdClass();
        foreach ($this->SuchFilter as $oSuchFilter) {
            if (isset($oSuchFilter->kSuchanfrage) && $oSuchFilter->kSuchanfrage > 0) {
                $oZusatzFilter->FilterLoesen->SuchFilter                = $oSuchFilter->kSuchanfrage;
                $this->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $this->getURL($bSeo, $oZusatzFilter);
            }
        }

        $oZusatzFilter->FilterLoesen = new stdClass();
        foreach ($this->filters as $filter) {
            if ($filter->isInitialized() && $filter->isCustom()) {
                $className       = $filter->getClassName();
                $idx             = 'cAlle' . $className;
                $this->URL->$idx = [];
                if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    foreach ($filter->getValue() as $filterValue) {
                        $oZusatzFilter->FilterLoesen->$className = $filterValue;
                        $this->URL->$idx[$filterValue] = $this->getURL($bSeo, $oZusatzFilter);
                    }
                } else {
                    $oZusatzFilter->FilterLoesen->$className = true;
                    $this->URL->$idx = $this->getURL($bSeo, $oZusatzFilter);
                }
            }
        }
        // Filter reset
        $cSeite = (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1)
            ? SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite
            : '';

        $this->URL->cNoFilter = $this->getURL(true, null, true) . $cSeite;
    }
}
