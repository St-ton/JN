<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletPanel
 */
class PortletPanel extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-window-maximize"></i><br/> Panel';
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $this->addClass('panel')->addClass('panel-' . $this->properties['panel-state']);

        $ret  = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';
        $ret .= !empty($this->properties['title-flag']) ? '<div class="panel-heading cle-area"></div>' : '';
        $ret .= '<div class="panel-body cle-area"></div>';
        $ret .= !empty($this->properties['footer-flag']) ? '<div class="panel-footer cle-area"></div>' : '';
        $ret .= '</div>';

        return $ret;
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        $this->addClass('panel')->addClass('panel-' . $this->properties['panel-state']);

        $i   = 0;
        $ret = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';

        if (!empty($this->properties['title-flag'])) {
            $subArea = $this->subAreas[$i];
            $ret    .= '<div class="panel-heading cle-area">';

            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }
            $ret .= '</div>';
            $i++;
        }

        $ret    .= '<div class="panel-body cle-area">';
        $subArea = $this->subAreas[$i];

        foreach ($subArea as $subPortlet) {
            $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                ->setProperties($subPortlet['properties'])
                ->setSubAreas($subPortlet['subAreas']);
            $subPortletHtml = $portlet->getFinalHtml();
            $ret           .= $subPortletHtml;
        }

        $ret .= '</div>';
        $i++;

        if (!empty($this->properties['footer-flag'])) {
            $subArea = $this->subAreas[$i];
            $ret    .= '<div class="panel-footer cle-area">';

            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }

            $ret .= '</div>';
        }
        $ret .= '</div>';

        return $ret;
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            'title-flag' => '0',
            'footer-flag' => '0',
            'panel-state' => 'primary',
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

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.panel.tpl');
    }
}