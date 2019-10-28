<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Link\LinkInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Interface BootstrapperInterface
 * @package JTL\Plugin
 */
interface BootstrapperInterface
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher);

    /**
     * @return mixed
     */
    public function installed();

    /**
     * @param bool $deleteData
     * @return mixed
     */
    public function uninstalled(bool $deleteData = true);

    /**
     * @return mixed
     */
    public function enabled();

    /**
     * @return mixed
     */
    public function disabled();

    /**
     * @param mixed $oldVersion
     * @param mixed $newVersion
     * @return mixed
     */
    public function updated($oldVersion, $newVersion);

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    public function addNotify($type, $title, $description = null): void;

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface;

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void;

    /**
     * @param string    $tabName
     * @param int       $menuID
     * @param JTLSmarty $smarty
     * @return string
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string;

    /**
     * @param LinkInterface $link
     * @param JTLSmarty     $smarty
     * @return bool
     */
    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool;

    /**
     * @return int
     */
    public function loaded(): int;
}
