<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use function Couchbase\defaultDecoder;
use OPC\PortletInstance;

/**
 * Class PList
 * @package OPC\Portlets
 */
class PList extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $res = '<div ' . $instance->getAttributeString() . ' ' . $instance->getDataAttributeString() . '>
                    <' . $instance->getProperty('listType');
        if (!empty($instance->getProperty('list-style-type'))) {
            $res .= ' style="list-style-type:' . $instance->getProperty('list-style-type') . '"';
        }
        $res .= '>';

        for ($x = 0; $x < (int)$instance->getProperty('count'); ++$x) {
            $res .= '<li><div class="opc-area" data-area-id="sub_' . $x . '">'
                . $instance->getSubareaPreviewHtml('sub_' . $x) . '</div></li>';
        }

        $res .= '</' . $instance->getProperty('listType') . '></div>';

        return $res;
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $res = '<div ' . $instance->getAttributeString() . '>
                    <' . $instance->getProperty('listType');
        if (!empty($instance->getProperty('list-style-type'))) {
            $res .= ' style="list-style-type:' . $instance->getProperty('list-style-type') . '"';
        }
        $res .= '>';

        for ($x = 0; $x < (int)$instance->getProperty('count'); ++$x) {
            $res .= '<li><div>'
                . $instance->getSubareaFinalHtml('sub_' . $x) . '</div></li>';
        }

        $res .= '</' . $instance->getProperty('listType') . '></div>';

        return $res;
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-list-ol"></i><br/> Liste';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            // general
            'list-class'      => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
            'listType'        => [
                'label'      => 'Layout',
                'type'       => 'radio',
                'dspl_width' => 50,
                'options'    => [
                    'ol' => 'geordnete Liste',
                    'ul' => 'einfache Aufzählung',
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
                'label'      => 'Typ',
                'type'       => 'select',
                'dspl_width' => 50,
                'options'    => [
                    ''            => 'default',
                    'lower-latin' => 'latein. Kleinbuchstaben',
                    'lower-roman' => 'kleine röm. Zahlen',
                    'upper-latin' => 'latein. Großbuchstaben',
                    'upper-roman' => 'große röm. Zahlen',
                ],
                'required'   => true,
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
