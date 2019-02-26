<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Filter\Visibility;
use JTL\Shop;

/**
 * Class FilterAttribute
 * @package JTL\Boxes\Items
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
        $filter        = Shop::getProductFilter()->getAttributeFilterCollection();
        $searchResults = Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getAttributeFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setItems($searchResults->getAttributeFilterOptions());
    }
}
