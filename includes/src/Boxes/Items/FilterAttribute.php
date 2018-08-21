<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


use Filter\Visibility;

/**
 * Class FilterAttribute
 * @package Boxes
 */
final class FilterAttribute extends AbstractBox
{
    /**
     * FilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = \Shop::getProductFilter()->getAttributeFilterCollection();
        $searchResults = \Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getAttributeFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setItems($searchResults->getAttributeFilterOptions());
    }
}
