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
     * @param string      $exsid
     * @param Release     $release
     */
    public function __construct(DbInterface $db, string $exsid, Release $release)
    {
        $installed = $db->select('tplugin', 'exsID', $exsid);
        if ($installed !== null) {
            $installedVersion = Version::parse($installed->nVersion);
            $this->setID($installed->cPluginID);
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
        }
    }
}
