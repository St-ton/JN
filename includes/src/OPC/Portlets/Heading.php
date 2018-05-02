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
        $level         = $instance->getProperty('level');
        $text          = $instance->getProperty('text');
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();

        return "<h$level $attributes $dataAttribute >$text</h$level>";
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
                'dspl_width' => 50,
            ],
            'text'  => [
                'label'   => 'Text',
                'type'    => 'text',
                'default' => 'Heading',
                'dspl_width' => 50,
            ],
            'number'  => [
                'label'   => 'Numebr',
                'type'    => 'number',
                'default' => 5,
                'dspl_width' => 50,
            ],
            'mail'  => [
                'label'   => 'Email',
                'type'    => 'email',
                'default' => '',
                'dspl_width' => 50,
            ],
            'date'  => [
                'label'   => 'Datum',
                'type'    => 'date',
                'default' => '',
                'dspl_width' => 50,
            ],
            'pass'  => [
                'label'   => 'Passwort',
                'type'    => 'password',
                'default' => '',
                'dspl_width' => 50,
            ],
            'checkbox' => [
                'label' => 'Checkbox',
                'type'  => 'checkbox',
                'dspl_width' => 50,
                'default' => 'this is optional',
            ],
            'radio' => [
                'label' => 'radio',
                'type'  => 'radio',
                'options' => ['female', 'male', 'other'],
                'dspl_width' => 50,
                'default' => 'female',
            ],
             'myColor' => [
                'label' => 'Farbe',
                'type'  => 'color',
                'dspl_width' => 50,
                'default' => '#ff0000',
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