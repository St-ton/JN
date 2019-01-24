<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;

/**
 * Class Panel
 * @package OPC\Portlets
 */
class Panel extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
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
        return '<i class="fa fa-square-o"></i><br/> Panel';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'panel-class' => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
            'panel-state' => [
                'label'      => 'Typ',
                'type'       => 'select',
                'dspl_width' => 50,
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
                'label'      => 'Kopf anzeigen?',
                'type'       => 'checkbox',
                'dspl_width' => 50,
            ],
            'footer-flag' => [
                'label'      => 'Fuß anzeigen?',
                'type'       => 'checkbox',
                'dspl_width' => 50,
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
