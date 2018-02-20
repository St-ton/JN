<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletRow
 */
class PortletList extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-list-ol"></i><br/> Liste';
    }

    public function getPreviewHtml()
    {
        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>
                    <'. $this->properties['listType'];
        if (!empty($this->properties['list-style-type'])) {
            $res .= ' style="list-style-type:'. $this->properties['list-style-type'] .'"';
        }
        $res .='>';

        for ($x=0; $x<(int)$this->properties['count']; ++$x) {
            $res .= '<li><div class="cle-area"></div></li>';
        }

        $res .= '</'. $this->properties['listType'] .'></div>';

        return $res;
    }

    public function getFinalHtml()
    {
        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>
                    <'. $this->properties['listType'];
        if (!empty($this->properties['list-style-type'])) {
            $res .= ' style="list-style-type:'. $this->properties['list-style-type'] .'"';
        }
        $res .='>';

        for ($x=0; $x<(int)$this->properties['count']; ++$x) {
            $subArea = $this->subAreas[$x];
            $res .= '<li><div class="cle-area">';
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $res           .= $subPortletHtml;
            }
            $res.='</div></li>';
        }
        $res .= '</'. $this->properties['listType'] .'></div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        unset($this->properties['tab']['NEU']);

        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.list.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'listType' => 'ul',
            'count' => '3',
            'list-style-type' => '',
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
}