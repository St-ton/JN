<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletProductSlider
 */
class PortletProductStream extends \CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-ProductStream.svg">
            <br/> Product Stream';
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $style       = $this->properties['listStyle'];
        $styleString = $this->getStyleString();

        return
            '<div class="text-center" ' . $styleString . '>' .
            '<img src="' . PFAD_TEMPLATES . 'Evo/portlets/preview.productstream.' . $style . '.png" ' .
            'style="width: 98%;filter: grayscale(50%) opacity(60%)">' .
            '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Produktliste</p>' .
            '</div>';
    }

    public function getFinalHtml()
    {
        $articleIds   = explode(',', $this->properties['articleIds']);
        $oArtikel_arr = [];

        foreach ($articleIds as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            $oArtikel = new \Artikel($kArtikel);
            $oArtikel->fuelleArtikel($kArtikel, null);
            $oArtikel_arr[] = $oArtikel;
        }

        $style = $this->properties['listStyle'];
        $grid  = 'col-xs-12';

        if ($style === 'gallery') {
            $grid = 'col-xs-6 col-lg-4';
        }

        return \Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('productlist', $oArtikel_arr)
            ->assign('styleString', $this->getStyleString())
            ->assign('style', $style)
            ->assign('grid', $grid)
            ->fetch('portlets/final.productstream.tpl');
    }

    public function getConfigPanelHtml()
    {
        return \Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch(PFAD_ROOT . PFAD_TEMPLATES . 'Evo/portlets/settings.productstream.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'listStyle' => 'gallery',
            'articleIds' => '',
            'attr' => [
                'class'               => '',
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