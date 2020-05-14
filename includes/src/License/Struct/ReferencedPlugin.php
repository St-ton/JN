<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTL\DB\DbInterface;
use JTL\Plugin\State;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class ReferencedPlugin
 * @package JTL\License\Struct
 */
class ReferencedPlugin extends ReferencedItem
{
    /**
     * ReferencedPlugin constructor.
     * @param DbInterface $db
     * @param stdClass    $license
     * @param Release     $release
     */
    public function __construct(DbInterface $db, stdClass $license, Release $release)
    {
        $installed = $db->select('tplugin', 'exsID', $license->exsid);
        if ($installed !== null) {
            $installedVersion = Version::parse($installed->nVersion);
            $this->setID($installed->cPluginID);
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
            $this->setActive((int)$installed->nStatus === State::ACTIVATED);
        }
    }
}
