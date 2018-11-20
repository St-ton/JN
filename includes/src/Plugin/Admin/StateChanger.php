<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use Plugin\Admin\Validation\Shop4Validator;
use Plugin\Admin\Validation\ValidatorInterface;
use Plugin\ExtensionLoader;
use Plugin\Helper;
use Plugin\InstallCode;
use Plugin\Plugin;
use Plugin\PluginLoader;
use Plugin\State;

/**
 * Class StateChanger
 * @package Plugin\Admin
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
     * @var Shop4Validator
     */
    private $validator;

    /**
     * @var ValidatorInterface
     */
    protected $modernValidator;

    /**
     * StateChanger constructor.
     * @param DbInterface             $db
     * @param JTLCacheInterface       $cache
     * @param ValidatorInterface|null $validator
     * @param ValidatorInterface|null $modernValidator
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ValidatorInterface $validator = null,
        ValidatorInterface $modernValidator = null
    ) {
        $this->db              = $db;
        $this->cache           = $cache;
        $this->validator       = $validator;
        $this->modernValidator = $modernValidator;
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
        $path       = \PFAD_ROOT . \PFAD_PLUGIN;
        $validation = $this->validator->validateByPath($path . $pluginData->cVerzeichnis);

        if ($validation === InstallCode::OK
            || $validation === InstallCode::DUPLICATE_PLUGIN_ID
            || $validation === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE
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
                $loader = new ExtensionLoader($this->db, $this->cache);
            } else {
                $loader = new PluginLoader($this->db, $this->cache);
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
            $loader = new ExtensionLoader($this->db, $this->cache);
        } else {
            $loader = new PluginLoader($this->db, $this->cache);
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
     * @param Plugin $plugin
     * @param bool   $forceReload
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
            $uninstaller = new Uninstaller($this->db);
            $installer   = new Installer($this->db, $uninstaller, $this->validator, $this->modernValidator);
            $installer->setDir($plugin->getPaths()->getBaseDir());
            $installer->setPlugin($plugin);

            return $installer->prepare();
        }

        return 200; // kein Reload nötig, da info file älter als dZuletztAktualisiert
    }
}
