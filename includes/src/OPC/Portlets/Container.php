<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Container
 * @package JTL\OPC\Portlets
 */
class Container extends Portlet
{
    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('far fa-object-group');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'min-height'      => [
                'type'    => InputType::NUMBER,
                'label'   => __('minHeightPX'),
                'default' => 300,
                'width'   => 50,
            ],
            'boxed' => [
                'type'  => InputType::CHECKBOX,
                'default' => false,
                'label' => __('boxedContainer'),
                'width' => 50,
            ],
            'background-flag' => [
                'type'    => InputType::RADIO,
                'label'   => __('background'),
                'options' => [
                    'image' => __('imageParallax'),
                    'video' => __('backgroundVideo'),
                    'false' => __('noBackground'),
                ],
                'default' => 'false',
                'width'   => 50,
                'childrenFor' => [
                    'image' => [
                        'src'  => [
                            'label' => __('backgroundImage'),
                            'type'  => InputType::IMAGE,
                        ],
                    ],
                    'video' => [
                        'video-src' => [
                            'type'  => InputType::VIDEO,
                            'label' => __('video'),
                            'width' => 50,
                        ],
                        'video-poster' => [
                            'type'  => InputType::IMAGE,
                            'label' => __('placeholderImage'),
                            'width' => 50,
                        ],
                    ],
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
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
