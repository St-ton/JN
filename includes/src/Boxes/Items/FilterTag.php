<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Filter\Visibility;
use JTL\Shop;

/**
 * Class FilterTag
 * @package JTL\Boxes\Items
 */
final class FilterTag extends AbstractBox
{
    /**
     * FilterTag constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter        = Shop::getProductFilter()->tagFilterCompat;
        $searchResults = Shop::getProductFilter()->getSearchResults();
        $show          = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && (!empty($searchResults->getTagFilterOptions()) || $filter->isInitialized());
        $this->setShow($show);
        $this->setItems($searchResults->getTagFilterOptions());
    }
}
