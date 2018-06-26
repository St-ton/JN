<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Accordion
 * @package OPC\Portlets
 */
class Accordion extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml($instance): string
    {
        $instance->setProperty('uid', uniqid('cllps_', false));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml($instance): string
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Akkordeon';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
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
                'type'                 => 'text',
                'default'              => 'hier ein text',
                'dspl_width'           => 50,
                'collapseControlStart' => true,
                'showOnProp'           => 'layout',
                'showOnPropValue'      => 'button',
                'required'             => true,
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

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles' => 'styles',
        ];
    }
}
