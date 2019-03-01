<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;

/**
 * Class None
 * @package JTL\Filter\SortingOptions
 */
class None extends AbstractSortingOption
{
    /**
     * None constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('');
        $this->setName('');
        $this->setPriority(-1);
        $this->setValue(\SEARCH_SORT_NONE);
    }
}
