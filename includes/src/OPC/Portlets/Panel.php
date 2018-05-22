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
        $ret .= !empty($instance->getProperty('title-flag')) ? '<div class="panel-heading opc-area" data-area-id="pnl_' . $instance->getProperty("uid") . '_title">' . $instance->getSubareaPreviewHtml('pnl_' . $instance->getProperty("uid") . '_title') . '</div>' : '';
        $ret .= '<div class="panel-body opc-area" data-area-id="pnl_' . $instance->getProperty("uid") . '_body">' . $instance->getSubareaPreviewHtml('pnl_' . $instance->getProperty("uid") . '_body') . '</div>';
        $ret .= !empty($instance->getProperty('footer-flag')) ? '<div class="panel-footer opc-area" data-area-id="pnl_' . $instance->getProperty("uid") . '_footer">' . $instance->getSubareaPreviewHtml('pnl_' . $instance->getProperty("uid") . '_footer') . '</div>' : '';
        $ret .= '</div>';

        return $ret;
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('panel')->addClass('panel-' . $instance->getProperty('panel-state'))->addClass($instance->getProperty('panel-class'));

        $ret  = '<div ' . $instance->getAttributeString() . '>';
        $ret .= !empty($instance->getProperty('title-flag')) ? '<div class="panel-heading">' . $instance->getSubareaFinalHtml('pnl_' . $instance->getProperty("uid") . '_title') . '</div>' : '';
        $ret .= '<div class="panel-body">' . $instance->getSubareaFinalHtml('pnl_' . $instance->getProperty("uid") . '_body') . '</div>';
        $ret .= !empty($instance->getProperty('footer-flag')) ? '<div class="panel-footer">' . $instance->getSubareaFinalHtml('pnl_' . $instance->getProperty("uid") . '_footer') . '</div>' : '';
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
            'panel-id'         => [
                'label'      => 'ID',
                'dspl_width' => 50,
                'default'    => uniqid(),
            ],
            'panel-class'      => [
                'label'      => 'Class',
                'dspl_width' => 50,
            ],
            'title-flag' => [
                'label' => 'titel anzeigen',
                'type'  => 'checkbox',
                'dspl_width' => 50,
            ],
            'footer-flag' => [
                'label' => 'footer anzeigen',
                'type'  => 'checkbox',
                'dspl_width' => 50,
            ],
            'panel-state' => [
                'label' => 'panel type',
                'type' => 'select',
                'dspl_width' => 50,
                'options' => [
                  'default',
                  'primary',
                  'success',
                  'info',
                  'warning',
                  'danger',
                ],
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