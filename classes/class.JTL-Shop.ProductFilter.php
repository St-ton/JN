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
    private $articleLimit = 0;

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
    public $URL;

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
     * @var stdClass
     */
    private $searchResults;

    /**
     * @var Metadata
     */
    private $metaData;

    /**
     * @var array
     * @todo: fix working with arrays
     * @see https://stackoverflow.com/questions/13421661/getting-indirect-modification-of-overloaded-property-has-no-effect-notice
     */
    private static $mapping = [
        'nAnzahlFilter'      => 'FilterCount',
        'nAnzahlProSeite'    => 'ArticleLimit',
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
        'oSprache_arr'       => 'Languages'
    ];

    /**
     * @param array  $languages
     * @param int    $currentLanguageID
     * @param array  $config
     * @param NiceDB $db
     */
    public function __construct($languages = null, $currentLanguageID = null, $config = null, $db = null)
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

        $this->URL             = $urls;
        $this->languages       = $languages === null
            ? Shop::Lang()->getLangArray()
            : $languages;
        $this->conf            = $config === null
            ? Shopsetting::getInstance()->getAll()
            : $config;
        $this->languageID      = $currentLanguageID === null
            ? Shop::getLanguage()
            : (int)$currentLanguageID;
        $this->customerGroupID = Session::CustomerGroup()->getID();
        $this->baseURL         = Shop::getURL() . '/';
        $this->metaData        = new Metadata($this);
        executeHook(HOOK_PRODUCTFILTER_CREATE, ['productFilter' => $this]);
        $this->initBaseStates();
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
     * @param bool $articles
     * @return stdClass
     */
    public function getSearchResults($articles = true)
    {
        return $articles === true && isset($this->searchResults->Artikel->elemente)
            ? $this->searchResults->Artikel->elemente
            : $this->searchResults;
    }

    /**
     * @param stdClass $results
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
    public function getArticleLimit()
    {
        return $this->articleLimit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setArticleLimit($limit)
    {
        $this->articleLimit = (int)$limit;

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
            'customFilters'          => []
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
        }
        if ($params['kKategorieFilter'] > 0) {
            $this->addActiveFilter($this->categoryFilter, $params['kKategorieFilter']);
        }
        if ($params['kHersteller'] > 0) {
            $this->manufacturer->init($params['kHersteller']);
            $this->baseState = $this->manufacturer;
        }
        if ($params['kHerstellerFilter'] > 0) {
            $this->addActiveFilter($this->manufacturerFilter, $params['kHerstellerFilter']);
        }
        if ($params['kMerkmalWert'] > 0) {
            $this->attributeValue = (new FilterBaseAttribute($this))->init($params['kMerkmalWert']);
            $this->baseState      = $this->attributeValue;
        }
        if (count($params['MerkmalFilter_arr']) > 0) {
            $this->initAttributeFilters($params['MerkmalFilter_arr']);
        }
        if ($params['kTag'] > 0) {
            $this->tag->init($params['kTag']);
            $this->baseState = $this->tag;
        }
        foreach ($params['TagFilter_arr'] as $tf) {
            $this->tagFilter[] = $this->addActiveFilter(new FilterItemTag($this), $tf);
        }
        if ($params['kSuchspecial'] > 0) {
            $this->searchSpecial->init($params['kSuchspecial']);
            $this->baseState = $this->searchSpecial;
        }
        if ($params['kSuchspecialFilter'] > 0) {
            $this->addActiveFilter($this->searchSpecialFilter, $params['kSuchspecialFilter']);
        }

        // @todo - same as suchfilter?
        foreach ($params['SuchFilter_arr'] as $sf) {
            $this->searchFilter[] = $this->addActiveFilter(new FilterSearch($this), $sf);
        }

        if ($params['nBewertungSterneFilter'] > 0) {
            $this->addActiveFilter($this->ratingFilter, $params['nBewertungSterneFilter']);
        }
        if (strlen($params['cPreisspannenFilter']) > 0) {
            $this->addActiveFilter($this->priceRangeFilter, $params['cPreisspannenFilter']);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] > 0) {
            $this->articleLimit = (int)$params['nArtikelProSeite'];
        }
        if ($params['kSuchanfrage'] > 0) {
            $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->search->cSuche = $oSuchanfrage->cSuche;
            }
            // Suchcache beachten / erstellen
            if (!empty($this->search->cSuche)) {
                $this->search->kSuchCache = $this->searchQuery->editSearchCache();
                $this->searchQuery->init($oSuchanfrage->kSuchanfrage);
                $this->searchQuery->kSuchCache = $this->search->kSuchCache;
                $this->searchQuery->cSuche     = $this->search->cSuche;
                $this->baseState               = $this->searchQuery;
            }
        } elseif (strlen($params['cSuche']) > 0) {
            $params['cSuche']              = StringHandler::filterXSS($params['cSuche']);
            $this->search->cSuche          = $params['cSuche'];
            $this->searchQuery->cSuche     = $this->search->cSuche;
            $oSuchanfrage                  = Shop::DB()->select(
                'tsuchanfrage',
                'cSuche', $this->search->cSuche,
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
            $this->searchQuery->init($kSuchAnfrage);
            $this->searchQuery->cSuche = $params['cSuche'];
            $this->EchteSuche          = new stdClass();
            $this->EchteSuche->cSuche  = $params['cSuche'];
            $this->baseState           = $this->searchQuery;
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
    private function initAttributeFilters($values)
    {
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
        $this->filters[] = $filter->setData($this);

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
        $this->activeFilters[] = $filter->setData($this)->init($filterValue)->generateActiveFilterData();

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
                        || $f->getVisibility() === AbstractFilter::SHOW_CONTENT)
                    && count($f->getOptions()) > 0
                    && (!$f->isInitialized() || $f->getType() === AbstractFilter::FILTER_TYPE_OR);
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
     * @return array|IFilter[]
     */
    public function getAttributeFilters()
    {
        return $this->attributeFilterCollection;
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
        return $this->search->kSuchanfrage > 0;
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
        if ($this->getFilterCount() > 0) {
            if (empty($this->search->cSuche)
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
                    header('Location: ' . $this->baseURL . $this->manufacturerFilter->getSeo($this->getLanguageID()));
                    exit();
                }
                // we have a category filter that doesn't filter anything
                if ($this->categoryFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . $this->baseURL . $this->categoryFilter->getSeo($this->getLanguageID()));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() &&
                $this->manufacturer->getSeo($this->getLanguageID()) !== null
            ) {
                // we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . $this->baseURL . $this->manufacturer->getSeo($this->getLanguageID()));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() &&
                $this->category->getSeo($this->getLanguageID()) !== null
            ) {
                // we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . $this->baseURL . $this->category->getSeo($this->getLanguageID()));
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
        $sort->join        = (new FilterJoin())->setOrigin(__CLASS__);
        if (isset($_SESSION['Usersortierung'])) {
            $Artikelsortierung          = $this->metaData->mapUserSorting($_SESSION['Usersortierung']);
            $_SESSION['Usersortierung'] = $Artikelsortierung;
        }
        if ($this->nSortierung > 0 && $_SESSION['Usersortierung'] === 100) {
            $Artikelsortierung = $this->nSortierung;
        }
        $sort->orderBy = 'tartikel.nSort, tartikel.cName';
        switch ((int)$Artikelsortierung) {
            case SEARCH_SORT_STANDARD:
                $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                if ($this->category->getValue() > 0) {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                } elseif (isset($_SESSION['Usersortierung'])
                    && $_SESSION['Usersortierung'] === 100
                    && $this->search->isInitialized()
                ) {
                    $sort->orderBy = 'tsuchcachetreffer.nSort';
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
                $sort->join->setComment('join from SORT by bestseller')
                           ->setType('LEFT JOIN')
                           ->setTable('tbestseller')
                           ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
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
    public function getArticlesPerPageLimit()
    {
        if ($this->articleLimit > 0) {
            $limit = (int)$this->getArticleLimit();
        } elseif (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            $limit = (int)$_SESSION['ArtikelProSeite'];
        } elseif (isset($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel)
            && $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0
        ) {
            $limit = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = ($max = $this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) > 0
                ? (int)$max
                : 20;
        }

        return min($limit, ARTICLES_PER_PAGE_HARD_LIMIT);
    }

    /**
     * @return string
     */
    public function getStorageFilterSQL()
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
     * get list of product IDs matching the current filter
     *
     * @return int[]
     */
    public function getProductKeys()
    {
        $order = $this->getOrder();
        $state = $this->getCurrentStateData();

        $state->joins[] = $order->join;

        return array_map(
            function ($e) {
                return (int)$e->kArtikel;
            },
            Shop::DB()->query(
                $this->getBaseQuery(
                    ['tartikel.kArtikel'],
                    $state->joins,
                    $state->conditions,
                    $state->having,
                    $order->orderBy
                ),
                2
            )
        );
    }

    /**
     * @return string
     */
    private function getHash()
    {
        $state = $this->getBaseState();
        $hash  = [
            'state' => $state->getClassName() . $state->getValue(),
            'page'  => $this->nSeite,
            'order' => $this->getOrder(),
            'app'   => $this->getArticlesPerPageLimit(),
            'lid'   => $this->getLanguageID(),
            'cgrp'  => $this->getCustomerGroupID()
        ];
        foreach ($this->getActiveFilters() as $filter) {
            $hash[$filter->getClassName()][] = $filter->getValue();
        }

        return md5(json_encode($hash));
    }

    /**
     * @param bool           $forProductListing - if true, return $oSuchergebnisse object, otherwise keys only
     * @param Kategorie|null $currentCategory
     * @param bool           $fillArticles - if true, return Artikel class instances, otherwise keys only
     * @param int            $limit
     * @return stdClass|Collection
     */
    public function getProducts($forProductListing = true, $currentCategory = null, $fillArticles = true, $limit = 0)
    {
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel, die auch wirklich auf der Seite angezeigt werden

        $limitPerPage = $limit > 0 ? $limit : $this->getArticlesPerPageLimit();
        $nLimitN      = $limitPerPage * ($this->nSeite - 1);
        $max          = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];
        if ($this->searchResults === null) {
            $this->searchResults                       = new stdClass();
            $this->searchResults->Artikel              = new stdClass();
            $this->searchResults->Artikel->elemente    = new Collection();
            $this->searchResults->Artikel->articleKeys = $this->getProductKeys();
            $this->searchResults->GesamtanzahlArtikel  = count($this->searchResults->Artikel->articleKeys);

            if (!empty($this->search->cSuche)) {
                $this->search->saveQuery($this->searchResults->GesamtanzahlArtikel);
                $this->search->setQueryID($this->search->cSuche, $this->getLanguageID());
                $this->searchQuery->setValue($this->search->kSuchanfrage)->setSeo($this->languages);
            }

            $this->searchResults->ArtikelVon                  = $nLimitN + 1;
            $this->searchResults->ArtikelBis                  = min(
                $nLimitN + $limitPerPage,
                $this->searchResults->GesamtanzahlArtikel
            );
            $this->searchResults->Seitenzahlen                = new stdClass();
            $this->searchResults->Seitenzahlen->AktuelleSeite = $this->nSeite;
            $this->searchResults->Seitenzahlen->MaxSeiten     = ceil(
                $this->searchResults->GesamtanzahlArtikel / $limitPerPage
            );
            $this->searchResults->Seitenzahlen->minSeite      = min(
                $this->searchResults->Seitenzahlen->AktuelleSeite - $max / 2,
                0
            );
            $this->searchResults->Seitenzahlen->maxSeite      = max(
                $this->searchResults->Seitenzahlen->MaxSeiten,
                $this->searchResults->Seitenzahlen->minSeite + $max - 1
            );
            if ($this->searchResults->Seitenzahlen->maxSeite > $this->searchResults->Seitenzahlen->MaxSeiten) {
                $this->searchResults->Seitenzahlen->maxSeite = $this->searchResults->Seitenzahlen->MaxSeiten;
            }
            $this->searchResults = $this->setFilterOptions($this->searchResults, $currentCategory);
            // Header bauen
            $this->searchResults->SuchausdruckWrite = $this->metaData->getHeader();
        }
        if ($fillArticles === true) {
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
            foreach (array_slice($this->searchResults->Artikel->articleKeys, $nLimitN, $limitPerPage) as $id) {
                $article = (new Artikel())->fuelleArtikel($id, $opt);
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($article !== null && $article->nIstVater === 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $id;
                }
                $this->searchResults->Artikel->elemente->addItem($article);
            }
        }
        $this->createUnsetFilterURLs();
        $_SESSION['oArtikelUebersichtKey_arr']   = $this->searchResults->Artikel->articleKeys;
        $_SESSION['nArtikelUebersichtVLKey_arr'] = [];

        return $forProductListing === true
            ? $this->searchResults
            : $this->searchResults->Artikel->elemente;
    }

    /**
     * @param bool $byType
     * @return array|IFilter[]
     */
    public function getActiveFilters($byType = false)
    {
        $filters = $byType === false
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
                    $filters['custom'][] = $activeFilter;
                } else {
                    $filters[] = $activeFilter;
                }
            } else {
                // get built-in filters
                $found = false;
                if ($activeFilter->isInitialized() && ($urlPram = $activeFilter->getUrlParam()) !== '') {
                    if ($byType) {
                        $filters[$urlPram][] = $activeFilter;
                    } else {
                        $filters[] = $activeFilter;
                    }
                    continue;
                }
                // get built-in filters that were manually set
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
        foreach ($this->getActiveFilters(true) as $type => $filters) {
            $count = count($filters);
            if ($count > 1 && $type !== 'misc' && $type !== 'custom') {
                $singleConditions = [];
                $filters          = array_filter(
                    $filters,
                    function ($f) use ($ignore) {
                        /** @var IFilter $f */
                        return $ignore === null
                                || (is_string($ignore) && $f->getClassName() !== $ignore)
                                || (is_object($ignore) && $f !== $ignore);
                    }
                );
                $orFilters        = array_filter(
                    $filters,
                    function ($f) {
                        /** @var IFilter $f */
                        return $f->getType() === AbstractFilter::FILTER_TYPE_OR;
                    }
                );

                /** @var AbstractFilter $filter */
                foreach ($filters as $idx => $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
                    $itemJoin = $filter->getSQLJoin();
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
                if (!empty($singleConditions)) {
                    foreach ($singleConditions as $singleCondition) {
                        $data->conditions[] = $singleCondition;
                    }
                }
            } elseif ($count === 1) {
                /** @var IFilter[] $filters */
                if ($ignore === null
                    || (is_object($ignore) && $filters[0] !== $ignore)
                    || (is_string($ignore) && $filters[0]->getClassName() !== $ignore)
                ) {
                    $itemJoin    = $filters[0]->getSQLJoin();
                    $_condition  = $filters[0]->getSQLCondition();
                    $data->joins = array_merge($data->joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!empty($_condition)) {
                        $data->conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            } elseif ($count > 0 && ($type !== 'misc' || $type !== 'custom')) {
                // this is the most clean and usual behaviour.
                // 'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($filters as $filter) {
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
     * @param stdClass       $searchResults
     * @param null|Kategorie $currentCategory
     * @param bool           $selectionWizard
     * @return mixed
     */
    public function setFilterOptions($searchResults, $currentCategory = null, $selectionWizard = false)
    {
        if (!isset($searchResults->Herstellerauswahl)) {
            $searchResults->Herstellerauswahl = $this->manufacturerFilter->getOptions();
        }
        if (!isset($searchResults->Bewertung)) {
            $searchResults->Bewertung = $this->ratingFilter->getOptions();
        }
        if (!isset($searchResults->Tags)) {
            $searchResults->Tags = $this->tag->getOptions();
        }

        if (!isset($searchResults->TagsJSON)
            && $this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y'
        ) {
            $searchResults->TagsJSON = Boxen::gibJSONString(array_map(
                function ($e) {
                    $e->cURL = StringHandler::htmlentitydecode($e->cURL);
                    return $e;
                },
                $searchResults->Tags
            ));
        }
        if (!isset($searchResults->MerkmalFilter)) {
            $searchResults->MerkmalFilter = $this->attributeFilterCollection->getOptions([
                'oAktuelleKategorie' => $currentCategory,
                'bForce'             => $selectionWizard === true && function_exists('starteAuswahlAssistent')
            ]);
            if (count($searchResults->MerkmalFilter) < 1) {
                $this->attributeFilterCollection->setVisibility(AbstractFilter::SHOW_NEVER);
            }
        }
        $this->attributeFilterCollection->setFilterCollection($searchResults->MerkmalFilter);

        if (!isset($searchResults->Preisspanne)) {
            $searchResults->Preisspanne = $this->priceRangeFilter->getOptions($searchResults->GesamtanzahlArtikel);
        }
        if (!isset($searchResults->Kategorieauswahl)) {
            $searchResults->Kategorieauswahl = $this->categoryFilter->getOptions();
        }
        if (!isset($searchResults->SuchFilter)) {
            $searchResults->SuchFilter = $this->searchFilterCompat->getOptions();
        }
        if (!isset($searchResults->SuchFilterJSON)) {
            $searchResults->SuchFilterJSON = Boxen::gibJSONString(array_map(
                function ($e) {
                    $e->cURL = StringHandler::htmlentitydecode($e->cURL);
                    return $e;
                },
                $searchResults->SuchFilter
            ));
        }
        if (!isset($searchResults->Suchspecialauswahl)) {
            $searchResults->Suchspecialauswahl = empty($this->params['kSuchspecial']) && empty($this->params['kSuchspecialFilter'])
                ? $this->searchSpecialFilter->getOptions()
                : null;
        }
        if (empty($searchResults->Suchspecialauswahl)) {
            // hide category filter when a category is being browsed
            $this->searchSpecialFilter->hide();
        }
        $searchResults->customFilters = [];

        if (empty($searchResults->Kategorieauswahl) || count($searchResults->Kategorieauswahl) <= 1) {
            // hide category filter when a category is being browsed
            $this->categoryFilter->hide();
        }
        if (empty($searchResults->Preisspanne) || count($searchResults->Preisspanne) === 0) {
            // hide manufacturer filter when browsing manufacturer products
            $this->priceRangeFilter->hide();
        }
        if (empty($searchResults->Herstellerauswahl) || count($searchResults->Herstellerauswahl) === 0
            || $this->manufacturer->isInitialized()
            || ($this->manufacturerFilter->isInitialized() && count($searchResults->Herstellerauswahl) === 1)
        ) {
            // hide manufacturer filter when browsing manufacturer products
            $this->manufacturerFilter->hide();
        }
        if (empty($searchResults->Bewertung)) {
            $this->ratingFilter->hide();
        }
        $searchResults->customFilters = array_filter(
            $this->filters,
            function ($e) {
                /** @var IFilter $e */
                $isCustom = $e->isCustom();
                if ($isCustom && count($e->getOptions()) === 0) {
                    $e->hide();
                }

                return $isCustom;
            }
        );

        return $searchResults;
    }

    /**
     * @return string|null
     */
    public function getUnsetAllFiltersURL()
    {
        return isset($this->URL->cNoFilter)
            ? $this->URL->cNoFilter
            : null;
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
        array $select = ['tartikel.kArtikel'],
        array $joins,
        array $conditions,
        array $having = [],
        $order = '',
        $limit = '',
        array $groupBy = ['tartikel.kArtikel']
    ) {
        $joins[] = (new FilterJoin())
            ->setComment('article visiblity join from getBaseQuery')
            ->setType('LEFT JOIN')
            ->setTable('tartikelsichtbarkeit')
            ->setOrigin(__CLASS__)
            ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID());
        // remove duplicate joins
        $checked = [];
        $joins   = array_filter(
            $joins,
            function ($j) use (&$checked) {
                if (is_string($j)) {
                    throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $j);
                }
                /** @var FilterJoin $j */
                if (!in_array($j->getTable(), $checked, true)) {
                    $checked[] = $j->getTable();
                    return true;
                }

                return false;
            }
        );
        // default base conditions
        $conditions[] = 'tartikelsichtbarkeit.kArtikel IS NULL';
        $conditions[] = 'tartikel.kVaterArtikel = 0';
        $conditions[] = $this->getStorageFilterSQL();
        // remove empty conditions
        $conditions = array_filter($conditions);
        executeHook(HOOK_PRODUCTFILTER_GET_BASE_QUERY, [
            'select'        => &$select,
            'joins'         => &$joins,
            'conditions'    => &$conditions,
            'groupBy'       => &$groupBy,
            'having'        => &$having,
            'order'         => &$order,
            'limit'         => &$limit,
            'productFilter' => $this
        ]);
        // merge FilterQuery-Conditions
        $filterQueryIndices = [];
        foreach ($conditions as $idx => $condition) {
            if (is_object($condition) && get_class($condition) === 'FilterQuery') {
                /** @var FilterQuery $condition */
                if (count($filterQueryIndices) === 0) {
                    $filterQueryIndices[] = $idx;
                    continue;
                }
                $found        = false;
                $currentWhere = $condition->getWhere();
                foreach ($filterQueryIndices as $i) {
                    $check = $conditions[$i];
                    /** @var FilterQuery $check */
                    if ($currentWhere === $check->getWhere()) {
                        $found = true;
                        $check->setParams(array_merge_recursive($check->getParams(), $condition->getParams()));
                        unset($conditions[$idx]);
                        break;
                    }
                }
                if ($found === false) {
                    $filterQueryIndices[] = $idx;
                }
            }
        }
        // build sql string
        $conditionsString = implode(' AND ', array_map(function ($a) {
            if (is_string($a) || (is_object($a) && get_class($a) === 'FilterQuery')) {
                return $a;
            }

            return '(' . implode(' AND ', $a) . ')';
        }, $conditions));
        $joinString       = implode("\n", $joins);
        $havingString     = implode(' AND ', $having);
        if (!empty($limit)) {
            $limit = ' LIMIT ' . $limit;
        }
        if (!empty($order)) {
            $order = 'ORDER BY ' . $order;
        }
        if (!empty($conditionsString)) {
            $conditionsString = ' WHERE ' . $conditionsString;
        }
        $groupByString = empty($groupBy)
            ? ''
            : 'GROUP BY ' . implode(', ', $groupBy);

        return 'SELECT ' . implode(', ', $select) . '
            FROM tartikel ' . $joinString . "\n" .
            $conditionsString . "\n" .
            '#default group by' . "\n" .
            $groupByString . "\n" .
            $havingString . "\n" .
            '#order by' . "\n" .
            $order . "\n" .
            '#limit sql' . "\n" .
            $limit;
    }

    /**
     * converts legacy stdClass filters to real filter instances
     *
     * @param stdClass|IFilter $extraFilter
     * @return IFilter
     * @throws InvalidArgumentException
     */
    private function convertExtraFilter($extraFilter = null)
    {
        if ($extraFilter === null || get_class($extraFilter) !== 'stdClass') {
            return $extraFilter;
        }
        $filter = null;
        if (isset($extraFilter->KategorieFilter->kKategorie)
            || (isset($extraFilter->FilterLoesen->Kategorie) && $extraFilter->FilterLoesen->Kategorie === true)
        ) {
            $filter = (new FilterItemCategory($this))->init(isset($extraFilter->KategorieFilter->kKategorie)
                ? $extraFilter->KategorieFilter->kKategorie
                : null
            );
        } elseif (isset($extraFilter->HerstellerFilter->kHersteller)
            || (isset($extraFilter->FilterLoesen->Hersteller) && $extraFilter->FilterLoesen->Hersteller === true)
        ) {
            $filter = (new FilterItemManufacturer($this))->init(isset($extraFilter->HerstellerFilter->kHersteller)
                ? $extraFilter->HerstellerFilter->kHersteller
                : null
            );
        } elseif (isset($extraFilter->MerkmalFilter->kMerkmalWert)
            || isset($extraFilter->FilterLoesen->MerkmalWert)
        ) {
            $filter = (new FilterItemAttribute($this))->init(isset($extraFilter->MerkmalFilter->kMerkmalWert)
                ? $extraFilter->MerkmalFilter->kMerkmalWert
                : $extraFilter->FilterLoesen->MerkmalWert
            );
        } elseif (isset($extraFilter->FilterLoesen->Merkmale)) {
            $filter = (new FilterItemAttribute($this))->init($extraFilter->FilterLoesen->Merkmale);
        } elseif (isset($extraFilter->PreisspannenFilter->fVon)
            || (isset($extraFilter->FilterLoesen->Preisspannen) && $extraFilter->FilterLoesen->Preisspannen === true)
        ) {
            $filter = (new FilterItemPriceRange($this))->init(isset($extraFilter->PreisspannenFilter->fVon)
                ? ($extraFilter->PreisspannenFilter->fVon . '_' . $extraFilter->PreisspannenFilter->fBis)
                : null
            );
        } elseif (isset($extraFilter->BewertungFilter->nSterne)
            || (isset($extraFilter->FilterLoesen->Bewertungen) && $extraFilter->FilterLoesen->Bewertungen === true)
        ) {
            $filter = (new FilterItemRating($this))->init(isset($extraFilter->BewertungFilter->nSterne)
                ? $extraFilter->BewertungFilter->nSterne
                : null
            );
        } elseif (isset($extraFilter->TagFilter->kTag)
            || (isset($extraFilter->FilterLoesen->Tags) && $extraFilter->FilterLoesen->Tags === true)
        ) {
            $filter = (new FilterItemTag($this))->init(isset($extraFilter->TagFilter->kTag)
                ? $extraFilter->TagFilter->kTag
                : null
            );
        } elseif (isset($extraFilter->SuchspecialFilter->kKey)
            || (isset($extraFilter->FilterLoesen->Suchspecials) && $extraFilter->FilterLoesen->Suchspecials === true)
        ) {
            $filter = (new FilterItemSearchSpecial($this))->init(isset($extraFilter->SuchspecialFilter->kKey)
                ? $extraFilter->SuchspecialFilter->kKey
                : null
            );
        } elseif (isset($extraFilter->searchFilter->kSuchanfrage)
            || !empty($extraFilter->FilterLoesen->searchFilter)
        ) {
            $filter = (new FilterBaseSearchQuery($this))->init(isset($extraFilter->searchFilter->kSuchanfrage)
                ? $extraFilter->searchFilter->kSuchanfrage
                : null
            );
        } elseif (isset($extraFilter->FilterLoesen->searchFilter)) {
            $filter = (new FilterBaseSearchQuery($this))->init($extraFilter->FilterLoesen->searchFilter);
        } elseif (isset($extraFilter->FilterLoesen->Erscheinungsdatum)
            && $extraFilter->FilterLoesen->Erscheinungsdatum === true
        ) {
            //@todo@todo@todo
            return $filter;
        } else {
            Shop::dbg($extraFilter, false, 'ExtraFilter:');
            throw new InvalidArgumentException('Unrecognized additional unset filter: ' . json_encode($extraFilter));
        }

        return $filter->setDoUnset(isset($extraFilter->FilterLoesen));
    }

    /**
     * @param IFilter $extraFilter
     * @param bool    $bCanonical
     * @param bool    $debug
     * @return string
     */
    public function getURL($extraFilter = null, $bCanonical = false, $debug = false)
    {
        $extraFilter        = $this->convertExtraFilter($extraFilter);
        $baseURL            = $this->baseURL;
        $nonSeoFilterParams = [];
        $seoFilterParams    = [];
        $urlParams          = [
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
        if (($baseState = $this->getBaseState())->isInitialized()) {
            $filterSeoUrl = $baseState->getSeo($this->getLanguageID());
            if (!empty($filterSeoUrl)) {
                $seoParam          = new stdClass();
                $seoParam->value   = '';
                $seoParam->sep     = '';
                $seoParam->param   = '';
                $seoParam->seo     = $filterSeoUrl;
                $seoFilterParams[] = $seoParam;
            } else {
                $nonSeoFilterParams[] = [$baseState->getUrlParam() => $baseState->getValue()];
            }
        }
        if ($bCanonical === true) {
            return $baseURL . $this->buildURLString($seoFilterParams, $nonSeoFilterParams);
        }
        $url           = $baseURL;
        $activeFilters = $this->getActiveFilters();
        // we need the base state + all active filters + optionally the additional filter to generate the correct url
        if ($extraFilter !== null && $extraFilter !== null && !$extraFilter->getDoUnset()) {
            $activeFilters[] = $extraFilter;
        }
        $ignore      = null;
        $ignoreValue = null;
        // remove extra filters from url array if getDoUnset equals true
        if ($extraFilter !== null && $extraFilter->getDoUnset() === true) {
            $ignore      = $extraFilter->getUrlParam();
            $ignoreValue = $extraFilter->getValue();
        }
        // add all filter urls to an array indexed by the filter's url param
        /** @var IFilter $filter */
        foreach ($activeFilters as $filter) {
            $urlParam    = $filter->getUrlParam();
            $filterValue = $filter->getValue();
            if ($ignore !== null && $urlParam === $ignore) {
                if ($ignoreValue === 0 || $ignoreValue === $filterValue) {
                    // unset filter was given for this whole filter or this current value
                    continue;
                }
                if (is_array($filterValue) && in_array($ignoreValue, $filterValue, true)) {
                    // ignored value was found in array of values
                    $idx = array_search($ignoreValue, $filterValue, true);
                    unset($filterValue[$idx]);
                }
            }
            if (!isset($urlParams[$urlParam])) {
                $urlParams[$urlParam] = [];
            }

            if (isset($urlParams[$urlParam][0]->value) && is_array($urlParams[$urlParam][0]->value)) {
                if (is_array($filterValue)) {
                    foreach ($filterValue as $v) {
                        $urlParams[$urlParam][0]->value[] = $v;
                    }
                } else {
                    $urlParams[$urlParam][0]->value[] = $filterValue;
                }
                if (!is_array($urlParams[$urlParam][0]->seo)) {
                    $urlParams[$urlParam][0]->seo = [];
                }
                $urlParams[$urlParam][0]->seo[] = $filter->getSeo($this->getLanguageID());
            } else {
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filterValue;
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filter->getSeo($this->getLanguageID());
                $filterSeoData->param   = $urlParam;
                $urlParams[$urlParam][] = $filterSeoData;

                $activeValues = $filter->getActiveValues();
                if (is_array($activeValues) && count($activeValues) > 0) {
                    $filterSeoData->value = [];
                    $filterSeoData->seo   = [];
                    foreach ($activeValues as $activeValue) {
                        $val = $activeValue->getValue();
                        if ($ignore === null || $ignore !== $urlParam || $ignoreValue === 0 || $ignoreValue !== $val) {
                            $filterSeoData->value[] = $activeValue->getValue();
                            $filterSeoData->seo[]   = $activeValue->getURL();
                        }
                    }
                }
            }
        }
        // build url string from data array
        foreach ($urlParams as $filterID => $filters) {
            foreach ($filters as $f) {
                if (!empty($f->seo) && !empty($f->sep)) {
                    $seoFilterParams[] = $f;
                } else {
                    if (!isset($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID] = $f->value;
                    } elseif (is_string($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID]   = [$nonSeoFilterParams[$filterID]];
                        $nonSeoFilterParams[$filterID][] = $f->value;
                    }
                }
            }
        }
        $url .= $this->buildURLString($seoFilterParams, $nonSeoFilterParams);
        if ($debug) {
            Shop::dbg($url, false, 'returning:');
        }

        return $url;
    }

    /**
     * @param stdClass[] $seoParts
     * @param array      $nonSeoParts
     * @return mixed
     */
    private function buildURLString($seoParts, $nonSeoParts)
    {
        $url = '';
        foreach ($seoParts as $seoData) {
            $url .= $seoData->sep . (is_array($seoData->seo)
                    ? implode($seoData->sep, $seoData->seo)
                    : $seoData->seo);
        }
        $nonSeoPart = http_build_query($nonSeoParts);
        if ($nonSeoPart !== '') {
            $url .= '?' . $nonSeoPart;
        }

        // remove numeric indices from array representation
        return preg_replace('/%5B[\d]+%5D/imU', '%5B%5D', $url);
    }

    /**
     * URLs generieren, die Filter lösen
     *
     * @param stdClass $searchResults
     * @return $this
     */
    public function createUnsetFilterURLs($searchResults = null)
    {
        if ($searchResults === null) {
            $searchResults = $this->searchResults;
        }
        $extraFilter                = (new FilterItemCategory($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleKategorien = $this->getURL($extraFilter);
        $this->categoryFilter->setUnsetFilterURL($this->URL->cAlleKategorien);

        $extraFilter                = (new FilterItemManufacturer($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleHersteller = $this->getURL($extraFilter);
        $this->manufacturer->setUnsetFilterURL($this->URL->cAlleHersteller);
        $this->manufacturerFilter->setUnsetFilterURL($this->URL->cAlleHersteller);

        $additionalFilter = (new FilterItemAttribute($this))->setDoUnset(true);

        foreach ($this->attributeFilter as $oMerkmal) {
            if ($oMerkmal->getAttributeID() > 0) {
                $this->URL->cAlleMerkmale[$oMerkmal->getAttributeID()] = $this->getURL(
                    $additionalFilter->init($oMerkmal->getAttributeID())->setSeo($this->languages)
                );
                $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmale);
            }
            if (is_array($oMerkmal->getValue())) {
                $urls = [];
                foreach ($oMerkmal->getValue() as $mmw) {
                    $additionalFilter->init($mmw)->setValue($mmw);
                    $this->URL->cAlleMerkmalWerte[$mmw] = $this->getURL(
                        $additionalFilter
                    );
                    $urls[$mmw]                         = $this->URL->cAlleMerkmalWerte[$mmw];
                }
                $oMerkmal->setUnsetFilterURL($urls);
            } else {
                $this->URL->cAlleMerkmalWerte[$oMerkmal->getValue()] = $this->getURL(
                    $additionalFilter->init($oMerkmal->getValue())
                );
                $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmalWerte);
            }
        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->attributeValue->isInitialized()
            && !isset($this->URL->cAlleMerkmalWerte[$this->attributeValue->getValue()])
        ) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace(
                $this->attributeValue->getSeo($this->getLanguageID()) . SEP_MERKMAL,
                '',
                $this->URL->cAlleKategorien
            );
            if ($_mmwSeo !== $this->URL->cAlleKategorien) {
                $_url                                                            = $_mmwSeo;
                $this->URL->cAlleMerkmalWerte[$this->attributeValue->getValue()] = $_url;
                $this->attributeValue->setUnsetFilterURL($_url);
            }
        }
        $extraFilter                  = (new FilterItemPriceRange($this))->init(null)->setDoUnset(true);
        $this->URL->cAllePreisspannen = $this->getURL($extraFilter);
        $this->priceRangeFilter->setUnsetFilterURL($this->URL->cAllePreisspannen);

        $extraFilter                 = (new FilterItemRating($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleBewertungen = $this->getURL($extraFilter);
        $this->ratingFilter->setUnsetFilterURL($this->URL->cAlleBewertungen);

        $extraFilter          = (new FilterItemTag($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleTags = $this->getURL($extraFilter);
        $this->tag->setUnsetFilterURL($this->URL->cAlleTags);
        $this->tagFilterCompat->setUnsetFilterURL($this->URL->cAlleTags);
        foreach ($this->tagFilter as $tagFilter) {
            $tagFilter->setUnsetFilterURL($this->URL->cAlleTags);
        }

        $extraFilter                  = (new FilterItemSearchSpecial($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleSuchspecials = $this->getURL($extraFilter);
        $this->searchSpecialFilter->setUnsetFilterURL($this->URL->cAlleSuchspecials);

        $extraFilter = (new FilterBaseSearchQuery($this))->init(null)->setDoUnset(true);
        foreach ($this->searchFilter as $oSuchFilter) {
            if ($oSuchFilter->getValue() > 0) {
                $_url                                                   = $this->getURL($extraFilter);
                $this->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $_url;
                $oSuchFilter->setUnsetFilterURL($_url);
            }
        }

        foreach (array_filter(
                     $this->filters,
                     function ($f) {
                         /** @var IFilter $f */
                         return $f->isInitialized() && $f->isCustom();
                     }
                 ) as $filter
        ) {
            $className       = $filter->getClassName();
            $idx             = 'cAlle' . $className;
            $extraFilter     = clone $filter;
            $this->URL->$idx = [];
            $extraFilter->setDoUnset(true);
            if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                foreach ($filter->getValue() as $filterValue) {
                    $extraFilter->setValue($filterValue);
                    $this->URL->$idx[$filterValue] = $this->getURL($extraFilter);
                }
            } else {
                $extraFilter->setValue($filter->getValue());
                $this->URL->$idx = $this->getURL($extraFilter);
            }
            $filter->setUnsetFilterURL($this->URL->$idx);
        }
        // Filter reset
        $cSeite = $searchResults->Seitenzahlen->AktuelleSeite > 1
            ? SEP_SEITE . $searchResults->Seitenzahlen->AktuelleSeite
            : '';

        $this->URL->cNoFilter = $this->getURL(null, true) . $cSeite;

        return $this;
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
