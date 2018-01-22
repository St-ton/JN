<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletImage
 */
class PortletImageSlider extends CMSPortlet
{
    public function getPreviewHtml($renderLinks = false)
    {
        if (!empty($this->properties['slides'])) {
            usort(
                $this->properties['slides'],
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }

        $this->properties['slider-id'] = uniqid('', false);

        foreach ($this->properties['slides'] as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url']);
        }

        if (!empty($this->properties['slides'][0]['url'])) {
            return
                '<div class="text-center" ' . $this->getStyleString() .
                '><img' . $this->properties['slides'][0]['srcStr'] .
                ' style="width: 98%; filter: grayscale(50%) opacity(60%)">' .
                '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">' .
                'Slider</p></div>';
        }

        if (!empty($this->properties['slides'][1]['url'])) {
            return
                '<div class="text-center" '  . $this->getStyleString() .
                '><img' . $this->properties['slides'][1]['srcStr'] .
                ' style="width: 98%;filter: grayscale(50%) opacity(60%)">' .
                '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">' .
                'Slider</p></div>';
        }

        return '<img src="/gfx/keinBild.gif">';

        /*TODO EDITOR: platzhalter oder preview prüfen*/
        /*
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('noImageUrl', Shop::getURL() . "/gfx/keinBild.gif")
            ->assign('styleString', $this->getStyleString())
            ->assign('renderLinks', false)
            ->fetch('portlets/final.imageslider.tpl');*/
    }

    public function getFinalHtml()
    {
        if (!empty($this->properties['slides'])) {
            usort(
                $this->properties['slides'],
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }

        $this->properties['slider-id'] = uniqid('', false);

        foreach ($this->properties['slides'] as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url']);
        }

        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('noImageUrl', Shop::getURL() . "/gfx/keinBild.gif")
            ->assign('styleString', $this->getStyleString())
            ->assign('renderLinks', true)
            ->fetch('portlets/final.imageslider.tpl');
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.imageslider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'slider-id' => '',
            'slider-theme' => 'default',
            'slider-animation-speed' => '',
            'slider-animation-pause' => '',
            'slider-start' => 'false',
            'slider-pause' => 'false',
            'slider-navigation' => 'false',
            'slider-direction-navigation' => 'false',
            'slider-kenburns' => 'false',
            'slider-effects-random' => 'true',
            'effects' => [
                'sliceDown' => '',
                'sliceDownLeft' => '',
                'sliceUp' => '',
                'sliceUpLeft' => '',
                'sliceUpDown' => '',
                'sliceUpDownLeft' => '',
                'fold' => '',
                'fade' => '',
                'slideInRight' => '',
                'slideInLeft' => '',
                'boxRandom' => '',
                'boxRain' => '',
                'boxRainReverse' => '',
                'boxRainGrow' => '',
                'boxRainGrowReverse' => '',
            ],
            'slides' => [],
            // attributes
            'attr' => [
                'class'               => '',
            ],
            // style
            'style' => [
                'color'               => '',
                'margin-top'          => '',
                'margin-right'        => '',
                'margin-bottom'       => '',
                'margin-left'         => '',
                'background-color'    => '',
                'padding-top'         => '',
                'padding-right'       => '',
                'padding-bottom'      => '',
                'padding-left'        => '',
                'border-top-width'    => '',
                'border-right-width'  => '',
                'border-bottom-width' => '',
                'border-left-width'   => '',
                'border-style'        => '',
                'border-color'        => '',
            ],
        ];
    }
}