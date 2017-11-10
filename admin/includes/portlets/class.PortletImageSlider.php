<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletImage
 */
class PortletImageSlider extends PortletBase
{
    public function getPreviewHtml($renderLinks = false)
    {
        // general
        $theme = $this->properties['slider-theme'];
        $animationSpeed = $this->properties['slider-animation-speed'];
        $animationPause = $this->properties['slider-animation-pause'];
        $class = StringHandler::filterXSS($this->properties['slider-class']);
        $start = $this->properties['slider-start'];
        $pause = $this->properties['slider-pause'];
        $navigation = $this->properties['slider-navigation'];
        $thumbNavigation = $this->properties['slider-thumb-navigation'];
        $directionNavigation = $this->properties['slider-direction-navigation'];
        $kenburns = $this->properties['slider-kenburns'];
        $effectsRandom = $this->properties['slider-effects-random'];
        $effectsSliceDown = $this->properties['slider-effects-sliceDown'];
        $effectsSliceDownLeft = $this->properties['slider-effects-sliceDownLeft'];
        $effectsSliceUp = $this->properties['slider-effects-sliceUp'];
        $effectsSliceUpLeft = $this->properties['slider-effects-sliceUpLeft'];
        $effectsSliceUpDown = $this->properties['slider-effects-sliceUpDown'];
        $effectsSliceUpDownLeft = $this->properties['slider-effects-sliceUpDownLeft'];
        $effectsFold = $this->properties['slider-effects-fold'];
        $effectsFade = $this->properties['slider-effects-fade'];
        $effectsSlideInRight = $this->properties['slider-effects-slideInRight'];
        $effectsSlideInLeft = $this->properties['slider-effects-slideInLeft'];
        $effectsBoxRandom = $this->properties['slider-effects-boxRandom'];
        $effectsBoxRain = $this->properties['slider-effects-boxRain'];
        $effectsBoxRainReverse = $this->properties['slider-effects-boxRainReverse'];
        $effectsBoxRainGrow = $this->properties['slider-effects-boxRainGrow'];
        $effectsBoxRainGrowReverse = $this->properties['slider-effects-boxRainGrowReverse'];

        // style
        // $this->properties['style']

        if (!empty($animationStyle)){
            $class .= ' wow '.$animationStyle;
            if (!empty($animationDuration) && trim($animationDuration) != ''){
                $this->properties['attr']['data-wow-duration'] = $animationDuration;
            }
            if (!empty($animationDelay) && trim($animationDelay) != ''){
                $this->properties['attr']['data-wow-delay'] = $animationDelay;
            }
            if (!empty($animationOffset) && trim($animationOffset) != ''){
                $this->properties['attr']['data-wow-offset'] = $animationOffset;
            }
            if (!empty($animationIteration) && trim($animationIteration) != ''){
                $this->properties['attr']['data-wow-iteration'] = $animationIteration;
            }
        }

        $content = "<img class=\"img-responsive\" src=\"".Shop::getURL() . "/gfx/keinBild.gif\" >";

        return $content;
    }

    public function getFinalHtml()
    {
        return $this->getPreviewHtml();
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.imageslider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'slider-theme' => 'default',
            'slider-animation-speed' => '',
            'slider-animation-pause' => '',
            'slider-class' => '',
            'slider-start' => 'no',
            'slider-pause' => 'no',
            'slider-navigation' => 'no',
            'slider-thumb-navigation' => 'no',
            'slider-direction-navigation' => 'no',
            'slider-kenburns' => 'no',
            'slider-effects-random' => 'yes',
            'slider-effects-sliceDown' => '',
            'slider-effects-sliceDownLeft' => '',
            'slider-effects-sliceUp' => '',
            'slider-effects-sliceUpLeft' => '',
            'slider-effects-sliceUpDown' => '',
            'slider-effects-sliceUpDownLeft' => '',
            'slider-effects-fold' => '',
            'slider-effects-fade' => '',
            'slider-effects-slideInRight' => '',
            'slider-effects-slideInLeft' => '',
            'slider-effects-boxRandom' => '',
            'slider-effects-boxRain' => '',
            'slider-effects-boxRainReverse' => '',
            'slider-effects-boxRainGrow' => '',
            'slider-effects-boxRainGrowReverse' => '',
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