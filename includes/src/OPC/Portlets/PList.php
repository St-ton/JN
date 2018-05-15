<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use function Couchbase\defaultDecoder;
use OPC\PortletInstance;

class PList extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $res = '<div ' . $instance->getAttributeString() . ' ' . $instance->getDataAttributeString() . '>
                    <'. $instance->getProperty('listType');
        if (!empty($instance->getProperty('list-style-type'))) {
            $res .= ' style="list-style-type:'. $instance->getProperty('list-style-type') .'"';
        }
        $res .= '>';

        for ($x = 0; $x < (int)$instance->getProperty('count'); ++$x) {
            $res .= '<li><div class="opc-area" data-area-id="' . $instance->getProperty("list-id"). '_'. $x . '">'
                . $instance->getSubareaPreviewHtml($instance->getProperty("list-id"). '_'. $x) . '</div></li>';
        }

        $res .= '</'. $instance->getProperty('listType') .'></div>';

        return $res;
    }

    public function getFinalHtml($instance)
    {
        $res = '<div ' . $instance->getAttributeString() . '>
                    <'. $instance->getProperty('listType');
        if (!empty($instance->getProperty('list-style-type'))) {
            $res .= ' style="list-style-type:'. $instance->getProperty('list-style-type') .'"';
        }
        $res .= '>';

        for ($x = 0; $x < (int)$instance->getProperty('count'); ++$x) {
            $res .= '<li><div class="opc-area">'
                . $instance->getSubareaFinalHtml($instance->getProperty("list-id") . '_'. $x) . '</div></li>';
        }

        $res .= '</'. $instance->getProperty('listType') .'></div>';

        return $res;
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-list-ol"></i><br/> Liste';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            // general
            'list-id'         => [
                'label'      => 'ID',
                'dspl_width' => 50,
                'default'    => uniqid('', false),
            ],
            'list-class'      => [
                'label'      => 'Class',
                'dspl_width' => 50,
            ],
            'listType'        => [
                'label'      => 'Layout',
                'type'       => 'radio',
                'dspl_width' => 50,
                'options'    => [
                    'ordered list'   => 'ol',
                    'unordered list' => 'ul',
                ],
                'default'    => 'ol',
            ],
            'count'           => [
                'label'      => 'Anzahl Elemente',
                'type'       => 'number',
                'default'    => 3,
                'dspl_width' => 50,
            ],
            'list-style-type' => [
                'label'      => 'Type',
                'type'       => 'select',
                'dspl_width' => 50,
                'options'    => [
                    ''            => 'default',
                    'lower-latin' => 'lower-latin',
                    'lower-roman' => 'lower-roman',
                    'upper-latin' => 'upper-latin',
                    'upper-roman' => 'upper-roman',
                ]
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
        ];
    }
}