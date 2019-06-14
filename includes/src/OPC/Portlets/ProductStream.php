<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Filter\AbstractFilter;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;

/**
 * Class ProductStream
 * @package JTL\OPC\Portlets
 */
class ProductStream extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'listStyle'    => [
                'type'    => InputType::SELECT,
                'label'   => 'Darstellung',
                'options' => [
                    'gallery'    => 'Galerie',
                    'list'       => 'Liste',
                    'slider'     => 'Slider',
                    'vertSlider' => 'vertikaler Slider'
                ],
                'default' => 'gallery',
                'childrenFor' => [
                    'slider' => [
                        'sliderTitle'  => [
                            'label' => 'Slidertitel',
                            'width' => 50,
                        ],
                        'productCount' => [
                            'type'    => InputType::NUMBER,
                            'label'   => 'Anzahl sichtbare Artikel',
                            'width'   => 50,
                            'default' => 3,
                        ],
                    ]
                ]
            ],
            'filters'      => [
                'type'    => InputType::FILTER,
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

        foreach ($this->getFilteredProductIds($instance) as $productID) {
            $product = new Artikel();
            $product->fuelleArtikel($productID, $options);
            $products[] = $product;
        }

        return $products;
    }
}
