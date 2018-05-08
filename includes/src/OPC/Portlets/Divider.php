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
        $res = '<hr ' . $instance->getAttributeString(). ' ' . $instance->getDataAttributeString() . '>';

        return $res;
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-HR.svg">
            <br/> Trennlinie';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'id'           => [
                'label'    => 'ID',
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