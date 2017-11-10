<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletHeading
 */
class PortletButton extends PortletBase
{
    /**
     * @return string
     */
    public function getPreviewHtml($renderLinks = false)
    {
//        Shop::dbg($this->properties, false);

        // general
        $text          = $this->properties['button-text'];
        $type          = $this->properties['button-type'];
        $size          = $this->properties['button-size'];
        $alignment     = $this->properties['button-alignment'];
        $fullWidthflag = $this->properties['button-full-width-flag'];
        $class         = $this->properties['button-class'];
        // icon
        $iconFlag      = $this->properties['icon-flag'];
        $icon          = $this->properties['icon'];
        $iconAlignment = $this->properties['icon-alignment'];
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

        $previewButton  = "<a class='btn btn-$type btn-$size";
        $previewButton .= !empty($class) ? " $class" : "";
        $previewButton .= ($fullWidthflag == 'yes') ? " btn-block" : "";
        $previewButton .= "'";

        if ($renderLinks && $linkFlag == 'yes' && !empty($linkUrl)) {
            $previewButton .= " href='$linkUrl' title='$linkTitle'";
            $previewButton .= !empty($linkNewTabFlag) ? " target='_blank'" : "";
        }

        $previewButton .= $this->style_str() . ">";
        if ($iconFlag == 'yes' && $icon != '') {
            if ($iconAlignment == 'left') {
                $previewButton .= "<i class='$icon' style='top:2px'></i> $text</a>";
            } else {
                $previewButton .= "$text <i class='$icon' style='top:2px'></i></a>";
            }
        } else {
            $previewButton .= "$text</a>";
        }
        /*return $previewButton;*/

        if (!empty($alignment)) {
            if ($alignment != 'inline') {
                $this->properties['attr']['class'] = ((!empty($class)) ? $class : '') . ' text-' . $alignment;
            } else {
                $this->properties['style']['display'] = 'inline-block';
            }
        }

        $this->properties['attr']['class'] = (!empty($this->properties['attr']['class'])) ? $this->properties['attr']['class'] : '';
        if (!empty($animationStyle)){
            $this->properties['attr']['class'] .= ' wow '.$animationStyle;
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

        $content  = '';
        $content .= "<div".$this->attr_str()."> \n";
        $content .= $previewButton."\n";
        $content .= "</div> \n";

        return $content;
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        return $this->getPreviewHtml(true);
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            // general
            'button-text'                => 'Button Text',
            'button-type'                => 'default',
            'button-size'                => 'md',
            'button-alignment'           => 'inline',
            'button-full-width-flag'     => 'no',
            'button-class'               => '',
            // icon
            'icon-flag'           => 'no',
            'icon'                => '',
            'icon-alignment'      => 'left',
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

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.button.tpl');
    }
}