<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterManufacturerFilter
 */
class FilterManufacturerFilter extends FilterManufacturer
{
    /**
     * @var string
     */
    public $urlParam = 'hf';

    /**
     * @var string
     */
    public $urlParamSEO = SEP_HST;

    /**
     * @var bool
     */
    public $isCustom = false;

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
