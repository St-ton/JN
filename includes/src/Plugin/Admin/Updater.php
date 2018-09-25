<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;

/**
 * Class Updater
 * @package Plugin\Admin
 */
class Updater
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * Updater constructor.
     * @param DbInterface $db
     * @param Installer   $installer
     */
    public function __construct(DbInterface $db, Installer $installer)
    {
        $this->db        = $db;
        $this->installer = $installer;
    }

    /**
     * Versucht ein ausgewähltes Plugin zu updaten
     *
     * @param int $kPlugin
     * @return int
     */
    public function updatePlugin(int $kPlugin): int
    {
        if ($kPlugin <= 0) {
            return \Plugin\InstallCode::WRONG_PARAM;
        }
        $oPluginTMP = $this->db->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
            $oPlugin = new \Plugin($oPluginTMP->kPlugin);
            $this->installer->setPlugin($oPlugin);
            $this->installer->setDir($oPlugin->cVerzeichnis);

            return $this->installer->installierePluginVorbereitung();
        }

        return \Plugin\InstallCode::NO_PLUGIN_FOUND;
    }
}
