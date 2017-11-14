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
            usort($this->properties['slides'], function($a,$b) {
                return $a['nSort']>$b['nSort'];
            });
        }

        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('noImageUrl', Shop::getURL() . "/gfx/keinBild.gif")
            ->assign('styleString', $this->getStyleString())
            ->assign('renderLinks', false)
            ->fetch('portlets/final.imageslider.tpl');


        $content = "<img class=\"img-responsive\" src=\"".Shop::getURL() . "/gfx/keinBild.gif\" >";

        return $content;
    }

    public function getFinalHtml()
    {
        if (!empty($this->properties['slides'])) {
            usort($this->properties['slides'], function($a,$b) {
                return $a['nSort']>$b['nSort'];
            });
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