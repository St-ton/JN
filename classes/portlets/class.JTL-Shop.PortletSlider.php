<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletSlider
 */
class PortletSlider extends CMSPortlet
{
    public function getPreviewHtml()
    {
        return $this->getFinalHtml();
    }

    public function getFinalHtml()
    {
        $articleIds   = $this->properties['articleIds'];
        $oArtikel_arr = [];

//        $options = self::getDefaultOptions();
//        $options->

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
            ->fetch('portlets/final.slider.tpl');
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.slider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'articleIds' => [1]
        ];
    }
}