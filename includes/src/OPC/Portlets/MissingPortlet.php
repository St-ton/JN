<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;

/**
 * Class MissingPortlet
 * @package OPC\Portlets
 */
class MissingPortlet extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return '<div ' . $instance->getDataAttributeString() . '>' . $this->getTitle() . '</div>';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return '';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(PortletInstance $instance): string
    {
        return '<p>The <b>"' . $this->class . '"</b> Portlet is either not installed or currently just set inactive.</p>'
            . '<p>Please install the missing Plugin that provides this Portlet!';
    }
}
