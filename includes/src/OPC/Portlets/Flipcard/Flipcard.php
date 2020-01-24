<?php declare(strict_types=1);
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
                'width'   => 50,
                'options' => [
                    'v' => __('vertical'),
                    'h' => __('horizontal'),
                ],
                'default' => 'v',
            ],
            'flip-trigger' => [
                'type'    => InputType::RADIO,
                'label'   => __('flipTrigger'),
                'width'   => 50,
                'options' => [
                    'click' => __('onClick'),
                    'hover' => __('onHover'),
                ],
                'default' => 'click',
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
