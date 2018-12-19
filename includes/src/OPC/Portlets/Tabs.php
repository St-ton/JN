<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;

/**
 * Class Tabs
 * @package OPC\Portlets
 */
class Tabs extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
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
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img alt="" class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Tabs';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'tabs' => [
                'label'   => 'Tabs',
                'type'    => 'textlist',
                'default' => ['Tab eins', 'Tab zwei', 'Tab drei'],
            ],
            'uniqid' => [
                'type'    => 'hidden',
                'default' => \uniqid('', false),
            ]
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
