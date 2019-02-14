<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\Catalog\Product\Artikel;
use JTL\Filter\AbstractFilter;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;
use Tightenco\Collect\Support\Collection;

/**
 * Class ProductStream
 * @package JTL\OPC\Portlets
 */
class ProductStream extends Portlet
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

        return '<div ' . $attributes . ' ' . $dataAttribute . '>'
            . '<img alt="" src="' . \PFAD_TEMPLATES . 'Evo/portlets/ProductStream/preview.' . $style . '.png" '
            . 'style="width:98%;filter:grayscale(50%) opacity(60%)">'
            . '<div style="color:#5cbcf6;font-size:40px;font-weight:bold;margin: -1em 0 0 0;line-height:1em;">
                Produktliste</div>'
            . '</div>';
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
        return '<img alt="" class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Product<br>Stream';
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
     * @return Collection
     */
    public function getFilteredProductIds(PortletInstance $instance): Collection
    {
        $enabledFilters = $instance->getProperty('filters');
        $productFilter  = new ProductFilter(
            Config::getDefault(),
            Shop::Container()->getDB(),
            Shop::Container()->getCache()
        );

        foreach ($enabledFilters as $enabledFilter) {
            /** @var AbstractFilter $newFilter * */
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND);
            $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
        }

        return $productFilter->getProductKeys();
    }

    /**
     * @param PortletInstance $instance
     * @return Artikel[]
     */
    public function getFilteredProducts(PortletInstance $instance): array
    {
        $products = [];
        $options  = Artikel::getDefaultOptions();
        foreach ($this->getFilteredProductIds($instance) as $kArtikel) {
            $product = new Artikel();
            $product->fuelleArtikel($kArtikel, $options);
            $products[] = $product;
        }

        return $products;
    }
}
