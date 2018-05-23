<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use Filter\Type;
use OPC\PortletInstance;

class ProductStream extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->addClass('text-center');
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();
        $style         = $instance->getProperty('listStyle');

        return "<div $attributes $dataAttribute>"
            . "<img src='" . PFAD_TEMPLATES . "Evo/portlets/ProductStream/preview.$style.png' "
            . "style='width:98%;filter:grayscale(50%) opacity(60%)'>"
            . "<div style='color:#5cbcf6;font-size:40px;font-weight:bold;margin:0;margin-top:-1em;line-height:1em;'>
                Produktliste</div>"
            . "</div>";
    }

    public function getFinalHtml($instance)
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Product<br>Stream';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'listStyle'   => [
                'type'    => 'select',
                'label'   => 'Darstellung',
                'options' => [
                    'gallery' => 'Galerie',
                    'list'    => 'Liste',
                    'slider'  => 'Slider',
                ],
                'default' => 'gallery',
            ],
            'sliderTitle' => [
                'label'                => 'Slidertitel',
                'showOnProp'           => 'listStyle',
                'showOnPropValue'      => 'slider',
                'collapseControlStart' => true,
                'collapseControlEnd'   => true,
            ],
            'filters'     => [
                'type'    => 'filter',
                'label'   => 'Artikelfilter',
                'default' => [],
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return int[]
     */
    public function getFilteredProductIds(PortletInstance $instance)
    {
        \Shop::setLanguage(1);

        $enabledFilters = $instance->getProperty('filters');
        $productFilter  = new \Filter\ProductFilter();

        foreach ($enabledFilters as $enabledFilter) {
            /** @var \Filter\AbstractFilter $newFilter **/
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND());
            $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
        }

        return $productFilter->getProductKeys();
    }

    /**
     * @param PortletInstance $instance
     * @return \Artikel[]
     */
    public function getFilteredProducts(PortletInstance $instance)
    {
        $products = [];

        foreach ($this->getFilteredProductIds($instance) as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            $product  = new \Artikel($kArtikel);
            $product->fuelleArtikel($kArtikel, null);
            $products[] = $product;
        }

        return $products;
    }
}