<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;

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
            '_' . \Shop::getLanguage();
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
    public function loadFromObject(\stdClass $obj): Extension
    {
        $paths = $this->loadPaths($obj->cVerzeichnis);

        $extension = new Extension();
        $extension->setMeta($this->loadMetaData($obj));
        $extension->setPaths($paths);
        $extension->setState((int)$obj->nStatus);
        $extension->setID((int)$obj->kPlugin);
        $extension->setLinks($this->loadLinks((int)$obj->kPlugin));
        $extension->setPluginID($obj->cPluginID);
        $extension->setPriority((int)$obj->nPrio);
        $extension->setVersion($obj->nVersion);
        $extension->setConfig($this->loadConfig($paths->getAdminPath(), $extension->getID()));

        return $extension;
    }

    /**
     * @param int $id
     * @return Links
     */
    public function loadLinks(int $id): Links
    {
        $data  = $this->db->queryPrepared(
            "SELECT tlink.kLink
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn
                GROUP BY tlink.kLink",
            ['plgn' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $links = new Links();

        return $links->load($data);
    }

    /**
     * @param \stdClass $obj
     * @return Meta
     */
    private function loadMetaData(\stdClass $obj): Meta
    {
        $metaData = new Meta();

        return $metaData->loadDBMapping($obj);
    }

    /**
     * @param string $path
     * @param int    $id
     * @return Config
     */
    private function loadConfig(string $path, int $id): Config
    {
        $data   = $this->db->queryPrepared(
            'SELECT c.kPluginEinstellungenConf AS id, c.cName AS name,
            c.cBeschreibung AS description, c.kPluginAdminMenu AS menuID, c.cConf AS confType,
            c.nSort, c.cInputTyp AS inputType, c.cSourceFile AS sourceFile,
            v.cName AS confNicename, v.cWert AS confValue, v.nSort AS confSort, e.cWert AS currentValue,
            c.cWertName AS confName
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
        $shopURL  = \Shop::getURL(true) . '/';
        $basePath = \PFAD_ROOT . \PFAD_EXTENSIONS . $pluginDir . \DIRECTORY_SEPARATOR;
        $baseURL  = $shopURL . \PFAD_EXTENSIONS . $pluginDir . '/';

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
}
