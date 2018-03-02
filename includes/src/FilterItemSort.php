<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemSort
 */
class FilterItemSort extends AbstractFilter
{
    /**
     * FilterItemSort constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('Sortierung')
             ->setFrontendName(Shop::Lang()->get('sorting', 'productOverview'));
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
        foreach ($this->productFilter->getMetaData()->getSortingOptions() as $i => $sortingOption) {
            $options[] = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($sortingOption->angezeigterName)
                ->setValue((int)$sortingOption->value)
                ->setCount(null)
                ->setSort($i)
                ->setIsActive(isset($_SESSION['Usersortierung']) && $_SESSION['Usersortierung'] === (int)$sortingOption->value)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$sortingOption->value)
                ));
        }
        $this->options = $options;

        return $options;
    }
}
