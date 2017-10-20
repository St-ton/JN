<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class WidgetClock
 */
class PortletParagraph_jtl_portlets extends PortletBase
{
    public function getPreviewContent($settings = null)
    {
        $text = isset($settings['text']) ? $settings['text'] : 'ein neuer Abschnitt';

        return "<div>$text</div>";
    }

    public function getHTMLContent($portletData)
    {
        $settings = $portletData['settings'];

        return "<div>" . $settings['text'] . "</div>";
    }

    public function getSettingsHTML($settings)
    {
        return $this->oSmarty
            ->assign('settings', $settings)
            ->fetch(__DIR__ . '/portletParagraphSettings.tpl');
    }

    public function getInitialSettings()
    {
        return [
            'text' => 'ein neuer Abschnitt',
        ];
    }
}