<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use Plugin\ExtensionData\Cache;

/**
 * Class ExtensionLoader
 * @package Plugin
 */
class ExtensionLoader extends AbstractLoader
{
    /**
     * PluginLoader constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @param int  $id
     * @param bool $invalidateCache
     * @return Extension
     */
    public function init(int $id, bool $invalidateCache = false): Extension
    {
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id .
            '_' . \Shop::getLanguageID();
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        }
//        elseif (($data = $this->cache->get($this->cacheID)) !== false) {
//            $extension = new Extension();
//            foreach (\get_object_vars($data) as $k => $v) {
//                $extension->$k = $v;
//            }
//
//            return $extension;
//        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        if ($obj === null) {
            throw new \InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }

        return $this->loadFromObject($obj);
    }

    /**
     * @param \stdClass $obj
     * @return Extension
     */
    public function loadFromObject($obj): Extension
    {
        $paths = $this->loadPaths($obj->cVerzeichnis);

        $extension = new Extension();
        $extension->setMeta($this->loadMetaData($obj));
        $extension->setPaths($paths);
        $this->loadMarkdownFiles($paths->getBasePath(), $extension->getMeta());
        $extension->setState((int)$obj->nStatus);
        $extension->setID((int)$obj->kPlugin);
        $extension->setBootstrap(true);
        $extension->setLinks($this->loadLinks((int)$obj->kPlugin));
        $extension->setPluginID($obj->cPluginID);
        $extension->setPriority((int)$obj->nPrio);
        $extension->setConfig($this->loadConfig($paths->getAdminPath(), $extension->getID()));
        $extension->setLicense($this->loadLicense($obj));

        $cache = new Cache();
        $cache->setGroup(\CACHING_GROUP_PLUGIN . '_' . $extension->getID());
        $cache->setID($cache->getGroup() . '_' . $extension->getMeta()->getVersion());
        $extension->setCache($cache);

        $this->loadAdminMenu($extension);

        return $extension;
    }
}
