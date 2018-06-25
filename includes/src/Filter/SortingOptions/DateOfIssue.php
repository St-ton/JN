<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class DateOfIssue
 * @package Filter\SortingOptions
 */
class DateOfIssue extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.dErscheinungsdatum DESC, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortDateofissue'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_erscheinungsdatum']);
        $this->setValue(SEARCH_SORT_DATEOFISSUE);
    }
}
