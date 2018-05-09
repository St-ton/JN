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
        $instance->setImageAttributes();
        $instance->addClass('img-responsive');
        if (!empty($instance->getProperty('shape'))) {
            $instance->addClass($instance->getProperty('shape'));
        }

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
            'shape' => [
                'label'=> 'shape',
                'type' => 'select',
                'options' => [
                    '',
                    'img-rounded',
                    'img-circle',
                    'img-thumbnail'
                ],
            ],
            'alt'  => [
                'label'   => 'Alternativ-Text',
            ],
            'title' => [
                'label' => 'title',
            ]
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
        ];
    }
}