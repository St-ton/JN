<?php

/**
 * Interface IMedia
 */
interface IFilter
{
    /**
     * @param int|array $value - the current filter value(s)
     * @param array $languages
     * @return $this
     */
    public function init($value, $languages);

    /**
     * @return bool
     */
    public function isInitialized();

    /**
     * @return int|string|array
     */
    public function getValue();

    /**
     * @param int|string|array $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @param int|string $value
     * @return $this
     */
    public function addValue($value);

    /**
     * @param int $idx
     * @return string|null|array
     */
    public function getSeo($idx = null);

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return string
     */
    public function getPrimaryKeyRow();

    /**
     * @return string
     */
    public function getSQLCondition();

    /**
     * @return FilterJoin|FilterJoin[]
     */
    public function getSQLJoin();

    /**
     * @param mixed|null $mixed
     * @return []
     */
    public function getOptions($mixed = null);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getUrlParam();

    /**
     * @return bool
     */
    public function isCustom();

    /**
     * @param int   $languageID
     * @param int   $customerGroupID
     * @param array $config
     * @return $this
     */
    public function setData($languageID, $customerGroupID, $config);

    /**
     * @return int
     */
    public function getLanguageID();

    /**
     * @return int
     */
    public function getCustomerGroupID();

    /**
     * @return array
     */
    public function getConfig();
}
