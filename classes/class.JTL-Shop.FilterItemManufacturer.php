<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemManufacturer
 */
class FilterItemManufacturer extends FilterBaseManufacturer
{
    use FilterItemTrait;

    /**
     * FilterItemManufacturer constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct($productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'hf';
        $this->urlParamSEO = SEP_HST;
        $this->setVisibility($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'])
             ->setFrontendName(Shop::Lang()->get('allManufacturers'));
    }
}
