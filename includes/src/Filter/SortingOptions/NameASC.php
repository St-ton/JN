<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;
use JTL\Shop;

/**
 * Class NameASC
 * @package JTL\Filter\SortingOptions
 */
class NameASC extends AbstractSortingOption
{
    /**
     * NameASC constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tartikel.cName');
        $this->setName(Shop::Lang()->get('sortNameAsc'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_name']);
        $this->setValue(\SEARCH_SORT_NAME_ASC);
    }
}
