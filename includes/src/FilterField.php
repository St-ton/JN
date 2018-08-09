<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterField
 */
abstract class FilterField
{
    /**
     * @var Filter
     */
    protected $oFilter;

    /**
     * @var string
     */
    protected $cType = '';

    /**
     * @var array|mixed|string
     */
    protected $cTitle = '';

    /**
     * @var mixed|string
     */
    protected $cTitleLong = '';

    /**
     * @var string
     */
    protected $cColumn = '';

    /**
     * @var mixed|string
     */
    protected $cValue = '';

    /**
     * @var null|string|string[]
     */
    protected $cId = '';

    /**
     * FilterField constructor.
     *
     * @param Filter       $oFilter
     * @param string       $cType
     * @param string|array $cTitle - either title-string for this field or a pair of short title and long title
     * @param string       $cColumn
     * @param string       $cDefValue
     */
    public function __construct($oFilter, $cType, $cTitle, $cColumn, $cDefValue = '')
    {
        $this->oFilter    = $oFilter;
        $this->cType      = $cType;
        $this->cTitle     = is_array($cTitle) ? $cTitle[0] : $cTitle;
        $this->cTitleLong = is_array($cTitle) ? $cTitle[1] : '';
        $this->cColumn    = $cColumn;
        $this->cId        = preg_replace('/[^a-zA-Z0-9_]+/', '', $this->cTitle);
        $this->cValue     =
            $oFilter->getAction() === $oFilter->getId() . '_filter' ? $_GET[$oFilter->getId() . '_' . $this->cId] : (
            $oFilter->getAction() === $oFilter->getId() . '_resetfilter' ? $cDefValue : (
            $oFilter->hasSessionField($this->cId) ? $oFilter->getSessionField($this->cId) :
                $cDefValue
            ));
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->cType;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->cColumn;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->cTitle;
    }

    /**
     * @return mixed
     */
    public function getTitleLong()
    {
        return $this->cTitleLong;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->cId;
    }

    /**
     * @return string|null
     */
    abstract public function getWhereClause();
}
