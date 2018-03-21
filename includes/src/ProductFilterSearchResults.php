<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ProductFilterSearchResults
 */
class ProductFilterSearchResults
{
    use MagicCompatibilityTrait;

    /**
     * @var \Tightenco\Collect\Support\Collection()
     * @former Artikel
     */
    private $products;

    /**
     * @var int
     * @former GesamtanzahlArtikel
     */
    private $productCount = 0;

    /**
     * @var int
     */
    private $visibileProductCount = 0;

    /**
     * @var int
     * former ArtikelVon
     */
    private $offsetStart = 0;

    /**
     * @var int
     * @former ArtikelBis
     */
    private $offsetEnd = 0;

    /**
     * @var stdClass
     * @former Seitenzahlen
     */
    private $pages;

    /**
     * @var string
     * @former cSuche
     */
    private $searchTerm;

    /**
     * @var string
     * @former SuchausdruckWrite
     */
    private $searchTermWrite;

    /**
     * @var bool
     * @former SucheErfolglos
     */
    private $searchUnsuccessful = false;

    /**
     * @var FilterOption[]
     * @former Herstellerauswahl
     */
    private $manufacturerFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former Bewertung
     */
    private $ratingFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former Tags
     */
    private $tagFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former MerkmalFilter
     */
    private $attributeFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former Preisspanne
     */
    private $priceRangeFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former Kategorieauswahl
     */
    private $categoryFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former SuchFilter
     */
    private $searchFilterOptions = [];

    /**
     * @var FilterOption[]
     * @former Suchspecialauswahl
     */
    private $searchSpecialFilterOptions = [];

    /**
     * @var FilterOption[]
     */
    private $customFilterOptions = [];

    /**
     * @var int[]
     */
    private $productKeys = [];

    /**
     * @var string
     * @former cFehler
     */
    private $error;

    /**
     * @var string
     */
    public $searchFilterJSON;
    /**
     * @var string
     */
    public $tagFilterJSON;

    /**
     * @var array
     */
    private $sortingOptions = [];

    /**
     * @var array
     */
    private $limitOptions = [];

    /**
     * @var array
     */
    private static $mapping = [
        'Artikel'             => 'ProductsCompat',
        'GesamtanzahlArtikel' => 'ProductCount',
        'ArtikelBis'          => 'OffsetEnd',
        'ArtikelVon'          => 'OffsetStart',
        'Seitenzahlen'        => 'Pages',
        'SuchausdruckWrite'   => 'SearchTermWrite',
        'cSuche'              => 'SearchTerm',
        'cFehler'             => 'Error',
        'SucheErfolglos'      => 'SearchUnsuccessful',
        'Herstellerauswahl'   => 'ManufacturerFilterOptions',
        'Bewertung'           => 'RatingFilterOptions',
        'Tags'                => 'TagFilterOptions',
        'MerkmalFilter'       => 'AttributeFilterOptions',
        'Preisspanne'         => 'PriceRangeFilterOptions',
        'Kategorieauswahl'    => 'CategoryFilterOptions',
        'SuchFilter'          => 'SearchFilterOptions',
        'Suchspecialauswahl'  => 'SearchSpecialFilterOptions',
        'SuchFilterJSON'      => 'SearchFilterJSON',
        'TagJSON'             => 'TagFilterJSON'
    ];

    /**
     * ProductFilterSearchResults constructor.
     * @param null $legacy - optional stdClass object to convert to instance
     */
    public function __construct($legacy = null)
    {
        $this->products             = new \Tightenco\Collect\Support\Collection();
        $this->pages                = new stdClass();
        $this->pages->AktuelleSeite = 0;
        $this->pages->MaxSeiten     = 0;
        $this->pages->minSeite      = 0;
        $this->pages->maxSeite      = 0;
        if ($legacy !== null) {
            $this->convert($legacy);
        }
    }

