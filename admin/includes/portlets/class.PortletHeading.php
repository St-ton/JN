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
    public function getPreviewContent()
    {
        return '<h1>Heading</h1>';
    }

    public function getHTMLContent()
    {
        return '<h1>Heading</h1>';
    }
}