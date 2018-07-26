<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class Bestseller
 * @package Filter\SortingOptions
 */
class Bestseller extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tbestseller.fAnzahl DESC, tartikel.cName';
        $this->join->setComment('join from SORT by bestseller')
                   ->setType('LEFT JOIN')
                   ->setTable('tbestseller')
                   ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
        $this->setName(\Shop::Lang()->get('bestseller'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_bestseller']);
        $this->setValue(SEARCH_SORT_BESTSELLER);
    }
}
