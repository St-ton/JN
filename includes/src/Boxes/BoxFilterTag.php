<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Filter\Visibility;

/**
 * Class BoxFilterTag
 * @package Boxes
 */
final class BoxFilterTag extends AbstractBox
{
    /**
     * BoxFilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = \Shop::getProductFilter()->tagFilterCompat;
        $searchResults = \Shop::getProductFilter()->getSearchResults(false);
        $show          = !$filter->getVisibility()->equals(Visibility::SHOW_NEVER())
            && !$filter->getVisibility()->equals(Visibility::SHOW_CONTENT())
            && (!empty($searchResults->getTagFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setItems($searchResults->getTagFilterOptions());
    }
}
