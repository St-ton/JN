<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\Catalog\Product\Artikel;
use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;

/**
 * Class Banner
 * @package JTL\OPC\Portlets
 */
class Banner extends Portlet
{
    /**
     * @param int $kArtikel
     * @return Artikel
     */
    public function getProduct(int $kArtikel)
    {
        $defaultOptions = Artikel::getDefaultOptions();
        $oArtikel       = new Artikel();
        $oArtikel->fuelleArtikel($kArtikel, $defaultOptions);

        return $oArtikel;
    }

    /**
     * @return string
     */
    public function getPlaceholderImgUrl(): string
    {
        return $this->getTemplateUrl() . 'preview.banner.png';
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
            ],
            'zones' => [
                'type'    => InputType::ZONES,
                'label'   => 'Banner-Zonen',
                'srcProp' => 'src',
                'default' => [],
            ],
            'class' => [
                'label' => __('CSS class'),
            ],
            'alt'   => [
                'label' => __('Alternativ text'),
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
