<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin;

use JTL\DB\DbInterface;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;

/**
 * Class Updater
 * @package JTL\Plugin\Admin
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
     * @param int $pluginID
     * @return int
     * @former updatePlugin()
     */
    public function update(int $pluginID): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $loader = Helper::getLoaderByPluginID($pluginID, $this->db);
        if ($loader === null) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $plugin = $loader->init($pluginID, true);
        $this->installer->setPlugin($plugin);
        $this->installer->setDir($plugin->getPaths()->getBaseDir());

        return $this->installer->prepare();
    }
}
