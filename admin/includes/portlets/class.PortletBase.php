<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WidgetBase
 */
class PortletBase
{
    /**
     * @var JTLSmarty
     */
    public $oSmarty;

    /**
     * @var NiceDB
     */
    public $oDB;

    /**
     * @var Plugin
     */
    public $oPlugin;

    /**
     * @param JTLSmarty $oSmarty
     * @param NiceDB    $oDB
     * @param Plugin    $oPlugin
     */
    public function __construct($oSmarty = null, $oDB = null, &$oPlugin)
    {
        $this->oSmarty = Shop::Smarty();
        $this->oDB     = Shop::DB();
        $this->oPlugin = $oPlugin;
    }

    /**
     * @return string
     */
    public function getPreviewContent()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getHTMLContent()
    {
        return '';
    }
}
