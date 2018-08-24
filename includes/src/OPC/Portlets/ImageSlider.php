<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class ImageSlider
 * @package OPC\Portlets
 */
class ImageSlider extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', \uniqid('sldr-', false));
        $images = $instance->getProperty('slides');
        unset($images['NEU']);
        if (!empty($images)) {
            \usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        $instance->setProperty('slides', $images);
        $instance->addClass('text-center');

        if (!empty($images[0]['url'])) {
            return
                '<div ' . $instance->getAttributeString() . $instance->getDataAttributeString() .
                '><img src="' . $images[0]['url'] .
                '" style="width: 98%; filter: grayscale(50%) opacity(60%)">' .
                '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">' .
                'Slider</p></div>';
        }

        return '<div ' . $instance->getAttributeString() . $instance->getDataAttributeString() . '>Slider</div>';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $images = $instance->getProperty('slides');
        unset($images['NEU']);
        if (!empty($images)) {
            \usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        foreach ($images as &$slide) {
            if (empty($slide['width']['xs'])) {
                $slide['width']['xs'] = 12;
            }
            if (empty($slide['width']['sm'])) {
                $slide['width']['sm'] = $slide['width']['xs'];
            }
            if (empty($slide['width']['md'])) {
                $slide['width']['md'] = $slide['width']['sm'];
            }
            if (empty($slide['width']['lg'])) {
                $slide['width']['lg'] = $slide['width']['md'];
            }
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }
        unset($slide);
        $instance->setProperty('slides', $images);

        //effects
        $effects = [];
        if ((bool)$instance->getProperty('effects-sliceDown') === true) {
            $effects[] = 'sliceDown';
        }
        if ((bool)$instance->getProperty('effects-sliceDownLeft') === true) {
            $effects[] = 'sliceDownLeft';
        }
        if ((bool)$instance->getProperty('effects-sliceUp') === true) {
            $effects[] = 'sliceUp';
        }
        if ((bool)$instance->getProperty('effects-sliceUpLeft') === true) {
            $effects[] = 'sliceUpLeft';
        }
        if ((bool)$instance->getProperty('effects-sliceUpDown') === true) {
            $effects[] = 'sliceUpDown';
        }
        if ((bool)$instance->getProperty('effects-sliceUpDownLeft') === true) {
            $effects[] = 'sliceUpDownLeft';
        }
        if ((bool)$instance->getProperty('effects-fold') === true) {
            $effects[] = 'fold';
        }
        if ((bool)$instance->getProperty('effects-fade') === true) {
            $effects[] = 'fade';
        }
        if ((bool)$instance->getProperty('effects-slideInRight') === true) {
            $effects[] = 'slideInRight';
        }
        if ((bool)$instance->getProperty('effects-slideInLeft') === true) {
            $effects[] = 'slideInLeft';
        }
        if ((bool)$instance->getProperty('effects-boxRandom') === true) {
            $effects[] = 'boxRandom';
        }
        if ((bool)$instance->getProperty('effects-boxRain') === true) {
            $effects[] = 'boxRain';
        }
        if ((bool)$instance->getProperty('effects-boxRainReverse') === true) {
            $effects[] = 'boxRainReverse';
        }
        if ((bool)$instance->getProperty('effects-boxRainGrow') === true) {
            $effects[] = 'boxRainGrow';
        }
        if ((bool)$instance->getProperty('effects-boxRainGrowReverse') === true) {
            $effects[] = 'boxRainGrowReverse';
        }
        $instance->setProperty('effects', $effects);

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Image Slider';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'slider-theme'                => [
                'label'      => 'Theme',
                'type'       => 'select',
                'options'    => [
                    'default' => 'Standard',
                    'bar'     => 'Balken',
                    'light'   => 'Hell',
                    'dark'    => 'Dunkel',
                ],
                'dspl_width' => 50,
            ],
            'slider-animation-speed'      => [
                'label'      => 'Slidergeschwindigkeit',
                'type'       => 'number',
                'default'    => 1500,
                'dspl_width' => 50,
            ],
            'slider-animation-pause'      => [
                'label'      => 'Pause',
                'type'       => 'number',
                'default'    => 6000,
                'dspl_width' => 50,
            ],
            'slider-start'                => [
                'label'      => 'Autostart?',
                'type'       => 'radio',
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'true',
                'inline'     => true,
                'dspl_width' => 50,
            ],
            'slider-pause'                => [
                'label'      => 'Pause bei "hover"?',
                'type'       => 'radio',
                'options'    => [
                    'true'  => 'anhalten',
                    'false' => 'weitermachen',
                ],
                'default'    => 'false',
                'dspl_width' => 50,
            ],
            'slider-navigation'           => [
                'label'      => 'Punktnavigation?',
                'type'       => 'radio',
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'false',
                'dspl_width' => 50,
            ],
            'slider-direction-navigation' => [
                'label'      => 'Navipfeile anzeigen?',
                'type'       => 'radio',
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default'    => 'false',
                'dspl_width' => 50
            ],
            'slider-kenburns'             => [
                'label'      => 'Ken-Burns-Effekt nutzen?',
                'type'       => 'checkbox',
                'dspl_width' => 50,
                'hint'       => 'overrides other settings',
            ],
            'slider-effects-random'       => [
                'label'   => 'zufällige Effekte?',
                'type'    => 'radio',
                'options' => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default' => 'true',
            ],
            'effects-sliceDown'           => [
                'label'                => 'sliceDown',
                'type'                 => 'checkbox',
                'collapseControlStart' => true,
                'showOnProp'           => 'slider-effects-random',
                'showOnPropValue'      => 'false',
                'dspl_width'           => 25,
            ],
            'effects-sliceDownLeft'       => [
                'label'      => 'sliceDownLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-sliceUp'             => [
                'label'      => 'sliceUp',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-sliceUpLeft'         => [
                'label'      => 'sliceUpLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-sliceUpDown'         => [
                'label'      => 'sliceUpDown',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-sliceUpDownLeft'     => [
                'label'      => 'sliceUpDownLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-fold'                => [
                'label'      => 'fold',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-fade'                => [
                'label'      => 'fade',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-slideInRight'        => [
                'label'      => 'sliceInRight',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-slideInLeft'         => [
                'label'      => 'slideInRight',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-boxRandom'           => [
                'label'      => 'boxRandom',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-boxRain'             => [
                'label'      => 'boxRain',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-boxRainReverse'      => [
                'label'      => 'boxRainReverse',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-boxRainGrow'         => [
                'label'      => 'boxRainGrow',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects-boxRainGrowReverse'  => [
                'label'              => 'boxRainGrowReverse',
                'type'               => 'checkbox',
                'collapseControlEnd' => true,
                'dspl_width'         => 25,
            ],
            'slides'                      => [
                'label'      => 'Bilder',
                'type'       => 'image-set',
                'default'    => [],
                'useColumns' => false,
                'useLinks'   => true,
                'useTitles'  => true,
            ],
        ];
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
