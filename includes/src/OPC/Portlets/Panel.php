<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Panel
 * @package JTL\OPC\Portlets
 */
class Panel extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->addClass('panel')
                 ->addClass('panel-' . $instance->getProperty('panel-state'))
                 ->addClass($instance->getProperty('panel-class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->addClass('panel')
                 ->addClass('panel-' . $instance->getProperty('panel-state'))
                 ->addClass($instance->getProperty('panel-class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('far fa-square');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'panel-class' => [
                'label' => 'CSS Klasse',
                'width' => 50,
            ],
            'panel-state' => [
                'label' => 'Typ',
                'type'  => InputType::SELECT,
                'width' => 50,
                'options'    => [
                    'default' => 'Standard',
                    'primary' => 'Primär',
                    'success' => 'Erfolg',
                    'info'    => 'Info',
                    'warning' => 'Warnung',
                    'danger'  => 'Gefahr',
                ],
            ],
            'title-flag'  => [
                'label' => 'Kopf anzeigen?',
                'type'  => InputType::CHECKBOX,
                'width' => 50,
            ],
            'footer-flag' => [
                'label' => 'Fuß anzeigen?',
                'type'  => InputType::CHECKBOX,
                'width' => 50,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
