<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterHerstellerFilter
 */
class FilterHerstellerFilter extends FilterHersteller
{
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
