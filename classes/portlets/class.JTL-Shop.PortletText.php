<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WidgetClock
 */
class PortletText extends CMSPortlet
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
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.text.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'text' => 'ein neuer Abschnitt',
        ];
    }
}