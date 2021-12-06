<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\ProductStream;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Filter\Config;
use JTL\Filter\FilterInterface;
use JTL\Filter\Items\Category;
use JTL\Filter\Items\Characteristic;
use JTL\Filter\Items\Manufacturer;
use JTL\Filter\Items\PriceRange;
use JTL\Filter\Items\Rating;
use JTL\Filter\Items\Search;
use JTL\Filter\Items\SearchSpecial;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\Helpers\Product;
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
                'width'   => 66,
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
                'width'    => 33,
                'default'  => 15,
                'required' => true,
            ],
            'search' => [
                'type'        => InputType::SEARCH,
                'label'       => \__('search'),
                'placeholder' => \__('search'),
                'width'       => 50,
            ],
            'filters'      => [
                'type'     => InputType::FILTER,
                'label'    => \__('itemFilter'),
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
            \__('Styles') => 'styles',
        ];
    }

    /**
     * @param string        $filterClass
     * @param array         $params
     * @param mixed         $value
     * @param ProductFilter $pf
     * @return void
     */
    private function getFilterClassParamMapping(string $filterClass, array &$params, $value, ProductFilter $pf): void
    {
        switch ($filterClass) {
            case Category::class:
                $params['kKategorie'] = $value;
                break;
            case Characteristic::class:
                $params['MerkmalFilter_arr'][] = $value;
                break;
            case PriceRange::class:
                $params['cPreisspannenFilter'] = $value;
                break;
            case Manufacturer::class:
                $params['kHersteller'] = $value;
                break;
            case Rating::class:
                $params['nBewertungSterneFilter'] = $value;
                break;
            case SearchSpecial::class:
                $params['kSuchspecialFilter'] = $value;
                break;
            case Search::class:
                $params['kSuchFilter']      = $value;
                $params['SuchFilter'][]     = $value;
                $params['SuchFilter_arr'][] = $value;
                break;
            default:
                /** @var FilterInterface $instance */
                $instance                       = new $filterClass($pf);
                $_GET[$instance->getUrlParam()] = $value;
                break;
        }
    }

    /**
     * @param PortletInstance $instance
     * @return Collection
     */
    public function getFilteredProductIds(PortletInstance $instance): Collection
    {
        $params         = ['MerkmalFilter_arr' => [], 'SuchFilter_arr' => [], 'SuchFilter' => []];
        $enabledFilters = $instance->getProperty('filters');
        $pf             = new ProductFilter(
            Config::getDefault(),
            Shop::Container()->getDB(),
            Shop::Container()->getCache()
        );

        foreach ($enabledFilters as $enabledFilter) {
            $this->getFilterClassParamMapping($enabledFilter['class'], $params, $enabledFilter['value'], $pf);
        }
        $pf->initStates($params);
        foreach ($pf->getActiveFilters() as $filter) {
            $filter->setType(Type::AND);
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
        foreach ($this->getFilteredProductIds($instance) as $productID) {
            $products[] = (new Artikel())->fuelleArtikel($productID, $defaultOptions);
        }

        return Product::separateByAvailability($products);
    }
}
