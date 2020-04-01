<?php declare(strict_types=1);

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;
use JTL\Shop;

/**
 * Class DateOfIssue
 * @package JTL\Filter\SortingOptions
 */
class DateOfIssue extends AbstractSortingOption
{
    /**
     * DateOfIssue constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tartikel.dErscheinungsdatum DESC, tartikel.cName');
        $this->setName(Shop::Lang()->get('sortDateofissue'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_erscheinungsdatum']);
        $this->setValue(\SEARCH_SORT_DATEOFISSUE);
    }
}
