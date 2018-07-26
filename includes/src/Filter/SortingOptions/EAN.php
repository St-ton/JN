<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\ProductFilter;

/**
 * Class EAN
 * @package Filter\SortingOptions
 */
class EAN extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->orderBy = 'tartikel.cBarcode, tartikel.cName';
        $this->setName(\Shop::Lang()->get('sortEan'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_ean']);
        $this->setValue(SEARCH_SORT_EAN);
    }
}
