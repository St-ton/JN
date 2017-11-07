<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletHeading
 */
class PortletImage extends PortletBase
{
    public function getPreviewHtml()
    {
        $url   = $this->properties['url'];
        $alt   = StringHandler::filterXSS($this->properties['alt']);
        $shape = StringHandler::filterXSS($this->properties['shape']);
        $title = StringHandler::filterXSS($this->properties['title']);

        return "<img class=\"img-responsive $shape\" src=\"$url\" alt=\"$alt\" title=\"$title\" style='min-width:2em;min-height: 2em;'>";
    }

    public function getFinalHtml()
    {
        $url   = $this->properties['url'];
        $alt   = StringHandler::filterXSS($this->properties['alt']);
        $shape = StringHandler::filterXSS($this->properties['shape']);
        $title = StringHandler::filterXSS($this->properties['title']);

        return "<img class=\"img-responsive $shape\" src=\"$url\" alt=\"$alt\" title=\"$title\">";
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.image.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'url' => Shop::getURL() . '/gfx/keinBild.gif',
            'alt' => '',
            'shape' => '',
            'title' => '',
        ];
    }
}