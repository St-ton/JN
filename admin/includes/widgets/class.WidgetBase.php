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
     * @var Smarty\JTLSmarty
     */
    public $oSmarty;

    /**
     * @var \DB\DbInterface
     */
    public $oDB;

    /**
     * @var Plugin
     */
    public $oPlugin;

    /**
     * @param Smarty\JTLSmarty $oSmarty
     * @param \DB\DbInterface  $oDB
     * @param Plugin           $oPlugin
     */
    public function __construct($oSmarty = null, $oDB = null, &$oPlugin = null)
    {
        $this->oSmarty = $oSmarty ?? Shop::Smarty();
        $this->oDB     = $oDB ?? Shop::Container()->getDB();
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
