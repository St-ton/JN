<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

abstract class FilterField
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

        $this->cValue =
            $oFilter->getAction() === $oFilter->getId() . '_filter'      ? $_GET[$oFilter->getId() . '_' . $cColumn] : (
            $oFilter->getAction() === $oFilter->getId() . '_resetfilter' ? $cDefValue : (
            $oFilter->hasSessionField($cColumn)                          ? $oFilter->getSessionField($cColumn) :
                                                                           $cDefValue
            ));
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
    abstract public function getWhereClause();
}
