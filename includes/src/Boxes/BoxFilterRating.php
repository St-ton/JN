<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Filter\Visibility;

/**
 * Class BoxFilterRating
 * @package Boxes
 */
final class BoxFilterRating extends AbstractBox
{
    /**
     * BoxFilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter = \Shop::getProductFilter()->getRatingFilter();
        $searchResults   = \Shop::getProductFilter()->getSearchResults();
        $show            = !$filter->getVisibility()->equals(Visibility::SHOW_NEVER())
            && !$filter->getVisibility()->equals(Visibility::SHOW_CONTENT())
            && (!empty($searchResults->getRatingFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
