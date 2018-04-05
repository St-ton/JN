<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Class NavigationURLs
 * @package Filter
 */
class NavigationURLs
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'cAllePreisspannen' => 'PriceRanges',
        'cAlleBewertungen'  => 'Ratings',
        'cAlleTags'         => 'Tags',
        'cAlleSuchspecials' => 'SearchSpecials',
        'cAlleKategorien'   => 'Categories',
        'cAlleHersteller'   => 'Manufacturers',
        'cAlleMerkmale'     => 'Attributes',
        'cAlleMerkmalWerte' => 'AttributeValues',
        'cAlleSuchFilter'   => 'SearchFilters',
        'cNoFilter'         => 'UnsetAll'
    ];

    /**
     * @var string
     */
    private $priceRanges = '';

    /**
     * @var string
     */
    private $ratings = '';

    /**
     * @var string
     */
    private $tags = '';

    /**
     * @var string
     */
    private $searchSpecials = '';

    /**
     * @var string
     */
    private $categories = '';

    /**
     * @var string
     */
    private $manufacturers = '';

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $attributeValues = [];

    /**
     * @var array
     */
    private $searchFilters = [];

    /**
     * @var string
     */
    private $unsetAll = '';


    /**
     * @return string
     */
    public function getPriceRanges(): string
    {
        return $this->priceRanges;
    }

    /**
     * @param string $priceRanges
     * @return NavigationURLs
     */
    public function setPriceRanges(string $priceRanges): NavigationURLs
    {
        $this->priceRanges = $priceRanges;

        return $this;
    }

    /**
     * @return string
     */
    public function getRatings(): string
    {
        return $this->ratings;
    }

    /**
     * @param string $ratings
     * @return NavigationURLs
     */
    public function setRatings(string $ratings): NavigationURLs
    {
        $this->ratings = $ratings;

        return $this;
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return NavigationURLs
     */
    public function setTags(string $tags): NavigationURLs
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchSpecials(): string
    {
        return $this->searchSpecials;
    }

    /**
     * @param string $searchSpecials
     * @return NavigationURLs
     */
    public function setSearchSpecials(string $searchSpecials): NavigationURLs
    {
        $this->searchSpecials = $searchSpecials;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategories(): string
    {
        return $this->categories;
    }

    /**
     * @param string $categories
     * @return NavigationURLs
     */
    public function setCategories(string $categories): NavigationURLs
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturers(): string
    {
        return $this->manufacturers;
    }

    /**
     * @param string $manufacturers
     * @return NavigationURLs
     */
    public function setManufacturers(string $manufacturers): NavigationURLs
    {
        $this->manufacturers = $manufacturers;

        return $this;
    }

    /**
     * @param string|int $idx
     * @param string     $manufacturer
     * @return NavigationURLs
     */
    public function addManufacturer($idx, string $manufacturer): NavigationURLs
    {
        $this->manufacturers[$idx] = $manufacturer;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     * @return NavigationURLs
     */
    public function setAttributes(string $attributes): NavigationURLs
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param string|int $idx
     * @param string     $attribute
     * @return NavigationURLs
     */
    public function addAttribute($idx, string $attribute): NavigationURLs
    {
        $this->attributes[$idx] = $attribute;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValues;
    }

    /**
     * @param string $attributeValues
     * @return NavigationURLs
     */
    public function setAttributeValues(string $attributeValues): NavigationURLs
    {
        $this->attributeValues = $attributeValues;

        return $this;
    }

    /**
     * @param string|int $idx
     * @param string     $attributeValue
     * @return NavigationURLs
     */
    public function addAttributeValue($idx, string $attributeValue): NavigationURLs
    {
        $this->attributeValues[$idx] = $attributeValue;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchFilters(): array
    {
        return $this->searchFilters;
    }

    /**
     * @param string $searchFilters
     * @return NavigationURLs
     */
    public function setSearchFilters(string $searchFilters): NavigationURLs
    {
        $this->searchFilters = $searchFilters;

        return $this;
    }

    /**
     * @param string|int $idx
     * @param string     $searchFilter
     * @return NavigationURLs
     */
    public function addSearchFilter($idx, string $searchFilter): NavigationURLs
    {
        $this->searchFilters[$idx] = $searchFilter;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnsetAll(): string
    {
        return $this->unsetAll;
    }

    /**
     * @param string $unsetAll
     * @return NavigationURLs
     */
    public function setUnsetAll(string $unsetAll): NavigationURLs
    {
        $this->unsetAll = $unsetAll;

        return $this;
    }
}