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
        return "<div>Slider</div>";
    }

    public function getFinalHtml()
    {
        return "<div>Slider</div>";
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
            'urls' => [],
            'url' => Shop::getURL() . '/gfx/keinBild.gif',
            'alt' => '',
        ];
    }
}