<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Divider extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $res = '<hr ' . $instance->getAttributeString(). ' ' . $instance->getDataAttributeString() . '>';

        return $res;
    }

    public function getFinalHtml($instance)
    {
        $res = '<hr ' . $instance->getAttributeString(). '>';

        return $res;
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Trennlinie';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'id' => [
                'label' => 'ID',
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