<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class PriceASC
 * @package Filter\SortingOptions
 */
class PriceASC extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tpreise.fVKNetto, tartikel.cName';
        $this->join->setComment('join from SORT by price ASC')
                   ->setType('JOIN')
                   ->setTable('tpreise')
                   ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                AND tpreise.kKundengruppe = ' . $productFilter->getCustomerGroupID());
        $this->setName(\Shop::Lang()->get('sortPriceAsc'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_preis']);
        $this->setValue(SEARCH_SORT_PRICE_ASC);
    }
}
