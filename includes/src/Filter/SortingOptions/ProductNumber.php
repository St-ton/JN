<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class ProductNumber
 * @package Filter\SortingOptions
 */
class ProductNumber extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.cArtNr, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortProductno'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_artikelnummer']);
        $this->setValue(SEARCH_SORT_PRODUCTNO);
    }
}
