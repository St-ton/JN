<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterSelectField extends FilterField
{
    public $oOption_arr = array();

    /**
     * FilterSelectField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     */
    public function __construct($oFilter, $cTitle, $cColumn)
    {
        parent::__construct($oFilter, 'select', $cTitle, $cColumn, '0');
    }

    /**
     * Add a select option to a filter select field
     *
     * @param string $cTitle - the label/title for this option
     * @param string $cCond - options conditional right part (e.g. "= 'Y'" or "> 10")
     * @return FilterSelectOption
     */
    public function addSelectOption($cTitle, $cCond)
    {
        $oOption             = new FilterSelectOption($cTitle, $cCond);
        $this->oOption_arr[] = $oOption;

        return $oOption;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->oOption_arr;
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        $cCond = $this->oOption_arr[(int)$this->cValue]->getCond();
        if ($cCond !== '') {
            return $this->cColumn . " " . $cCond;
        }

        return null;
    }
}
