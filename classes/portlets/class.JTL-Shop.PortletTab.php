<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletRow
 */
class PortletTab extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-Tab.svg">
            <br/> Tabs';
    }

    public function getPreviewHtml()
    {
        unset($this->properties['tab']['NEU']);

        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>
                    <ul class="nav nav-tabs" role="tablist">';

        foreach ($this->properties['tab'] as $key => $tab) {
            $res .= '<li role="presentation"';
            if ($key == $this->properties['active']) {
                $res .= ' class="active"';
            }
            $res .= '>
                 <a href="#prtlt_tb_' . $tab . '" aria-controls="prtlt_tb_' . $tab . '" role="tab" data-toggle="tab">' . $tab . '</a></li>';
        }
        $res .= '</ul>
                    <div class="tab-content">';
        foreach ($this->properties['tab'] as $key => $tab) {
            $res .= '<div role="tabpanel" class="tab-pane';
            if ($key == $this->properties['active']) {
                $res .= ' active';
            }
            $res .= ' cle-area" id="prtlt_tb_' . $tab . '"></div>';
        }

        $res .= '</div></div>';

        return $res;
    }

    public function getFinalHtml()
    {
        unset($this->properties['tab']['NEU']);

        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>
                    <ul class="nav nav-tabs" role="tablist">';

        foreach ($this->properties['tab'] as $key => $tab) {
            $res .= '<li role="presentation"';
            if ($key == $this->properties['active']) {
                $res .= ' class="active"';
            }
            $res .= '>
                 <a href="#prtlt_tb_' . $tab . '" aria-controls="prtlt_tb_' . $tab . '" role="tab" data-toggle="tab">' . $tab . '</a></li>';
        }
        $res .= '</ul>
                    <div class="tab-content">';
        foreach ($this->properties['tab'] as $key => $tab) {
            $subArea = $this->subAreas[$key-1];
            $res .= '<div role="tabpanel" class="tab-pane';
            if ($key == $this->properties['active']) {
                $res .= ' active';
            }
            $res .= ' cle-area" id="prtlt_tb_' . $tab . '">';
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $res           .= $subPortletHtml;
            }
            $res.='</div>';
        }

        $res .= '</div></div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        unset($this->properties['tab']['NEU']);

        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.tab.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'tab' => [
                '1' => 'Home',
                '2' => 'Profile',
                '3' => 'Messages',
            ],
            'active' => 1,
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