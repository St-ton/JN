<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletRow
 */
class PortletRow extends CMSPortlet
{
    public function getPreviewHtml()
    {
        $layout = $this->properties['layout'];
        $layout = explode('+', $layout);

        $this->properties['attr']['class'] .= " row";
        $res = '<div' . $this->getAttribString() . $this->getStyleString() . '>';

        foreach ($layout as $i => $col) {
            $res .= '<div class="col-xs-' . $col . ' cle-area"></div>';
        }

        $res .= '</div>';

        return $res;
    }

    public function getFinalHtml()
    {
        $layout = $this->properties['layout'];
        $layout = explode('+', $layout);

        $this->properties['attr']['class'] .= " row";
        $res = '<div' . $this->getAttribString() . $this->getStyleString() . '>';

        foreach ($layout as $i => $col) {
            $subArea  = $this->subAreas[$i];
            $res     .= '<div class="col-xs-' . $col . '">';

            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $res           .= $subPortletHtml;
            }

            $res .= '</div>';
        }

        $res .= '</div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.row.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'layout' => '6+6',
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