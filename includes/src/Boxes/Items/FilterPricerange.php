<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

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
        $templateSettings = Template::getInstance()->getConfig();
        parent::__construct($config);
        $filter        = Shop::getProductFilter()->getPriceRangeFilter();
        $searchResults = Shop::getProductFilter()->getSearchResults();
        $show          = ($templateSettings['sidebar_settings']['always_show_price_range'] ?? 'N' === 'Y')
            || ($filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getPriceRangeFilterOptions()) || $filter->isInitialized()));
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
        $this->setItems($filter);
    }
}
