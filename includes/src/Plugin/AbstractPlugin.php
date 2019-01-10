<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use Events\Dispatcher;

/**
 * Class AbstractPlugin
 * @package Plugin
 */
abstract class AbstractPlugin implements PluginInterface
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
     * @var Plugin
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
     * AbstractPlugin constructor.
     * @param AbstractExtension $plugin
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
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function (\Notification $notify) use (&$dispatcher) {
            $dispatcher->forget('backend.notification');
            foreach ($this->notifications as $n) {
                $notify->addNotify($n);
            }
        });
    }

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    final public function addNotify($type, $title, $description = null)
    {
        $this->notifications[] = (new \NotificationEntry($type, $title, $description))->setPluginId($this->pluginId);
    }

    /**
     *
     */
    public function installed()
    {
    }

    /**
     *
     */
    public function uninstalled()
    {
    }

    /**
     *
     */
    public function enabled()
    {
    }

    /**
     *
     */
    public function disabled()
    {
    }

    /**
     * @param mixed $oldVersion
     * @param mixed $newVersion
     */
    public function updated($oldVersion, $newVersion)
    {
    }

    /**
     * @return AbstractExtension
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }
}
