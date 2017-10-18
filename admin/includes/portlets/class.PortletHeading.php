<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletSelectVariable.php';

/**
 * Class WidgetClock
 */
class PortletHeading extends PortletBase
{
    public function getPreviewContent($settings = null)
    {
        $level = isset($settings['level']) ? (int)$settings['level'] : 1;

        if ($level < 1 || $level > 6) {
            $level = 1;
        }

        $text = isset($settings['text']) ? $settings['text'] : 'Unnamed Heading';

        return "<h$level>$text</h$level>";
    }

    public function getHTMLContent()
    {
        return '<h1>Heading</h1>';
    }

    public function getSettingsHTML()
    {
        return $this->oSmarty->fetch('tpl_inc/portlets/settings.heading.tpl');
    }
}