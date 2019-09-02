<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Smarty\JTLSmarty;

/**
 * Class Bootstrapper
 * @package JTL\Plugin
 */
abstract class Bootstrapper implements BootstrapperInterface
{
    /**
     * @var string
     */
    private $pluginId;

    /**
     * @var array
     */
    private $notifications = [];

    /**
     * @var LegacyPlugin
     */
    private $plugin;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Bootstrapper constructor.
     * @param PluginInterface   $plugin
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    final public function __construct($plugin, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->plugin   = $plugin;
        $this->pluginId = $plugin->getPluginID();
        $this->db       = $db;
        $this->cache    = $cache;
    }

    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function (Notification $notify) use (&$dispatcher) {
            $dispatcher->forget('backend.notification');
            foreach ($this->notifications as $n) {
                $notify->addNotify($n);
            }
        });
    }

    /**
     * @inheritdoc
     */
    final public function addNotify($type, $title, $description = null)
    {
        $this->notifications[] = (new NotificationEntry($type, $title, $description))->setPluginId($this->pluginId);
    }

    /**
     * @inheritdoc
     */
    public function installed()
    {
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true)
    {
    }

    /**
     * @inheritdoc
     */
    public function enabled()
    {
    }

    /**
     * @inheritdoc
     */
    public function disabled()
    {
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion)
    {
    }

    /**
     * @inheritdoc
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        return '';
    }
}
