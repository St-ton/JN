<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletHeading
 */
class PortletHeading extends \OPCPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-header"></i><br/> Überschrift';
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $level = $this->properties['level'];
        $text  = $this->properties['text'];

        return "<h$level " . $this->getAttribString() . ' ' . $this->getStyleString() . ">$text</h$level>";
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
             // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'id'                 => '',
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

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.heading.tpl');
    }
}