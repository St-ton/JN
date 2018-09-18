<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Class NavigationURLs
 * @package Filter
 */
class NavigationURLs implements NavigationURLsInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
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
     * @inheritdoc
     */
    public function getPriceRanges(): string
    {
        return $this->priceRanges;
    }

    /**
     * @inheritdoc
     */
    public function setPriceRanges(string $priceRanges): NavigationURLsInterface
    {
        $this->priceRanges = $priceRanges;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatings(): string
    {
        return $this->ratings;
    }

    /**
     * @inheritdoc
     */
    public function setRatings(string $ratings): NavigationURLsInterface
    {
        $this->ratings = $ratings;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @inheritdoc
     */
    public function setTags(string $tags): NavigationURLsInterface
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchSpecials(): string
    {
        return $this->searchSpecials;
    }

    /**
     * @inheritdoc
     */
    public function setSearchSpecials(string $searchSpecials): NavigationURLsInterface
    {
        $this->searchSpecials = $searchSpecials;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategories(): string
    {
        return $this->categories;
    }

    /**
     * @inheritdoc
     */
    public function setCategories(string $categories): NavigationURLsInterface
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getManufacturers(): string
    {
        return $this->manufacturers;
    }

    /**
     * @inheritdoc
     */
    public function setManufacturers(string $manufacturers): NavigationURLsInterface
    {
        $this->manufacturers = $manufacturers;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addManufacturer($idx, string $manufacturer): NavigationURLsInterface
    {
        $this->manufacturers[$idx] = $manufacturer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(string $attributes): NavigationURLsInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addAttribute($idx, string $attribute): NavigationURLsInterface
    {
        $this->attributes[$idx] = $attribute;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValues;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeValues(string $attributeValues): NavigationURLsInterface
    {
        $this->attributeValues = $attributeValues;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addAttributeValue($idx, string $attributeValue): NavigationURLsInterface
    {
        $this->attributeValues[$idx] = $attributeValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchFilters(): array
    {
        return $this->searchFilters;
    }

    /**
     * @inheritdoc
     */
    public function setSearchFilters(string $searchFilters): NavigationURLsInterface
    {
        $this->searchFilters = $searchFilters;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addSearchFilter($idx, string $searchFilter): NavigationURLsInterface
    {
        $this->searchFilters[$idx] = $searchFilter;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUnsetAll(): string
    {
        return $this->unsetAll;
    }

    /**
     * @inheritdoc
     */
    public function setUnsetAll(string $unsetAll): NavigationURLsInterface
    {
        $this->unsetAll = $unsetAll;

        return $this;
    }
}
