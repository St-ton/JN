<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterDummyState
 */
class FilterDummyState extends AbstractFilter
{
    /**
     * @var null
     */
    public $dummyValue;

    /**
     * FilterDummyState constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('ds')
             ->setUrlParamSEO(null);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->dummyValue = (int)$value;

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
     * @param int $id
     * @return $this
     */
    public function init($id)
    {
        return $this;
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
}
