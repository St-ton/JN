<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTLShop\SemVer\Version;

/**
 * Class Plugin
 * @package JTL\License\Struct
 */
interface ReferencedItemInterface
{
    /**
     * @return string
     */
    public function getID(): string;

    /**
     * @param string $id
     */
    public function setID(string $id): void;

    /**
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * @param bool $installed
     */
    public function setInstalled(bool $installed): void;

    /**
     * @return Version|null
     */
    public function getInstalledVersion(): ?Version;

    /**
     * @param Version|null $installedVersion
     */
    public function setInstalledVersion(?Version $installedVersion): void;

    /**
     * @return Version|null
     */
    public function getMaxInstallableVersion(): ?Version;

    /**
     * @param Version|null $maxInstallableVersion
     */
    public function setMaxInstallableVersion(?Version $maxInstallableVersion): void;

    /**
     * @return bool
     */
    public function hasUpdate(): bool;

    /**
     * @param bool $hasUpdate
     */
    public function setHasUpdate(bool $hasUpdate): void;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void;
}
