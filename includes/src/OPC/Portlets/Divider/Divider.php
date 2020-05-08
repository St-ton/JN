<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\Divider;

use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Divider
 * @package JTL\OPC\Portlets
 */
class Divider extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return '<hr ' . $instance->getAttributeString(). '>';
    }

    /**
     * @param PortletInstance $instance
     * @param bool $inContainer
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance, bool $inContainer = true): string
    {
        return '<hr class="' . $instance->getStyleClasses() . '" ' . $instance->getAttributeString(). '>';
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
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
