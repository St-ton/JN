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
        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-Accordion.svg"></i>
            <br/> Akkordeon';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'uid'                 => [
                'type'    => 'hidden',
                'default' => uniqid('cllps_'),
            ],
            'cllps-initial-state' => [
                'label'      => 'initial als ausgeklappt anzeigen',
                'type'       => 'checkbox',
                'help'       => 'In der Vorschau und beim Bearbeiten ist wird der Bereich immer angezeigt.',
                'dspl_width' => 50,
            ],
            'layout'              => [
                'label'      => 'layout',
                'type'       => 'radio',
                'options'    => [
                    'button',
                    'panel'
                ],
                'default'    => 'button',
                'dspl_width' => 100,

            ],
            'cllps-button-text'   => [
                'label'                => 'Button text',
                'type'                 => 'Text',
                'default'              => 'Button',
                'dspl_width'           => 50,
                'collapseControlStart' => true,
                'showOnProp'           => 'layout',
                'showOnPropValue'      => 'button',
            ],
            'cllps-button-type'   => [
                'label'      => 'Type',
                'type'       => 'select',
                'options'    => [
                    'default',
                    'primary',
                    'success',
                    'info',
                    'warning',
                    'danger',
                ],
                'default'    => 'default',
                'dspl_width' => 50,
            ],
            'cllps-button-size'   => [
                'label'              => 'Size',
                'type'               => 'select',
                'options'            => [
                    'xs',
                    'sm',
                    'md',
                    'lg',
                ],
                'default'            => 'md',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ],
            'only-on-panel'       => [
                'label'                => 'only-on-panel',
                'dspl_width'           => 50,
                'collapseControlStart' => true,
                'showOnProp'           => 'layout',
                'showOnPropValue'      => 'panel',
                'collapseControlEnd'   => true,
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