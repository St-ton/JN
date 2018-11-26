<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

/**
 * Class Sample
 * @package OPC\Portlets
 */
class Alert extends \OPC\Portlet
{
    public function getPreviewHtml(\OPC\PortletInstance $instance): string
    {
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml(\OPC\PortletInstance $instance): string
    {
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml(): string
    {
        return '<i class="fa fa-exclamation-circle"></i><br>Alert';
    }

    public function getPropertyDesc(): array
    {
        return [
            'some-text'   => [
                'label'   => __('a text'),
                'type'    => 'text',
                'default' => __('Hello world!'),
            ],
            'type-select' => [
                'label'   => __('Alert Type'),
                'type'    => 'select',
                'options'    => [
                    'success' => __('Success'),
                    'info'    => __('Info'),
                    'warning' => __('Warning'),
                    'danger'  => __('Danger'),
                ],
                'default' => 'info',
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