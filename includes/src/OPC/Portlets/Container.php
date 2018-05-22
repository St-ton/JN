<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Container extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        if (!empty($instance->getProperty('parallax-flag')) && !empty($instance->getProperty('src'))) {
            $name      = explode('/', $instance->getProperty('src'));
            $name      = end($name);
            $instance->setStyle('background', 'url("../' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name .'")');
            $instance->setStyle('background-size', 'cover');
            $instance->setStyle('min-height', $instance->getProperty('min-height'));
        }
        if (!empty($instance->getProperty("class"))) {
            $instance->addClass($instance->getProperty("class"));
        }

        $res = "<div ".$instance->getAttributeString().$instance->getDataAttributeString().">";

        $res .= "<div class='opc-area' data-area-id='" . $instance->getProperty("uid") . "'>"
                . $instance->getSubareaPreviewHtml($instance->getProperty("uid")) . "</div></div>";

        return $res;
    }

    public function getFinalHtml($instance)
    {
        if (!empty($instance->getProperty('parallax-flag')) && !empty($instance->getProperty('src'))) {
            $name      = explode('/', $instance->getProperty('src'));
            $name      = end($name);
            $instance->addClass('parallax-window');
            $instance->setStyle('min-height', $instance->getProperty('min-height'));
            $instance->setAttribute('data-parallax', 'scroll');
            $instance->setAttribute('data-z-index', '1');
            $instance->setAttribute('data-image-src', \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.lg/' . $name);
        }
        if (!empty($instance->getProperty("class"))) {
            $instance->addClass($instance->getProperty("class"));
        }

        $res = "<div ".$instance->getAttributeString().">";

        $res.= $instance->getSubareaFinalHtml($instance->getProperty("uid"));
        $res .= "</div>";

        return $res;
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-object-group"></i><br/> Container';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'uid'           => [
                'label'    => 'ID',
                'default' => uniqid('cntr-', false),
                'dspl_width' => 50,
            ],
            'class' => [
                'label' => 'CSS class',
                'dspl_width' => 50,
            ],
            'parallax-flag' => [
                'label'   => 'use parrallax effect',
                'type'    => 'radio',
                'options' => [
                    'mitlaufendes Bild'   => 'true',
                    'einfacher Container' => 'false'
                ],
                'inline' => true,
            ],
            'src'           => [
                'type'                 => 'image',
                'collapseControlStart' => true,
                'showOnProp'           => 'parallax-flag',
                'showOnPropValue'      => 'true',
                'dspl_width'           => 50,
            ],
            'min-height'    => [
                'label'              => 'min-height in px',
                'type'               => 'number',
                'default'            => 300,
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ]
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