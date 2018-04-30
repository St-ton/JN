<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Image extends \OPC\Portlet
{
    public function getHtml(PortletInstance $instance, $preview = false)
    {
        $instance->setImageAttributes($instance->getProperty('src'), $instance->getProperty('alt'));
        $instance->addClass('img-responsive');

        return '<img '
            . $instance->getAttributeString()
            . ($preview ? ' ' . $instance->getDataAttributeString() : '')
            . '>';
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
        return '<i class="fa fa-image"></i><br/> Bild';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'src' => [
                'label'   => 'Bild',
                'type'    => 'image',
                'default' => '',
            ],
            'alt'  => [
                'label'   => 'Alternativ-Text',
                'type'    => 'text',
                'default' => 'Ein Bild',
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