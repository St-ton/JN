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
    public function getPreviewContent($settings = null)
    {
        $url = isset($settings['url']) ? $settings['url'] : '';
        $alt = isset($settings['alt']) ? $settings['alt'] : '';

        return "<img src=\"$url\" alt=\"$alt\" style='min-width:2em;min-height: 2em;'>";
    }

    public function getHTMLContent($portletData)
    {
        $settings = $portletData['settings'];
        $url = isset($settings['url']) ? $settings['url'] : '';
        $alt = isset($settings['alt']) ? $settings['alt'] : '';

        return "<img src=\"$url\" alt=\"$alt\">";
    }

    public function getSettingsHTML($settings)
    {
        return $this->oSmarty
            ->assign('settings', $settings)
            ->fetch('tpl_inc/portlets/settings.image.tpl');
    }

    public function getInitialSettings()
    {
        return [
            'url' => '',
            'alt' => '',
        ];
    }
}