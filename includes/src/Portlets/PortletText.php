<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class WidgetClock
 */
class PortletText extends \CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-font"></i><br/> Text';
    }

    public function getPreviewHtml()
    {
        $text = $this->properties['text'];

        return '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . ">$text</div>";
    }

    public function getFinalHtml()
    {
        return $this->getPreviewHtml();
    }

    public function getConfigPanelHtml()
    {
        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.text.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'text' => 'ein neuer Abschnitt',
            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'class'              => '',
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
}