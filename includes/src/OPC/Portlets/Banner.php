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
        $oImageMap = (object)[
            'cTitel'    => $instance->getProperty('kImageMap'),
            'cBildPfad' => $instance->getProperty('src'),
            'oArea_arr' => !empty($instance->getProperty('zones'))
                ? \json_decode($instance->getProperty('zones'))
                : [],
        ];

        if (empty($oImageMap->cBildPfad)) {
            return $oImageMap;
        }

        $cBildPfad            = \PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . \basename($instance->getProperty('src'));
        $oImageMap->cBildPfad = \Shop::getURL() . $oImageMap->cBildPfad;
        $cParse_arr           = \parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = \substr($cParse_arr['path'], \strrpos($cParse_arr['path'], '/') + 1);
        [$width, $height]     = \getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = \Artikel::getDefaultOptions();

        if (!empty($oImageMap->oArea_arr)) {
            foreach ($oImageMap->oArea_arr as &$oArea) {
                $oArea->oArtikel = null;

                if ((int)$oArea->kArtikel > 0) {
                    $oArea->oArtikel = new \Artikel();
                    $oArea->oArtikel->fuelleArtikel($oArea->kArtikel, $defaultOptions);

                    if ($oArea->cTitel === '') {
                        $oArea->cTitel = $oArea->oArtikel->cName;
                    }
                    if ($oArea->cUrl === '') {
                        $oArea->cUrl = $oArea->oArtikel->cURL;
                    }
                    if ($oArea->cBeschreibung === '') {
                        $oArea->cBeschreibung = $oArea->oArtikel->cKurzBeschreibung;
                    }
                }
            }
        }

        return $oImageMap;
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
