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
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'title' => [
                'label' => __('dividerTitle'),
                'default' => __('Divider'),
            ],
            'moreLink' => [
                'label' => __('dividerMoreLink'),
            ],
            'moreTitle' => [
                'label' => __('dividerMoreTitle'),
            ],
            'id' => [
                'label' => __('dividerElmID'),
                'desc'  => __('dividerIdDesc'),
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
