<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;

/**
 * Class ExtensionLoader
 * @package Plugin
 */
class ExtensionLoader
{
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
     * @var string
     */
    private $cacheID;

    /**
     * @var MetaData
     */
    private $metaData;

    /**
     * PluginLoader constructor.
     * @param Plugin            $plugin
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, MetaData $metaData)
    {
//        $this->plugin = $plugin;
        $this->db     = $db;
        $this->cache  = $cache;
        $this->metaData = $metaData;
    }

    /**
     * @param int  $id
     * @param bool $invalidateCache
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function init(int $id, bool $invalidateCache = false): bool
    {
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id .
            '_' . \RequestHelper::checkSSL() .
            '_' . \Shop::getLanguage();
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        }
//        elseif (($plugin = $this->cache->get($this->cacheID)) !== false) {
//            foreach (\get_object_vars($plugin) as $k => $v) {
//                $this->plugin->$k = $v;
//            }
//
//            return true;
//        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        \Shop::dbg($obj, false, 'loaded:');
        if ($obj === null) {
            throw new \InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }
        $extension = new Extension();

        $extension->setMeta($this->metaData->loadDBMapping($obj));
        $paths = $this->loadPaths($obj->cVerzeichnis);
        $extension->setPaths($paths);
        $extension->setState((int)$obj->nStatus);
        $extension->setID((int)$obj->kPlugin);
        $extension->setPluginID($obj->cPluginID);
        $extension->setPriority((int)$obj->nPrio);
        $extension->setVersion($obj->nVersion);
        $config = $this->loadConfig($paths->getAdminPath(), $extension->getID());
        \Shop::dbg($config, true, 'config:');
//        $extension->setConfig();
//        \Shop::dbg($this->loadHooks(), false, 'loaded hooks:');
        \Shop::dbg($extension, true, 'Extension:');

        return true;
    }

    /**
     * @param string $path
     * @param int    $id
     * @return array
     */
    private function loadConfig(string $path, int $id): array
    {
        $data = $this->db->queryPrepared(
            'SELECT c.kPluginEinstellungenConf AS id, c.cName AS name,
            c.cBeschreibung AS description, c.kPluginAdminMenu AS menuID, c.cConf AS confType,
            c.nSort, c.cInputTyp AS inputType, c.cSourceFile AS sourceFile,
            v.cName AS confName, v.cWert AS confValue, v.nSort AS confSort, e.cWert AS currentValue 
            FROM tplugineinstellungenconf AS c
            LEFT JOIN tplugineinstellungenconfwerte AS v
              ON c.kPluginEinstellungenConf = v.kPluginEinstellungenConf
            LEFT JOIN tplugineinstellungen AS e
			  ON e.kPlugin = c.kPlugin AND e.cName = c.cWertName
            WHERE c.kPlugin = :pid
            ORDER BY c.nSort',
            ['pid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $config = new Config($path);

        return $config->load($data);
    }

    /**
     * @param string $pluginDir
     * @return Paths
     */
    private function loadPaths(string $pluginDir): Paths
    {
        $shopURL                         = \Shop::getURL() . '/';
        $basePath                           = \PFAD_ROOT .
            'plugins' . \DIRECTORY_SEPARATOR .
            $pluginDir . \DIRECTORY_SEPARATOR;

        $baseURL = $shopURL . 'plugins/' . $pluginDir . '/';

        $paths = new Paths();
        $paths->setBasePath($basePath);
        $paths->setFrontendPath($basePath . \PFAD_PLUGIN_FRONTEND);
        $paths->setFrontendURL($baseURL . \PFAD_PLUGIN_FRONTEND);
        $paths->setAdminPath($basePath . \PFAD_PLUGIN_ADMINMENU);
        $paths->setAdminURL($baseURL . \PFAD_PLUGIN_ADMINMENU);
        $paths->setLicencePath($basePath . \PFAD_PLUGIN_LICENCE);

        return $paths;
    }

    /**
     * @return array
     */
    private function loadHooks(): array
    {
        $hooks = \array_map(function ($data) {
            $hook = new Hook();
            $hook->setPriority((int)$data->nPriority);
            $hook->setFile($data->cDateiname);
            $hook->setID((int)$data->nHook);
            $hook->setPluginID((int)$data->kPlugin);

            return $hook;
        }, $this->db->selectAll('tpluginhook', 'kPlugin', $this->plugin->kPlugin));

        return $hooks;
    }

    public function loadMetaData()
    {

    }
}
