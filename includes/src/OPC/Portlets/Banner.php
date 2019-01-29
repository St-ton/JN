<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;

/**
 * Class Banner
 * @package OPC\Portlets
 */
class Banner extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return object
     */
    public function getImageMap(PortletInstance $instance)
    {
        $imageMap = (object)[
            'cTitel'    => $instance->getProperty('kImageMap'),
            'cBildPfad' => $instance->getProperty('src'),
            'oArea_arr' => !empty($instance->getProperty('zones'))
                ? \json_decode($instance->getProperty('zones'))
                : [],
        ];

        if (empty($imageMap->cBildPfad)) {
            return $imageMap;
        }

        $imgPath             = \PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . \basename($instance->getProperty('src'));
        $parsed              = \parse_url($imageMap->cBildPfad);
        $imageMap->cBildPfad = \Shop::getURL() . $imageMap->cBildPfad;
        $imageMap->cBild     = \mb_substr($parsed['path'], \mb_strrpos($parsed['path'], '/') + 1);
        [$width, $height]    = \getimagesize($imgPath);
        $imageMap->fWidth    = $width;
        $imageMap->fHeight   = $height;
        $defaultOptions      = \Artikel::getDefaultOptions();

        if (!empty($imageMap->oArea_arr)) {
            foreach ($imageMap->oArea_arr as &$area) {
                $area->oArtikel = null;

                if ((int)$area->kArtikel > 0) {
                    $area->oArtikel = new \Artikel();
                    $area->oArtikel->fuelleArtikel($area->kArtikel, $defaultOptions);

                    if ($area->cTitel === '') {
                        $area->cTitel = $area->oArtikel->cName;
                    }
                    if ($area->cUrl === '') {
                        $area->cUrl = $area->oArtikel->cURL;
                    }
                    if ($area->cBeschreibung === '') {
                        $area->cBeschreibung = $area->oArtikel->cKurzBeschreibung;
                    }
                }
            }
        }

        return $imageMap;
    }

    /**
     * @return string
     */
    public function getPlaceholderImgUrl(): string
    {
        return \Shop::getURL() . '/' . \PFAD_TEMPLATES . 'Evo/portlets/Banner/preview.banner.png';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('kImageMap', \uniqid('', false));
        $instance->addClass('img-responsive');

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \SmartyException|\Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->addClass('img-responsive');
        $instance->addClass('banner');

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'src'   => [
                'label'      => __('Image'),
                'type'       => 'image',
                'default'    => '',
                'dspl_width' => 50,
                'required'   => true,
            ],
            'zones' => [
                'label'   => __('Zones'),
                'type'    => 'banner-zones',
                'default' => '[]',
            ],
            'class' => [
                'label' => __('CSS class'),
            ],
            'alt'   => [
                'label' => __('Altenativ text'),
            ],
            'title' => [
                'label' => __('Title')
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
