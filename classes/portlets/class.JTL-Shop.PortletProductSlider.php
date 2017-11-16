<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletProductSlider
 */
class PortletProductSlider extends CMSPortlet
{
    public function getPreviewHtml()
    {
        return '<div>Produkt-Slider</div>';
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

        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('productlist', $oArtikel_arr)
            ->assign('title', 'Produkte')
            ->assign('Einstellungen', Shop::getConfig([CONF_BEWERTUNG]))
            ->fetch('portlets/final.productslider.tpl');
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch(PFAD_ROOT . PFAD_TEMPLATES . 'Evo/portlets/settings.productslider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'articleIds' => '1,2',
        ];
    }
}