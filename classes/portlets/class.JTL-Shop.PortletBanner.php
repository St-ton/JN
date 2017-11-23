<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletBanner
 */
class PortletBanner extends CMSPortlet
{
    public function getPreviewHtml($renderLinks = false)
    {
        $zones = !empty($this->properties['zones']) ? json_decode($this->properties['zones']) : '';
//        Shop::dbg($zones, true);
        $styleString = $this->getStyleString();
        if (!empty($this->properties['attr']['src']) && strpos($this->properties['attr']['src'], 'gfx/keinBild.gif') === false) {
            return '<div class="text-center"' . $styleString . '><img src="' . $this->properties['attr']['src'] . '" style="margin-top: 4px; width: 98%;filter: grayscale() opacity(60%)"/>Banner<p><small>preview images mit transparenten rand anlegen, graustufen, img responsive nutzen</small></p></div>';
        }
        return '<div class="text-center"' . $styleString . '><img src="' . PFAD_TEMPLATES . 'Evo/portlets/preview.banner.png" style="margin-top: 4px; width: 98%;filter: grayscale() opacity(60%)"/>Banner<p><small>preview images mit transparenten rand anlegen, graustufen, img responsive nutzen</small></p></div>';
    }

    public function getFinalHtml()
    {
        $zones = !empty($this->properties['zones']) ? json_decode($this->properties['zones']) : '';
        $oImageMap = new stdClass();
        $oImageMap->cTitel    = $this->properties['data']['kImageMap'];
        $oImageMap->cBildPfad = $this->properties['attr']['src'];
        $oImageMap->oArea_arr = $zones['oArea_arr'];



        return $this->getPreviewHtml(true);
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('frontendPfad', PFAD_TEMPLATES . 'Evo')
            ->fetch('portlets/settings.banner.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'banner-img' => '',
            'data' => [
                'kImageMap' => uniqid(),
                'oArea_arr' => [],
            ],
            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'class'              => '',
                'src'                => Shop::getURL() . '/gfx/keinBild.gif',
                'alt'                => '',
                'title'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
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
}