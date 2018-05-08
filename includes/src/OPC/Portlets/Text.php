<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Text extends \OPC\Portlet
{
    public function getHtml(PortletInstance $instance, $preview = false)
    {
        return '<div '
            . $instance->getAttributeString()
            . ($preview ? ' ' . $instance->getDataAttributeString() : '') . '>'
            . $instance->getProperty('text')
            . '</div>';
    }

    public function getPreviewHtml($instance)
    {
        return $this->getHtml($instance, true);
    }

    public function getFinalHtml($instance)
    {
        return $this->getHtml($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-font"></i><br>Text';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'text'  => [
                'label'   => 'Text',
                'type'    => 'richtext',
                'default' => '<p>Rich Text Content</p>',
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
        ];
    }
}