<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class SortDefault
 * @package Filter\SortingOptions
 */
class SortDefault extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.nSort, tartikel.cName';
        if ($this->productFilter->getCategory()->getValue() > 0) {
            $this->orderBy = 'tartikel.nSort, tartikel.cName';
        } elseif (isset($_SESSION['Usersortierung'])
            && $_SESSION['Usersortierung'] === \SEARCH_SORT_STANDARD
            && $this->productFilter->getSearch()->getSearchCacheID() > 0
        ) {
            $this->orderBy = 'jSuche.nSort'; // was tsuchcachetreffer in 4.06, but is aliased to jSuche
        }
    }
}
