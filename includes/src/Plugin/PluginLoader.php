<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Hook;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Paths;
use Plugin\ExtensionData\Widget;

/**
 * Class PluginLoader
 * @package Plugin
 */
class PluginLoader extends AbstractLoader
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var string
     */
    private $basePath = \PFAD_ROOT . \PFAD_PLUGIN;

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
     * @param Plugin $plugin
     * @return PluginLoader
     */
    public function setPlugin(Plugin $plugin): self
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init(int $id, bool $invalidateCache = false)
    {
        if ($this->plugin === null) {
            $this->plugin = new Plugin();
        }
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id;// . '_' . \Shop::getLanguageID();
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        }
//        elseif (($plugin = $this->cache->get($this->cacheID)) !== false) {
//            foreach (\get_object_vars($plugin) as $k => $v) {
//                $this->plugin->$k = $v;
//            }
//
//            return $this->plugin;
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
    public function loadFromObject($obj): Plugin
    {
        $this->plugin->setID((int)$obj->kPlugin);
        $this->plugin->setPluginID($obj->cPluginID);
        $this->plugin->setState((int)$obj->nStatus);
        $this->plugin->setPriority((int)$obj->nPrio);
        $this->plugin->setBootstrap((int)$obj->bBootstrap === 1);
        $this->plugin->setIsExtension(isset($obj->bExtension) && (int)$obj->bExtension === 1);

        $this->plugin->setMeta($this->loadMetaData($obj));
        $this->plugin->setLicense($this->loadLicense($obj));
        $this->plugin->setLinks(new Links());

        $this->plugin->setCache($this->loadCacheData($this->plugin));

        $this->basePath = \PFAD_ROOT . \PFAD_PLUGIN;

        $this->plugin->setPaths($this->loadPaths($obj->cVerzeichnis));
        $this->plugin->oPluginHook_arr = $this->loadHooks((int)$obj->kPlugin);
        $this->loadMarkdownFiles($this->plugin->getPaths()->getBasePath(), $this->plugin->getMeta());
        $this->loadAdminMenu($this->plugin);
        $this->plugin->setConfig($this->loadConfig($this->plugin->getPaths()->getAdminPath(), $this->plugin->getID()));
        $this->plugin->setLocalization($this->loadLocalization($this->plugin->getID()));
        $this->plugin->setLinks($this->loadLinks($this->plugin->getID()));
        $this->plugin->setWidgets($this->loadWidgets($this->plugin));
        $this->plugin->setPaymentMethods($this->loadPaymentMethods($this->plugin));
        $this->plugin->setMailTemplates($this->loadMailTemplates($this->plugin));
        $this->cache();

        return $this->plugin;
    }

    /**
     * @param AbstractExtension $extension
     * @return Widget
     */
    protected function loadWidgets(AbstractExtension $extension): Widget
    {
        $widgets = parent::loadWidgets($extension);
        foreach ($widgets->getWidgets() as $widget) {
            $widget->className = \str_replace($widget->namespace, 'Widget', $widget->className);
            $widget->namespace = null;
            $widget->classFile = \str_replace(
                PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_WIDGET,
                PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_WIDGET . 'class.Widget',
                $widget->classFile
            );
        }
        return $widgets;
    }

    /**
     * @param int $id
     * @return array
     */
    protected function loadHooks(int $id): array
    {
        $hooks = \array_map(function ($data) {
            $hook = new Hook();
            $hook->setPriority((int)$data->nPriority);
            $hook->setFile($data->cDateiname);
            $hook->setID((int)$data->nHook);
            $hook->setPluginID((int)$data->kPlugin);

            return $hook;
        }, $this->db->selectAll('tpluginhook', 'kPlugin', $id));

        return $hooks;
    }

    /**
     * @return bool
     */
    protected function cache(): bool
    {
        return $this->cacheID !== null
            ? $this->cache->set($this->cacheID, $this->plugin, [\CACHING_GROUP_PLUGIN, $this->plugin->pluginCacheGroup])
            : false;
    }

    /**
     * @inheritdoc
     */
    protected function loadPaths(string $pluginDir): Paths
    {
        $paths     = parent::loadPaths($pluginDir);
        $shopURL   = $paths->getShopURL();
        $basePath  = \PFAD_ROOT . \PFAD_PLUGIN . $pluginDir . '/';
        $versioned = \PFAD_PLUGIN_VERSION . $this->plugin->getMeta()->getVersion() . '/';
        $baseURL   = $shopURL . \PFAD_PLUGIN . $pluginDir . '/';

        $paths->setBaseDir($pluginDir);
        $paths->setBasePath($basePath);
        $paths->setVersionedPath($basePath . $versioned);
        $paths->setFrontendPath($basePath . $versioned . \PFAD_PLUGIN_FRONTEND);
        $paths->setFrontendURL($baseURL . $versioned . \PFAD_PLUGIN_FRONTEND);
        $paths->setAdminPath($basePath . $versioned . \PFAD_PLUGIN_ADMINMENU);
        $paths->setAdminURL($baseURL . $versioned . \PFAD_PLUGIN_ADMINMENU);
        $paths->setLicencePath($basePath . $versioned . \PFAD_PLUGIN_LICENCE);
        $paths->setUninstaller($basePath . $versioned . \PFAD_PLUGIN_UNINSTALL);
        $paths->setPortletsPath($basePath . $versioned . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS);
        $paths->setExportPath($basePath . $versioned . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS);

        return $paths;
    }

    /**
     * @inheritdoc
     */
    protected function loadConfig(string $path, int $id): Config
    {
        $config       = parent::loadConfig($path, $id);
        $assocCompat  = [];
        $confCompat   = [];
        $configCompat = [];
        foreach ($config->getOptions()->toArray() as $option) {
            $assocCompat[$option->valueID] = $option->value;

            $configCompatItem          = new \stdClass();
            $configCompatItem->kPlugin = $id;
            $configCompatItem->cName   = $option->niceName;
            $configCompatItem->cWert   = $option->value;
            $configCompat[]            = $configCompatItem;

            $confCompatItem                                    = new \stdClass();
            $confCompatItem->kPluginEinstellungenConf          = $option->id;
            $confCompatItem->kPlugin                           = $id;
            $confCompatItem->kPluginAdminMenu                  = $option->menuID;
            $confCompatItem->cName                             = $option->niceName;
            $confCompatItem->cBeschreibung                     = $option->description;
            $confCompatItem->cWertName                         = $option->valueID;
            $confCompatItem->cInputTyp                         = $option->inputType;
            $confCompatItem->nSort                             = $option->sort;
            $confCompatItem->cConf                             = $option->confType;
            $confCompatItem->cSourceFile                       = $option->sourceFile;
            $confCompatItem->oPluginEinstellungenConfWerte_arr = $option->options;
            $confCompat[]                                      = $confCompatItem;
        }
        $this->plugin->oPluginEinstellung_arr      = $configCompat;
        $this->plugin->oPluginEinstellungAssoc_arr = $assocCompat;
        $this->plugin->oPluginEinstellungConf_arr  = $confCompat;

        return $config;
    }
}
