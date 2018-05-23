<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use function Couchbase\defaultDecoder;
use OPC\PortletInstance;

class Panel extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->addClass('panel')->addClass('panel-' . $instance->getProperty('panel-state'))->addClass($instance->getProperty('panel-class'));

        $ret  = '<div ' . $instance->getAttributeString() . ' ' . $instance->getDataAttributeString() . '>';
        $ret .= !empty($instance->getProperty('title-flag')) ? '<div class="panel-heading opc-area" data-area-id="pnl_title">' . $instance->getSubareaPreviewHtml('pnl_title') . '</div>' : '';
        $ret .= '<div class="panel-body opc-area" data-area-id="pnl_body">' . $instance->getSubareaPreviewHtml('pnl_body') . '</div>';
        $ret .= !empty($instance->getProperty('footer-flag')) ? '<div class="panel-footer opc-area" data-area-id="pnl_footer">' . $instance->getSubareaPreviewHtml('pnl_footer') . '</div>' : '';
        $ret .= '</div>';

        return $ret;
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('panel')->addClass('panel-' . $instance->getProperty('panel-state'))->addClass($instance->getProperty('panel-class'));

        $ret  = '<div ' . $instance->getAttributeString() . '>';
        $ret .= !empty($instance->getProperty('title-flag')) ? '<div class="panel-heading">' . $instance->getSubareaFinalHtml('pnl_title') . '</div>' : '';
        $ret .= '<div class="panel-body">' . $instance->getSubareaFinalHtml('pnl_body') . '</div>';
        $ret .= !empty($instance->getProperty('footer-flag')) ? '<div class="panel-footer">' . $instance->getSubareaFinalHtml('pnl_footer') . '</div>' : '';
        $ret .= '</div>';

        return $ret;
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-square-o"></i><br/> Panel';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
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
                    'warning' => 'Warunug',
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

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}