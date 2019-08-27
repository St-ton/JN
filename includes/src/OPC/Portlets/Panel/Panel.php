<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Panel;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Panel
 * @package JTL\OPC\Portlets
 */
class Panel extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->addClass('panel')
                 ->addClass('panel-' . $instance->getProperty('panel-state'))
                 ->addClass($instance->getProperty('panel-class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->addClass('panel')
                 ->addClass('panel-' . $instance->getProperty('panel-state'))
                 ->addClass($instance->getProperty('panel-class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('far fa-square');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'panel-state' => [
                'label' => __('type'),
                'type'  => InputType::SELECT,
                'width' => 50,
                'options'    => [
                    'default' => __('standard'),
                    'primary' => __('stylePrimary'),
                    'success' => __('styleSuccess'),
                    'info'    => __('styleInfo'),
                    'warning' => __('styleWarning'),
                    'danger'  => __('styleDanger'),
                ],
            ],
            'title-flag'  => [
                'label' => __('showHeader'),
                'type'  => InputType::CHECKBOX,
                'width' => 50,
            ],
            'footer-flag' => [
                'label' => __('showFooter'),
                'type'  => InputType::CHECKBOX,
                'width' => 50,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
