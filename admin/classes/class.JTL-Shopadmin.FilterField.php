<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterField
{
    protected $oFilter = null;
    protected $cType   = '';
    protected $cTitle  = '';
    protected $cColumn = '';
    protected $cValue  = '';

    /**
     * FilterField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cType
     * @param string $cTitle
     * @param string $cColumn
     * @param string $cDefValue
     */
    public function __construct($oFilter, $cType, $cTitle, $cColumn, $cDefValue = '')
    {
        $this->oFilter = $oFilter;
        $this->cType   = $cType;
        $this->cTitle  = $cTitle;
        $this->cColumn = $cColumn;

        if ($oFilter->getAction() === 'filter') {
            $this->cValue = $_GET[$cColumn];
        } elseif ($oFilter->getAction() === 'resetfilter') {
            $this->cValue = $cDefValue;
        } elseif ($oFilter->hasSessionField($cColumn)) {
            $this->cValue = $oFilter->getSessionField($cColumn);
        } else {
            $this->cValue = $cDefValue;
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->cType;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->cColumn;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->cTitle;
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        return '';
    }
}
