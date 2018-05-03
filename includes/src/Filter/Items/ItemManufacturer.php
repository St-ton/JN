<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\ProductFilter;
use Filter\States\BaseManufacturer;

/**
 * Class ItemManufacturer
 * @package Filter\Items
 */
class ItemManufacturer extends BaseManufacturer
{
    /**
     * ItemManufacturer constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('hf')
             ->setUrlParamSEO(SEP_HST)
             ->setVisibility($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'])
             ->setFrontendName(\Shop::Lang()->get('allManufacturers'))
             ->setType($this->getConfig()['navigationsfilter']['manufacturer_filter_type'] === 'O'
                 ? AbstractFilter::FILTER_TYPE_OR
                 : AbstractFilter::FILTER_TYPE_AND);
    }

    /**
     * @param array|int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->value = is_array($value) ? $value : (int)$value;

        return $this;
    }
}
