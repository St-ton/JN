<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


use Filter\Visibility;

/**
 * Class FilterPricerange
 * @package Boxes
 */
final class FilterPricerange extends AbstractBox
{
    /**
     * FilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = \Shop::getProductFilter()->getPriceRangeFilter();
        $searchResults = \Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getPriceRangeFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
