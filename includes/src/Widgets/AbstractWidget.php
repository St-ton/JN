<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

use Plugin\AbstractExtension;
use Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 *
 * @package Widgets
 */
abstract class AbstractWidget
{
    /**
     * @var JTLSmarty
     */
    public $oSmarty;

    /**
     * @var \DB\DbInterface
     */
    public $oDB;

    /**
     * @var AbstractExtension
     */
    public $oPlugin;

    /**
     * @var bool
     */
    public $hasBody = true;

    /**
     * @param \Smarty\JTLSmarty $smarty
     * @param \DB\DbInterface  $db
     * @param \Plugin|\Plugin\Extension $oPlugin
     */
    public function __construct($smarty = null, $db = null, &$oPlugin = null)
    {
        $this->oSmarty = $smarty ?? \Shop::Smarty();
        $this->oDB     = $db ?? \Shop::Container()->getDB();
        $this->oPlugin = $oPlugin;
        $this->init();
    }

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty
    {
        return $this->oSmarty;
    }

    /**
     * @param JTLSmarty $oSmarty
     */
    public function setSmarty(JTLSmarty $oSmarty): void
    {
        $this->oSmarty = $oSmarty;
    }

    /**
     * @return \DB\DbInterface
     */
    public function getDB(): \DB\DbInterface
    {
        return $this->oDB;
    }

    /**
     * @param \DB\DbInterface $oDB
     */
    public function setDB(\DB\DbInterface $oDB): void
    {
        $this->oDB = $oDB;
    }

    /**
     * @return AbstractExtension
     */
    public function getPlugin(): AbstractExtension
    {
        return $this->oPlugin;
    }

    /**
     * @param AbstractExtension $oPlugin
     */
    public function setPlugin(AbstractExtension $oPlugin): void
    {
        $this->oPlugin = $oPlugin;
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
