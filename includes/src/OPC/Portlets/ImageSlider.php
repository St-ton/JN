<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class ImageSlider extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $images = $instance->getProperty('slides');
        unset($images['NEU']);
        if (!empty($images)) {
            usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        /*foreach ($images as &$slide) {
            if(empty($slide['width']['xs'])) $slide['width']['xs'] = 12;
            if(empty($slide['width']['sm'])) $slide['width']['sm'] = $slide['width']['xs'];
            if(empty($slide['width']['md'])) $slide['width']['md'] = $slide['width']['sm'];
            if(empty($slide['width']['lg'])) $slide['width']['lg'] = $slide['width']['md'];
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }*/
        $instance->setProperty('slides',$images);
        $id = !empty($instance->getProperty('slider-id')) ? $instance->getProperty('slider-id') : uniqid('sldr_');
        $instance->setAttribute('slider-id',$id);

        $instance->addClass('text-center');

        if (!empty($images[0]['url'])) {
            return
                '<div ' . $instance->getAttributeString() . $instance->getDataAttributeString() .
                '><img src="'.$images[0]['url'].
                '" style="width: 98%; filter: grayscale(50%) opacity(60%)">' .
                '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">' .
                'Slider</p></div>';
        }

        return '<div ' . $instance->getAttributeString() . $instance->getDataAttributeString() .'>Slider</div>';
    }

    public function getFinalHtml($instance)
    {
        $images = $instance->getProperty('slides');
        unset($images['NEU']);
        if (!empty($images)) {
            usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        foreach ($images as &$slide) {
            if(empty($slide['width']['xs'])) $slide['width']['xs'] = 12;
            if(empty($slide['width']['sm'])) $slide['width']['sm'] = $slide['width']['xs'];
            if(empty($slide['width']['md'])) $slide['width']['md'] = $slide['width']['sm'];
            if(empty($slide['width']['lg'])) $slide['width']['lg'] = $slide['width']['md'];
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }

        $instance->setProperty('slides',$images);
        $id = !empty($instance->getProperty('slider-id')) ? $instance->getProperty('slider-id') : uniqid('sldr');
        $instance->setAttribute('slider-id',$id);

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-ImageSlider.svg">
            <br/> Image Slider';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'slider-id'                   => [
                'label'      => 'Slider ID',
                'dspl_width' => 50,
            ],
            'slider-theme'                => [
                'label'      => 'theme',
                'type'       => 'select',
                'options'    => [
                    'default',
                    'bar',
                    'light',
                    'dark'
                ],
                'dspl_width' => 50,
            ],
            'slider-animation-speed'      => [
                'label'      => 'slider speed',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'slider-animation-pause'      => [
                'label'      => 'pause',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'slider-start'                => [
                'label'      => 'auto start',
                'type'       => 'checkbox',
                'dspl_width' => 50,
            ],
            'slider-pause'                => [
                'label'      => 'pause on hover',
                'type'       => 'checkbox',
                'dspl_width' => 50,
            ],
            'slider-navigation'           => [
                'label'      => 'slider navigation',
                'type'       => 'checkbox',
                'dspl_width' => 50,
            ],
            'slider-direction-navigation' => [
                'label'      => 'direction nav',
                'type'       => 'checkbox',
                'dspl_width' => '50'
            ],
            'slider-kenburns'             => [
                'label'      => 'use Ken-Burns',
                'type'       => 'checkbox',
                'dspl_width' => 50,
                'hint'       => 'overrides other settings',
            ],
            'slider-effects-random'       => [
                'label' => 'randow effects',
                'type'  => 'checkbox',
            ],
            'effects[sliceDown]'                   => [
                'label'                => 'sliceDown',
                'type'                 => 'checkbox',
                'collapseControlStart' => true,
                'showOnProp'           => 'slider-effects-random',
                'showOnPropValue'      => 0,
                'dspl_width'           => 25,
            ],
            'effects[sliceDownLeft]'               => [
                'label'      => 'sliceDownLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[sliceUp]'                     => [
                'label'      => 'sliceUp',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[sliceUpLeft]'                 => [
                'label'      => 'sliceUpLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[sliceUpDown]'                 => [
                'label'      => 'sliceUpDown',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[sliceUpDownLeft]'             => [
                'label'      => 'sliceUpDownLeft',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[fold]'                        => [
                'label'      => 'fold',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[fade]'                        => [
                'label'      => 'fade',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[slideInRight]'                => [
                'label'      => 'sliceInRight',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[slideInLeft]'                 => [
                'label'      => 'slideInRight',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[boxRandom]'                   => [
                'label'      => 'boxRandom',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[boxRain]'                     => [
                'label'      => 'boxRain',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[boxRainReverse]'              => [
                'label'      => 'boxRainReverse',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[boxRainGrow]'                 => [
                'label'      => 'boxRainGrow',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'effects[boxRainGrowReverse]'          => [
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
                'useLinks'   => true
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Slides' => ['slides'],
            'Styles' => 'styles',
        ];
    }
}