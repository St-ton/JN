<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Tabs;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Tabs
 * @package JTL\OPC\Portlets
 */
class Tabs extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'tabs' => [
                'label'   => __('Tabs'),
                'type'    => InputType::TEXT_LIST,
                'default' => [__('tabOne'), __('tabTwo'), __('tabThree')],
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
