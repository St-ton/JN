<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Data\Hook;
use JTL\Plugin\Data\Links;
use JTL\Plugin\Data\Paths;
use JTL\Plugin\Data\Widget;
use JTL\Shop;
use JTL\Sprache;
use stdClass;

/**
 * Class LegacyPluginLoader
 * @package JTL\Plugin
 */
class LegacyPluginLoader extends AbstractLoader
{
    /**
     * @var LegacyPlugin
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
     * @param LegacyPlugin $plugin
     * @return LegacyPluginLoader
     */
    public function setPlugin(LegacyPlugin $plugin): self
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init(int $id, bool $invalidateCache = false, int $languageID = null)
    {
        if (($languageID = $languageID ?? Shop::getLanguageID()) === 0) {
            $languageID = Shop::Lang()::getDefaultLanguage()->kSprache;
        }
        $languageCode  = Shop::Lang()->getIsoFromLangID($languageID)->cISO;
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id . '_' . $languageID;
        if ($this->plugin === null) {
            $this->plugin = new LegacyPlugin();
        }
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id . '_' . $languageID;
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        } elseif (($plugin = $this->loadFromCache()) !== null) {
            $this->plugin = $plugin;

            return $this->plugin;
        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        if ($obj === null) {
            throw new InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }

        return $this->loadFromObject($obj, $languageCode);
    }

    /**
     * @inheritdoc
     */
    public function saveToCache(PluginInterface $extension): bool
    {
        return $this->cacheID !== null
            ? $this->cache->set($this->cacheID, $extension, [\CACHING_GROUP_PLUGIN, $extension->getCache()->getGroup()])
            : false;
    }

    /**
     * @inheritdoc
     */
    public function loadFromCache(): ?PluginInterface
    {
        return ($plugin = $this->cache->get($this->cacheID)) === false ? null : $plugin;
    }

    /**
     * @inheritdoc
     */
    public function loadFromObject($obj, string $currentLanguageCode): LegacyPlugin
    {
        $currentLanguageCode = $currentLanguageCode
            ?? Shop::getLanguageCode()
            ?? Sprache::getDefaultLanguage()->cISO;

        Shop::Container()->getGetText();

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
        $this->plugin->setLocalization($this->loadLocalization($this->plugin->getID(), $currentLanguageCode));
        $this->plugin->setLinks($this->loadLinks($this->plugin->getID()));
        $this->plugin->setWidgets($this->loadWidgets($this->plugin));
        $this->plugin->setPaymentMethods($this->loadPaymentMethods($this->plugin));
        $this->plugin->setMailTemplates($this->loadMailTemplates($this->plugin));
        $this->saveToCache($this->plugin);

        return $this->plugin;
    }

    /**
     * @param PluginInterface $extension
     * @return Widget
     */
    protected function loadWidgets(PluginInterface $extension): Widget
    {
        $widgets = parent::loadWidgets($extension);
        foreach ($widgets->getWidgets() as $widget) {
            $widget->className = \str_replace($widget->namespace, 'Widget', $widget->className);
            $widget->namespace = null;
            $widget->classFile = \str_replace(
                \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET,
                \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET . 'class.Widget',
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
        $paths->setExportPath($basePath . $versioned . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT);

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

            $configCompatItem          = new stdClass();
            $configCompatItem->kPlugin = $id;
            $configCompatItem->cName   = $option->valueID;
            $configCompatItem->cWert   = $option->value;
            $configCompat[]            = $configCompatItem;

            $confCompatItem                                    = new stdClass();
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
