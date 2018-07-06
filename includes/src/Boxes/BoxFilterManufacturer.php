<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Filter\Visibility;

/**
 * Class BoxFilterManufacturer
 * @package Boxes
 */
final class BoxFilterManufacturer extends AbstractBox
{
    /**
     * BoxFilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = \Shop::getProductFilter()->getManufacturerFilter();
        $searchResults = \Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getManufacturerFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setItems($filter);
    }
}
