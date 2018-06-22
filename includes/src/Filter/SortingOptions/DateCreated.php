<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class DateCreated
 * @package Filter\SortingOptions
 */
class DateCreated extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.dErstellt DESC, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortNewestFirst'));
        $this->setPriority($this->getConfig()['artikeluebersicht']['suche_sortierprio_erstelldatum']);
        $this->setValue(SEARCH_SORT_NEWEST_FIRST);
    }
}
