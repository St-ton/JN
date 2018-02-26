<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletProductSlider
 */
class PortletProductSlider extends \CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-ProductSlider.svg">
            <br/> Product Slider';
    }

    public function getPreviewHtml()
    {
        $styleString = $this->getStyleString();

        return
            '<div class="text-center" ' . $styleString . '>' .
            '<img src="' . PFAD_TEMPLATES . 'Evo/portlets/preview.productslider.png" ' .
            'style="width: 98%; filter: grayscale(50%) opacity(60%)">' .
            '<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">Produktslider</p>' .
            '</div>';
    }

    public function getFinalHtml()
    {
        $articleIds   = $this->getFilteredProductIds();
        $oArtikel_arr = [];

        foreach ($articleIds as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            $p        = new \Artikel($kArtikel);
            $p->fuelleArtikel($kArtikel, null);
            $oArtikel_arr[] = $p;
        }

        return \Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('productlist', $oArtikel_arr)
            ->assign('styleString', $this->getStyleString())
            ->assign('title', $this->properties['title'])
            ->fetch('portlets/final.productslider.tpl');
    }

    public function getConfigPanelHtml()
    {
        return \Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch(PFAD_ROOT . PFAD_TEMPLATES . 'Evo/portlets/settings.productslider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'title' => '',
            'articleIds' => '',
            'filters' => [],
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