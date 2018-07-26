<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Text
 * @package OPC\Portlets
 */
class Text extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @param bool            $preview
     * @return string
     */
    public function getHtml(PortletInstance $instance, $preview = false): string
    {
        return '<div '
            . $instance->getAttributeString()
            . ($preview ? ' ' . $instance->getDataAttributeString() : '') . '>'
            . $instance->getProperty('text')
            . '</div>';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance, true);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-font"></i><br>Text';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'text' => [
                'label'   => 'Text',
                'type'    => 'richtext',
                'default' => '<p>Rich Text Content</p>',
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
