<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletImage
 */
class PortletImage extends CMSPortlet
{
    public function getPreviewHtml($renderLinks = false)
    {
        // general
        $url   = $this->properties['url'];
        $alt   = StringHandler::filterXSS($this->properties['alt']);
        $shape = StringHandler::filterXSS($this->properties['shape']);
        $title = StringHandler::filterXSS($this->properties['title']);
        $class = StringHandler::filterXSS($this->properties['class']);
        // URL
        $linkFlag       = $this->properties['link-flag'];
        $linkUrl        = $this->properties['link-url'];
        $linkTitle      = $this->properties['link-title'];
        $linkNewTabFlag = $this->properties['link-new-tab-flag'];
        // animation
        $animationStyle     = $this->properties['animation-style'];
        $animationDuration  = $this->properties['animation-duration'];
        $animationDelay     = $this->properties['animation-delay'];
        $animationOffset    = $this->properties['animation-offset'];
        $animationIteration = $this->properties['animation-iteration'];
        // style
        $this->properties['style']['background-color']    = $this->properties['background-color'];
        $this->properties['style']['margin-top']          = $this->properties['margin-top'];
        $this->properties['style']['margin-right']        = $this->properties['margin-right'];
        $this->properties['style']['margin-bottom']       = $this->properties['margin-bottom'];
        $this->properties['style']['margin-left']         = $this->properties['margin-left'];
        $this->properties['style']['padding-top']         = $this->properties['padding-top'];
        $this->properties['style']['padding-right']       = $this->properties['padding-right'];
        $this->properties['style']['padding-bottom']      = $this->properties['padding-bottom'];
        $this->properties['style']['padding-left']        = $this->properties['padding-left'];
        $this->properties['style']['border-top-width']    = $this->properties['border-top-width'];
        $this->properties['style']['border-right-width']  = $this->properties['border-right-width'];
        $this->properties['style']['border-bottom-width'] = $this->properties['border-bottom-width'];
        $this->properties['style']['border-left-width']   = $this->properties['border-left-width'];
        $this->properties['style']['border-style']        = $this->properties['border-style'];
        $this->properties['style']['border-color']        = $this->properties['border-color'];

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

        $content = "<img class=\"img-responsive $shape $class\" src=\"$url\" alt=\"$alt\" title=\"$title\"" . $this->attr_str() . $this->style_str() .">";

        if ($renderLinks && $linkFlag == 'yes' && !empty($linkUrl)) {
            if ($linkNewTabFlag) {
                $content = '<a href="' . $linkUrl . '" title="' . $linkTitle . '" target="_blank">' . $content . '</a>';
            } else {
                $content = '<a href="' . $linkUrl . '" title="' . $linkTitle . '">' . $content . '</a>';
            }
        }

        return $content;
    }

    public function getFinalHtml()
    {
        return $this->getPreviewHtml(true);
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.image.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'url' => Shop::getURL() . '/gfx/keinBild.gif',
            'alt' => '',
            'shape' => '',
            'title' => '',
            'class' => '',
            // URL
            'link-flag'           => 'no',
            'link-url'            => '',
            'link-title'          => '',
            'link-new-tab-flag'   => 'no',
            // animation
            'animation-style'     => '',
            'animation-duration'  => '',
            'animation-delay'     => '',
            'animation-offset'    => '',
            'animation-iteration' => '',
            // style
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
            'border-color'        => ''
        ];
    }
}