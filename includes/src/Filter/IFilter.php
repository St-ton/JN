<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Interface IFilter
 * @package Filter
 */
interface IFilter
{
    /**
     * initialize an active filter
     *
     * @param int|array $value - the current filter value(s)
     * @return $this
     */
    public function init($value): IFilter;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive($active): IFilter;

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsInitialized($value): IFilter;

    /**
     * @return $this
     */
    public function generateActiveFilterData(): IFilter;

    /**
     * @param array $collection
     * @return $this
     */
    public function setFilterCollection(array $collection): IFilter;

    /**
     * @return array
     */
    public function getFilterCollection(): array;

    /**
     * @return ProductFilter
     */
    public function getProductFilter(): ProductFilter;

    /**
     * @param ProductFilter $productFilter
     * @return mixed
     */
    public function setProductFilter(ProductFilter $productFilter): IFilter;

    /**
     * check if filter is already initialized
     *
     * @return bool
     */
    public function isInitialized(): bool;

    /**
     * get an active filter's current filter value(s)
     *
     * @return int|string|array
     */
    public function getValue();

    /**
     * set the active filter's filter value
     *
     * @param int|string|array $value
     * @return $this
     */
    public function setValue($value): IFilter;

    /**
     * add filter value to active filter (only for FILTER_TYPE_OR filters)
     *
     * @param int|string $value
     * @return $this
     */
    public function addValue($value): IFilter;

    /**
     * get the filter's SEO url for a language
     *
     * @param int $idx - usually the language ID
     * @return string|null|array
     */
    public function getSeo($idx = null);

    /**
     * calculate SEO urls for given languages
     *
     * @param array $languages
     * @return $this
     */
    public function setSeo(array $languages): IFilter;

    /**
     * @param string $name
     * @return IFilter
     */
    public function setName($name): IFilter;

    /**
     * get the filter's type - FILTER_TYPE_OR/FILTER_TYPE_AND
     *
     * @return int
     */
    public function getType();

    /**
     * set a filter's type - FILTER_TYPE_OR/FILTER_TYPE_AND
     *
     * @param int $type
     * @return $this
     */
    public function setType($type): IFilter;

    /**
     * the filter's base MySQL table name
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setTableName($name): IFilter;

    /**
     * alias the filter's base MySQL table name
     *
     * @return string
     */
    public function getTableAlias(): string;

    /**
     * the filter's primary key row
     *
     * @return string
     */
    public function getPrimaryKeyRow(): string;

    /**
     * base MySQL filter condition
     *
     * @return string
     */
    public function getSQLCondition(): string;

    /**
     * list of necessary joins
     *
     * @return FilterJoin|FilterJoin[]
     */
    public function getSQLJoin();

    /**
     * get list of available filter options in the current view
     *
     * @param mixed|null $mixed - additional data that might be needed
     * @return FilterOption[]
     */
    public function getOptions($mixed = null): array;

    /**
     * set the list of available options
     *
     * @param mixed $mixed
     * @return $this
     */
    public function setOptions($mixed): IFilter;

    /**
     * get a nice name
     *
     * @return string
     */
    public function getName();

    /**
     * get the GET parameter used in frontend for filtering products
     *
     * @return string
     */
    public function getUrlParam();

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParam($param): IFilter;

    /**
     * get the SEO url parameter used in frontend for filtering products
     *
     * @return string
     */
    public function getUrlParamSEO();

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParamSEO($param): IFilter;

    /**
     * check if this filter is built-in or not
     *
     * @return bool
     */
    public function isCustom(): bool;

    /**
     * @param bool $custom
     * @return $this
     */
    public function setIsCustom(bool $custom): IFilter;

    /**
     * set basic information for using this filter
     *
     * @param ProductFilter|null $productFilter
     * @return $this
     */
    public function setBaseData($productFilter): IFilter;

    /**
     * the language ID currently active in the shop
     *
     * @return int
     */
    public function getLanguageID(): int;

    /**
     * the customer group ID currently active in the shop
     *
     * @return int
     */
    public function getCustomerGroupID(): int;

    /**
     * get shop settings, derived from Navigationsfilter class
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * get the filter's class name
     *
     * @return string
     */
    public function getClassName();

    /**
     * set the filter's class name
     *
     * @param string $className
     * @return $this
     */
    public function setClassName($className): IFilter;

    /**
     * @return int
     */
    public function getCount();

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count);

    /**
     * @return int
     */
    public function getSort();

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort($sort);

    /**
     * @return bool
     */
    public function getIsChecked(): bool;

    /**
     * @param bool $isChecked
     * @return $this
     */
    public function setIsChecked(bool $isChecked): IFilter;

    /**
     * @return bool
     */
    public function getDoUnset(): bool;

    /**
     * @param bool $doUnset
     * @return $this
     */
    public function setDoUnset(bool $doUnset): IFilter;

    /**
     * @param string|array $url
     * @return $this
     */
    public function setUnsetFilterURL($url): IFilter;

    /**
     * @param string|null
     * @return string
     */
    public function getUnsetFilterURL($idx = null);

    /**
     * @return array
     */
    public function getAvailableLanguages(): array;

    /**
     * @return int
     */
    public function getVisibility();

    /**
     * @param int|string $visibility
     * @return $this
     */
    public function setVisibility($visibility): IFilter;

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontendName(string $name): IFilter;

    /**
     * @return string
     */
    public function getFrontendName();

    /**
     * @param int $type
     * @return $this
     */
    public function setInputType($type): IFilter;

    /**
     * @return int
     */
    public function getInputType();

    /**
     * @param string|null $icon
     * @return $this
     */
    public function setIcon($icon): IFilter;

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @return FilterOption|FilterOption[]
     */
    public function getActiveValues();

    /**
     * @return $this
     */
    public function hide(): IFilter;

    /**
     * @return bool
     */
    public function isHidden(): bool;
}
