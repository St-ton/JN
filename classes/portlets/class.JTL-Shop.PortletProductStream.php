<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletProductSlider
 */
class PortletProductStream extends CMSPortlet
{
    public function getPreviewHtml()
    {
        $style = $this->properties['listStyle'];
        $styleString = $this->getStyleString();

        return '<div class="text-center"' . $styleString . '><img src="' . PFAD_TEMPLATES . 'Evo/portlets/preview.productstream.' . $style . '.png" style="width: 98%;filter: grayscale() opacity(60%)"/><p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -65px;">Produktliste</p></div>';
        //return $this->getFinalHtml();
    }

    public function getFinalHtml()
    {
        $articleIds   = explode(',', $this->properties['articleIds']);
        $oArtikel_arr = [];

        foreach ($articleIds as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            $p = new Artikel($kArtikel);
            $p->fuelleArtikel($kArtikel, null);
            $oArtikel_arr[] = $p;
        }

        $style = $this->properties['listStyle'];
        $grid = 'col-xs-12';

        if ($style === 'gallery') {
            $grid = 'col-xs-6 col-lg-4';
        }

        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('productlist', $oArtikel_arr)
            ->assign('styleString', $this->getStyleString())
            ->assign('style', $style)
            ->assign('grid', $grid)
            ->assign('Einstellungen', Shop::getConfig([CONF_BEWERTUNG, CONF_ARTIKELUEBERSICHT, CONF_TEMPLATE, CONF_ARTIKELDETAILS, CONF_GLOBAL]))
            ->fetch('portlets/final.productstream.tpl');
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
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