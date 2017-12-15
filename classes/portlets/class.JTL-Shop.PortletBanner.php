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
    /**
     * @return string - sidepanel button
     */
    public function getButton()
    {
        return '<div class="text-center" style="width: 100px;"><img src="../' . PFAD_TEMPLATES . 'Evo/portlets/preview.banner.png" style="width: 98%;filter: grayscale() opacity(60%)"/></div>';
    }

    public function getPreviewHtml($renderLinks = false)
    {
        $zones = !empty($this->properties['zones']) ? json_decode($this->properties['zones']) : '';
        $styleString = $this->getStyleString();
        if (!empty($this->properties['attr']['src']) && strpos($this->properties['attr']['src'], 'gfx/keinBild.gif') === false) {
            return '<div class="text-center"' . $styleString . '><img src="' . $this->properties['attr']['src'] . '" style="width: 98%;filter: grayscale() opacity(60%)"/><p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">Banner</p></div>';
        }
        return '<div class="text-center"' . $styleString . '><img src="' . PFAD_TEMPLATES . 'Evo/portlets/preview.banner.png" style="width: 98%;filter: grayscale() opacity(60%)"/><p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">Banner</p></div>';
    }

    public function getFinalHtml()
    {
        $zones = !empty($this->properties['zones']) ? json_decode($this->properties['zones']) : '';
        $oImageMap = new stdClass();
        $oImageMap->cTitel    = $this->properties['data']['kImageMap'];
        $oImageMap->cBildPfad = $this->properties['attr']['src'];
        $oImageMap->oArea_arr = !empty($zones->oArea_arr) ? $zones->oArea_arr : null;
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

        if (!empty($oImageMap->oArea_arr)) {
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
        }

        return Shop::Smarty()->assign('properties', $this->properties)
            ->assign('oBanner', $oImageMap)
            ->assign('isFluidBanner', false)
            ->assign('attribString', $this->getAttribString())
            ->assign('isFluid', $isFluid)
            ->fetch('portlets/final.banner.tpl');
    }

    public function getConfigPanelHtml()
    {
        $oArea_arr = json_decode($this->properties['zones']);
        $this->properties['data']['oArea_arr'] = $oArea_arr->oArea_arr;

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
                'src'                => '',
                'alt'                => '',
                'title'              => '',
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