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
    public const EFFECT_LIST = [
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

        return \implode(',', $effects);
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        $desc = [
            'slider-theme'                => [
                'label'      => __('Theme'),
                'type'       => InputType::SELECT,
                'options'    => [
                    'default' => __('themeStandard'),
                    'bar'     => __('themeBar'),
                    'light'   => __('themeBright'),
                    'dark'    => __('themeDark'),
                ],
                'width' => 34,
            ],
            'slider-animation-speed'      => [
                'label'      => __('sliderAnimationSpeed'),
                'type'       => InputType::NUMBER,
                'default'    => 1500,
                'width'      => 34,
            ],
            'slider-animation-pause'      => [
                'label'      => __('pause'),
                'type'       => InputType::NUMBER,
                'default'    => 6000,
                'width'      => 34,
            ],
            'slider-start'                => [
                'label'      => __('autoStart'),
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => __('yes'),
                    'false' => __('no'),
                ],
                'default'    => 'true',
                'inline'     => true,
                'width'      => 34,
            ],
            'slider-pause'                => [
                'label'      => __('pauseOnHover'),
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => __('pauseOnHoverPause'),
                    'false' => __('pauseOnHoverContinue'),
                ],
                'default'    => 'false',
                'width'      => 34,
            ],
            'slider-navigation'           => [
                'label'      => __('pointNavigation'),
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => __('yes'),
                    'false' => __('no'),
                ],
                'default'    => 'false',
                'width'      => 34,
            ],
            'slider-kenburns'             => [
                'label'      => __('useKenBurnsEffect'),
                'type'       => InputType::CHECKBOX,
                'hint'       => __('overridesOtherSettings'),
            ],
            'slider-direction-navigation' => [
                'label'      => __('showNavigationArrows'),
                'type'       => InputType::RADIO,
                'options'    => [
                    'true'  => __('yes'),
                    'false' => __('no'),
                ],
                'default'    => 'false',
                'width'      => 50
            ],
            'slider-effects-random'       => [
                'label'   => __('randomEffects'),
                'type'    => InputType::RADIO,
                'options' => [
                    'true'  => __('yes'),
                    'false' => __('no'),
                ],
                'default' => 'true',
                'width'   => 50
            ],
            'slides'                      => [
                'label'      => __('images'),
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
            __('Slides') => ['slides'],
            __('Styles') => 'styles',
        ];
    }
}
