<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class WidgetClock
 */
class PortletColumn extends PortletBase
{
    public function getPreviewContent()
    {
        return htmlspecialchars('<div class="row"><div class="col-xs-6 jle-editable"></div><div class="col-xs-6 jle-editable"></div></div>');
    }

    public function getHTMLContent()
    {
        return htmlspecialchars('<div class="row"><div class="col-xs-6 jle-editable"></div><div class="col-xs-6 jle-editable"></div></div>');
    }
}