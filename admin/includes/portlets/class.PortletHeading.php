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