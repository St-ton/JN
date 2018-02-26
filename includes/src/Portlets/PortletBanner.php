<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletBanner
 */
class PortletBanner extends \CMSPortlet
{
    /**
     * @return string - sidepanel button
     */
    public function getButton()
    {
        return '<img class="fa" src="' . \Shop::getURL() .'/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-Banner.svg"></i>
            <br/> Banner';
    }

    public function getPreviewHtml($renderLinks = false)
    {
        $styleString = $this->getStyleString();
        $imgSrc      = PFAD_TEMPLATES . 'Evo/portlets/preview.banner.png';

        if (!empty($this->properties['attr']['src']) &&
            strpos($this->properties['attr']['src'], 'gfx/keinBild.gif') === false
        ) {
            $imgSrc = $this->properties['attr']['src'];
        }

        return
            '<div class="text-center" ' . $styleString . '>' .
            '<img src="' . $imgSrc . '" ' .
            'style="width: 98%;filter: grayscale(50%) opacity(60%)">' .
            '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>'.
            '</div>';
    }

    public function getFinalHtml()
    {
        $zones = !empty($this->properties['zones']) ? json_decode($this->properties['zones']) : '';

        $oImageMap = (object)[
            'cTitel' => $this->properties['data']['kImageMap'],
            'cBildPfad' => $this->properties['src'],
            'oArea_arr' => !empty($zones->oArea_arr) ? $zones->oArea_arr : null,
        ];

        $isFluid              = false;
        $cBildPfad            = PFAD_ROOT . $this->properties['src'];
        $oImageMap->cBildPfad = \Shop::getURL() . $oImageMap->cBildPfad;
        $cParse_arr           = parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = substr($cParse_arr['path'], strrpos($cParse_arr['path'], '/') + 1);
        list($width, $height) = getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = \Artikel::getDefaultOptions();
        $fill                 = true;

        if (!empty($oImageMap->oArea_arr)) {
            foreach ($oImageMap->oArea_arr as &$oArea) {
                $oArea->oArtikel = null;

                if ((int)$oArea->kArtikel > 0) {
                    $oArea->oArtikel = new \Artikel();

                    if ($fill === true) {
                        $oArea->oArtikel->fuelleArtikel($oArea->kArtikel, $defaultOptions);
                    } else {
                        $oArea->oArtikel->kArtikel = $oArea->kArtikel;
                        $oArea->oArtikel->cName    = utf8_encode(
                            \Shop::DB()->select(
                                'tartikel', 'kArtikel', $oArea->kArtikel, null, null, null, null, false, 'cName'
                            )->cName
                        );
                    }

                    if ($oArea->cTitel === '') {
                        $oArea->cTitel = $oArea->oArtikel->cName;
                    }
                    if ($oArea->cUrl === '') {
                        $oArea->cUrl = $oArea->oArtikel->cURL;
                    }
                    if ($oArea->cBeschreibung === '') {
                        $oArea->cBeschreibung = $oArea->oArtikel->cKurzBeschreibung;
                    }
                }
            }
        }

        return \Shop::Smarty()->assign('properties', $this->properties)
            ->assign('oBanner', $oImageMap)
            ->assign('isFluidBanner', false)
            ->assign('attribString', $this->getAttribString())
            ->assign('srcString', $this->getSrcString($this->properties['src'], $this->properties['widthHeuristics']))
            ->assign('isFluid', $isFluid)
            ->fetch('portlets/final.banner.tpl');
    }

    public function getConfigPanelHtml()
    {
        $oArea_arr                             = json_decode($this->properties['zones']);
        $this->properties['data']['oArea_arr'] = $oArea_arr->oArea_arr;

        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('frontendPfad', PFAD_TEMPLATES . 'Evo')
            ->fetch('portlets/settings.banner.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'banner-img' => '',
            'data' => [
                'kImageMap' => uniqid('', false),
                'oArea_arr' => [],
            ],
            'src'                => '',
            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'class'              => '',
                'alt'                => '',
                'title'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
            'widthHeuristics' => ['xs' => 1, 'sm' => 1, 'md' => 1, 'lg' => 1],
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