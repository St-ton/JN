<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class ImageSlider
 * @package JTL\OPC\Portlets
 */
class ImageSlider extends Portlet
{
    const EFFECT_LIST = [
        'sliceDown', 'sliceDownLeft', 'sliceUp', 'sliceUpLeft', 'sliceUpDown', 'sliceUpDownLeft', 'fold', 'fade',
        'slideInRight', 'slideInLeft', 'boxRandom', 'boxRain', 'boxRainReverse', 'boxRainGrow', 'boxRainGrowReverse'
    ];

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getEnabledEffectList(PortletInstance $instance): string
    {
        $effects = [];

        foreach (self::EFFECT_LIST as $effect) {
            if ($instance->getProperty('effects-' . $effect) === true) {
                $effects[] = $effect;
            }
        }

        return implode(',', $effects);
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        $desc = [
            'slider-theme'                => [
                'label'      => 'Theme',
                'type'       => InputType::SELECT,
                'options'    => [
                    'default' => 'Standard',
                    'bar'     => 'Balken',
                    'light'   => 'Hell',
                    'dark'    => 'Dunkel',
                ],
                'width' => 34,
            ],
            'slider-animation-speed'      => [
                'label'      => 'Slidergeschwindigkeit',
                'type'       => InputType::NUMBER,
                'default'    => 1500,
                'width'      => 34,
            ],
            'slider-animation-pause'      => [
                'label'      => 'Pause',
                'type'       => InputType::NUMBER,
                'default'    => 6000,
                'width'      => 34,
            ],
            'slider-start'                => [
                'label'      => 'Autostart?',
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'true',
                'inline'     => true,
                'width'      => 34,
            ],
            'slider-pause'                => [
                'label'      => 'Pause bei "hover"?',
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => 'anhalten',
                    'false' => 'weitermachen',
                ],
                'default'    => 'false',
                'width'      => 34,
            ],
            'slider-navigation'           => [
                'label'      => 'Punktnavigation?',
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'false',
                'width'      => 34,
            ],
            'slider-kenburns'             => [
                'label'      => 'Ken-Burns-Effekt nutzen?',
                'type'       => InputType::CHECKBOX,
                'hint'       => 'overrides other settings',
            ],
            'slider-direction-navigation' => [
                'label'      => 'Navipfeile anzeigen?',
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'false',
                'width'      => 50
            ],
            'slider-effects-random'       => [
                'label'   => 'zufällige Effekte?',
                'type'    => InputType::RADIO,
                'options' => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default' => 'true',
                'width'   => 50
            ],
            'slides'                      => [
                'label'      => 'Bilder',
                'type'       => InputType::IMAGE_SET,
                'default'    => [],
                'useColumns' => false,
                'useLinks'   => true,
                'useTitles'  => true,
            ],
        ];

        foreach (self::EFFECT_LIST as $effect) {
            $desc['effects-' . $effect] = [
                'label' => $effect,
                'type'  => InputType::CHECKBOX,
                'width' => 25,
            ];
        }

        return $desc;
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Slides' => ['slides'],
            'Styles' => 'styles',
        ];
    }
}
