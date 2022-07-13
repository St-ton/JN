<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\ProductStream;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Filter\Config;
use JTL\Filter\Items\Manufacturer;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\Helpers\Product;
use JTL\Helpers\Text;
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
                'label'   => \__('presentation'),
                'width'   => 33,
                'options' => [
                    'gallery'      => \__('presentationGallery'),
                    'list'         => \__('presentationList'),
                    'simpleSlider' => \__('presentationSimpleSlider'),
                    'slider'       => \__('presentationSlider'),
                    'box-slider'   => \__('presentationBoxSlider'),
                ],
                'default' => 'gallery',
            ],
            'maxProducts' => [
                'type'     => InputType::NUMBER,
                'label'    => \__('maxProducts'),
                'width'    => 30,
                'default'  => 15,
                'required' => true,
            ],
            'source' => [
                'type'     => InputType::SELECT,
                'label'    => \__('productSource'),
                'width'    => 33,
                'options'  => [
                    'filter'    => \__('productSourceFiltering'),
                    'explicit'  => \__('productSourceExplicit'),
                ],
                'childrenFor' => [
                    'filter' => [
                        'search' => [
                            'type'        => InputType::SEARCH,
                            'label'       => '',
                            'placeholder' => \__('search'),
                            'width'       => 100,
                        ],
                        'filters'      => [
                            'type'     => InputType::FILTER,
                            'label'    => \__('itemFilter'),
                            'default'  => [],
                            'searcher' => 'search',
                        ],
                    ],
                    'explicit' => [
                        'searchExplicit' => [
                            'type'        => InputType::SEARCH,
                            'label'       => \__('labelSearchProduct'),
                            'placeholder' => \__('search'),
                            'width'       => 100,
                        ],
                        'productIds' => [
                            'type'           => InputType::SEARCHPICKER,
                            'searcher'       => 'searchExplicit',
                            'dataIoFuncName' => 'getProducts',
                            'keyName'        => 'kArtikel',
                        ],
                    ],
                ],
                'default'  => 'filter',
                'required' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            \__('Styles') => 'styles',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return int[]
     */
    public function getExplicitProductIds(PortletInstance $instance)
    {
        return Text::parseSSKint($instance->getProperty('productIds'));
    }

    /**
     * @param PortletInstance $instance
     * @return Collection
     */
    public function getFilteredProductIds(PortletInstance $instance): Collection
    {
        if ($instance->getProperty('source') === 'explicit') {
            return (new Collection($this->getExplicitProductIds($instance)))
                ->slice(0, $instance->getProperty('maxProducts'));
        }

        $params         = [
            'MerkmalFilter_arr'   => [],
            'SuchFilter_arr'      => [],
            'SuchFilter'          => [],
            'manufacturerFilters' => []
        ];
        $enabledFilters = $instance->getProperty('filters');
        $pf             = new ProductFilter(
            Config::getDefault(),
            Shop::Container()->getDB(),
            Shop::Container()->getCache()
        );
        $service        = Shop::Container()->getOPC();
        $pf->getBaseState()->init(0);
        foreach ($enabledFilters as $enabledFilter) {
            $service->getFilterClassParamMapping($enabledFilter['class'], $params, $enabledFilter['value'], $pf);
        }
        $service->overrideConfig($pf);
        $pf->initStates($params);
        foreach ($pf->getActiveFilters() as $filter) {
            if ($filter->getClassName() !== Manufacturer::class) {
                $filter->setType(Type::AND);
            }
        }

        return $pf->getProductKeys()->slice(0, $instance->getProperty('maxProducts'));
    }

    /**
     * @param PortletInstance $instance
     * @return array
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getFilteredProducts(PortletInstance $instance): array
    {
        $products       = [];
        $defaultOptions = Artikel::getDefaultOptions();
        $db             = Shop::Container()->getDB();
        foreach ($this->getFilteredProductIds($instance) as $productID) {
            $products[] = (new Artikel($db))->fuelleArtikel($productID, $defaultOptions);
        }

        return Product::separateByAvailability($products);
    }
}
