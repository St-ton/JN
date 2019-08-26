<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Flipcard;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Flipcard
 * @package JTL\OPC\Portlets
 */
class Flipcard extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'flip-dir' => [
                'type'    => InputType::RADIO,
                'label'   => __('flipcardOrientation'),
                'options' => [
                    'flipcard-v' => __('vertical'),
                    'flipcard-h' => __('horizontal'),
                ],
                'default' => 'flipcard-v',
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
