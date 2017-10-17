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
    public function getPreviewContent()
    {
        return htmlspecialchars('<p class="jle-editable">ein neuer Paragraph</p>');
    }

    public function getHTMLContent()
    {
        return htmlspecialchars('<p class="jle-editable">ein neuer Paragraph</p>');
    }
}