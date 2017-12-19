<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface IMedia
 */
interface IFilter
{
    /**
     * initialize an active filter
     *
     * @param int|array $value - the current filter value(s)
     * @return $this
     */
    public function init($value);

    /**
     * check if filter is already initialized
     *
     * @return bool
     */
    public function isInitialized();

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
    public function setValue($value);

    /**
     * add filter value to active filter (only for FILTER_TYPE_OR filters)
     *
     * @param int|string $value
     * @return $this
     */
    public function addValue($value);

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
    public function setSeo($languages);

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
    public function setType($type);

    /**
     * the filter's base MySQL table name
     *
     * @return string
     */
    public function getTableName();

    /**
     * alias the filter's base MySQL table name
     *
     * @return string
     */
    public function getTableAlias();

    /**
     * the filter's primary key row
     *
     * @return string
     */
    public function getPrimaryKeyRow();

    /**
     * base MySQL filter condition
     *
     * @return string
     */
    public function getSQLCondition();

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
     * @return array
     */
    public function getOptions($mixed = null);

    /**
     * set the list of available options
     *
     * @param mixed $mixed
     * @return $this
     */
    public function setOptions($mixed);

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
     * get the SEO url parameter used in frontend for filtering products
     *
     * @return string
     */
    public function getUrlParamSEO();

    /**
     * check if this filter is built-in or not
     *
     * @return bool
     */
    public function isCustom();

    /**
     * set basic information for using this filter
     *
     * @param ProductFilter|null $productFilter
     * @return $this
     */
    public function setData($productFilter);

    /**
     * the language ID currently active in the shop
     *
     * @return int
     */
    public function getLanguageID();

    /**
     * the customer group ID currently active in the shop
     *
     * @return int
     */
    public function getCustomerGroupID();

    /**
     * get shop settings, derived from Navigationsfilter class
     *
     * @return array
     */
    public function getConfig();

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
    public function setClassName($className);

    /**
     * @return bool
     */
    public function getIsChecked();

    /**
     * @param bool $isChecked
     * @return bool
     */
    public function setIsChecked($isChecked);

    /**
     * @return bool
     */
    public function getDoUnset();

    /**
     * @param bool $doUnset
     * @return $this
     */
    public function setDoUnset($doUnset);

    /**
     * @param string|array $url
     * @return $this
     */
    public function setUnsetFilterURL($url);

    /**
     * @param string|null
     * @return string
     */
    public function getUnsetFilterURL($idx = null);

    /**
     * @return int
     */
    public function getVisibility();

    /**
     * @param int|string $visibility
     * @return $this
     */
    public function setVisibility($visibility);

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontendName($name);

    /**
     * @return string
     */
    public function getFrontendName();

    /**
     * @param array $collection
     * @return $this
     */
    public function setFilterCollection(array $collection);

    /**
     * @return array
     */
    public function getFilterCollection();

    /**
     * @param int $type
     * @return $this
     */
    public function setInputType($type);

    /**
     * @return int
     */
    public function getInputType();

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @return FilterExtra|FilterExtra[]
     */
    public function getActiveValues();

    /**
     * @return $this
     */
    public function generateActiveFilterData();

    /**
     * @return $this
     */
    public function hide();

    /**
     * @return bool
     */
    public function isHidden();
}
