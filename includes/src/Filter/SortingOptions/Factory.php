<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\SortingOptions;

use Illuminate\Support\Collection;
use JTL\Filter\ProductFilter;
use JTL\Mapper\SortingType;

/**
 * Class Factory
 * @package JTL\Filter\SortingOptions
 */
class Factory
{
    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private static $defaultSortingOptions = [
        \SEARCH_SORT_STANDARD,
        \SEARCH_SORT_NAME_ASC,
        \SEARCH_SORT_NAME_DESC,
        \SEARCH_SORT_PRICE_ASC,
        \SEARCH_SORT_PRICE_DESC,
        \SEARCH_SORT_EAN,
        \SEARCH_SORT_NEWEST_FIRST,
        \SEARCH_SORT_PRODUCTNO,
        \SEARCH_SORT_AVAILABILITY,
        \SEARCH_SORT_WEIGHT,
        \SEARCH_SORT_DATEOFISSUE,
        \SEARCH_SORT_BESTSELLER,
        \SEARCH_SORT_RATING,
    ];

    /**
     * @var array
     */
    private $mapping = [];

    /**
     * Factory constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        $this->productFilter = $productFilter;

        \executeHook(\HOOK_PRODUCTFILTER_REGISTER_SEARCH_OPTION, [
            'factory'       => $this,
            'productFilter' => $this->productFilter
        ]);
    }

    /**
     * @param int    $value
     * @param string $className
     */
    public function registerSortingOption(int $value, string $className): void
    {
        $this->mapping[$value] = $className;
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
        foreach ($this->mapping as $id => $class) {
            $all->push(new $class($this->productFilter));
        }

        return $all;
    }

    /**
     * @param int $type
     * @return SortingOptionInterface|null
     * @throws \InvalidArgumentException
     */
    public function getSortingOption(int $type): ?SortingOptionInterface
    {
        $mapper  = new SortingType();
        $mapping = $mapper->mapSortTypeToClassName($type);
        if ($mapping === null) {
            $mapping = $this->mapping[$type] ?? null;
        }
        if ($mapping === null) {
            throw new \InvalidArgumentException('Cannot map type ' . $type);
        }

        return new $mapping($this->productFilter);
    }
}
