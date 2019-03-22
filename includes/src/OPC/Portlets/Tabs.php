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
 * Class Tabs
 * @package JTL\OPC\Portlets
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
                'type'    => InputType::TEXT_LIST,
                'default' => ['Tab eins', 'Tab zwei', 'Tab drei'],
            ],
            'uniqid' => [
                'type'    => InputType::HIDDEN,
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
