<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Banner;

use JTL\Catalog\Product\Artikel;
use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Banner
 * @package JTL\OPC\Portlets
 */
class Banner extends Portlet
{
    /**
     * @param int $productID
     * @return Artikel
     */
    public function getProduct(int $productID)
    {
        $defaultOptions = Artikel::getDefaultOptions();
        $product        = new Artikel();
        $product->fuelleArtikel($productID, $defaultOptions);

        return $product;
    }

    /**
     * @return string
     */
    public function getPlaceholderImgUrl(): string
    {
        return $this->getBaseUrl() . 'preview.banner.jpg';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'src' => [
                'type'  => InputType::IMAGE,
                'label' => __('Image'),
                'thumb' => true,
            ],
            'zones' => [
                'type'    => InputType::ZONES,
                'label'   => __('bannerAreas'),
                'srcProp' => 'src',
                'default' => [],
            ],
            'alt'   => [
                'label' => __('Alternative text'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
