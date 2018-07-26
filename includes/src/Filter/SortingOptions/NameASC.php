<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class NameASC
 * @package Filter\SortingOptions
 */
class NameASC extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortNameAsc'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_name']);
        $this->setValue(SEARCH_SORT_NAME_ASC);
    }
}
