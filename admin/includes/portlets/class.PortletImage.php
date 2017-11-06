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
        $url = $this->properties['url'];
        $alt = $this->properties['alt'];

        return "<img src=\"$url\" alt=\"$alt\" style='min-width:2em;min-height: 2em;'>";
    }

    public function getFinalHtml()
    {
        $url = $this->properties['url'];
        $alt = $this->properties['alt'];

        return "<img src=\"$url\" alt=\"$alt\">";
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('settings', $this->properties)
            ->fetch('tpl_inc/portlets/settings.image.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'url' => '',
            'alt' => '',
        ];
    }
}