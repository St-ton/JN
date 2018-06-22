<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class PriceDESC
 * @package Filter\SortingOptions
 */
class PriceDESC extends PriceASC
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortPriceDesc'));
        $this->setPriority($this->getConfig()['artikeluebersicht']['suche_sortierprio_preis_ab']);
        $this->setValue(SEARCH_SORT_PRICE_DESC);
    }
}
