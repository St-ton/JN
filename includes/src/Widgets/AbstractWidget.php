<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

use DB\DbInterface;
use Plugin\AbstractPlugin;
use Plugin\PluginInterface;
use Smarty\ContextType;
use Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 * @package Widgets
 */
abstract class AbstractWidget implements WidgetInterface
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
     * @var PluginInterface
     */
    public $oPlugin;

    /**
     * @var bool
     */
    public $hasBody = true;

    /**
     * @inheritdoc
     */
    public function __construct(JTLSmarty $smarty = null, DbInterface $db = null, $oPlugin = null)
    {
        $this->oSmarty = $smarty ?? \Shop::Smarty(false, ContextType::BACKEND);
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
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->oDB;
    }

    /**
     * @param DbInterface $oDB
     */
    public function setDB(DbInterface $oDB): void
    {
        $this->oDB = $oDB;
    }

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->oPlugin;
    }

    /**
     * @param PluginInterface $oPlugin
     */
    public function setPlugin(PluginInterface $oPlugin): void
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
