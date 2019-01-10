<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use Plugin\Admin\Installation\Installer;
use Plugin\Helper;
use Plugin\InstallCode;
use Plugin\Plugin;

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
        if ($loader !== null) {
            $plugin = $loader->init($pluginID, true);
            $this->installer->setPlugin($plugin);
            $this->installer->setDir($plugin->getPaths()->getBaseDir());

            return $this->installer->prepare();
        }

        return InstallCode::NO_PLUGIN_FOUND;
    }
}
