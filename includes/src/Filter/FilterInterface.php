<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Interface FilterInterface
 * @package Filter
 */
interface FilterInterface
{
    /**
     * initialize an active filter
     *
     * @param int|array $value - the current filter value(s)
     * @return $this
     */
    public function init($value): FilterInterface;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive($active): FilterInterface;

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsInitialized($value): FilterInterface;

    /**
     * @return $this
     */
    public function generateActiveFilterData(): FilterInterface;

    /**
     * @param array $collection
     * @return $this
     */
    public function setFilterCollection(array $collection): FilterInterface;

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
    public function setProductFilter(ProductFilter $productFilter): FilterInterface;

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
    public function setValue($value): FilterInterface;

    /**
     * add filter value to active filter (only for FilterType::OR filters)
     *
     * @param int|string $value
     * @return $this
     */
    public function addValue($value): FilterInterface;

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
    public function setSeo(array $languages): FilterInterface;

    /**
     * @param string $name
     * @return FilterInterface
     */
    public function setName($name): FilterInterface;

    /**
     * get the filter's type - \Filter\Type::AND/\Filter\Type::OR
     *
     * @return Type
     */
    public function getType(): Type;

    /**
     * set a filter's type - \Filter\Type::AND/\Filter\Type::OR
     *
     * @param Type $type
     * @return $this
     */
    public function setType(Type $type): FilterInterface;

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
    public function setTableName($name): FilterInterface;

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
     * @return FilterJoinInterface|FilterJoinInterface[]
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
    public function setOptions($mixed): FilterInterface;

    /**
     * get a nice name
     *
     * @return string|null
     */
    public function getName();

    /**
     * get the GET parameter used in frontend for filtering products
     *
     * @return string
     */
    public function getUrlParam(): string;

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParam($param): FilterInterface;

    /**
     * get the SEO url parameter used in frontend for filtering products
     *
     * @return string
     */
    public function getUrlParamSEO(): string;

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParamSEO($param): FilterInterface;

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
    public function setIsCustom(bool $custom): FilterInterface;

    /**
     * set basic information for using this filter
     *
     * @param ProductFilter $productFilter
     * @return $this
     */
    public function setBaseData(ProductFilter $productFilter): FilterInterface;

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
     * @param string|null $idx
     * @return array|string|int
     */
    public function getConfig($idx = null);

    /**
     * get the filter's class name
     *
     * @return string
     */
    public function getClassName(): string;

    /**
     * set the filter's class name
     *
     * @param string $name
     * @return $this
     */
    public function setClassName($name): FilterInterface;

    /**
     * get the filter's nice name without namespace
     *
     * @return string
     */
    public function getNiceName(): string;

    /**
     * set the filter's class name
     *
     * @param string $name
     * @return $this
     */
    public function setNiceName($name): FilterInterface;

    /**
     * @return int
     */
    public function getCount(): int;

    /**
     * @param int $count
     * @return $this
     */
    public function setCount(int $count): FilterInterface;

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort): FilterInterface;

    /**
     * @return bool
     */
    public function getIsChecked(): bool;

    /**
     * @param bool $isChecked
     * @return $this
     */
    public function setIsChecked(bool $isChecked): FilterInterface;

    /**
     * @return bool
     */
    public function getDoUnset(): bool;

    /**
     * @param bool $doUnset
     * @return $this
     */
    public function setDoUnset(bool $doUnset): FilterInterface;

    /**
     * @param string|array $url
     * @return $this
     */
    public function setUnsetFilterURL($url): FilterInterface;

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
     * @return Visibility
     */
    public function getVisibility(): Visibility;

    /**
     * @param int|string $visibility
     * @return $this
     */
    public function setVisibility($visibility): FilterInterface;

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontendName(string $name): FilterInterface;

    /**
     * @return string
     */
    public function getFrontendName(): string;

    /**
     * @param InputType $type
     * @return $this
     */
    public function setInputType(InputType $type): FilterInterface;

    /**
     * @return InputType
     */
    public function getInputType(): InputType;

    /**
     * @param string|null $icon
     * @return $this
     */
    public function setIcon($icon): FilterInterface;

    /**
     * @return string|null
     */
    public function getIcon();

    /**
     * @return FilterOption|FilterOption[]
     */
    public function getActiveValues();

    /**
     * @return $this
     */
    public function hide(): FilterInterface;

    /**
     * @return bool
     */
    public function isHidden(): bool;
}
