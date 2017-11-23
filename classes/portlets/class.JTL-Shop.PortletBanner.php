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
        $oImageMap->oArea_arr = $zones->oArea_arr;
        $isFluid   = false;

        $cBildPfad            = PFAD_ROOT . $this->properties['attr']['src'];
        $oImageMap->cBildPfad = Shop::getURL() . $oImageMap->cBildPfad;
        $cParse_arr           = parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = substr($cParse_arr['path'], strrpos($cParse_arr['path'], '/') + 1);
        list($width, $height) = getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = Artikel::getDefaultOptions();
        $fill = true;

        foreach ($oImageMap->oArea_arr as &$oArea) {
            $oArea->oArtikel = null;
            if ((int)$oArea->kArtikel > 0) {
                $oArea->oArtikel = new Artikel();
                if ($fill === true) {
                    $oArea->oArtikel->fuelleArtikel(
                        $oArea->kArtikel,
                        $defaultOptions
                    );
                } else {
                    $oArea->oArtikel->kArtikel = $oArea->kArtikel;
                    $oArea->oArtikel->cName    = utf8_encode(
                        Shop::DB()->select(
                            'tartikel', 'kArtikel', $oArea->kArtikel, null, null, null, null, false, 'cName'
                        )->cName
                    );
                }
                if (strlen($oArea->cTitel) === 0) {
                    $oArea->cTitel = $oArea->oArtikel->cName;
                }
                if (strlen($oArea->cUrl) === 0) {
                    $oArea->cUrl = $oArea->oArtikel->cURL;
                }
                if (strlen($oArea->cBeschreibung) === 0) {
                    $oArea->cBeschreibung = $oArea->oArtikel->cKurzBeschreibung;
                }
            }
        }

        return Shop::Smarty()->assign('properties', $this->properties)
            ->assign('oBanner', $oImageMap)
            ->assign('isFluidBanner', false)
            ->assign('isFluid', $isFluid)
            ->fetch('portlets/final.banner.tpl');
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