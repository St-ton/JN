<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

trait PortletAnimations
{
    public function getAnimationsPropertyDesc()
    {
        return [
            'animation-style'    => [
                'label'      => 'animation-style',
                'type'       => 'select',
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
                'label'       => 'duration',
                'help'        => 'Change the animation duration.',
                'placeholder' => '1s',
                'dspl_width'  => 50,
            ],
            'data-wow-delay'     => [
                'label'      => 'Delay',
                'help'       => 'Delay before the animation starts.',
                'dspl_width' => 50,
            ],
            'data-wow-offset'    => [
                'label'       => 'Offset',
                'type'        => 'number',
                'placeholder' => 200,
                'help'        => 'Distance to start the animation.',
                'dspl_width'  => 50,
            ],
            'data-wow-Iteration' => [
                'label'      => 'iteration',
                'type'       => 'number',
                'help'       => 'The animation number times is repeated.',
                'dspl_width' => 50,
            ]
        ];
    }
}