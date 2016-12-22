<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterDummyState
 */
class FilterDummyState extends AbstractFilter implements IFilter
{
    use FilterItemTrait;

    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var string
     */
    public $urlParam = 'ds';

    /**
     * @var string
     */
    public $urlParamSEO = null;

    /**
     * @var null
     */
    public $dummyValue = null;

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->dummyValue = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->dummyValue;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        return $this;
    }

    /**
     * @param int   $id
     * @return $this
     */
    public function init($id)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @param int $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        return [];
    }
}
