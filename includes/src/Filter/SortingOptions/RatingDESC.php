<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class RatingDESC
 * @package Filter\SortingOptions
 */
class RatingDESC extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
        $this->join->setComment('join from SORT by rating')
                   ->setType('LEFT JOIN')
                   ->setTable('tbewertung')
                   ->setOn('tbewertung.kArtikel = tartikel.kArtikel');
        $this->setName(\Shop::Lang()->get('rating'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_bewertung']);
        $this->setValue(SEARCH_SORT_RATING);
    }
}
