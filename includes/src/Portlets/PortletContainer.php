<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletRow
 */
class PortletContainer extends \OPCPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-object-group"></i><br/> Container';
    }

    public function getPreviewHtml()
    {
        $res = "<div ";
        if (strpos($this->properties['src'], 'gfx/keinBild.gif') === false) {
            $this->getSrcString($this->properties['src']);
        }
        if ($this->properties['parallax-flag'] === 'yes') {
            $name      = explode('/', $this->properties['src']);
            $name      = end($name);
            $this->properties['style']['background'] = 'url("../' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name .'")';
            $this->properties['style']['background-size'] = 'cover';
        }
        $res.= $this->getAttribString() . ' ' . $this->getStyleString() .'>';
        $res .= "<div class='cle-area'></div></div>";

        return $res;
    }

    public function getFinalHtml()
    {
        if (strpos($this->properties['src'], 'gfx/keinBild.gif') === false) {
            $this->getSrcString($this->properties['src']);
        }
        unset($this->properties['style']['background']);
        unset($this->properties['style']['background-size']);
        $res = "<div ";
        if ($this->properties['parallax-flag'] === 'yes') {
            $name      = explode('/', $this->properties['src']);
            $name      = end($name);
            $this->addClass('parallax-window');
            $res.= " data-parallax='scroll' data-image-src='"
                . PFAD_MEDIAFILES . 'Bilder/.lg/' . $name
                . "' data-z-index='1'";
        }
        $res.= $this->getAttribString() . ' ' . $this->getStyleString() .'>';

        if (!empty($this->subAreas)) {
            foreach ($this->subAreas[0] as $subPortlet){
                $portlet = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $res           .= $subPortletHtml;
            }
        }
        $res .= '</div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.container.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'parallax-flag' => 'no',
            'src'           => '',
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
            'widthHeuristics' => ['xs' => 1, 'sm' => 1, 'md' => 1, 'lg' => 1],
            // style
            'style' => [
                'min-height'          => '300',
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