<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use Boxes\AbstractBox;
use function Functional\every;
use Tightenco\Collect\Support\Collection;

/**
 * Class ProductFilterSearchResults
 * @package Filter
 */
class ProductFilterSearchResults implements ProductFilterSearchResultsInterface
{
    use \MagicCompatibilityTrait;

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
     * @var \stdClass
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
     * @var Collection
     */
    private $productKeys;

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
        $this->products             = new Collection();
        $this->productKeys          = new Collection();
        $this->pages                = new \stdClass();
        $this->pages->AktuelleSeite = 0;
        $this->pages->MaxSeiten     = 0;
        $this->pages->minSeite      = 0;
        $this->pages->maxSeite      = 0;
        if ($legacy !== null) {
            $this->convert($legacy);
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($legacy): ProductFilterSearchResultsInterface
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
     * @inheritdoc
     */
    public function getProductsCompat(): \stdClass
    {
        $compat              = new \stdClass();
        $compat->elemente    = $this->getProducts();
        $compat->productKeys = $this->getProductKeys();

        return $compat;
    }

    /**
     * @inheritdoc
     */
    public function setProductsCompat(): ProductFilterSearchResultsInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductKeys(): Collection
    {
        return $this->productKeys;
    }

    /**
     * @inheritdoc
     */
    public function setProductKeys(Collection $keys): ProductFilterSearchResultsInterface
    {
        $this->productKeys = $keys;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function setProducts($products): ProductFilterSearchResultsInterface
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductCount(): int
    {
        return $this->productCount;
    }

    /**
     * @inheritdoc
     */
    public function setProductCount($productCount): ProductFilterSearchResultsInterface
    {
        $this->productCount = $productCount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleProductCount(): int
    {
        return $this->visibileProductCount;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleProductCount(int $count): ProductFilterSearchResultsInterface
    {
        $this->visibileProductCount = $count;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffsetStart(): int
    {
        return $this->offsetStart;
    }

    /**
     * @inheritdoc
     */
    public function setOffsetStart($offsetStart): ProductFilterSearchResultsInterface
    {
        $this->offsetStart = $offsetStart;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffsetEnd(): int
    {
        return $this->offsetEnd;
    }

    /**
     * @inheritdoc
     */
    public function setOffsetEnd($offsetEnd): ProductFilterSearchResultsInterface
    {
        $this->offsetEnd = $offsetEnd;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPages(): \stdClass
    {
        return $this->pages;
    }

    /**
     * @inheritdoc
     */
    public function setPages($pages): ProductFilterSearchResultsInterface
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @inheritdoc
     */
    public function setSearchTerm($searchTerm): ProductFilterSearchResultsInterface
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getSearchTermWrite()
    {
        return $this->searchTermWrite;
    }

    /**
     * @inheritdoc
     */
    public function setSearchTermWrite($searchTerm): ProductFilterSearchResultsInterface
    {
        $this->searchTermWrite = $searchTerm;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchUnsuccessful(): bool
    {
        return $this->searchUnsuccessful;
    }

    /**
     * @inheritdoc
     */
    public function setSearchUnsuccessful($searchUnsuccessful): ProductFilterSearchResultsInterface
    {
        $this->searchUnsuccessful = $searchUnsuccessful;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getManufacturerFilterOptions(): array
    {
        return $this->manufacturerFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setManufacturerFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->manufacturerFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingFilterOptions(): array
    {
        return $this->ratingFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setRatingFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->ratingFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTagFilterOptions(): array
    {
        return $this->tagFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setTagFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->tagFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeFilterOptions(): array
    {
        return $this->attributeFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->attributeFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceRangeFilterOptions(): array
    {
        return $this->priceRangeFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setPriceRangeFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->priceRangeFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategoryFilterOptions(): array
    {
        return $this->categoryFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setCategoryFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->categoryFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchFilterOptions(): array
    {
        return $this->searchFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSearchFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->searchFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchSpecialFilterOptions(): array
    {
        return $this->searchSpecialFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSearchSpecialFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->searchSpecialFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomFilterOptions(): array
    {
        return $this->customFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setCustomFilterOptions($options): ProductFilterSearchResultsInterface
    {
        $this->customFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTagFilterJSON()
    {
        return $this->tagFilterJSON;
    }

    /**
     * @inheritdoc
     */
    public function setTagFilterJSON($json): ProductFilterSearchResultsInterface
    {
        $this->tagFilterJSON = $json;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchFilterJSON()
    {
        return $this->searchFilterJSON;
    }

    /**
     * @inheritdoc
     */
    public function setSearchFilterJSON($json): ProductFilterSearchResultsInterface
    {
        $this->searchFilterJSON = $json;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function setError($error): ProductFilterSearchResultsInterface
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortingOptions(): array
    {
        return $this->sortingOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSortingOptions($options): ProductFilterSearchResultsInterface
    {
        $this->sortingOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLimitOptions(): array
    {
        return $this->limitOptions;
    }

    /**
     * @inheritdoc
     */
    public function setLimitOptions($options): ProductFilterSearchResultsInterface
    {
        $this->limitOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAllFilterOptions(): array
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
     * @inheritdoc
     */
    public function setFilterOptions(
        $productFilter,
        $currentCategory = null,
        $selectionWizard = false
    ): ProductFilterSearchResultsInterface {
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
                     /** @var FilterInterface $e */
                     $isCustom = $e->isCustom();
                     if ($isCustom && count($e->getOptions()) === 0) {
                         $e->hide();
                     }

                     return $isCustom;
                 }
             ))
             ->setSearchFilterJSON(AbstractBox::getJSONString(array_map(
                 function ($e) {
                     $e->cURL = \StringHandler::htmlentitydecode($e->cURL);

                     return $e;
                 },
                 $searchFilterOptions
             )));

        if ($productFilter->getConfig()['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $this->setTagFilterJSON(AbstractBox::getJSONString(array_map(
                function ($e) {
                    /** @var FilterOption $e */
                    return $e->setURL(\StringHandler::htmlentitydecode($e->getURL()));
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
                    && !$af->getVisibility()->equals(Visibility::SHOW_NEVER())
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
            if (every($attribtuteFilterOptions, function (FilterOption $item) {
                return $item->getVisibility()->equals(Visibility::SHOW_NEVER());
            })) {
                // hide the whole attribute filter collection if every filter consists of only active options
                $productFilter->getAttributeFilterCollection()->hide();
            }

        }
        $productFilter->getAttributeFilterCollection()
                      ->setFilterCollection($attribtuteFilterOptions);

        return $this;
    }
}
