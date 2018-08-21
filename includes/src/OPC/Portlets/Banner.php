<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Banner
 * @package OPC\Portlets
 */
class Banner extends \OPC\Portlet
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

        $cBildPfad            = PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . basename($instance->getProperty('src'));
        $oImageMap->cBildPfad = \Shop::getURL() . $oImageMap->cBildPfad;
        $cParse_arr           = \parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = \substr($cParse_arr['path'], \strrpos($cParse_arr['path'], '/') + 1);
        list($width, $height) = \getimagesize($cBildPfad);
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
    public function getPlaceholderImgUrl()
    {
        return \Shop::getURL() . '/' . PFAD_TEMPLATES . 'Evo/portlets/Banner/preview.banner.png';
    }

    /**
     * @param PortletInstance $instance
     * @return string
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
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Banner';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'src'   => [
                'label'      => 'Bild',
                'type'       => 'image',
                'default'    => '',
                'dspl_width' => 50,
                'required'   => true,
            ],
            'zones' => [
                'label'   => 'Zonen',
                'type'    => 'banner-zones',
                'default' => '[]',
            ],
            'class' => [
                'label' => 'CSS Class',
            ],
            'alt'   => [
                'label' => 'Altenativtext',
            ],
            'title' => [
                'label' => 'Titel'
            ],
        ];

    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
