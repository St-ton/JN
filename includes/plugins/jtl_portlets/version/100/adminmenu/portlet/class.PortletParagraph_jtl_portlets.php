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
    public function getPreviewHtml()
    {
        $text = $this->properties['text'];

        return "<div>$text</div>";
    }

    public function getFinalHtml()
    {
        return $this->getPreviewHtml();
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch(__DIR__ . '/portletParagraphSettings.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'text' => 'ein neuer Abschnitt',
        ];
    }
}