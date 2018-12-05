<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

use Plugin\AbstractExtension;
use Smarty\JTLSmarty;

/**
 * Class WidgetBase
 */
abstract class WidgetBase
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
