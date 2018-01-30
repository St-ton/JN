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
        $params           = $this->productFilter->getParams();
        $view             = $this->productFilter->getMetaData()->getExtendedView($params['nDarstellung'])->nDarstellung;
        $optionIdx        = $view === ERWDARSTELLUNG_ANSICHT_LISTE
            ? 'products_per_page_list'
            : 'products_per_page_gallery';
        $limitOptions     = explode(',', $this->getConfig()['artikeluebersicht'][$optionIdx]);
        foreach ($limitOptions as $i => $limitOption) {
            $limitOption = (int)trim($limitOption);
            $name        = $limitOption > 0 ? $limitOption : Shop::Lang()->get('showAll');
            $options[]   = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($name)
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
