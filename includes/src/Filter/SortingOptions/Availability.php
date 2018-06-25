<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class Availability
 * @package Filter\SortingOptions
 */
class Availability extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortAvailability'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_lagerbestand']);
        $this->setValue(SEARCH_SORT_AVAILABILITY);
    }
}
