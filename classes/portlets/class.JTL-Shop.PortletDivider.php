<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletRow
 */
class PortletDivider extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-minus"></i> Trennlinie';
    }

    public function getPreviewHtml()
    {
        $res = '<hr ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';

        return $res;
    }

    public function getFinalHtml()
    {
        $res = '<hr ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.divider.tpl');
    }

    public function getDefaultProps()
    {
        return [
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