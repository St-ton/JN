<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Accordion extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->setProperty('uid', uniqid('cllps_', false));
        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Akkordeon';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'cllps-initial-state' => [
                'label'      => 'initial als ausgeklappt anzeigen',
                'type'       => 'checkbox',
                'help'       => 'In der Vorschau und beim Bearbeiten ist wird der Bereich immer angezeigt.',
                'dspl_width' => 50,
            ],
            'layout'              => [
                'label'      => 'Anzeigen als',
                'type'       => 'radio',
                'options'    => [
                    'button' => 'Button',
                    'panel'  => 'Panel',
                ],
                'inline'     => true,
                'default'    => 'button',
                'dspl_width' => 100,

            ],
            'cllps-button-text'   => [
                'label'                => 'Buttontext',
                'type'                 => 'Text',
                'default'              => 'Button',
                'dspl_width'           => 50,
                'collapseControlStart' => true,
                'showOnProp'           => 'layout',
                'showOnPropValue'      => 'button',
            ],
            'cllps-button-type'   => [
                'label'      => 'Typ',
                'type'       => 'select',
                'options'    => [
                    'default' => 'Standard',
                    'primary' => 'Primär',
                    'success' => 'Erfolg',
                    'info'    => 'Info',
                    'warning' => 'Warnung',
                    'danger'  => 'Gefahr',
                ],
                'default'    => 'default',
                'dspl_width' => 50,
            ],
            'cllps-button-size'   => [
                'label'              => 'Größe',
                'type'               => 'select',
                'options'            => [
                    'xs' => 'XS',
                    'sm' => 'S',
                    'md' => 'M',
                    'lg' => 'L',
                ],
                'default'            => 'md',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
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