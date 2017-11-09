<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletSlider
 */
class PortletSlider extends PortletBase
{
    public function getPreviewHtml()
    {
        return $this->getFinalHtml();
    }

    public function getFinalHtml()
    {
        $articleIds   = $this->properties['articleIds'];
        $oArtikel_arr = [];

        foreach ($articleIds as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            $p = new Artikel($kArtikel);
            $p->fuelleArtikel($kArtikel, null);
            $oArtikel_arr[] = $p;
        }

        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('productlist', $oArtikel_arr)
            ->assign('title', 'Produkte')
            ->assign('Einstellungen', Shop::getConfig([CONF_BEWERTUNG]))
            ->fetch('tpl_inc/portlets/final.slider.tpl');
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.slider.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'articleIds' => [1]
        ];
    }
}