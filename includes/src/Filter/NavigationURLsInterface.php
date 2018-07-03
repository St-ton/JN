<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class NavigationURLs
 * @package Filter
 */
interface NavigationURLsInterface
{
    /**
     * @return string
     */
    public function getPriceRanges(): string;

    /**
     * @param string $priceRanges
     * @return NavigationURLsInterface
     */
    public function setPriceRanges(string $priceRanges): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getRatings(): string;

    /**
     * @param string $ratings
     * @return NavigationURLsInterface
     */
    public function setRatings(string $ratings): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getTags(): string;

    /**
     * @param string $tags
     * @return NavigationURLsInterface
     */
    public function setTags(string $tags): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getSearchSpecials(): string;

    /**
     * @param string $searchSpecials
     * @return NavigationURLsInterface
     */
    public function setSearchSpecials(string $searchSpecials): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getCategories(): string;

    /**
     * @param string $categories
     * @return NavigationURLsInterface
     */
    public function setCategories(string $categories): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getManufacturers(): string;

    /**
     * @param string $manufacturers
     * @return NavigationURLsInterface
     */
    public function setManufacturers(string $manufacturers): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $manufacturer
     * @return NavigationURLsInterface
     */
    public function addManufacturer($idx, string $manufacturer): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $attributes
     * @return NavigationURLsInterface
     */
    public function setAttributes(string $attributes): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $attribute
     * @return NavigationURLsInterface
     */
    public function addAttribute($idx, string $attribute): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getAttributeValues(): array;

    /**
     * @param string $attributeValues
     * @return NavigationURLsInterface
     */
    public function setAttributeValues(string $attributeValues): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $attributeValue
     * @return NavigationURLsInterface
     */
    public function addAttributeValue($idx, string $attributeValue): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getSearchFilters(): array;

    /**
     * @param string $searchFilters
     * @return NavigationURLsInterface
     */
    public function setSearchFilters(string $searchFilters): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $searchFilter
     * @return NavigationURLsInterface
     */
    public function addSearchFilter($idx, string $searchFilter): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getUnsetAll(): string;

    /**
     * @param string $unsetAll
     * @return NavigationURLsInterface
     */
    public function setUnsetAll(string $unsetAll): NavigationURLsInterface;
}
