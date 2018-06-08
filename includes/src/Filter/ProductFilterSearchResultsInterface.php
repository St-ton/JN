<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use Tightenco\Collect\Support\Collection;


/**
 * Class ProductFilterSearchResults
 * @package Filter
 */
interface ProductFilterSearchResultsInterface
{
    /**
     * @param \stdClass $legacy
     * @return $this
     */
    public function convert($legacy): ProductFilterSearchResultsInterface;

    /**
     * @return \stdClass
     */
    public function getProductsCompat(): \stdClass;

    /**
     * @return $this
     */
    public function setProductsCompat(): ProductFilterSearchResultsInterface;

    /**
     * @return Collection
     */
    public function getProductKeys(): Collection;

    /**
     * @param Collection $keys
     * @return $this
     */
    public function setProductKeys(Collection $keys): ProductFilterSearchResultsInterface;

    /**
     * @return \Tightenco\Collect\Support\Collection()
     */
    public function getProducts(): Collection;

    /**
     * @param \Tightenco\Collect\Support\Collection() $products
     * @return $this
     */
    public function setProducts($products): ProductFilterSearchResultsInterface;

    /**
     * @return int
     */
    public function getProductCount(): int;

    /**
     * @param int $productCount
     * @return $this
     */
    public function setProductCount($productCount): ProductFilterSearchResultsInterface;

    /**
     * @return int
     */
    public function getVisibleProductCount(): int;

    /**
     * @param int $count
     * @return $this
     */
    public function setVisibleProductCount(int $count): ProductFilterSearchResultsInterface;

    /**
     * @return int
     */
    public function getOffsetStart(): int;

    /**
     * @param int $offsetStart
     * @return $this
     */
    public function setOffsetStart($offsetStart): ProductFilterSearchResultsInterface;

    /**
     * @return int
     */
    public function getOffsetEnd(): int;

    /**
     * @param int $offsetEnd
     * @return $this
     */
    public function setOffsetEnd($offsetEnd): ProductFilterSearchResultsInterface;

    /**
     * @return \stdClass
     */
    public function getPages(): \stdClass;

    /**
     * @param \stdClass $pages
     * @return $this
     */
    public function setPages($pages): ProductFilterSearchResultsInterface;

    /**
     * @return string|null
     */
    public function getSearchTerm();

    /**
     * @param string $searchTerm
     * @return $this
     */
    public function setSearchTerm($searchTerm): ProductFilterSearchResultsInterface;

    /**
     * @return string
     */
    public function getSearchTermWrite();

    /**
     * @param string $searchTerm
     * @return $this
     */
    public function setSearchTermWrite($searchTerm): ProductFilterSearchResultsInterface;

    /**
     * @return bool
     */
    public function getSearchUnsuccessful(): bool;

    /**
     * @param bool $searchUnsuccessful
     * @return $this
     */
    public function setSearchUnsuccessful($searchUnsuccessful): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getManufacturerFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setManufacturerFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getRatingFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setRatingFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getTagFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setTagFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getAttributeFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setAttributeFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getPriceRangeFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setPriceRangeFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getCategoryFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setCategoryFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getSearchFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setSearchFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getSearchSpecialFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setSearchSpecialFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return FilterOption[]
     */
    public function getCustomFilterOptions(): array;

    /**
     * @param FilterOption[] $options
     * @return $this
     */
    public function setCustomFilterOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return string|null
     */
    public function getTagFilterJSON();

    /**
     * @param string $json
     * @return $this
     */
    public function setTagFilterJSON($json): ProductFilterSearchResultsInterface;

    /**
     * @return string|null
     */
    public function getSearchFilterJSON();

    /**
     * @param string $json
     * @return $this
     */
    public function setSearchFilterJSON($json): ProductFilterSearchResultsInterface;

    /**
     * @return string|null
     */
    public function getError();

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error): ProductFilterSearchResultsInterface;

    /**
     * @return array
     */
    public function getSortingOptions(): array;

    /**
     * @param array $options
     * @return $this
     */
    public function setSortingOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return array
     */
    public function getLimitOptions(): array;

    /**
     * @param array $options
     * @return $this
     */
    public function setLimitOptions($options): ProductFilterSearchResultsInterface;

    /**
     * @return array
     */
    public function getAllFilterOptions(): array;

    /**
     * @param ProductFilter   $productFilter
     * @param null|\Kategorie $currentCategory
     * @param bool            $selectionWizard
     * @return $this
     */
    public function setFilterOptions(
        ProductFilter $productFilter,
        $currentCategory = null,
        $selectionWizard = false
    ): ProductFilterSearchResultsInterface;
}
