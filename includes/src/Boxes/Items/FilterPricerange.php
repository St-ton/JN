<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Filter\Visibility;
use JTL\Shop;
use JTL\Template;

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
        $templateSettings = Template::getInstance()->getConfig();
        $filter           = Shop::getProductFilter()->getPriceRangeFilter();
        $searchResults    = Shop::getProductFilter()->getSearchResults();
        $show             = (isset($templateSettings['productlist'])
                && ($templateSettings['productlist']['always_show_price_range'] ?? 'N') === 'Y')
            || ($filter->getVisibility() !== Visibility::SHOW_NEVER
                && $filter->getVisibility() !== Visibility::SHOW_CONTENT
                && (!empty($searchResults->getPriceRangeFilterOptions()) || $filter->isInitialized()));
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
