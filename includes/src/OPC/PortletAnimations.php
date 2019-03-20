<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

/**
 * Trait PortletAnimations
 * @package JTL\OPC
 */
trait PortletAnimations
{
    /**
     * @return array
     */
    public function getAnimationsPropertyDesc(): array
    {
        return [
            'animation-style'    => [
                'label'      => __('Animation style'),
                'type'       => InputType::SELECT,
                'options'    => [
                    'optgroup1' => [
                        'label'   => 'Attention Seekers',
                        'options' => [
                            '',
                            'bounce',
                            'flash',
                            'pulse',
                            'rubberBand',
                            'shake',
                            'swing',
                            'tada',
                            'wobble',
                            'jello',
                        ],
                    ],
                    'optgroup2' => [
                        'label'   => 'Bouncing Entrances',
                        'options' => [
                            'bounceIn',
                            'bounceInDown',
                            'bounceInLeft',
                            'bounceInRight',
                            'bounceInUp',
                        ],
                    ],
                    'optgroup3' => [
                        'label'   => 'Fading Entrances',
                        'options' => [
                            'fadeIn',
                            'fadeInDown',
                            'fadeInDownBig',
                            'fadeInLeft',
                            'fadeInLeftBig',
                        ],
                    ],
                    'optgroup4' => [
                        'label'   => 'Flippers',
                        'options' => [
                            'flip',
                            'flipInX',
                            'flipInY',
                        ],
                    ],
                    'optgroup5' => [
                        'label'   => 'lightspeed',
                        'options' => [
                            'lightSpeedIn',
                        ],
                    ],
                    'optgroup6' => [
                        'label'   => 'Rotating Entrances',
                        'options' => [
                            'rotateIn',
                            'rotateInDownLeft',
                            'rotateInDownRight',
                            'rotateInUpLeft',
                            'rotateInUpRight',
                        ],
                    ],
                    'optgroup7' => [
                        'label'   => 'Sliding Entrances',
                        'options' => [
                            'slideInUp',
                            'slideInDown',
                            'slideInLeft',
                            'slideInRight',
                        ],
                    ],
                    'optgroup8' => [
                        'label'   => 'Zoom Entrances',
                        'options' => [
                            'zoomIn',
                            'zoomInDown',
                            'zoomInLeft',
                            'zoomInRight',
                            'zoomInUp',
                        ],
                    ],
                    'optgroup9' => [
                        'label'   => 'Specials',
                        'options' => [
                            'hinge',
                            'rollIn',
                        ],
                    ],
                ],
                'dspl_width' => 50,
            ],
            'data-wow-duration'  => [
                'label'       => __('Duration'),
                'help'        => __('Change the animation duration (e.g. 2s)'),
                'placeholder' => '1s',
                'dspl_width'  => 50,
            ],
            'data-wow-delay'     => [
                'label'      => __('Delay'),
                'help'       => __('Delay before the animation starts'),
                'dspl_width' => 50,
            ],
            'data-wow-offset'    => [
                'label'       => __('Offset (px)'),
                'type'        => InputType::NUMBER,
                'placeholder' => 200,
                'help'        => __('Distance to start the animation (related to the browser bottom)'),
                'dspl_width'  => 50,
            ],
            'data-wow-Iteration' => [
                'label'      => __('Iteration'),
                'type'       => InputType::NUMBER,
                'help'       => __('Number of times the animation is repeated'),
                'dspl_width' => 50,
            ]
        ];
    }
}
