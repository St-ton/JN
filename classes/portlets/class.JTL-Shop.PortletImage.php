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

        $content = "<img class=\"img-responsive $shape $class\" src=\"$url\" alt=\"$alt\" title=\"$title\"" . $this->getAttribString() . $this->getStyleString() .">";

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
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.image.tpl');
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