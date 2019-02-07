<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;

/**
 * Class Divider
 * @package OPC\Portlets
 */
class Divider extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return '<hr ' . $instance->getAttributeString(). ' ' . $instance->getDataAttributeString() . '>';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return '<hr ' . $instance->getAttributeString(). '>';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'id' => [
                'label' => 'ID',
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
