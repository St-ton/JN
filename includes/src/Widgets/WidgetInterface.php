<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

use DB\DbInterface;
use Plugin\AbstractPlugin;
use Plugin\PluginInterface;
use Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 * @package Widgets
 */
interface WidgetInterface
{
    /**
     * @param JTLSmarty              $smarty
     * @param DbInterface            $db
     * @param \Plugin|\Plugin\Plugin $oPlugin
     */
    public function __construct(JTLSmarty $smarty = null, DbInterface $db = null, $oPlugin = null);

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty;

    /**
     * @param JTLSmarty $oSmarty
     */
    public function setSmarty(JTLSmarty $oSmarty): void;

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $oDB
     */
    public function setDB(DbInterface $oDB): void;

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface;

    /**
     * @param PluginInterface $oPlugin
     */
    public function setPlugin(PluginInterface $oPlugin): void;

    /**
     *
     */
    public function init();

    /**
     * @return string
     */
    public function getContent();
}
