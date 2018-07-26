<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use Filter\Type;
use OPC\PortletInstance;

/**
 * Class ProductStream
 * @package OPC\Portlets
 */
class ProductStream extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->addClass('text-center');
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();
        $style         = $instance->getProperty('listStyle');

        return "<div $attributes $dataAttribute>"
            . "<img src='" . \PFAD_TEMPLATES . "Evo/portlets/ProductStream/preview.$style.png' "
            . "style='width:98%;filter:grayscale(50%) opacity(60%)'>"
            . "<div style='color:#5cbcf6;font-size:40px;font-weight:bold;margin:0;margin-top:-1em;line-height:1em;'>
                Produktliste</div>"
            . "</div>";
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Product<br>Stream';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'listStyle'    => [
                'type'    => 'select',
                'label'   => 'Darstellung',
                'options' => [
                    'gallery'    => 'Galerie',
                    'list'       => 'Liste',
                    'slider'     => 'Slider',
                    'vertSlider' => 'vertikaler Slider'
                ],
                'default' => 'gallery',
            ],
            'sliderTitle'  => [
                'label'                => 'Slidertitel',
                'showOnProp'           => 'listStyle',
                'showOnPropValue'      => 'slider',
                'collapseControlStart' => true,
                'dspl_width'           => 50,
            ],
            'productCount' => [
                'label'              => 'Anzahl sichtbare Artikel',
                'type'               => 'number',
                'collapseControlEnd' => true,
                'dspl_width'         => 50,
                'default'            => 3,
            ],
            'filters'      => [
                'type'    => 'filter',
                'label'   => 'Artikelfilter',
                'default' => [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles' => 'styles',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getFilteredProductIds(PortletInstance $instance)
    {
        $enabledFilters = $instance->getProperty('filters');
        $productFilter  = new \Filter\ProductFilter();

        foreach ($enabledFilters as $enabledFilter) {
            /** @var \Filter\AbstractFilter $newFilter * */
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
    public function getFilteredProducts(PortletInstance $instance): array
    {
        $products = [];
        $options  = \Artikel::getDefaultOptions();
        foreach ($this->getFilteredProductIds($instance) as $kArtikel) {
            $product = new \Artikel();
            $product->fuelleArtikel($kArtikel, $options);
            $products[] = $product;
        }

        return $products;
    }
}
