<?php declare(strict_types=1);

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;
use JTL\Shop;

/**
 * Class Availability
 * @package JTL\Filter\SortingOptions
 */
class Availability extends AbstractSortingOption
{
    /**
     * Availability constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName');
        $this->setName(Shop::Lang()->get('sortAvailability'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_lagerbestand']);
        $this->setValue(\SEARCH_SORT_AVAILABILITY);
    }
}
