<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class ListPortlet
 * @package JTL\OPC\Portlets
 */
class ListPortlet extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setStyle('list-style-type', $instance->getProperty('type'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->setStyle('list-style-type', $instance->getProperty('type'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('list-ol');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'count' => [
                'label'   => __('Item Count'),
                'type'    => 'number',
                'default' => 3,
                'dspl_width' => 50,
            ],
            'type'  => [
                'label'      => __('List Style'),
                'type'       => 'select',
                'default'    => 'disc',
                'dspl_width' => 50,
                'options'    => [
                    'disc'        => __('Disc'),
                    'circle'      => __('Circle'),
                    'square'      => __('Square'),
                    'decimal'     => __('Decimal'),
                    'lower-latin' => __('Lower Latin'),
                    'upper-latin' => __('Upper Latin'),
                    'lower-roman' => __('Lower Roman'),
                    'upper-roman' => __('Upper Roman'),
                ],
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

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getListTag(PortletInstance $instance): string
    {
        $type = $instance->getProperty('type');

        if ($type === 'disc' || $type === 'circle' || $type === 'square') {
            return 'ul';
        }

        return 'ol';
    }
}
