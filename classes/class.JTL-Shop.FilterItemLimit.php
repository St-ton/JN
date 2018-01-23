<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemLimit
 */
class FilterItemLimit extends AbstractFilter
{
    /**
     * FilterItemLimit constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('af')
             ->setFrontendName(Shop::Lang()->get('productsPerPage', 'productOverview'));
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
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return null;
    }

    /**
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        foreach ([0, 9, 18, 30, 90] as $i => $limitOption) {
            $options[] = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($limitOption)
                ->setValue($limitOption)
                ->setCount(null)
                ->setSort($i)
                ->setIsActive(isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] === $limitOption)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init($limitOption)
                ));
        }
        $this->options = $options;

        return $options;
    }
}
