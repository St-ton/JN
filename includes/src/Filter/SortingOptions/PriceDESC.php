<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;
use JTL\Shop;

/**
 * Class PriceDESC
 * @package JTL\Filter\SortingOptions
 */
class PriceDESC extends PriceASC
{
    /**
     * PriceDESC constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tpreise.fVKNetto DESC, tartikel.cName');
        $this->setName(Shop::Lang()->get('sortPriceDesc'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_preis_ab']);
        $this->setValue(\SEARCH_SORT_PRICE_DESC);
    }
}
