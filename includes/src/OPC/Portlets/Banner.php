<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Banner extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->setProperty('kImageMap', uniqid('', false));
        $instance->addClass('img-responsive');

        return
            '<div class="text-center" ' . $instance->getAttributeString() . $instance->getDataAttributeString() . '>' .
            '<img src="' . $instance->getProperty('src') . '" class="' . $instance->getAttribute('class') .
            '" style="width: 98%;filter: grayscale(50%) opacity(60%)">' .
            '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>' .
            '</div>';
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('img-responsive');
        $instance->setImageAttributes();
        $oImageMap = (object)[
            'cTitel' => $instance->getProperty('kImageMap'),
            'cBildPfad' => $instance->getProperty('src'),
            'oArea_arr' => !empty($instance->getProperty('zones')) ? json_decode($instance->getProperty('zones')) : null,
        ];

        $isFluid              = false;
        $cBildPfad            = PFAD_ROOT . $instance->getProperty('src');
        $oImageMap->cBildPfad = \Shop::getURL() . $oImageMap->cBildPfad;
        $cParse_arr           = parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = substr($cParse_arr['path'], strrpos($cParse_arr['path'], '/') + 1);
        list($width, $height) = getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = \Artikel::getDefaultOptions();
        $fill                 = true;

        if (!empty($oImageMap->oArea_arr)) {
            foreach ($oImageMap->oArea_arr as &$oArea) {
                $oArea->oArtikel = null;

                if ((int)$oArea->kArtikel > 0) {
                    $oArea->oArtikel = new \Artikel();

                    if ($fill === true) {
                        $oArea->oArtikel->fuelleArtikel($oArea->kArtikel, $defaultOptions);
                    } else {
                        $oArea->oArtikel->kArtikel = $oArea->kArtikel;
                        $oArea->oArtikel->cName    = utf8_encode(
                            \Shop::Container()->getDB()->select(
                                'tartikel', 'kArtikel', $oArea->kArtikel, null, null, null, null, false, 'cName'
                            )->cName
                        );
                    }

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

        return \Shop::Smarty()->assign('oBanner', $oImageMap)
                    ->assign('attribString', $instance->getAttributeString())
                    ->assign('srcString', $instance->getProperty('src'))
                    ->assign('isFluid', $isFluid)
                    ->fetch('portlets/Banner/final.banner.tpl');
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Banner';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'src'       => [
                'label'      => 'Bild',
                'default'    => \Shop::getURL() . '/' . PFAD_TEMPLATES . 'Evo/portlets/Banner/preview.banner.png',
                'type'       => 'image',
                'dspl_width' => 50,
            ],
            'zones'     => [
                'type'    => 'banner-zones',
                'default' => [],
            ],
            'class'     => [
                'label' => 'CSS Class',
            ],
            'alt'       => [
                'label' => 'alt text',
            ],
            'title'     => [
                'label' => 'title'
            ],
        ];

    }

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}