<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPlugin;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;

/**
 * Class StateChanger
 * @package JTL\Plugin\Admin
 */
class StateChanger
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var ValidatorInterface|LegacyPluginValidator
     */
    private $legacyValidator;

    /**
     * @var ValidatorInterface|PluginValidator
     */
    protected $pluginValidator;

    /**
     * StateChanger constructor.
     * @param DbInterface             $db
     * @param JTLCacheInterface       $cache
     * @param ValidatorInterface|null $legacyValidator
     * @param ValidatorInterface|null $pluginValidator
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ValidatorInterface $legacyValidator = null,
        ValidatorInterface $pluginValidator = null
    ) {
        $this->db              = $db;
        $this->cache           = $cache;
        $this->legacyValidator = $legacyValidator;
        $this->pluginValidator = $pluginValidator;
    }

    /**
     * Versucht ein ausgewähltes Plugin zu aktivieren
     *
     * @param int $pluginID
     * @return int
     * @former aktivierePlugin()
     */
    public function activate(int $pluginID): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $pluginData = $this->db->select('tplugin', 'kPlugin', $pluginID);
        if (empty($pluginData->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if ((int)$pluginData->bExtension === 1) {
            $path       = \PFAD_ROOT . \PLUGIN_DIR;
            $validation = $this->pluginValidator->validateByPath($path . $pluginData->cVerzeichnis);
        } else {
            $path       = \PFAD_ROOT . \PFAD_PLUGIN;
            $validation = $this->legacyValidator->validateByPath($path . $pluginData->cVerzeichnis);
        }
        if ($validation === InstallCode::OK
            || $validation === InstallCode::OK_LEGACY
            || $validation === InstallCode::DUPLICATE_PLUGIN_ID
        ) {
            $affectedRow = $this->db->update(
                'tplugin',
                'kPlugin',
                $pluginID,
                (object)['nStatus' => State::ACTIVATED]
            );
            $this->db->update('tadminwidgets', 'kPlugin', $pluginID, (object)['bActive' => 1]);
            $this->db->update('tlink', 'kPlugin', $pluginID, (object)['bIsActive' => 1]);
            $this->db->update('topcportlet', 'kPlugin', $pluginID, (object)['bActive' => 1]);
            $this->db->update('topcblueprint', 'kPlugin', $pluginID, (object)['bActive' => 1]);
            if ((int)$pluginData->bExtension === 1) {
                $loader = new PluginLoader($this->db, $this->cache);
            } else {
                $loader = new LegacyPluginLoader($this->db, $this->cache);
            }

            if (($p = Helper::bootstrap($pluginID, $loader)) !== null) {
                $p->enabled();
            }

            return $affectedRow > 0
                ? InstallCode::OK
                : InstallCode::NO_PLUGIN_FOUND;
        }

        return $validation;
    }

    /**
     * Versucht ein ausgewähltes Plugin zu deaktivieren
     *
     * @param int $pluginID
     * @return int
     * @former deaktivierePlugin()
     */
    public function deactivate(int $pluginID): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $pluginData = $this->db->select('tplugin', 'kPlugin', $pluginID);
        if ((int)$pluginData->bExtension === 1) {
            $loader = new PluginLoader($this->db, $this->cache);
        } else {
            $loader = new LegacyPluginLoader($this->db, $this->cache);
        }
        if (($p = Helper::bootstrap($pluginID, $loader)) !== null) {
            $p->disabled();
        }
        $this->db->update('tplugin', 'kPlugin', $pluginID, (object)['nStatus' => State::DISABLED]);
        $this->db->update('tadminwidgets', 'kPlugin', $pluginID, (object)['bActive' => 0]);
        $this->db->update('tlink', 'kPlugin', $pluginID, (object)['bIsActive' => 0]);
        $this->db->update('topcportlet', 'kPlugin', $pluginID, (object)['bActive' => 0]);
        $this->db->update('topcblueprint', 'kPlugin', $pluginID, (object)['bActive' => 0]);

        $this->cache->flushTags([\CACHING_GROUP_PLUGIN . '_' . $pluginID]);

        return InstallCode::OK;
    }


    /**
     * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
     *
     * @param LegacyPlugin $plugin
     * @param bool         $forceReload
     * @throws \Exception
     * @return int
     * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
     * siehe return Codes von installierePluginVorbereitung()
     * @former reloadPlugin()
     */
    public function reload($plugin, $forceReload = false): int
    {
        $info = $plugin->getPaths()->getBasePath() . \PLUGIN_INFO_FILE;
        if (!\file_exists($info)) {
            return -1;
        }
        $lastUpdate    = $plugin->getMeta()->getDateLastUpdate();
        $lastXMLChange = \filemtime($info);
        if ($forceReload === true || $lastXMLChange > $lastUpdate->getTimestamp()) {
            $uninstaller = new Uninstaller($this->db, $this->cache);
            $installer   = new Installer($this->db, $uninstaller, $this->legacyValidator, $this->pluginValidator);
            $installer->setDir($plugin->getPaths()->getBaseDir());
            $installer->setPlugin($plugin);

            return $installer->prepare();
        }

        return 200; // kein Reload nötig, da info file älter als dZuletztAktualisiert
    }
}
