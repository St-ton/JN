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

        return
            '<div class="text-center" ' . $instance->getAttributeString() . '>' .
            '<img src="' . $instance->getProperty('src') . '" class="' . $instance->getAttribute('class') .
            '"></div>';
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() .'/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-Banner.svg"></i>
            <br/> Banner';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'src' => [
                'label'      => 'Bild',
                'default'    => \Shop::getURL() . '/' . PFAD_TEMPLATES . 'Evo/portlets/Banner/preview.banner.png',
                'type'=> 'image',
                'dspl_width' => 50,
            ],
            'kImageMap'  => [
                'type' => 'hidden',
                'default' =>uniqid(),
            ],
            'zones' => [
                'type' => 'banner-zones',
            ],
            'class'      => [
                'label'=> 'CSS Class',
            ],
            'alt'        => [
                'label'=> 'alt text',
            ],
            'title'      => [
                'label'=> 'title'
            ],
        ];

    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
            'Animation' => 'animations',
        ];
    }
}