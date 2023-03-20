<?php

namespace JTL\Update;

use Exception;
use JTL\Network\JTLApi;
use JTL\Nice;
use JTL\Shop;
use JTLShop\SemVer\Version;
use JTLShop\SemVer\VersionCollection;
use stdClass;

class UpdaterForTestEnvironment extends Updater
{
    /**
     * @param Version $version
     * @return Version
     */
    public function getTargetVersion(Version $version): Version
    {
        $majors        = ['2.19' => Version::parse('3.00.0'), '3.20' => Version::parse('4.00.0')];
        $targetVersion = null;

        foreach ($majors as $preMajor => $major) {
            if ($version->equals(Version::parse($preMajor))) {
                $targetVersion = $major;
            }
        }

        if (empty($targetVersion)) {
            /** @var JTLApi $api */
            $api              = new JTLApi($_SESSION, Nice::getInstance());
            $availableUpdates = $api->getAvailableVersions(true) ?? [];
            foreach ($availableUpdates as $key => $availVersion) {
                try {
                    $availVersion->referenceVersion = Version::parse($availVersion->reference);
                } catch (Exception $e) {
                    unset($availableUpdates[$key]);
                }
            }
            // sort versions ascending
            \usort($availableUpdates, static function (stdClass $x, stdClass $y): int {
                /** @var Version $versionX */
                $versionX = $x->referenceVersion;
                /** @var Version $versionY */
                $versionY = $y->referenceVersion;
                if ($versionX->smallerThan($versionY)) {
                    return -1;
                }
                if ($versionX->greaterThan($versionY)) {
                    return 1;
                }

                return 0;
            });

            $versionCollection = new VersionCollection();
            foreach ($availableUpdates as $availableUpdate) {
                /** @var Version $referenceVersion */
                $referenceVersion = $availableUpdate->referenceVersion;
                if ($availableUpdate->isPublic === 0
                    && $referenceVersion->equals($this->getCurrentFileVersion()) === false
                ) {
                    continue;
                }
                $versionCollection->append($availableUpdate->reference);
            }

            $targetVersion = $version->smallerThan(Version::parse($this->getCurrentFileVersion()))
                ? $versionCollection->getNextVersion($version)
                : $version;

            // if target version is greater than file version: set file version as target version to avoid
            // mistakes with missing versions in the version list from the API (fallback)
            if ($targetVersion?->greaterThan($this->getCurrentFileVersion()) ?? false) {
                $targetVersion = Version::parse($this->getCurrentFileVersion());
            }
        }

        return $targetVersion ?? Version::parse(\APPLICATION_VERSION);
    }

}