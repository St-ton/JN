<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

class Heading extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->setStyle('background', $instance->getProperty('bgcolor'));
        $level         = $instance->getProperty('level');
        $text          = $instance->getProperty('text');
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();

        return "<h$level $attributes $dataAttribute>$text</h$level>";
    }

    public function getFinalHtml($instance)
    {
        $level = $instance->getProperty('level');
        $text  = $instance->getProperty('text');

        return "<h$level>$text</h$level>";
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-header"></i><br>Überschrift';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'level' => [
                'label'   => 'Level',
                'type'    => 'select',
                'options' => ['1', '2', '3', '4', '5', '6'],
                'default' => '1',
            ],
            'text'  => [
                'label'   => 'Text',
                'type'    => 'text',
                'default' => 'Heading',
            ],
            'bgcolor' => [
                'type' => 'color',
            ]
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
            'Styles2' => ['bgcolor']
        ];
    }
}