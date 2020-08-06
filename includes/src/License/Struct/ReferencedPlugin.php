<?php declare(strict_types=1);

namespace JTL\License\Struct;

use Carbon\Carbon;
use Exception;
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
     * @param DbInterface  $db
     * @param stdClass     $license
     * @param Release|null $release
     */
    public function __construct(DbInterface $db, stdClass $license, ?Release $release)
    {
        $installed = $db->select('tplugin', 'exsID', $license->exsid);
        if ($installed !== null) {
            $installedVersion = Version::parse($installed->nVersion);
            $this->setID($installed->cPluginID);
            if ($release !== null) {
                $this->setMaxInstallableVersion($release->getVersion());
                $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            } else {
                $this->setMaxInstallableVersion(Version::parse('0.0.0'));
            }
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
            $this->setActive((int)$installed->nStatus === State::ACTIVATED);
            $this->setInternalID((int)$installed->kPlugin);
            try {
                $t             = new Carbon($installed->dInstalliert);
                $dateInstalled = $t->toIso8601ZuluString();
            } catch (Exception $e) {
                $dateInstalled = null;
            }
            $this->setDateInstalled($dateInstalled);
            $this->setInitialized(true);
        }
    }
}
