<?php

class ProductFilterSearchResults
{
    use MagicCompatibilityTrait;

    /**
     * @var stdClass
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
        'Artikel'             => 'Products',
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
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param stdClass $products
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
}
