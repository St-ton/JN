<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTL\DB\DbInterface;
use JTLShop\SemVer\Version;

/**
 * Class ReferencedPlugin
 * @package JTL\License\Struct
 */
class ReferencedPlugin extends ReferencedItem
{
    /**
     * ReferencedPlugin constructor.
     * @param DbInterface $db
     * @param string      $id
     * @param Release     $release
     */
    public function __construct(DbInterface $db, string $id, Release $release)
    {
        $this->setID($id);
        $installed = $db->select('tplugin', 'cPluginID', $id);
        if ($installed !== null) {
            $installedVersion = Version::parse($installed->nVersion);
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
        }
    }
}
