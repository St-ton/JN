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
            'search' => [
                'type'  => InputType::SEARCH,
                'label' => 'Suche',
                'placeholder' => __('search'),
                'width' => 67,
                'order' => 2,
            ],
            'listStyle'    => [
                'type'    => InputType::SELECT,
                'label'   => __('presentation'),
                'width'   => 34,
                'order'   => 1,
                'options' => [
                    'gallery'    => __('presentationGallery'),
                    'list'       => __('presentationList'),
                    'slider'     => __('presentationSlider'),
                    'vertSlider' => __('presentationSliderVertical'),
                ],
                'default' => 'gallery',
                'childrenFor' => [
                    'slider' => [
                        'sliderTitle'  => [
                            'label' => __('sliderTitle'),
                            'width' => 50,
                        ],
                        'productCount' => [
                            'type'    => InputType::NUMBER,
                            'label'   => __('numberVisibleItems'),
                            'width'   => 50,
                            'default' => 3,
                        ],
                    ]
                ]
            ],
            'filters'      => [
                'type'     => InputType::FILTER,
                'label'    => __('itemFilter'),
                'default'  => [],
                'searcher' => 'search',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles') => 'styles',
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
