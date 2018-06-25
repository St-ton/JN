<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use DB\ReturnType;
use Filter\Items\ItemSearch;
use Filter\Items\ItemAttribute;
use Filter\Items\ItemCategory;
use Filter\Items\ItemLimit;
use Filter\Items\ItemManufacturer;
use Filter\Items\ItemPriceRange;
use Filter\Items\ItemRating;
use Filter\Items\ItemSearchSpecial;
use Filter\Items\ItemSort;
use Filter\Items\ItemTag;
use Filter\Pagination\Info;
use Filter\SortingOptions\Factory;
use Filter\States\DummyState;
use Filter\States\BaseAttribute;
use Filter\States\BaseCategory;
use Filter\States\BaseManufacturer;
use Filter\States\BaseSearchQuery;
use Filter\States\BaseSearchSpecial;
use Filter\States\BaseTag;
use function Functional\first;
use function Functional\group;
use function Functional\map;
use function Functional\select;
use Mapper\SortingType;
use Tightenco\Collect\Support\Collection;

/**
 * Class ProductFilter
 */
class ProductFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var BaseCategory
     */
    private $category;

    /**
     * @var ItemCategory
     */
    private $categoryFilter;

    /**
     * @var BaseManufacturer
     */
    private $manufacturer;

    /**
     * @var ItemManufacturer
     */
    private $manufacturerFilter;

    /**
     * @var BaseAttribute
     */
    private $attributeValue;

    /**
     * @var BaseSearchQuery
     */
    private $searchQuery;

    /**
     * @var ItemSearch[]
     */
    private $searchFilter = [];

    /**
     * @var ItemTag[]
     */
    private $tagFilter = [];

    /**
     * @var ItemAttribute[]
     */
    private $attributeFilter = [];

    /**
     * @var ItemSearchSpecial
     */
    private $searchSpecialFilter;

    /**
     * @var ItemRating
     */
    private $ratingFilter;

    /**
     * @var ItemPriceRange
     */
    private $priceRangeFilter;

    /**
     * @var BaseTag
     */
    private $tag;

    /**
     * @var BaseSearchSpecial
     */
    private $searchSpecial;

    /**
     * @var ItemSearch
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
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @var FilterInterface[]
     */
    private $activeFilters = [];

    /**
     * @var FilterInterface
     */
    private $baseState;

    /**
     * @var NavigationURLsInterface
     */
    private $url;

    /**
     * @var ItemTag
     */
    public $tagFilterCompat;

    /**
     * @var ItemAttribute
     */
    private $attributeFilterCollection;

    /**
     * @var ItemSearch
     */
    public $searchFilterCompat;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var ProductFilterSearchResultsInterface
     */
    private $searchResults;

    /**
     * @var MetadataInterface
     */
    private $metaData;

    /**
     * @var ProductFilterSQLInterface
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
     * @var ItemSort
     */
    private $sorting;

    /**
     * @var ItemLimit
     */
    private $limits;

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
     * @param array $languages
     * @param int   $currentLanguageID
     * @param array $config
     */
    public function __construct(array $languages = null, int $currentLanguageID = null, array $config = null)
    {
        $this->url               = new NavigationURLs();
        $this->languages         = $languages ?? \Sprache::getInstance()->getLangArray();
        $this->conf              = $config ?? \Shopsetting::getInstance()->getAll();
        $this->languageID        = $currentLanguageID ?? \Shop::getLanguageID();
        $this->customerGroupID   = \Session::CustomerGroup()->getID();
        $this->baseURL           = \Shop::getURL() . '/';
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
    public function showChildProducts(): bool
    {
        return $this->showChildProducts;
    }

    /**
     * @param bool $showChildProducts
     * @return ProductFilter
     */
    public function setShowChildProducts(bool $showChildProducts): self
    {
        $this->showChildProducts = $showChildProducts;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->nSortierung;
    }

    /**
     * @param int $nSortierung
     * @return ProductFilter
     */
    public function setSort(int $nSortierung): self
    {
        $this->nSortierung = $nSortierung;

        return $this;
    }

    /**
     * @return NavigationURLsInterface
     */
    public function getURL(): NavigationURLsInterface
    {
        return $this->url;
    }

    /**
     * @param NavigationURLsInterface $url
     * @return ProductFilter
     */
    public function setURL(NavigationURLsInterface $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly read from ProductFilter instance
     *
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly set on ProductFilter instance
     *
     * @param array $languages
     * @return array
     */
    public function setLanguages(array $languages): array
    {
        $this->languages = $languages;

        return $languages;
    }

    /**
     * @return ProductFilterSQLInterface
     */
    public function getFilterSQL(): ProductFilterSQLInterface
    {
        return $this->filterSQL;
    }

    /**
     * @param ProductFilterSQLInterface $filterSQL
     * @return ProductFilter
     */
    public function setFilterSQL(ProductFilterSQLInterface $filterSQL): self
    {
        $this->filterSQL = $filterSQL;

        return $this;
    }

    /**
     * @return ProductFilterURL
     */
    public function getFilterURL(): ProductFilterURL
    {
        return $this->filterURL;
    }

    /**
     * @param ProductFilterURL $filterURL
     * @return ProductFilter
     */
    public function setFilterURL(ProductFilterURL $filterURL): self
    {
        $this->filterURL = $filterURL;

        return $this;
    }

    /**
     * @param bool $products
     * @return ProductFilterSearchResultsInterface|Collection
     */
    public function getSearchResults($products = true)
    {
        if ($this->searchResults === null) {
            $this->searchResults = new ProductFilterSearchResults();
            $this->searchResults->setProducts(new Collection());
        }

        return $products === true
            ? $this->searchResults->getProducts()
            : $this->searchResults;
    }

    /**
     * @param ProductFilterSearchResultsInterface $results
     * @return $this
     */
    public function setSearchResults(ProductFilterSearchResultsInterface $results): self
    {
        $this->searchResults = $results;

        return $this;
    }

    /**
     * @return MetadataInterface
     */
    public function getMetaData(): MetadataInterface
    {
        return $this->metaData;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->nSeite;
    }

    /**
     * @return array|null
     */
    public function getAvailableLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @return FilterInterface
     */
    public function getBaseState()
    {
        return $this->baseState;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setBaseState(FilterInterface $filter): self
    {
        $this->baseState = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     * @return ProductFilter
     */
    public function setBaseURL(string $baseURL): self
    {
        $this->baseURL = $baseURL;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->conf;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->conf = $config;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCustomerGroupID($id): self
    {
        $this->customerGroupID = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setLanguageID($id): self
    {
        $this->languageID = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductLimit(): int
    {
        return $this->productLimit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setProductLimit($limit): self
    {
        $this->productLimit = (int)$limit;

        return $this;
    }

    /**
     * @return ItemSort
     */
    public function getSorting(): ItemSort
    {
        return $this->sorting;
    }

    /**
     * @param ItemSort $sorting
     * @return $this
     */
    public function setSorting(ItemSort $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return ItemLimit
     */
    public function getLimits(): ItemLimit
    {
        return $this->limits;
    }

    /**
     * @param ItemLimit $limits
     * @return $this
     */
    public function setLimits(ItemLimit $limits): self
    {
        $this->limits = $limits;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->getParamsPrototype(), $this->params);
    }

    /**
     * @return array - default array keys
     */
    private function getParamsPrototype(): array
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
    public function initBaseStates(): self
    {
        $this->category       = new BaseCategory($this);
        $this->categoryFilter = new ItemCategory($this);

        $this->manufacturer       = new BaseManufacturer($this);
        $this->manufacturerFilter = new ItemManufacturer($this);

        $this->searchQuery = new BaseSearchQuery($this);

        $this->attributeValue = new BaseAttribute($this);

        $this->tag = new BaseTag($this);

        $this->searchSpecial = new BaseSearchSpecial($this);

        $this->attributeFilter = [];
        $this->searchFilter    = [];
        $this->tagFilter       = [];

        $this->searchSpecialFilter = new ItemSearchSpecial($this);

        $this->ratingFilter = new ItemRating($this);

        $this->priceRangeFilter = new ItemPriceRange($this);

        $this->tagFilterCompat           = new ItemTag($this);
        $this->attributeFilterCollection = new ItemAttribute($this);
        $this->searchFilterCompat        = new ItemSearch($this);

        $this->search = new ItemSearch($this);

        $this->baseState = new DummyState($this);

        executeHook(HOOK_PRODUCTFILTER_INIT, ['productFilter' => $this]);

        $this->filters[] = $this->categoryFilter;
        $this->filters[] = $this->manufacturerFilter;
        $this->filters[] = $this->attributeFilterCollection;
        $this->filters[] = $this->searchSpecialFilter;
        $this->filters[] = $this->priceRangeFilter;
        $this->filters[] = $this->ratingFilter;

        $this->sorting = new ItemSort($this);
        $this->limits  = new ItemLimit($this);

        $this->sorting->setFactory(new Factory($this));
        $this->sorting->registerSortingOptions();

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates(array $params): self
    {
        $params = array_merge($this->getParamsPrototype(), $params);
        if ($params['kKategorie'] > 0) {
            $this->baseState = $this->category->init($params['kKategorie']);
        } elseif ($params['kHersteller'] > 0) {
            $this->manufacturer->init($params['kHersteller']);
            $this->baseState = $this->manufacturer;
        } elseif ($params['kMerkmalWert'] > 0) {
            $this->attributeValue = (new BaseAttribute($this))->init($params['kMerkmalWert']);
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
            $this->tagFilter[] = $this->addActiveFilter(new ItemTag($this), $tf);
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
            $this->searchFilter[] = $this->addActiveFilter(new ItemSearch($this), $sf);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] !== 0) {
            $this->productLimit = (int)$params['nArtikelProSeite'];
        }
        // @todo: how to handle strlen($params['cSuche']) === 0?
        if ($params['kSuchanfrage'] > 0) {
            $oSuchanfrage = \Shop::Container()->getDB()->select('tsuchanfrage', 'kSuchanfrage',
                $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->search->setName($oSuchanfrage->cSuche);
            }
            // Suchcache beachten / erstellen
            $searchName = $this->search->getName();
            if (!empty($searchName)) {
                $this->search->setSearchCacheID($this->searchQuery->editSearchCache());
                $this->searchQuery->init($oSuchanfrage->kSuchanfrage);
                $this->searchQuery->setSearchCacheID($this->search->getSearchCacheID())
                                  ->setName($this->search->getName());
                if (!$this->baseState->isInitialized()) {
                    $this->baseState = $this->searchQuery;
                }
            }
        } elseif ($params['cSuche'] !== null && strlen($params['cSuche']) > 0) {
            $params['cSuche'] = \StringHandler::filterXSS($params['cSuche']);
            $this->search->setName($params['cSuche']);
            $this->searchQuery->setName($params['cSuche']);
            $oSuchanfrage = \Shop::Container()->getDB()->select(
                'tsuchanfrage',
                'cSuche', $params['cSuche'],
                'kSprache', $this->getLanguageID(),
                'nAktiv', 1,
                false,
                'kSuchanfrage'
            );
            $kSuchCache   = $this->searchQuery->editSearchCache();
            $kSuchAnfrage = isset($oSuchanfrage->kSuchanfrage)
                ? (int)$oSuchanfrage->kSuchanfrage
                : $params['kSuchanfrage'];
            $this->search->setSearchCacheID($kSuchCache);
            $this->searchQuery->setSearchCacheID($kSuchCache)
                              ->init($kSuchAnfrage)
                              ->setName($params['cSuche']);
            $this->EchteSuche         = new \stdClass();
            $this->EchteSuche->cSuche = $params['cSuche'];
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
                'nSortierung'                => $_SESSION['Usersortierung'] ?? null,
                'bLagerbeachten'             => (int)$this->getConfig()['global']['artikel_artikelanzeigefilter'] ===
                    EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
            ]);
        }
        $this->nSeite = max(1, \RequestHelper::verifyGPCDataInt('seite'));
        foreach ($this->getCustomFilters() as $filter) {
            $filterParam = $filter->getUrlParam();
            $filterClass = $filter->getClassName();
            if (isset($_GET[$filterParam])) {
                // OR filters should always get an array as input - even if there is just one value active
                if (!is_array($_GET[$filterParam]) && $filter->getType()->equals(Type::OR())) {
                    $_GET[$filterParam] = [$_GET[$filterParam]];
                }
                // escape all input values
                if (($filter->getType()->equals(Type::OR()) && is_array($_GET[$filterParam]))
                    || ($filter->getType()->equals(Type::AND())
                        && (\RequestHelper::verifyGPCDataInt($filterParam) > 0 || \RequestHelper::verifyGPDataString($filterParam) !== ''))
                ) {
                    $filterValue = is_array($_GET[$filterParam])
                        ? array_map([\Shop::Container()->getDB(), 'realEscape'], $_GET[$filterParam])
                        : \Shop::Container()->getDB()->realEscape($_GET[$filterParam]);
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
    private function initAttributeFilters(array $values): self
    {
        if (count($values) === 0) {
            return $this;
        }
        $attributes = \Shop::Container()->getDB()->executeYield(
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
            $this->attributeFilter[]     = $this->addActiveFilter(new ItemAttribute($this), $attribute);
        }

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function registerFilter(FilterInterface $filter): self
    {
        $this->filters[] = $filter->setBaseData($this);

        return $this;
    }

    /**
     * @param string $filterName
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function registerFilterByClassName(string $filterName): FilterInterface
    {
        $filter = null;
        if (class_exists($filterName)) {
            /** @var FilterInterface $filter */
            $filter          = new $filterName($this);
            $this->filters[] = $filter->setClassName($filterName);
        } else {
            throw new \InvalidArgumentException('Cannot register filter class ' . $filterName);
        }

        return $filter;
    }

    /**
     * @param FilterInterface $filter
     * @param mixed           $filterValue - shortcut to set active value (same as calling init($filterValue)
     * @return FilterInterface
     */
    public function addActiveFilter(FilterInterface $filter, $filterValue): FilterInterface
    {
        $this->activeFilters[] = $filter->setBaseData($this)->init($filterValue)->generateActiveFilterData();

        return $filter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function enableFilter(FilterInterface $filter): self
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
    public function getFilterValue(string $filterClassName)
    {
        return array_reduce(
            $this->activeFilters,
            function ($carry, $item) use ($filterClassName) {
                /** @var FilterInterface $item */
                return $carry ?? ($item->getClassName() === $filterClassName
                        ? $item->getValue()
                        : null);
            }
        );
    }

    /**
     * @param string $filterClassName
     * @return bool
     */
    public function hasFilter(string $filterClassName): bool
    {
        return $this->getActiveFilterByClassName($filterClassName) !== null;
    }

    /**
     * @param string $filterClassName
     * @return FilterInterface|null
     */
    public function getFilterByClassName(string $filterClassName)
    {
        $filter = array_filter(
            $this->filters,
            function ($f) use ($filterClassName) {
                /** @var FilterInterface $f */
                return $f->getClassName() === $filterClassName;
            }
        );

        return is_array($filter) ? current($filter) : null;
    }

    /**
     * @param string $filterClassName
     * @return FilterInterface|null
     */
    public function getActiveFilterByClassName(string $filterClassName)
    {
        $filter = array_filter(
            $this->activeFilters,
            function ($f) use ($filterClassName) {
                /** @var FilterInterface $f */
                return $f->getClassName() === $filterClassName;
            }
        );

        return is_array($filter) ? current($filter) : null;
    }

    /**
     * @return FilterInterface[]
     */
    public function getCustomFilters(): array
    {
        return array_filter(
            $this->filters,
            function ($e) {
                /** @var FilterInterface $e */
                return $e->isCustom();
            }
        );
    }

    /**
     * @return FilterInterface[]
     */
    public function getAvailableFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface[] $filters
     * @return $this
     */
    public function setAvailableFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * get filters that can be displayed at content level
     *
     * @return array|FilterInterface[]
     */
    public function getAvailableContentFilters(): array
    {
        return array_filter(
            $this->filters,
            function ($f) {
                /** @var FilterInterface $f */
                return $f->getVisibility()->equals(Visibility::SHOW_ALWAYS())
                    || $f->getVisibility()->equals(Visibility::SHOW_CONTENT());
            }
        );
    }

    /**
     * @return int
     */
    public function getFilterCount(): int
    {
        return count($this->activeFilters);
    }

    /**
     * @param string          $className
     * @param FilterInterface $filter
     * @return bool
     */
    public function override(string $className, FilterInterface $filter): bool
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
     * @return ItemManufacturer
     */
    public function getManufacturerFilter(): FilterInterface
    {
        return $this->manufacturerFilter;
    }

    /**
     * @param ItemManufacturer|\stdClass $filter
     * @return $this
     */
    public function setManufacturerFilter($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kHersteller)) {
            // disallow setting manufacturer filter to empty stdClass
            return $this;
        }
        $this->manufacturerFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturerFilter(): bool
    {
        return $this->manufacturerFilter->isInitialized();
    }

    /**
     * @return BaseManufacturer
     */
    public function getManufacturer(): FilterInterface
    {
        return $this->manufacturer;
    }

    /**
     * @param ItemManufacturer $filter
     * @return $this
     */
    public function setManufacturer($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kHersteller)) {
            // disallow setting manufacturer base to empty stdClass
            return $this;
        }
        $this->manufacturer = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturer(): bool
    {
        return $this->manufacturer->isInitialized();
    }

    /**
     * returns ALL registered attribute filters
     *
     * @return ItemAttribute[]
     */
    public function getAttributeFilters(): array
    {
        return $this->attributeFilter;
    }

    /**
     * this method works like pre Shop 4.06 - only returns ACTIVE attribute filters
     *
     * @param null|int $idx
     * @return ItemAttribute|ItemAttribute[]
     */
    public function getAttributeFilter($idx = null)
    {
        return $idx === null ? $this->attributeFilter : $this->attributeFilter[$idx];
    }

    /**
     * @param array|\stdClass $filter
     * @return $this
     */
    public function setAttributeFilter($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kMerkmal)) {
            // disallow setting attribute filter to empty stdClass
            return $this;
        }
        $this->attributeFilter = $filter;

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function addAttributeFilter(FilterInterface $filter): self
    {
        $this->attributeFilter[] = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributeFilter(): bool
    {
        return count($this->attributeFilter) > 0;
    }

    /**
     * @return BaseAttribute
     */
    public function getAttributeValue(): FilterInterface
    {
        return $this->attributeValue;
    }

    /**
     * @param BaseAttribute $filter
     * @return $this
     */
    public function setAttributeValue($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kMerkmalWert)) {
            // disallow setting attribute value to empty stdClass
            return $this;
        }
        $this->attributeFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributeValue(): bool
    {
        return $this->attributeValue->isInitialized();
    }

    /**
     * @return ItemAttribute
     */
    public function getAttributeFilterCollection(): FilterInterface
    {
        return $this->attributeFilterCollection;
    }

    /**
     * @param null|int $idx
     * @return ItemTag|ItemTag[]
     */
    public function getTagFilter($idx = null)
    {
        return $idx === null ? $this->tagFilter : $this->tagFilter[$idx];
    }

    /**
     * @return bool
     */
    public function hasTagFilter(): bool
    {
        return count($this->tagFilter) > 0;
    }

    /**
     * @return BaseTag
     */
    public function getTag(): FilterInterface
    {
        return $this->tag;
    }

    /**
     * @param BaseTag $filter
     * @return $this
     */
    public function setTag($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kTag)) {
            // disallow setting tag filter to empty stdClass
            return $this;
        }
        $this->tagFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTag(): bool
    {
        return $this->tag->isInitialized();
    }

    /**
     * @return BaseCategory
     */
    public function getCategory(): FilterInterface
    {
        return $this->category;
    }

    /**
     * @param BaseCategory $filter
     * @return $this
     */
    public function setCategory($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kKategorie)) {
            // disallow setting category base to empty stdClass
            return $this;
        }
        $this->category = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategory(): bool
    {
        return $this->category->isInitialized();
    }

    /**
     * @return ItemCategory
     */
    public function getCategoryFilter(): FilterInterface
    {
        return $this->categoryFilter;
    }

    /**
     * @param BaseTag $filter
     * @return $this
     */
    public function setCategoryFilter($filter): self
    {
        if (is_a($filter, \stdClass::class) && !isset($filter->kKategorie)) {
            // disallow setting category filter to empty stdClass
            return $this;
        }
        $this->categoryFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategoryFilter(): bool
    {
        return $this->categoryFilter->isInitialized();
    }

    /**
     * @return ItemSearch
     */
    public function getSearch(): FilterInterface
    {
        return $this->search;
    }

    /**
     * @return bool
     */
    public function hasSearch(): bool
    {
        return $this->search->getName() !== null;
    }

    /**
     * @return BaseSearchQuery
     */
    public function getSearchQuery(): FilterInterface
    {
        return $this->searchQuery;
    }

    /**
     * @return bool
     */
    public function hasSearchQuery(): bool
    {
        return $this->searchQuery->isInitialized();
    }

    /**
     * @param BaseSearchQuery $filter
     * @return $this
     */
    public function setSearchQuery($filter): self
    {
        $this->searchQuery = $filter;

        return $this;
    }

    /**
     * @param null|int $idx
     * @return ItemSearch|ItemSearch[]
     */
    public function getSearchFilter($idx = null)
    {
        return $idx === null ? $this->searchFilter : $this->searchFilter[$idx];
    }

    /**
     * @return bool
     */
    public function hasSearchFilter(): bool
    {
        return count($this->searchFilter) > 0;
    }

    /**
     * @return BaseSearchSpecial
     */
    public function getSearchSpecial(): FilterInterface
    {
        return $this->searchSpecial;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setSearchSpecial(FilterInterface $filter): self
    {
        $this->searchSpecial = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecial(): bool
    {
        return $this->searchSpecial->isInitialized();
    }

    /**
     * @return ItemSearchSpecial
     */
    public function getSearchSpecialFilter(): FilterInterface
    {
        return $this->searchSpecialFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setSearchSpecialFilter(FilterInterface $filter): self
    {
        $this->searchSpecialFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecialFilter(): bool
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
     * @return ItemRating
     */
    public function getRatingFilter(): FilterInterface
    {
        return $this->ratingFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setRatingFilter(FilterInterface $filter): self
    {
        $this->ratingFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRatingFilter(): bool
    {
        return $this->ratingFilter->isInitialized();
    }

    /**
     * @return ItemPriceRange
     */
    public function getPriceRangeFilter(): FilterInterface
    {
        return $this->priceRangeFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setPriceRangeFilter(FilterInterface $filter): self
    {
        $this->priceRangeFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPriceRangeFilter(): bool
    {
        return $this->priceRangeFilter->isInitialized();
    }

    /**
     * @return $this
     */
    public function validate(): self
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
     * @param \Kategorie|null $category
     * @return $this
     */
    public function setUserSort(\Kategorie $category = null): self
    {
        $gpcSort = \RequestHelper::verifyGPCDataInt('Sortierung');
        // user wants to reset default sorting
        if ($gpcSort === SEARCH_SORT_STANDARD) {
            unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
        }
        // no sorting configured - use default from config
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl']) && $this->getSearch()->getSearchCacheID() > 0) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // custom category attribute
        if ($category !== null && !empty($category->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $mapper = new SortingType();
            $_SESSION['Usersortierung'] = $mapper->mapUserSorting(
                $category->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]
            );
        }
        if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // search special sorting
        if ($this->hasSearchSpecial()) {
            $oSuchspecialEinstellung_arr = $this->getSearchSpecialConfigMapping();
            $idx    = $this->getSearchSpecial()->getValue();
            $ssConf = isset($oSuchspecialEinstellung_arr[$idx]) ?: null;
            if ($ssConf !== null && $ssConf !== -1 && count($oSuchspecialEinstellung_arr) > 0) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$idx];
            }
        }
        // explicitly set by user
        if ($gpcSort > 0 && $gpcSort !== SEARCH_SORT_STANDARD) {
            $_SESSION['Usersortierung']         = $gpcSort;
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
        }
        $this->sorting->setActiveSortingType($_SESSION['Usersortierung']);

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchSpecialConfigMapping(): array
    {
        $config  = $this->conf['suchspecials'];
        $mapping = [
            SEARCHSPECIALS_BESTSELLER       => $config['suchspecials_sortierung_bestseller'],
            SEARCHSPECIALS_SPECIALOFFERS    => $config['suchspecials_sortierung_sonderangebote'],
            SEARCHSPECIALS_NEWPRODUCTS      => $config['suchspecials_sortierung_neuimsortiment'],
            SEARCHSPECIALS_TOPOFFERS        => $config['suchspecials_sortierung_topangebote'],
            SEARCHSPECIALS_UPCOMINGPRODUCTS => $config['suchspecials_sortierung_inkuerzeverfuegbar'],
            SEARCHSPECIALS_TOPREVIEWS       => $config['suchspecials_sortierung_topbewertet'],
        ];

        return $mapping;
    }

    /**
     * get list of product IDs matching the current filter
     *
     * @return Collection
     */
    public function getProductKeys(): Collection
    {
        $state   = $this->getCurrentStateData();
        $sorting = $this->getSorting()->getActiveSorting();
        $joins   = $state->getJoins();
        $joins[] = $sorting->getJoin();
        $qry     = $this->getFilterSQL()->getBaseQuery(
            ['tartikel.kArtikel'],
            $joins,
            $state->getConditions(),
            $state->getHaving(),
            $sorting->getOrderBy(),
            '',
            ['tartikel.kArtikel'],
            'listing'
        );

        $productKeys = collect(array_map(
            function ($e) {
                return (int)$e->kArtikel;
            },
            \Shop::Container()->getDB()->query($qry, ReturnType::ARRAY_OF_OBJECTS)
        ));

        $orderData         = new \stdClass();
        $orderData->cJoin  = $sorting->getJoin()->getSQL();
        $orderData->cOrder = $sorting->getOrderBy();

        executeHook(HOOK_FILTER_INC_GIBARTIKELKEYS, [
            'oArtikelKey_arr' => &$productKeys,
            'FilterSQL'       => new \stdClass(),
            'NaviFilter'      => $this,
            'SortierungsSQL'  => &$orderData
        ]);

        return $productKeys;
    }

    /**
     * checks if a given combination of filter class and filter value is currently active
     *
     * @param string $class
     * @param mixed  $value
     * @return bool
     */
    public function filterOptionIsActive($class, $value): bool
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
     * @param bool            $listing - if true, return ProductFilterSearchResults instance, otherwise products only
     * @param \Kategorie|null $category
     * @param bool            $fill - if true, return Artikel class instances, otherwise keys only
     * @param int             $limit
     * @return ProductFilterSearchResultsInterface|\Tightenco\Collect\Support\Collection
     */
    public function getProducts(bool $listing = true, \Kategorie $category = null, bool $fill = true, int $limit = null)
    {
        $limitPerPage = $limit ?? $this->metaData->getProductsPerPageLimit();
        $nLimitN      = $limitPerPage * ($this->nSeite - 1);
        $max          = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];
        $error        = false;
        if ($this->searchResults === null) {
            $productList         = new Collection();
            $productKeys         = $this->getProductKeys();
            $productCount        = count($productKeys);
            $this->searchResults = (new ProductFilterSearchResults())
                ->setProductCount($productCount)
                ->setProductKeys($productKeys);
            if (!empty($this->search->getName())) {
                if ($this->searchQuery->getError() === null) {
                    $this->search->saveQuery($productCount, $this->search->getName(), !$this->bExtendedJTLSearch);
                    $this->search->setQueryID($this->search->getName(), $this->getLanguageID());
                    $this->searchQuery->setValue($this->search->getValue())->setSeo($this->languages);
                } else {
                    $error = $this->searchQuery->getError();
                }
            }
            $end = min($nLimitN + $limitPerPage, $productCount);

            $this->searchResults->setOffsetStart($nLimitN + 1)
                                ->setOffsetEnd($end > 0 ? $end : $productCount);

            $total   = $limitPerPage > 0 ? ceil($productCount / $limitPerPage) : 1;
            $minPage = max($this->nSeite - floor($max / 2), 1);
            $maxPage = $minPage + $max - 1;
            if ($maxPage > $total) {
                $diff    = $total - $maxPage;
                $maxPage = $total;
                $minPage += $diff;
                $minPage = max($minPage, 1);
            }
            $pages = new Info();
            $pages->setMinPage($minPage);
            $pages->setMaxPage($maxPage);
            $pages->setTotalPages($total);
            $pages->setCurrentPage($this->nSeite);

            $this->searchResults->setPages($pages)
                                ->setFilterOptions($this, $category)
                                ->setSearchTermWrite($this->metaData->getHeader());
        } else {
            $productList = $this->searchResults->getProducts();
            $productKeys = $this->searchResults->getProductKeys();
        }
        if ($error !== false) {
            return $this->searchResults
                ->setProductCount(0)
                ->setVisibleProductCount(0)
                ->setProducts($productList)
                ->setSearchUnsuccessful(true)
                ->setSearchTerm(strip_tags(trim($this->params['cSuche'])))
                ->setError($error);
        }
        if ($fill === true) {
            // @todo: slice list of IDs when not filling?
            $opt                        = new \stdClass();
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
            if ($limitPerPage < 0) {
                $limitPerPage = null;
            }
            foreach ($productKeys->forPage($this->nSeite, $limitPerPage) as $id) {
                $productList->push((new \Artikel())->fuelleArtikel($id, $opt));
            }
            $this->searchResults->setVisibleProductCount($productList->count());
        }
        $this->url                             = $this->filterURL->createUnsetFilterURLs($this->url);
        $_SESSION['oArtikelUebersichtKey_arr'] = $productKeys;

        $this->searchResults->setProducts($productList);

        if ($listing === true) {
            // Weiterleitung, falls nur 1 Artikel rausgeholt
            $hasSubCategories = ($categoryID = $this->getCategory()->getValue()) > 0
                ? (new \Kategorie($categoryID, $this->languageID, $this->customerGroupID))
                    ->existierenUnterkategorien()
                : false;
            if ($productList->count() === 1
                && $this->getConfig()['navigationsfilter']['allgemein_weiterleitung'] === 'Y'
                && ($this->getFilterCount() > 0
                    || ($this->getCategory()->getValue() > 0 && !$hasSubCategories)
                    || !empty($this->EchteSuche->cSuche))
            ) {
                http_response_code(301);
                $product = $productList->pop();
                $url     = empty($product->cURL)
                    ? (\Shop::getURL() . '/?a=' . $product->kArtikel)
                    : (\Shop::getURL() . '/' . $product->cURL);
                header('Location: ' . $url);
                exit;
            }
        }

        return $listing === true
            ? $this->searchResults
            : $productList;
    }

    /**
     * @param bool $byType
     * @return array|FilterInterface[]
     */
    public function getActiveFilters(bool $byType = false): array
    {
        if ($byType === false) {
            return $this->activeFilters;
        }
        $grouped = group($this->activeFilters, function (FilterInterface $f) {
            if ($f->isCustom()) {
                return 'custom';
            }

            return $f->isInitialized() && ($param = $f->getUrlParam()) !== ''
                ? $param
                : 'misc';
        });

        return array_merge([
            'kf'     => [],
            'hf'     => [],
            'mm'     => [],
            'ssf'    => [],
            'tf'     => [],
            'sf'     => [],
            'bf'     => [],
            'custom' => [],
            'misc'   => []
        ], map($grouped, function ($e) {
            return array_values($e);
        }));
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return FilterStateSQLInterface
     */
    public function getCurrentStateData($ignore = null): FilterStateSQLInterface
    {
        $state          = $this->getBaseState();
        $stateCondition = $state->getSQLCondition();
        $stateJoin      = $state->getSQLJoin();
        $data           = new FilterStateSQL();
        $having         = [];
        $conditions     = [];
        $joins          = is_array($stateJoin)
            ? $stateJoin
            : [$stateJoin];
        if (!empty($stateCondition)) {
            $conditions[] = $stateCondition;
        }
        /** @var FilterInterface $filter */
        foreach ($this->getActiveFilters(true) as $type => $active) {
            $count = count($active);
            if ($count > 1 && $type !== 'misc' && $type !== 'custom') {
                $singleConditions = [];
                $active           = select($active, function (FilterInterface $f) use ($ignore) {
                    return $ignore === null
                        || (is_string($ignore) && $f->getClassName() !== $ignore)
                        || (is_object($ignore) && $f !== $ignore);
                });
                $orFilters        = select($active, function (FilterInterface $f) {
                    return $f->getType()->equals(Type::OR());
                });
                /** @var AbstractFilter $filter */
                foreach ($active as $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
                    $itemJoin = $filter->getSQLJoin();
                    $joins    = array_merge($joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!in_array($filter, $orFilters, true)) {
                        $singleConditions[] = $filter->getSQLCondition();
                    }
                }
                if (count($orFilters) > 0) {
                    if ($type === 'mf') {
                        $groupedOrFilters = group($orFilters, function (ItemAttribute $f) {
                            return $f->getAttributeID();
                        });
                    } else {
                        // group OR filters by their primary key row
                        $groupedOrFilters = group($orFilters, function (FilterInterface $f) {
                            return $f->getPrimaryKeyRow();
                        });
                    }
                    foreach ($groupedOrFilters as $idx => $orFilters) {
                        /** @var FilterInterface[] $orFilters */
                        $values        = implode(
                            ',',
                            array_map(function ($f) {
                                /** @var FilterInterface $f */
                                $val = $f->getValue();

                                return is_array($val) ? implode(',', $val) : $val;
                            }, $orFilters)
                        );
                        $first         = first($orFilters);
                        $primaryKeyRow = $first->getPrimaryKeyRow();
                        $table         = $first->getTableAlias();
                        if (empty($table)) {
                            $table = first($orFilters)->getTableName();
                        }
                        $conditions[] = "\n#combined conditions from OR filter " . $primaryKeyRow . "\n" .
                            $table . '.kArtikel IN ' .
                            '(SELECT kArtikel FROM ' . $first->getTableName() . ' WHERE ' .
                            $primaryKeyRow . ' IN (' . $values . '))';
                    }
                }
                foreach ($singleConditions as $singleCondition) {
                    $conditions[] = $singleCondition;
                }
            } elseif ($count === 1) {
                /** @var FilterInterface[] $active */
                $first = first($active);
                if ($ignore === null
                    || (is_object($ignore) && $first !== $ignore)
                    || (is_string($ignore) && $first->getClassName() !== $ignore)
                ) {
                    $itemJoin   = $first->getSQLJoin();
                    $_condition = $first->getSQLCondition();
                    $joins      = array_merge($joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!empty($_condition)) {
                        $conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            } elseif ($count > 0 && ($type !== 'misc' || $type !== 'custom')) {
                // this is the most clean and usual behaviour.
                // 'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($active as $filter) {
                    $itemJoin   = $filter->getSQLJoin();
                    $_condition = $filter->getSQLCondition();
                    $joins      = array_merge($joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                    if (!empty($_condition)) {
                        $conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            }
        }
        $data->setConditions($conditions);
        $data->setHaving($having);
        $data->setJoins($joins);

        return $data;
    }

    /**
     * @param array $nFilter_arr
     * @return array
     */
    public static function initAttributeFilter(array $nFilter_arr = []): array
    {
        $filter = [];
        if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
            foreach ($nFilter_arr as $nFilter) {
                if ((int)$nFilter > 0) {
                    $filter[] = (int)$nFilter;
                }
            }
        } elseif (isset($_GET['mf'])) {
            if (is_string($_GET['mf'])) {
                $filter[] = $_GET['mf'];
            } else {
                foreach ($_GET['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['mf'])) {
            if (is_string($_POST['mf'])) {
                $filter[] = $_POST['mf'];
            } else {
                foreach ($_POST['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (count($_GET) > 0) {
            foreach ($_GET as $key => $value) {
                if (preg_match('/mf\d+/i', $key)) {
                    $filter[] = (int)$value;
                }
            }
        } elseif (count($_POST) > 0) {
            foreach ($_POST as $key => $value) {
                if (preg_match('/mf\d+/i', $key)) {
                    $filter[] = (int)$value;
                }
            }
        }

        return $filter;
    }

    /**
     * @param array $nFilter_arr
     * @return array
     */
    public static function initSearchFilter(array $nFilter_arr = []): array
    {
        $filter = [];
        if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
            foreach ($nFilter_arr as $nFilter) {
                if ((int)$nFilter > 0) {
                    $filter[] = (int)$nFilter;
                }
            }
        } elseif (isset($_GET['sf'])) {
            if (is_string($_GET['sf'])) {
                $filter[] = $_GET['sf'];
            } else {
                foreach ($_GET['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['sf'])) {
            if (is_string($_POST['sf'])) {
                $filter[] = $_POST['sf'];
            } else {
                foreach ($_POST['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (\RequestHelper::verifyGPCDataInt('sf' . $i) > 0) {
                    $filter[] = \RequestHelper::verifyGPCDataInt('sf' . $i);
                }
                ++$i;
            }
        }

        return $filter;
    }

    /**
     * @param array $nFilter_arr
     * @return array
     */
    public static function initTagFilter(array $nFilter_arr = []): array
    {
        $filter = [];
        if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
            foreach ($nFilter_arr as $nFilter) {
                if ((int)$nFilter > 0) {
                    $filter[] = (int)$nFilter;
                }
            }
        } elseif (isset($_GET['tf'])) {
            if (is_string($_GET['tf'])) {
                $filter[] = $_GET['tf'];
            } else {
                foreach ($_GET['tf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['tf'])) {
            if (is_string($_POST['tf'])) {
                $filter[] = $_POST['tf'];
            } else {
                foreach ($_POST['tf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (\RequestHelper::verifyGPCDataInt('tf' . $i) > 0) {
                    $filter[] = \RequestHelper::verifyGPCDataInt('tf' . $i);
                }
                ++$i;
            }
        }

        return $filter;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (property_exists($this, $name)) {
            return true;
        }
        $mapped = self::getMapping($name);
        if ($mapped === null) {
            return false;
        }
        $method = 'get' . $mapped;
        $result = $this->$method();
        if (is_a($result, FilterInterface::class)) {
            /** @var FilterInterface $result */
            return $result->isInitialized();
        }

        return is_array($result)
            ? count($result) > 0
            : false;
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
