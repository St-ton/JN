<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class PluginLoader
 * @package JTL\Plugin
 */
class PluginLoader extends AbstractLoader
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
        if (($languageID = $languageID ?? Shop::getLanguageID()) === 0) {
            $languageID = Shop::Lang()->getDefaultLanguage()->kSprache;
        }
        $languageCode  = Shop::Lang()->getIsoFromLangID($languageID)->cISO;
        $languageTag   = $_SESSION['AdminAccount']->language ?? 'de-DE';
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id . '_' . $languageID . '_' . $languageTag;
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        } elseif (($extension = $this->loadFromCache()) !== null) {
            $getText = Shop::Container()->getGetText();
            $getText->setLanguage($languageTag);
            $getText->loadPluginLocale('base', $extension);

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
    public function loadFromCache(): ?PluginInterface
    {
        return ($extension = $this->cache->get($this->cacheID)) === false ? null : $extension;
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
    public function loadFromObject($obj, string $currentLanguageCode): Plugin
    {
        $id        = (int)$obj->kPlugin;
        $paths     = $this->loadPaths($obj->cVerzeichnis);
        $extension = new Plugin();
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
        $getText = Shop::Container()->getGetText();
        $getText->setLanguage($_SESSION['AdminAccount']->language ?? 'de-DE');
        $getText->loadPluginLocale('base', $extension);
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
