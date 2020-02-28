<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Widgets;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 * @package JTL\Widgets
 */
interface WidgetInterface
{
    /**
     * @param JTLSmarty       $smarty
     * @param DbInterface     $db
     * @param PluginInterface $plugin
     */
    public function __construct(JTLSmarty $smarty = null, DbInterface $db = null, $plugin = null);

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
     * @param PluginInterface $plugin
     */
    public function setPlugin(PluginInterface $plugin): void;

    /**
     *
     */
    public function init();

    /**
     * @return string
     */
    public function getContent();
}