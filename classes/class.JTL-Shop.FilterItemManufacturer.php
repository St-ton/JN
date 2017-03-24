<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemManufacturer
 */
class FilterItemManufacturer extends FilterBaseManufacturer
{
    use FilterItemTrait;

    /**
     * FilterItemManufacturer constructor.
     *
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'hf';
        $this->urlParamSEO = SEP_HST;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kHersteller';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'thersteller';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tartikel.' . $this->getPrimaryKeyRow() . ' = ' . $this->getValue();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [];
    }
}
