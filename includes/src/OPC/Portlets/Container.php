<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;

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
        return '<i class="fa fa-object-group"></i><br/> Container';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'min-height'      => [
                'type'    => InputType::NUMBER,
                'label'   => 'Mindesthöhe in px',
                'default' => 300,
                'width'   => 50,
            ],
            'boxed' => [
                'type'  => InputType::CHECKBOX,
                'label' => 'Boxed Container',
                'width' => 50,
            ],
            'background-flag' => [
                'type'    => InputType::RADIO,
                'label'   => 'Hintergrund',
                'options' => [
                    'image' => 'mitlaufendes Bild (parallax)',
                    'video' => 'Hintergrundvideo',
                    'false' => 'kein Hintergrund',
                ],
                'default' => 'false',
                'width'   => 50,
                'childrenFor' => [
                    'image' => [
                        'src'  => [
                            'label' => 'Hintergrundbild',
                            'type'  => InputType::IMAGE,
                        ],
                    ],
                    'video' => [
                        'video-src' => [
                            'type'  => InputType::VIDEO,
                            'label' => 'Video',
                            'width' => 50,
                        ],
                        'video-poster' => [
                            'type'  => InputType::IMAGE,
                            'label' => 'Platzhalterbild',
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
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
