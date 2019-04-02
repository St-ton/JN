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
 * Class Accordion
 * @package JTL\OPC\Portlets
 */
class Accordion extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', \uniqid('cllps_', false));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'cllps-initial-state' => [
                'label'      => __('initially expanded'),
                'type'       => InputType::CHECKBOX,
                'help'       => __('In the preview and while editing the area is always visible.'),
                'dspl_width' => 50,
            ],
            'layout'              => [
                'label'      => __('Display as'),
                'type'       => InputType::RADIO,
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
                'type'                 => InputType::TEXT,
                'default'              => 'hier ein text',
                'dspl_width'           => 50,
                'collapseControlStart' => true,
                'showOnProp'           => 'layout',
                'showOnPropValue'      => 'button',
                'required'             => true,
            ],
            'cllps-button-type'   => [
                'label'      => __('Type'),
                'type'       => InputType::SELECT,
                'options'    => [
                    'default' => __('default'),
                    'primary' => __('primary'),
                    'success' => __('success'),
                    'info'    => __('info'),
                    'warning' => __('warning'),
                    'danger'  => __('danger'),
                ],
                'default'    => 'default',
                'dspl_width' => 50,
            ],
            'cllps-button-size'   => [
                'label'              => __('Size'),
                'type'               => InputType::SELECT,
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
            __('Styles') => 'styles',
        ];
    }
}
