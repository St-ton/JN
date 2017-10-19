<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class WidgetClock
 */
class PortletHeading extends PortletBase
{
    public function getPreviewContent($settings = null)
    {
        $level = isset($settings['level']) ? (int)$settings['level'] : 1;
        $text = isset($settings['text']) ? $settings['text'] : 'Heading Title';

        if ($level < 1 || $level > 6) {
            $level = 1;
        }

        return "<h$level>$text</h$level>";
    }

    public function getHTMLContent()
    {
        return '<h1>Heading</h1>';
    }

    public function getSettingsHTML($settings)
    {
        return $this->oSmarty
            ->assign('settings', $settings)
            ->fetch('tpl_inc/portlets/settings.heading.tpl');
    }

    public function getInitialSettings()
    {
        return [
            'level' => 1,
            'text' => 'Heading Title',
        ];
    }
}