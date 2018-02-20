<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletHeading
 */
class PortletButton extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-Button.svg">
            <br/> Button';
    }

    /**
     * @return string
     */
    public function getPreviewHtml($renderLinks = false)
    {
        // general
        $text          = $this->properties['button-text'];
        $type          = $this->properties['button-type'];
        $size          = $this->properties['button-size'];
        $alignment     = $this->properties['button-alignment'];
        $fullWidthflag = $this->properties['button-full-width-flag'];

        // icon
        $iconFlag      = $this->properties['icon-flag'];
        $icon          = $this->properties['icon'];
        $iconAlignment = $this->properties['icon-alignment'];

        // URL
        $linkFlag       = $this->properties['link-flag'];
        $linkUrl        = $this->properties['link-url'];
        $linkTitle      = $this->properties['link-title'];
        $linkNewTabFlag = $this->properties['link-new-tab-flag'];

        $this
            ->addClass('btn')
            ->addClass("btn-$type")
            ->addClass("btn-$size")
            ->addClass($fullWidthflag === 'yes' ? 'btn-block' : '');

        $previewButton = '<a';

        if ($renderLinks && $linkFlag === 'yes' && !empty($linkUrl)) {
            $previewButton .= ' href="' . $linkUrl . '" title="' . $linkTitle . '" ';
            $previewButton .= !empty($linkNewTabFlag) ? ' target="_blank" ' : '';
        }

        $wrapperClass = '';

        if (!empty($alignment)) {
            $wrapperClass = $alignment !== 'inline' ? 'text-' . $alignment : 'inline-block';
        }

        $previewButton .= ' ' . $this->getStyleString() . ' ' . $this->getAttribString() . ">";

        if ($iconFlag === 'yes' && $icon !== '') {
            if ($iconAlignment === 'left') {
                $previewButton .= "<i class='$icon' style='top:2px'></i> $text</a>";
            } else {
                $previewButton .= "$text <i class='$icon' style='top:2px'></i></a>";
            }
        } else {
            $previewButton .= "$text</a>";
        }

        return "<div class='" . $wrapperClass . "'>" . $previewButton . "</div>";
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
            // attributes
            'attr' => [
                'class'               => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
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
                'border'              => '0',
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
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.button.tpl');
    }
}