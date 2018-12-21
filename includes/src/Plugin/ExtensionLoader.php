<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\NiceDB;
use L10n\GetText;

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
     * @inheritdoc
     */
    public function init(int $id, bool $invalidateCache = false)
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
     * @inheritdoc
     */
    public function loadFromObject($obj): Extension
    {
        $id    = (int)$obj->kPlugin;
        $paths = $this->loadPaths($obj->cVerzeichnis);

        $extension = new Extension();
        $extension->setIsExtension(true);
        $extension->setMeta($this->loadMetaData($obj));
        $extension->setPaths($paths);
        $this->loadMarkdownFiles($paths->getBasePath(), $extension->getMeta());
        $extension->setState((int)$obj->nStatus);
        $extension->setID($id);
        $extension->setBootstrap(true);
        $extension->setLinks($this->loadLinks($id));
        $extension->setPluginID($obj->cPluginID);
        $extension->setPriority((int)$obj->nPrio);
        $extension->setLicense($this->loadLicense($obj));
        $extension->setCache($this->loadCacheData($extension));
        \Shop::Container()->getGetText()->loadPluginLocale($obj->cPluginID, $extension);
        $extension->setConfig($this->loadConfig($paths->getAdminPath(), $extension->getID()));
        $extension->setLocalization($this->loadLocalization($id));
        $extension->setWidgets($this->loadWidgets($extension));
        $extension->setMailTemplates($this->loadMailTemplates($extension));
        $extension->setPaymentMethods($this->loadPaymentMethods($extension));

        $this->loadAdminMenu($extension);

        return $extension;
    }
}
