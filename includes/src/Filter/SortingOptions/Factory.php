<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;
use Mapper\SortingType;
use Tightenco\Collect\Support\Collection;

/**
 * Class Factory
 * @package Filter\SortingOptions
 */
class Factory
{
    /**
     * @var ProductFilter
     */
    private $productFilter;

    private static $defaultSortingOptions = [
        SEARCH_SORT_STANDARD,
        SEARCH_SORT_NAME_ASC,
        SEARCH_SORT_NAME_DESC,
        SEARCH_SORT_PRICE_ASC,
        SEARCH_SORT_PRICE_DESC,
        SEARCH_SORT_EAN,
        SEARCH_SORT_NEWEST_FIRST,
        SEARCH_SORT_PRODUCTNO,
        SEARCH_SORT_AVAILABILITY,
        SEARCH_SORT_WEIGHT,
        SEARCH_SORT_DATEOFISSUE,
        SEARCH_SORT_BESTSELLER,
        SEARCH_SORT_RATING,
    ];

    /**
     * Factory constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        $this->productFilter = $productFilter;
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        $all = new Collection();
        foreach (self::$defaultSortingOptions as $defaultSortingOption) {
            $option = $this->getSortingOption($defaultSortingOption);
            if ($option !== null) {
                $all->push($option);
            }
        }

        return $all;
    }

    /**
     * @param int $type
     * @return SortingOptionInterface|null
     * @throws \InvalidArgumentException
     */
    public function getSortingOption(int $type)
    {
        $mapper  = new SortingType();
        $mapping = $mapper->mapSortTypeToClassName($type);
        if ($mapping === null) {
            throw new \InvalidArgumentException('Cannot map type ' . $type);
        }

        return new $mapping($this->productFilter);
    }
}
