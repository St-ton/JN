<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ProductFilter
 */
class ProductFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private $conf;
    /**
     * @var array
     */
    private $languages;

    /**
     * @var FilterBaseCategory
     */
    private $category;

    /**
     * @var FilterItemCategory
     */
    private $categoryFilter;

    /**
     * @var FilterBaseManufacturer
     */
    private $manufacturer;

    /**
     * @var FilterItemManufacturer
     */
    private $manufacturerFilter;

    /**
     * @var FilterBaseAttribute
     */
    private $attributeValue;

    /**
     * @var FilterBaseSearchQuery
     */
    private $searchQuery;

    /**
     * @var FilterSearch[]
     */
    private $searchFilter = [];

    /**
     * @var FilterItemTag[]
     */
    private $tagFilter = [];

    /**
     * @var FilterItemAttribute[]
     */
    private $attributeFilter = [];

    /**
     * @var FilterItemSearchSpecial
     */
    private $searchSpecialFilter;

    /**
     * @var FilterItemRating
     */
    private $ratingFilter;

    /**
     * @var FilterItemPriceRange
     */
    private $priceRangeFilter;

    /**
     * @var FilterBaseTag
     */
    private $tag;

    /**
     * @var FilterBaseSearchSpecial
     */
    private $searchSpecial;

    /**
     * @var FilterSearch
     */
    private $search;

    /**
     * @var object
     */
    private $EchteSuche;

    /**
     * @var int
     */
    private $productLimit = 0;

    /**
     * @var int
     */
    private $nSeite = 1;

    /**
     * @var int
     */
    private $nSortierung = 0;

    /**
     * @var int
     */
    private $languageID;

    /**
     * @var int
     */
    private $customerGroupID;

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
     * @var IFilter
     */
    private $baseState;

    /**
     * @var stdClass
     */
    private $url;

    /**
     * @var FilterItemTag
     */
    public $tagFilterCompat;

    /**
     * @var FilterItemAttribute
     */
    private $attributeFilterCollection;

    /**
     * @var FilterSearch
     */
    public $searchFilterCompat;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var ProductFilterSearchResults
     */
    private $searchResults;

    /**
     * @var Metadata
     */
    private $metaData;

    /**
     * @var ProductFilterSQL
     */
    private $filterSQL;

    /**
     * @var ProductFilterURL
     */
    private $filterURL;

    /**
     * @var bool
     */
    private $bExtendedJTLSearch;

    /**
     * @var bool
     */
    private $showChildProducts;

    /**
     * @var array
     * @todo: fix working with arrays
     * @see https://stackoverflow.com/questions/13421661/getting-indirect-modification-of-overloaded-property-has-no-effect-notice
     */
    private static $mapping = [
        'nAnzahlFilter'      => 'FilterCount',
        'nAnzahlProSeite'    => 'ProductLimit',
        'Kategorie'          => 'Category',
        'KategorieFilter'    => 'CategoryFilter',
        'Hersteller'         => 'Manufacturer',
        'HerstellerFilter'   => 'ManufacturerFilter',
        'Suchanfrage'        => 'SearchQuery',
        'MerkmalWert'        => 'AttributeValue',
        'Tag'                => 'Tag',
        'Suchspecial'        => 'SearchSpecial',
        'MerkmalFilter'      => 'AttributeFilter',
        'SuchFilter'         => 'SearchFilter',
        'TagFilter'          => 'TagFilter',
        'SuchspecialFilter'  => 'SearchSpecialFilter',
        'BewertungFilter'    => 'RatingFilter',
        'PreisspannenFilter' => 'PriceRangeFilter',
        'Suche'              => 'Search',
        'EchteSuche'         => 'RealSearch',
        'oSprache_arr'       => 'Languages',
        'URL'                => 'URL'
    ];

    /**
     * @param array  $languages
     * @param int    $currentLanguageID
     * @param array  $config
     */
    public function __construct($languages = null, $currentLanguageID = null, $config = null)
    {
        $urls                    = new stdClass();
        $urls->cAllePreisspannen = '';
        $urls->cAlleBewertungen  = '';
        $urls->cAlleTags         = '';
        $urls->cAlleSuchspecials = '';
        $urls->cAlleKategorien   = '';
        $urls->cAlleHersteller   = '';
        $urls->cAlleMerkmale     = [];
        $urls->cAlleMerkmalWerte = [];
        $urls->cAlleSuchFilter   = [];
        $urls->cNoFilter         = null;

        $this->url               = $urls;
        $this->languages         = $languages === null
            ? Sprache::getInstance()->getLangArray()
            : $languages;
        $this->conf              = $config === null
            ? Shopsetting::getInstance()->getAll()
            : $config;
        $this->languageID        = $currentLanguageID === null
            ? Shop::getLanguageID()
            : (int)$currentLanguageID;
        $this->customerGroupID   = Session::CustomerGroup()->getID();
        $this->baseURL           = Shop::getURL() . '/';
        $this->metaData          = new Metadata($this);
        $this->filterSQL         = new ProductFilterSQL($this);
        $this->filterURL         = new ProductFilterURL($this);
        $this->showChildProducts = defined('SHOW_CHILD_PRODUCTS')
            ? SHOW_CHILD_PRODUCTS
            : false;
        executeHook(HOOK_PRODUCTFILTER_CREATE, ['productFilter' => $this]);
        $this->initBaseStates();
    }

    /**
     * @return bool
     */
    public function showChildProducts()
    {
        return $this->showChildProducts;
    }

    /**
     * @param bool $showChildProducts
     * @return ProductFilter
     */
    public function setShowChildProducts($showChildProducts)
    {
        $this->showChildProducts = $showChildProducts;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->nSortierung;
    }

    /**
     * @param int $nSortierung
     * @return ProductFilter
     */
    public function setSort($nSortierung)
    {
        $this->nSortierung = $nSortierung;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @param stdClass $url
     * @return ProductFilter
     */
    public function setURL($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly read from ProductFilter instance
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly set on ProductFilter instance
     *
     * @param array $languages
     * @return mixed
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;

        return $languages;
    }

    /**
     * @return ProductFilterSQL
     */
    public function getFilterSQL()
    {
        return $this->filterSQL;
    }

    /**
     * @param ProductFilterSQL $filterSQL
     * @return ProductFilter
     */
    public function setFilterSQL($filterSQL)
    {
        $this->filterSQL = $filterSQL;

        return $this;
    }

    /**
     * @return ProductFilterURL
     */
    public function getFilterURL()
    {
        return $this->filterURL;
    }

    /**
     * @param ProductFilterURL $filterURL
     * @return ProductFilter
     */
    public function setFilterURL($filterURL)
    {
        $this->filterURL = $filterURL;

        return $this;
    }

    /**
     * @param bool $products
     * @return ProductFilterSearchResults|Collection
     */
    public function getSearchResults($products = true)
    {
        return $products === true && $this->searchResults->getProducts() !== null
            ? $this->searchResults->getProducts()->elemente
            : $this->searchResults;
    }

    /**
     * @param ProductFilterSearchResults $results
     * @return $this
     */
    public function setSearchResults($results)
    {
        $this->searchResults = $results;

        return $this;
    }

    /**
     * @return Metadata
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->nSeite;
    }

    /**
     * @return array|null
     */
    public function getAvailableLanguages()
    {
        return $this->languages;
    }

    /**
     * @return IFilter
     */
    public function getBaseState()
    {
        return $this->baseState;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function setBaseState($filter)
    {
        $this->baseState = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseURL()
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     * @return ProductFilter
     */
    public function setBaseURL($baseURL)
    {
        $this->baseURL = $baseURL;

        return $this;
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
     * @return int
     */
    public function getProductLimit()
    {
        return $this->productLimit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setProductLimit($limit)
    {
        $this->productLimit = (int)$limit;

        return $this;
    }

    /**
     * @return array - default array keys
     */
    private function getParamsPrototype()
    {
        return [
            'kKategorie'             => 0,
            'kKonfigPos'             => 0,
            'kHersteller'            => 0,
            'kArtikel'               => 0,
            'kVariKindArtikel'       => 0,
            'kSeite'                 => 0,
            'kLink'                  => 0,
            'kSuchanfrage'           => 0,
            'kMerkmalWert'           => 0,
            'kTag'                   => 0,
            'kSuchspecial'           => 0,
            'kUmfrage'               => 0,
            'kKategorieFilter'       => 0,
            'kHerstellerFilter'      => 0,
            'nBewertungSterneFilter' => 0,
            'cPreisspannenFilter'    => '',
            'kSuchspecialFilter'     => 0,
            'nSortierung'            => 0,
            'nSort'                  => 0,
            'MerkmalFilter_arr'      => [],
            'TagFilter_arr'          => [],
            'SuchFilter_arr'         => [],
            'nArtikelProSeite'       => null,
            'cSuche'                 => null,
            'seite'                  => null,
            'show'                   => true,
            'kSuchFilter'            => 0,
            'kWunschliste'           => 0,
            'MerkmalFilter'          => null,
            'SuchFilter'             => null,
            'TagFilter'              => null,
            'vergleichsliste'        => null,
            'nDarstellung'           => 0,
            'isSeoMainword'          => false,
            'cDatum'                 => '',
            'nAnzahl'                => 0,
            'nSterne'                => 0,
            'customFilters'          => [],
            'searchSpecialFilters'   => []
        ];
    }

    /**
     * @return $this
     */
    public function initBaseStates()
    {
        $this->category       = new FilterBaseCategory($this);
        $this->categoryFilter = new FilterItemCategory($this);

        $this->manufacturer       = new FilterBaseManufacturer($this);
        $this->manufacturerFilter = new FilterItemManufacturer($this);

        $this->searchQuery = new FilterBaseSearchQuery($this);

        $this->attributeValue = new FilterBaseAttribute($this);

        $this->tag = new FilterBaseTag($this);

        $this->searchSpecial = new FilterBaseSearchSpecial($this);

        $this->attributeFilter = [];
        $this->searchFilter    = [];
        $this->tagFilter       = [];

        $this->searchSpecialFilter = new FilterItemSearchSpecial($this);

        $this->ratingFilter = new FilterItemRating($this);

        $this->priceRangeFilter = new FilterItemPriceRange($this);

        $this->tagFilterCompat           = new FilterItemTag($this);
        $this->attributeFilterCollection = new FilterItemAttribute($this);
        $this->searchFilterCompat        = new FilterSearch($this);

        $this->search = new FilterSearch($this);

        $this->baseState = new FilterDummyState($this);

        executeHook(HOOK_PRODUCTFILTER_INIT, ['productFilter' => $this]);

        $this->filters[] = $this->categoryFilter;
        $this->filters[] = $this->manufacturerFilter;
        $this->filters[] = $this->attributeFilterCollection;
        $this->filters[] = $this->searchSpecialFilter;
        $this->filters[] = $this->priceRangeFilter;
        $this->filters[] = $this->ratingFilter;

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates($params)
    {
        $params = array_merge($this->getParamsPrototype(), $params);
        if ($params['kKategorie'] > 0) {
            $this->baseState = $this->category->init($params['kKategorie']);
        } elseif ($params['kHersteller'] > 0) {
            $this->manufacturer->init($params['kHersteller']);
            $this->baseState = $this->manufacturer;
        } elseif ($params['kMerkmalWert'] > 0) {
            $this->attributeValue = (new FilterBaseAttribute($this))->init($params['kMerkmalWert']);
            $this->baseState      = $this->attributeValue;
        } elseif ($params['kTag'] > 0) {
            $this->tag->init($params['kTag']);
            $this->baseState = $this->tag;
        } elseif ($params['kSuchspecial'] > 0) {
            $this->searchSpecial->init($params['kSuchspecial']);
            $this->baseState = $this->searchSpecial;
        }
        
        if ($params['kKategorieFilter'] > 0) {
            $this->addActiveFilter($this->categoryFilter, $params['kKategorieFilter']);
        }
        if ($params['kHerstellerFilter'] > 0) {
            $this->addActiveFilter($this->manufacturerFilter, $params['kHerstellerFilter']);
        }
        if ($params['nBewertungSterneFilter'] > 0) {
            $this->addActiveFilter($this->ratingFilter, $params['nBewertungSterneFilter']);
        }
        if (strlen($params['cPreisspannenFilter']) > 0) {
            $this->addActiveFilter($this->priceRangeFilter, $params['cPreisspannenFilter']);
        }
        $this->initAttributeFilters($params['MerkmalFilter_arr']);
        foreach ($params['TagFilter_arr'] as $tf) {
            $this->tagFilter[] = $this->addActiveFilter(new FilterItemTag($this), $tf);
        }
        if ($params['kSuchspecialFilter'] > 0 && count($params['searchSpecialFilters']) === 0) {
            // backwards compatibility
            $params['searchSpecialFilters'][] = $params['kSuchspecialFilter'];
        }
        if (count($params['searchSpecialFilters']) > 0) {
            $this->addActiveFilter($this->searchSpecialFilter, $params['searchSpecialFilters']);
        }

        // @todo - same as suchfilter?
        foreach ($params['SuchFilter_arr'] as $sf) {
            $this->searchFilter[] = $this->addActiveFilter(new FilterSearch($this), $sf);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] > 0) {
            $this->productLimit = (int)$params['nArtikelProSeite'];
        }
        // @todo: how to handle strlen($params['cSuche']) === 0?
        if ($params['kSuchanfrage'] > 0) {
            $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->search->setName($oSuchanfrage->cSuche);
            }
            // Suchcache beachten / erstellen
            $searchName = $this->search->getName();
            if (!empty($searchName)) {
                $this->search->kSuchCache = $this->searchQuery->editSearchCache();
                $this->searchQuery->init($oSuchanfrage->kSuchanfrage);
                $this->searchQuery->kSuchCache = $this->search->kSuchCache;
                $this->searchQuery->setName($this->search->getName());
                if (!$this->baseState->isInitialized()) {
                    $this->baseState = $this->searchQuery;
                }
            }
        } elseif (strlen($params['cSuche']) > 0) {
            $params['cSuche'] = StringHandler::filterXSS($params['cSuche']);
            $this->search->setName($params['cSuche']);
            $this->searchQuery->setName($params['cSuche']);
            $oSuchanfrage                  = Shop::DB()->select(
                'tsuchanfrage',
                'cSuche', $params['cSuche'],
                'kSprache', $this->getLanguageID(),
                'nAktiv', 1,
                false,
                'kSuchanfrage'
            );
            $kSuchCache                    = $this->searchQuery->editSearchCache();
            $kSuchAnfrage                  = isset($oSuchanfrage->kSuchanfrage)
                ? (int)$oSuchanfrage->kSuchanfrage
                : $params['kSuchanfrage'];
            $this->search->kSuchCache      = $kSuchCache;
            $this->searchQuery->kSuchCache = $kSuchCache;
            $this->searchQuery->init($kSuchAnfrage)->setName($params['cSuche']);
            $this->EchteSuche          = new stdClass();
            $this->EchteSuche->cSuche  = $params['cSuche'];
            if (!$this->baseState->isInitialized()) {
                $this->baseState = $this->searchQuery;
            }
            $limit                      = $this->metaData->getProductsPerPageLimit();
            $oExtendedJTLSearchResponse = null;
            $this->bExtendedJTLSearch   = false;

            executeHook(HOOK_NAVI_PRESUCHE, [
                'cValue'             => &$this->EchteSuche->cSuche,
                'bExtendedJTLSearch' => &$this->bExtendedJTLSearch
            ]);
            if (empty($params['cSuche'])) {
                $this->bExtendedJTLSearch = false;
            }
            $this->search->bExtendedJTLSearch = $this->bExtendedJTLSearch;

            executeHook(HOOK_NAVI_SUCHE, [
                'bExtendedJTLSearch'         => $this->bExtendedJTLSearch,
                'oExtendedJTLSearchResponse' => &$oExtendedJTLSearchResponse,
                'cValue'                     => &$this->EchteSuche->cSuche,
                'nArtikelProSeite'           => &$limit,
                'nSeite'                     => &$this->nSeite,
                'nSortierung'                => isset($_SESSION['Usersortierung'])
                    ? $_SESSION['Usersortierung']
                    : null,
                'bLagerbeachten'             => (int)$this->getConfig()['global']['artikel_artikelanzeigefilter'] ===
                    EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
            ]);
        }
        $this->nSeite = max(1, verifyGPCDataInteger('seite'));
        foreach ($this->getCustomFilters() as $filter) {
            $filterParam = $filter->getUrlParam();
            $filterClass = $filter->getClassName();
            if (isset($_GET[$filterParam])) {
                // OR filters should always get an array as input - even if there is just one value active
                if (!is_array($_GET[$filterParam]) && $filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    $_GET[$filterParam] = [$_GET[$filterParam]];
                }
                // escape all input values
                if (($filter->getType() === AbstractFilter::FILTER_TYPE_OR && is_array($_GET[$filterParam]))
                    || ($filter->getType() === AbstractFilter::FILTER_TYPE_AND
                        && (verifyGPCDataInteger($filterParam) > 0 || verifyGPDataString($filterParam) !== ''))
                ) {
                    $filterValue = is_array($_GET[$filterParam])
                        ? array_map([Shop::DB(), 'realEscape'], $_GET[$filterParam])
                        : Shop::DB()->realEscape($_GET[$filterParam]);
                    $this->addActiveFilter($filter, $filterValue);
                    $params[$filterParam] = $filterValue;
                }
            } elseif (count($params['customFilters']) > 0) {
                foreach ($params['customFilters'] as $className => $filterValue) {
                    if ($filterClass === $className) {
                        $this->addActiveFilter($filter, $filterValue);
                        $params[$filterParam] = $filterValue;
                    }
                }
            }
        }
        executeHook(HOOK_PRODUCTFILTER_INIT_STATES, [
            'productFilter' => $this,
            'params'        => $params
        ]);
        $this->params = $params;

        return $this->validate();
    }

    /**
     * @param array $values
     * @return $this
     */
    private function initAttributeFilters(array $values)
    {
        if (count($values) === 0) {
            return $this;
        }
        $attributes = Shop::DB()->executeYield(
            'SELECT tmerkmalwert.kMerkmal, tmerkmalwert.kMerkmalWert, tmerkmal.nMehrfachauswahl
                FROM tmerkmalwert
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                WHERE kMerkmalWert IN (' . implode(',', array_map('intval', $values)) . ')'
        );
        foreach ($attributes as $attribute) {
            $attribute->kMerkmal         = (int)$attribute->kMerkmal;
            $attribute->kMerkmalWert     = (int)$attribute->kMerkmalWert;
            $attribute->nMehrfachauswahl = (int)$attribute->nMehrfachauswahl;
            $this->attributeFilter[]     = $this->addActiveFilter(new FilterItemAttribute($this), $attribute);
        }

        return $this;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function registerFilter(IFilter $filter)
    {
        $this->filters[] = $filter->setBaseData($this);

        return $this;
    }

    /**
     * @param string $filterName
     * @return IFilter
     * @throws InvalidArgumentException
     */
    public function registerFilterByClassName($filterName)
    {
        $filter = null;
        if (class_exists($filterName)) {
            /** @var IFilter $filter */
            $filter          = new $filterName($this);
            $this->filters[] = $filter->setClassName($filterName);
        } else {
            throw new InvalidArgumentException('Cannot register filter class ' . $filterName);
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
        $this->activeFilters[] = $filter->setBaseData($this)->init($filterValue)->generateActiveFilterData();

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

        return $this;
    }

    /**
     * @param string $filterClassName
     * @return int|null
     */
    public function getFilterValue($filterClassName)
    {
        return array_reduce(
            $this->activeFilters,
            function ($carry, $item) use ($filterClassName) {
                /** @var IFilter $item */
                return $carry !== null
                    ? $carry
                    : ($item->getClassName() === $filterClassName
                        ? $item->getValue()
                        : null);
            }
        );
    }

    /**
     * @param string $filterClassName
     * @return bool
     */
    public function hasFilter($filterClassName)
    {
        return $this->getActiveFilterByClassName($filterClassName) !== null;
    }

    /**
     * @param string $filterClassName
     * @return IFilter|null
     */
    public function getFilterByClassName($filterClassName)
    {
        $filter = array_filter(
            $this->filters,
            function ($f) use ($filterClassName) {
                /** @var IFilter $f */
                return $f->getClassName() === $filterClassName;
            }
        );

        return is_array($filter) ? current($filter) : null;
    }

    /**
     * @param string $filterClassName
     * @return IFilter|null
     */
    public function getActiveFilterByClassName($filterClassName)
    {
        $filter = array_filter(
            $this->activeFilters,
            function ($f) use ($filterClassName) {
                /** @var IFilter $f */
                return $f->getClassName() === $filterClassName;
            }
        );

        return is_array($filter) ? current($filter) : null;
    }

    /**
     * @return IFilter[]
     */
    public function getCustomFilters()
    {
        return array_filter(
            $this->filters,
            function ($e) {
                /** @var IFilter $e */
                return $e->isCustom();
            }
        );
    }

    /**
     * @return IFilter[]
     */
    public function getAvailableFilters()
    {
        return $this->filters;
    }

    /**
     * @param IFilter[] $filters
     * @return $this
     */
    public function setAvailableFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * get filters that can be displayed at content level
     *
     * @return array|IFilter[]
     */
    public function getAvailableContentFilters()
    {
        return array_filter(
            $this->filters,
            function ($f) {
                /** @var IFilter $f */
                return ($f->getVisibility() === AbstractFilter::SHOW_ALWAYS
                        || $f->getVisibility() === AbstractFilter::SHOW_CONTENT);
            }
        );
    }

    /**
     * @return int
     */
    public function getFilterCount()
    {
        return count($this->activeFilters);
    }

    /**
     * @param string  $className
     * @param IFilter $filter
     * @return bool
     */
    public function override($className, IFilter $filter)
    {
        foreach ($this->filters as $i => $registerdFilter) {
            if ($registerdFilter->getClassName() === $className) {
                $this->filters[$i] = $filter;

                return true;
            }
        }

        return false;
    }

    /**
     * @return FilterItemManufacturer
     */
    public function getManufacturerFilter()
    {
        return $this->manufacturerFilter;
    }

    /**
     * @param FilterItemManufacturer $filter
     * @return $this
     */
    public function setManufacturerFilter($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kHersteller)) {
            // disallow setting manufacturer filter to empty stdClass
            return $this;
        }
        $this->manufacturerFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturerFilter()
    {
        return $this->manufacturerFilter->isInitialized();
    }

    /**
     * @return FilterBaseManufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param FilterItemManufacturer $filter
     * @return $this
     */
    public function setManufacturer($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kHersteller)) {
            // disallow setting manufacturer base to empty stdClass
            return $this;
        }
        $this->manufacturer = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturer()
    {
        return $this->manufacturer->isInitialized();
    }

    /**
     * returns ALL registered attribute filters
     *
     * @return FilterItemAttribute[]
     */
    public function getAttributeFilters()
    {
        return $this->attributeFilter;
    }

    /**
     * this method works like pre Shop 4.06 - only returns ACTIVE attribute filters
     *
     * @param null|int $idx
     * @return FilterItemAttribute|FilterItemAttribute[]
     */
    public function getAttributeFilter($idx = null)
    {
        return $idx === null ? $this->attributeFilter : $this->attributeFilter[$idx];
    }

    /**
     * @param array $filter
     * @return $this
     */
    public function setAttributeFilter(array $filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kMerkmal)) {
            // disallow setting attribute filter to empty stdClass
            return $this;
        }
        $this->attributeFilter = $filter;

        return $this;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function addAttributeFilter($filter)
    {
        $this->attributeFilter[] = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributeFilter()
    {
        return count($this->attributeFilter) > 0;
    }

    /**
     * @return FilterBaseAttribute
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @param FilterBaseAttribute $filter
     * @return $this
     */
    public function setAttributeValue($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kMerkmalWert)) {
            // disallow setting attribute value to empty stdClass
            return $this;
        }
        $this->attributeFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributeValue()
    {
        return $this->attributeValue->isInitialized();
    }

    /**
     * @return FilterItemAttribute
     */
    public function getAttributeFilterCollection()
    {
        return $this->attributeFilterCollection;
    }

    /**
     * @param null|int $idx
     * @return FilterItemTag|FilterItemTag[]
     */
    public function getTagFilter($idx = null)
    {
        return $idx === null ? $this->tagFilter : $this->tagFilter[$idx];
    }

    /**
     * @return bool
     */
    public function hasTagFilter()
    {
        return count($this->tagFilter) > 0;
    }

    /**
     * @return FilterBaseTag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param FilterBaseTag $filter
     * @return $this
     */
    public function setTag($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kTag)) {
            // disallow setting tag filter to empty stdClass
            return $this;
        }
        $this->tagFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTag()
    {
        return $this->tag->isInitialized();
    }

    /**
     * @return FilterBaseCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param FilterBaseCategory $filter
     * @return $this
     */
    public function setCategory($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kKategorie)) {
            // disallow setting category base to empty stdClass
            return $this;
        }
        $this->category = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategory()
    {
        return $this->category->isInitialized();
    }

    /**
     * @return FilterItemCategory
     */
    public function getCategoryFilter()
    {
        return $this->categoryFilter;
    }

    /**
     * @param FilterBaseTag $filter
     * @return $this
     */
    public function setCategoryFilter($filter)
    {
        if (is_a($filter, 'stdClass') && !isset($filter->kKategorie)) {
            // disallow setting category filter to empty stdClass
            return $this;
        }
        $this->categoryFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategoryFilter()
    {
        return $this->categoryFilter->isInitialized();
    }

    /**
     * @return FilterSearch
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return bool
     */
    public function hasSearch()
    {
        return $this->search->getName() !== null;
    }

    /**
     * @return FilterBaseSearchQuery
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @return bool
     */
    public function hasSearchQuery()
    {
        return $this->searchQuery->isInitialized();
    }

    /**
     * @param FilterBaseSearchQuery $filter
     * @return $this
     */
    public function setSearchQuery($filter)
    {
        $this->searchQuery = $filter;

        return $this;
    }

    /**
     * @param null|int $idx
     * @return FilterSearch|FilterSearch[]
     */
    public function getSearchFilter($idx = null)
    {
        return $idx === null ? $this->searchFilter : $this->searchFilter[$idx];
    }

    /**
     * @return bool
     */
    public function hasSearchFilter()
    {
        return count($this->searchFilter) > 0;
    }

    /**
     * @return FilterBaseSearchSpecial
     */
    public function getSearchSpecial()
    {
        return $this->searchSpecial;
    }

    /**
     * @param FilterBaseSearchSpecial $filter
     * @return $this
     */
    public function setSearchSpecial($filter)
    {
        $this->searchSpecial = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecial()
    {
        return $this->searchSpecial->isInitialized();
    }

    /**
     * @return FilterItemSearchSpecial
     */
    public function getSearchSpecialFilter()
    {
        return $this->searchSpecialFilter;
    }

    /**
     * @param FilterItemSearchSpecial $filter
     * @return $this
     */
    public function setSearchSpecialFilter($filter)
    {
        $this->searchSpecialFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecialFilter()
    {
        return $this->searchSpecialFilter->isInitialized();
    }

    /**
     * @return null|object
     */
    public function getRealSearch()
    {
        return empty($this->EchteSuche->cSuche)
            ? null
            : $this->EchteSuche;
    }

    /**
     * @return FilterItemRating
     */
    public function getRatingFilter()
    {
        return $this->ratingFilter;
    }

    /**
     * @param FilterItemRating $filter
     * @return $this
     */
    public function setRatingFilter($filter)
    {
        $this->ratingFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRatingFilter()
    {
        return $this->ratingFilter->isInitialized();
    }

    /**
     * @return FilterItemPriceRange
     */
    public function getPriceRangeFilter()
    {
        return $this->priceRangeFilter;
    }

    /**
     * @param FilterItemPriceRange $filter
     * @return $this
     */
    public function setPriceRangeFilter($filter)
    {
        $this->priceRangeFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPriceRangeFilter()
    {
        return $this->priceRangeFilter->isInitialized();
    }

    /**
     * @return $this
     */
    public function validate()
    {
        if ($this->getFilterCount() === 0) {
            return $this;
        }
        $location = 'Location: ' . $this->baseURL;
        if (empty($this->search->getName())
            && !$this->hasManufacturer()
            && !$this->hasCategory()
            && !$this->hasTag()
            && !$this->hasSearchQuery()
            && !$this->hasAttributeValue()
            && !$this->hasSearchSpecial()
        ) {
            // we have a manufacturer filter that doesn't filter anything
            if ($this->manufacturerFilter->getSeo($this->getLanguageID()) !== null) {
                http_response_code(301);
                header($location . $this->manufacturerFilter->getSeo($this->getLanguageID()));
                exit();
            }
            // we have a category filter that doesn't filter anything
            if ($this->categoryFilter->getSeo($this->getLanguageID()) !== null) {
                http_response_code(301);
                header($location . $this->categoryFilter->getSeo($this->getLanguageID()));
                exit();
            }
        } elseif ($this->hasManufacturer()
            && $this->hasManufacturerFilter()
            && $this->manufacturer->getSeo($this->getLanguageID()) !== null
        ) {
            // we have a manufacturer page with some manufacturer filter
            http_response_code(301);
            header($location . $this->manufacturer->getSeo($this->getLanguageID()));
            exit();
        } elseif ($this->hasCategory()
            && $this->hasCategoryFilter()
            && $this->category->getSeo($this->getLanguageID()) !== null
        ) {
            // we have a category page with some category filter
            http_response_code(301);
            header($location . $this->category->getSeo($this->getLanguageID()));
            exit();
        }

        return $this;
    }

    /**
     * get list of product IDs matching the current filter
     *
     * @return int[]
     */
    public function getProductKeys()
    {
        $state = $this->getCurrentStateData();
        $qry   = $this->getFilterSQL()->getBaseQuery(
            ['tartikel.kArtikel'],
            $state->joins,
            $state->conditions,
            $state->having,
            null,
            '',
            ['tartikel.kArtikel'],
            'listing'
        );

        return array_map(
            function ($e) {
                return (int)$e->kArtikel;
            },
            Shop::DB()->query($qry, 2)
        );
    }

    /**
     * checks if a given combination of filter class and filter value is currently active
     *
     * @param string $class
     * @param mixed  $value
     * @return bool
     */
    public function filterOptionIsActive($class, $value)
    {
        foreach ($this->getActiveFilters() as $filter) {
            if ($filter->getClassName() !== $class) {
                continue;
            }
            $filterValue = $filter->getValue();
            if ($value === $filterValue) {
                return true;
            }
            if (is_array($filterValue)) {
                foreach ($filterValue as $val) {
                    if ($val === $value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param bool           $forProductListing - if true, return ProductFilterSearchResults instance, otherwise keys only
     * @param Kategorie|null $currentCategory
     * @param bool           $fillProducts - if true, return Artikel class instances, otherwise keys only
     * @param int            $limit
     * @return ProductFilterSearchResults|Collection
     */
    public function getProducts($forProductListing = true, $currentCategory = null, $fillProducts = true, $limit = 0)
    {
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel, die auch wirklich auf der Seite angezeigt werden

        $limitPerPage = $limit > 0 ? $limit : $this->metaData->getProductsPerPageLimit();
        $nLimitN      = $limitPerPage * ($this->nSeite - 1);
        $max          = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];
        $error        = false;
        if ($this->searchResults === null) {
            $this->searchResults      = new ProductFilterSearchResults();
            $productList              = new stdClass();
            $productList->elemente    = new Collection();
            $productList->productKeys = $this->getProductKeys();
            $productCount = count($productList->productKeys);

            $this->searchResults->setProductCount($productCount);
            if (!empty($this->search->getName())) {
                if ($this->searchQuery->getError() === null) {
                    $this->search->saveQuery($productCount);
                    $this->search->setQueryID($this->search->getName(), $this->getLanguageID());
                    $this->searchQuery->setValue($this->search->getValue())->setSeo($this->languages);
                } else {
                    $error = $this->searchQuery->getError();
                }
            }
            $this->searchResults->setOffsetStart($nLimitN + 1)
                                ->setOffsetEnd(min(
                                    $nLimitN + $limitPerPage,
                                    $productCount
                                ));
            
            $pages                = new stdClass();
            $pages->AktuelleSeite = $this->nSeite;
            $pages->MaxSeiten     = ceil($productCount / $limitPerPage);
            $pages->minSeite      = min(
                $pages->AktuelleSeite - $max / 2,
                0
            );
            $pages->maxSeite      = max(
                $pages->MaxSeiten,
                $pages->minSeite + $max - 1
            );
            if ($pages->maxSeite > $pages->MaxSeiten) {
                $pages->maxSeite = $pages->MaxSeiten;
            }
            $this->searchResults->setPages($pages);
            $this->searchResults = $this->setFilterOptions($this->searchResults, $currentCategory);
            // Header bauen
            $this->searchResults->setSearchTermWrite($this->metaData->getHeader());
        } else {
            $productList = $this->searchResults->getProducts();
        }
        if ($error !== false) {
            $this->searchResults->setProductCount(0)
                                ->setSearchUnsuccessful(true)
                                ->setSearchTerm(strip_tags(trim($this->params['cSuche'])))
                                ->setError($error);

            return $this->searchResults;
        }
        if ($fillProducts === true) {
            // @todo: slice list of IDs when not filling?
            $opt                        = new stdClass();
            $opt->nMerkmale             = 1;
            $opt->nKategorie            = 1;
            $opt->nAttribute            = 1;
            $opt->nArtikelAttribute     = 1;
            $opt->nVariationKombiKinder = 1;
            $opt->nWarenlager           = 1;
            $opt->nRatings              = PRODUCT_LIST_SHOW_RATINGS === true ? 1 : 0;
            $opt->nVariationDetailPreis = (int)$this->conf['artikeldetails']['artikel_variationspreisanzeige'] !== 0
                ? 1
                : 0;
            foreach (array_slice($productList->productKeys, $nLimitN, $limitPerPage) as $id) {
                $product = (new Artikel())->fuelleArtikel($id, $opt);
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($product !== null && $product->nIstVater === 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $id;
                }
                $productList->elemente->addItem($product);
            }
        }
        $this->url = $this->filterURL->createUnsetFilterURLs($this->url);
        $_SESSION['oArtikelUebersichtKey_arr']   = $productList->productKeys;
        $_SESSION['nArtikelUebersichtVLKey_arr'] = [];

        $bEchteSuche = !$this->bExtendedJTLSearch && !empty($params['cSuche']);
        if (!$this->bExtendedJTLSearch && !empty($this->search->getName())) {
            $this->search->saveQuery($this->search->getName(), $this->searchResults->getProductCount(), $bEchteSuche);
        }
        $this->searchResults->setProducts($productList);

        if ($forProductListing === true) {
            //Weiterleitung, falls nur 1 Artikel rausgeholt
            $hasSubCategories = ($categoryID = $this->getCategory()->getValue()) > 0
                ? (new Kategorie($categoryID, $this->languageID, $this->customerGroupID))
                    ->existierenUnterkategorien()
                : false;
            if ($productList->elemente->count() === 1
                && $this->getConfig()['navigationsfilter']['allgemein_weiterleitung'] === 'Y'
                && ($this->getFilterCount() > 0
                    || ($this->getCategory()->getValue() > 0 && !$hasSubCategories)
                    || !empty($this->EchteSuche->cSuche))
            ) {
                http_response_code(301);
                $product = $productList->elemente->pop();
                $url = empty($product->cURL)
                    ? (Shop::getURL() . '/?a=' . $product->kArtikel)
                    : (Shop::getURL() . '/' . $product->cURL);
                header('Location: ' . $url);
                exit;
            }
        }

        return $forProductListing === true
            ? $this->searchResults
            : $productList->elemente;
    }

    /**
     * @param bool $byType
     * @return array|IFilter[]
     */
    public function getActiveFilters($byType = false)
    {
        $result = $byType === false
            ? []
            : [
                'kf'     => [],
                'hf'     => [],
                'mm'     => [],
                'ssf'    => [],
                'tf'     => [],
                'sf'     => [],
                'bf'     => [],
                'custom' => [],
                'misc'   => []
            ];
        foreach ($this->activeFilters as $activeFilter) {
            // get custom filters
            if ($activeFilter->isCustom()) {
                if ($byType) {
                    $result['custom'][] = $activeFilter;
                } else {
                    $result[] = $activeFilter;
                }
            } else {
                // get built-in filters
                $found = false;
                if ($activeFilter->isInitialized() && ($urlPram = $activeFilter->getUrlParam()) !== '') {
                    if ($byType) {
                        $result[$urlPram][] = $activeFilter;
                    } else {
                        $result[] = $activeFilter;
                    }
                    continue;
                }
                // get built-in filters that were manually set
                if ($found === false) {
                    if ($byType) {
                        $result['misc'][] = $activeFilter;
                    } else {
                        $result[] = $activeFilter;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return stdClass
     */
    public function getCurrentStateData($ignore = null)
    {
        $state            = $this->getBaseState();
        $stateCondition   = $state->getSQLCondition();
        $stateJoin        = $state->getSQLJoin();
        $data             = new stdClass();
        $data->having     = [];
        $data->conditions = [];
        $data->joins      = is_array($stateJoin)
            ? $stateJoin
            : [$stateJoin];
        if (!empty($stateCondition)) {
            $data->conditions[] = $stateCondition;
        }
        /** @var IFilter $filter */
        foreach ($this->getActiveFilters(true) as $type => $active) {
            $count = count($active);
            if ($count > 1 && $type !== 'misc' && $type !== 'custom') {
                $singleConditions = [];
                $active           = array_filter(
                    $active,
                    function ($f) use ($ignore) {
                        /** @var IFilter $f */
                        return $ignore === null
                            || (is_string($ignore) && $f->getClassName() !== $ignore)
                            || (is_object($ignore) && $f !== $ignore);
                    }
                );
                $orFilters        = array_filter(
                    $active,
                    function ($f) {
                        /** @var IFilter $f */
                        return $f->getType() === AbstractFilter::FILTER_TYPE_OR;
                    }
                );

                /** @var AbstractFilter $filter */
                foreach ($active as $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
                    $itemJoin    = $filter->getSQLJoin();
                    $data->joins = array_merge($data->joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!in_array($filter, $orFilters, true)) {
                        $singleConditions[] = $filter->getSQLCondition();
                    }
                }
                if (count($orFilters) > 0) {
                    // group OR filters by their primary key row
                    $groupedOrFilters = [];
                    foreach ($orFilters as $filter) {
                        $primaryKeyRow = $filter->getPrimaryKeyRow();
                        if (!isset($groupedOrFilters[$primaryKeyRow])) {
                            $groupedOrFilters[$primaryKeyRow] = [];
                        }
                        $groupedOrFilters[$primaryKeyRow][] = $filter;
                    }
                    foreach ($groupedOrFilters as $primaryKeyRow => $orFilters) {
                        /** @var IFilter[] $orFilters */
                        $values = implode(
                            ',',
                            array_map(function ($f) {
                                /** @var IFilter $f */
                                $val = $f->getValue();

                                return is_array($val) ? implode(',', $val) : $val;
                            }, $orFilters)
                        );
                        $table  = $orFilters[0]->getTableAlias();
                        if (empty($table)) {
                            $table = $orFilters[0]->getTableName();
                        }
                        $data->conditions[] = "\n#combined conditions from OR filter " . $primaryKeyRow . "\n" .
                            $table . '.kArtikel IN ' .
                            '(SELECT kArtikel FROM ' . $orFilters[0]->getTableName() . ' WHERE ' .
                            $primaryKeyRow . ' IN (' . $values . '))';
                    }
                }
                foreach ($singleConditions as $singleCondition) {
                    $data->conditions[] = $singleCondition;
                }
            } elseif ($count === 1) {
                /** @var IFilter[] $active */
                if ($ignore === null
                    || (is_object($ignore) && $active[0] !== $ignore)
                    || (is_string($ignore) && $active[0]->getClassName() !== $ignore)
                ) {
                    $itemJoin    = $active[0]->getSQLJoin();
                    $_condition  = $active[0]->getSQLCondition();
                    $data->joins = array_merge($data->joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!empty($_condition)) {
                        $data->conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            } elseif ($count > 0 && ($type !== 'misc' || $type !== 'custom')) {
                // this is the most clean and usual behaviour.
                // 'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($active as $filter) {
                    $itemJoin    = $filter->getSQLJoin();
                    $_condition  = $filter->getSQLCondition();
                    $data->joins = array_merge($data->joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!empty($_condition)) {
                        $data->conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param ProductFilterSearchResults $searchResults
     * @param null|Kategorie             $currentCategory
     * @param bool                       $selectionWizard
     * @return mixed
     */
    public function setFilterOptions($searchResults, $currentCategory = null, $selectionWizard = false)
    {
        // @todo: make option
        $hideActiveOnly          = true;
        $manufacturerOptions     = $this->manufacturerFilter->getOptions();
        $ratingOptions           = $this->ratingFilter->getOptions();
        $tagOptions              = $this->tag->getOptions();
        $categoryOptions         = $this->categoryFilter->getOptions();
        $priceRangeOptions       = $this->priceRangeFilter->getOptions($searchResults->getProductCount());
        $searchSpecialFilters    = $this->searchSpecialFilter->getOptions();
        $searchFilterOptions     = $this->searchFilterCompat->getOptions();
        $attribtuteFilterOptions = $this->attributeFilterCollection->getOptions([
            'oAktuelleKategorie' => $currentCategory,
            'bForce'             => $selectionWizard === true && function_exists('starteAuswahlAssistent')
        ]);

        $searchResults->setManufacturerFilterOptions($manufacturerOptions)
                      ->setRatingFilterOptions($ratingOptions)
                      ->setTagFilterOptions($tagOptions)
                      ->setPriceRangeFilterOptions($priceRangeOptions)
                      ->setCategoryFilterOptions($categoryOptions)
                      ->setSearchFilterOptions($searchFilterOptions)
                      ->setSearchSpecialFilterOptions($searchSpecialFilters)
                      ->setAttributeFilterOptions($attribtuteFilterOptions)
                      ->setCustomFilterOptions(array_filter(
                          $this->filters,
                          function ($e) {
                              /** @var IFilter $e */
                              $isCustom = $e->isCustom();
                              if ($isCustom && count($e->getOptions()) === 0) {
                                  $e->hide();
                              }

                              return $isCustom;
                          }
                      ))
                      ->setSearchFilterJSON(Boxen::gibJSONString(array_map(
                          function ($e) {
                              $e->cURL = StringHandler::htmlentitydecode($e->cURL);

                              return $e;
                          },
                          $searchFilterOptions
                      )));

        if ($this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
            $searchResults->setTagFilterJSON(Boxen::gibJSONString(array_map(
                function ($e) {
                    /** @var FilterOption $e */
                    return $e->setURL(StringHandler::htmlentitydecode($e->getURL()));
                },
                $tagOptions
            )));
        }

        if (empty($searchSpecialFilters)) {
            // hide category filter when a category is being browsed
            $this->searchSpecialFilter->hide();
        }
        if (empty($categoryOptions)
            || count($categoryOptions) === 0
            || ($this->category->isInitialized() && $this->category->getValue() !== null)
        ) {
            // hide category filter when a category is being browsed
            $this->categoryFilter->hide();
        }
        if (empty($priceRangeOptions)
            || count($priceRangeOptions) === 0
            || ($this->priceRangeFilter->isInitialized() && $this->priceRangeFilter->getValue() !== null)
       ) {
            // hide empty price ranges
            $this->priceRangeFilter->hide();
        }
        if (empty($manufacturerOptions) || count($manufacturerOptions) === 0
            || $this->manufacturer->isInitialized()
            || ($this->manufacturerFilter->isInitialized()
                && count($manufacturerOptions) === 1
                && $hideActiveOnly)
        ) {
            // hide manufacturer filter when browsing manufacturer products
            $this->manufacturerFilter->hide();
        }
        if (empty($ratingOptions)) {
            $this->ratingFilter->hide();
        }
        if (count($attribtuteFilterOptions) < 1) {
            $this->attributeFilterCollection->hide();
        } elseif ($hideActiveOnly === true) {
            foreach ($attribtuteFilterOptions as $af) {
                /** @var FilterOption $af */
                $options = $af->getOptions();
                if (is_array($options)
                    && $af->getVisibility() !== AbstractFilter::SHOW_NEVER
                    && array_reduce(
                        $options,
                        function ($carry, $option) {
                            /** @var FilterOption $option */
                            return $carry && $option->isActive();
                        },
                        true
                    ) === true
                ) {
                    $af->hide();
                }
            }
        }
        $this->attributeFilterCollection->setFilterCollection($attribtuteFilterOptions);

        return $searchResults;
    }

    /**
     * @return string|null
     */
    public function getUnsetAllFiltersURL()
    {
        return isset($this->url->cNoFilter)
            ? $this->url->cNoFilter
            : null;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res         = get_object_vars($this);
        $res['conf'] = '*truncated*';

        return $res;
    }
}