    /**
     * @param stdClass $legacy
     * @return $this
     */
    public function convert($legacy)
    {
        if (get_class($legacy) === __CLASS__) {
            return $legacy;
        }
        trigger_error('Using a stdClass object for search results is deprecated', E_USER_DEPRECATED);
        foreach (get_object_vars($legacy) as $var => $value) {
            $this->$var = $value;
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getProductsCompat()
    {
        $compat              = new stdClass();
        $compat->elemente    = $this->getProducts();
        $compat->productKeys = $this->getProductKeys();

        return $compat;
    }

    /**
     * @return $this
     */
    public function setProductsCompat()
    {
        return $this;
    }

    /**
     * @return int[]
     */
    public function getProductKeys()
    {
        return $this->productKeys;
    }

    /**
     * @param int[] $keys
     * @return $this
     */
    public function setProductKeys($keys)
    {
        $this->productKeys = $keys;

        return $this;
    }

    /**
     * @return \Tightenco\Collect\Support\Collection()
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param \Tightenco\Collect\Support\Collection() $products
     * @return ProductFilterSearchResults
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @param int $productCount
     * @return ProductFilterSearchResults
     */
    public function setProductCount($productCount)
    {
        $this->productCount = $productCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getVisibleProductCount()
    {
        return $this->visibileProductCount;
    }

    /**
     * @param int $count
     * @return ProductFilterSearchResults
     */
    public function setVisibleProductCount($count)
    {
        $this->visibileProductCount = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffsetStart()
    {
        return $this->offsetStart;
    }

    /**
     * @param int $offsetStart
     * @return ProductFilterSearchResults
     */
    public function setOffsetStart($offsetStart)
    {
        $this->offsetStart = $offsetStart;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffsetEnd()
    {
        return $this->offsetEnd;
    }

    /**
     * @param int $offsetEnd
     * @return ProductFilterSearchResults
     */
    public function setOffsetEnd($offsetEnd)
    {
        $this->offsetEnd = $offsetEnd;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param stdClass $pages
     * @return ProductFilterSearchResults
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchTerm
     * @return ProductFilterSearchResults
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchTermWrite()
    {
        return $this->searchTermWrite;
    }

    /**
     * @param string $searchTerm
     * @return ProductFilterSearchResults
     */
    public function setSearchTermWrite($searchTerm)
    {
        $this->searchTermWrite = $searchTerm;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchUnsuccessful()
    {
        return $this->searchUnsuccessful;
    }

    /**
     * @param bool $searchUnsuccessful
     * @return ProductFilterSearchResults
     */
    public function setSearchUnsuccessful($searchUnsuccessful)
    {
        $this->searchUnsuccessful = $searchUnsuccessful;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getManufacturerFilterOptions()
    {
        return $this->manufacturerFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setManufacturerFilterOptions($options)
    {
        $this->manufacturerFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getRatingFilterOptions()
    {
        return $this->ratingFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setRatingFilterOptions($options)
    {
        $this->ratingFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getTagFilterOptions()
    {
        return $this->tagFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setTagFilterOptions($options)
    {
        $this->tagFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getAttributeFilterOptions()
    {
        return $this->attributeFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setAttributeFilterOptions($options)
    {
        $this->attributeFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getPriceRangeFilterOptions()
    {
        return $this->priceRangeFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setPriceRangeFilterOptions($options)
    {
        $this->priceRangeFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getCategoryFilterOptions()
    {
        return $this->categoryFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setCategoryFilterOptions($options)
    {
        $this->categoryFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getSearchFilterOptions()
    {
        return $this->searchFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setSearchFilterOptions($options)
    {
        $this->searchFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getSearchSpecialFilterOptions()
    {
        return $this->searchSpecialFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setSearchSpecialFilterOptions($options)
    {
        $this->searchSpecialFilterOptions = $options;

        return $this;
    }

    /**
     * @return FilterOption[]
     */
    public function getCustomFilterOptions()
    {
        return $this->customFilterOptions;
    }

    /**
     * @param FilterOption[] $options
     * @return ProductFilterSearchResults
     */
    public function setCustomFilterOptions($options)
    {
        $this->customFilterOptions = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getTagFilterJSON()
    {
        return $this->tagFilterJSON;
    }

    /**
     * @param string $json
     * @return ProductFilterSearchResults
     */
    public function setTagFilterJSON($json)
    {
        $this->tagFilterJSON = $json;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchFilterJSON()
    {
        return $this->searchFilterJSON;
    }

    /**
     * @param string $json
     * @return ProductFilterSearchResults
     */
    public function setSearchFilterJSON($json)
    {
        $this->searchFilterJSON = $json;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return ProductFilterSearchResults
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return array
     */
    public function getSortingOptions()
    {
        return $this->sortingOptions;
    }

    /**
     * @param array $options
     * @return ProductFilterSearchResults
     */
    public function setSortingOptions($options)
    {
        $this->sortingOptions = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getLimitOptions()
    {
        return $this->limitOptions;
    }

    /**
     * @param array $options
     * @return ProductFilterSearchResults
     */
    public function setLimitOptions($options)
    {
        $this->limitOptions = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllFilterOptions()
    {
        return [
            'manufacturerFilterOptions'  => $this->getManufacturerFilterOptions(),
            'ratingFilterOptions'        => $this->getRatingFilterOptions(),
            'tagFilterOptions'           => $this->getTagFilterOptions(),
            'attributeFilterOptions'     => $this->getAttributeFilterOptions(),
            'priceRangeFilterOptions'    => $this->getPriceRangeFilterOptions(),
            'categoryFilterOptions'      => $this->getCategoryFilterOptions(),
            'searchFilterOptions'        => $this->getSearchFilterOptions(),
            'searchSpecialFilterOptions' => $this->getSearchSpecialFilterOptions(),
            'customFilterOptions'        => $this->getCustomFilterOptions()
        ];
    }


    /**
     * @param ProductFilter  $productFilter
     * @param null|Kategorie $currentCategory
     * @param bool           $selectionWizard
     * @return mixed
     */
    public function setFilterOptions($productFilter, $currentCategory = null, $selectionWizard = false)
    {
        // @todo: make option
        $hideActiveOnly          = true;
        $manufacturerOptions     = $productFilter->getManufacturerFilter()->getOptions();
        $ratingOptions           = $productFilter->getRatingFilter()->getOptions();
        $tagOptions              = $productFilter->getTag()->getOptions();
        $categoryOptions         = $productFilter->getCategoryFilter()->getOptions();
        $priceRangeOptions       = $productFilter->getPriceRangeFilter()->getOptions($this->getProductCount());
        $searchSpecialFilters    = $productFilter->getSearchSpecialFilter()->getOptions();
        $attribtuteFilterOptions = $productFilter->getAttributeFilterCollection()->getOptions([
            'oAktuelleKategorie' => $currentCategory,
            'bForce'             => $selectionWizard === true && function_exists('starteAuswahlAssistent')
        ]);
        $searchFilterOptions     = [];
        foreach ($productFilter->getSearchFilter() as $searchFilter) {
            // @todo: validate
            $opt = $searchFilter->getOptions();
            if (is_array($opt)) {
                foreach ($opt as $_o) {
                    $searchFilterOptions[] = $_o;
                }
            }
        }

        $this->setManufacturerFilterOptions($manufacturerOptions)
             ->setSortingOptions($productFilter->getSorting()->getOptions())
             ->setLimitOptions($productFilter->getLimits()->getOptions())
             ->setRatingFilterOptions($ratingOptions)
             ->setTagFilterOptions($tagOptions)
             ->setPriceRangeFilterOptions($priceRangeOptions)
             ->setCategoryFilterOptions($categoryOptions)
             ->setSearchFilterOptions($searchFilterOptions)
             ->setSearchSpecialFilterOptions($searchSpecialFilters)
             ->setAttributeFilterOptions($attribtuteFilterOptions)
             ->setCustomFilterOptions(array_filter(
                 $productFilter->getAvailableFilters(),
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

        if ($productFilter->getConfig()['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
            $this->setTagFilterJSON(Boxen::gibJSONString(array_map(
                function ($e) {
                    /** @var FilterOption $e */
                    return $e->setURL(StringHandler::htmlentitydecode($e->getURL()));
                },
                $tagOptions
            )));
        }

        if (empty($searchSpecialFilters)) {
            // hide category filter when a category is being browsed
            $productFilter->getSearchSpecialFilter()->hide();
        }
        if (empty($categoryOptions)
            || count($categoryOptions) === 0
            || ($productFilter->getCategory()->isInitialized()
                && $productFilter->getCategory()->getValue() !== null)
        ) {
            // hide category filter when a category is being browsed
            $productFilter->getCategoryFilter()->hide();
        }
        if (empty($priceRangeOptions)
            || count($priceRangeOptions) === 0
            || ($productFilter->getPriceRangeFilter()->isInitialized()
                && $productFilter->getPriceRangeFilter()->getValue() !== null)
        ) {
            // hide empty price ranges
            $productFilter->getPriceRangeFilter()->hide();
        }
        if (empty($manufacturerOptions) || count($manufacturerOptions) === 0
            || $productFilter->getManufacturer()->isInitialized()
            || ($productFilter->getManufacturerFilter()->isInitialized()
                && count($manufacturerOptions) === 1
                && $hideActiveOnly)
        ) {
            // hide manufacturer filter when browsing manufacturer products
            $productFilter->getManufacturerFilter()->hide();
        }
        if (empty($ratingOptions)) {
            $productFilter->getRatingFilter()->hide();
        }
        if (count($attribtuteFilterOptions) < 1) {
            $productFilter->getAttributeFilterCollection()->hide();
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
        $productFilter->getAttributeFilterCollection()
                      ->setFilterCollection($attribtuteFilterOptions);

        return $this;
    }
}
