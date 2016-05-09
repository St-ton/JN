<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WidgetBase
 */
class WidgetBase
{
    /**
     * @var null|JTLSmarty
     */
    public $oSmarty = null;

    /**
     * @var null|NiceDB
     */
    public $oDB = null;

    /**
     * @var null|Plugin
     */
    public $oPlugin = null;

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
        $this->init();
    }

    /**
     *
     */
    public function init()
    {
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return '';
    }
}
