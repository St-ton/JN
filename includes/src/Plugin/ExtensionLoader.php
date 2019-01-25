<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;

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
    public function init(int $id, bool $invalidateCache = false, int $languageID = null)
    {
        if (($languageID = $languageID ?? \Shop::getLanguageID()) === 0) {
            $languageID = \Shop::Lang()->getDefaultLanguage()->kSprache;
        }
        $languageCode  = \Shop::Lang()->getIsoFromLangID($languageID)->cISO;
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id . '_' . $languageID;
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        } elseif (($extension = $this->loadFromCache()) !== null) {
            $getText = \Shop::Container()->getGetText();
            $getText->setLangIso($_SESSION['AdminAccount']->cISO ?? $languageCode);
            $getText->loadPluginLocale($extension->getPluginID(), $extension);

            return $extension;
        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        if ($obj === null) {
            throw new \InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }

        return $this->loadFromObject($obj, $languageCode);
    }

    /**
     * @inheritdoc
     */
    public function loadFromCache(): ?AbstractExtension
    {
        return ($extension = $this->cache->get($this->cacheID)) === false ? null : $extension;
    }

    /**
     * @inheritdoc
     */
    public function saveToCache(AbstractExtension $extension): bool
    {
        return $this->cacheID !== null
            ? $this->cache->set($this->cacheID, $extension, [\CACHING_GROUP_PLUGIN, $extension->getCache()->getGroup()])
            : false;
    }

    /**
     * @inheritdoc
     */
    public function loadFromObject($obj, string $currentLanguageCode): Extension
    {
        $id        = (int)$obj->kPlugin;
        $paths     = $this->loadPaths($obj->cVerzeichnis);
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
        $getText = \Shop::Container()->getGetText();
        $getText->setLangIso($_SESSION['AdminAccount']->cISO ?? $currentLanguageCode);
        $getText->loadPluginLocale($obj->cPluginID, $extension);
        $extension->setConfig($this->loadConfig($paths->getAdminPath(), $extension->getID()));
        $extension->setLocalization($this->loadLocalization($id, $currentLanguageCode));
        $extension->setWidgets($this->loadWidgets($extension));
        $extension->setMailTemplates($this->loadMailTemplates($extension));
        $extension->setPaymentMethods($this->loadPaymentMethods($extension));

        $this->loadAdminMenu($extension);
        $this->saveToCache($extension);

        return $extension;
    }
}
