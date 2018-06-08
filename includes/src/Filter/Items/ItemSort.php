<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\AbstractFilter;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\ProductFilter;

/**
 * Class ItemSort
 * @package Filter\Items
 */
class ItemSort extends AbstractFilter
{
    /**
     * ItemSort constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('Sortierung')
             ->setFrontendName(\Shop::Lang()->get('sorting', 'productOverview'));
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        foreach ($this->productFilter->getMetaData()->getSortingOptions() as $i => $sortingOption) {
            $options[] = (new FilterOption())
                ->setIsActive(isset($_SESSION['Usersortierung'])
                    && $_SESSION['Usersortierung'] === (int)$sortingOption->value
                )
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$sortingOption->value)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($sortingOption->angezeigterName)
                ->setValue((int)$sortingOption->value)
                ->setSort($i);
        }
        $this->options = $options;

        return $options;
    }
}
