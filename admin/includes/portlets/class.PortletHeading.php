<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletHeading
 */
class PortletHeading extends PortletBase
{
    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $level = $this->properties['level'];
        $text  = $this->properties['text'];
        $class = $this->properties['class'];

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

        return "<h$level class=\"$class\"" . $this->attr_str() . $this->style_str() . ">$text</h$level>";
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        return $this->getPreviewHtml();
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            'level' => 1,
            'text'  => 'Heading Title',
            'class' => '',
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

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.heading.tpl');
    }
}