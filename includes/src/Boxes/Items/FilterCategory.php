<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Filter\Visibility;

/**
 * Class FilterCategory
 * @package Boxes\Items
 */
final class FilterCategory extends AbstractBox
{
    /**
     * FilterCategory constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = \Shop::getProductFilter()->getCategoryFilter();
        $searchResults = \Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getCategoryFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
