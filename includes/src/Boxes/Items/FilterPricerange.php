<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Filter\Visibility;
use JTL\Shop;

/**
 * Class FilterPricerange
 * @package JTL\Boxes\Items
 */
final class FilterPricerange extends AbstractBox
{
    /**
     * FilterPricerange constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = Shop::getProductFilter()->getPriceRangeFilter();
        $searchResults = Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getPriceRangeFilterOptions()) || $filter->isInitialized());
        $this->setShow(true);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
