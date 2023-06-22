<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTLShop\SemVer\Version;

/**
 * Class ReferencedItem
 * @package JTL\License\Struct
 */
abstract class ReferencedItem implements ReferencedItemInterface
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var bool
     */
    private bool $installed = false;

    /**
     * @var Version|null
     */
    private ?Version $installedVersion = null;

    /**
     * @var Version|null
     */
    private ?Version $maxInstallableVersion = null;

    /**
     * @var bool
     */
    private bool $hasUpdate = false;

    /**
     * @var bool
     */
    private bool $canBeUpdated = true;

    /**
     * @var bool
     */
    private bool $shopVersionOK = true;

    /**
     * @var bool
     */
    private bool $active = false;

    /**
     * @var int
     */
    private int $internalID = 0;

    /**
     * @var bool
     */
    private bool $initialized = false;

    /**
     * @var string|null
     */
    private ?string $dateInstalled = null;

    /**
     * @var bool
     */
    private bool $filesMissing = false;

    /**
     * @var bool
     */
    private bool $releaseAvailable = false;

    /**
     * @inheritdoc
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritdoc
     */
    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
    }

    /**
     * @inheritdoc
     */
    public function getInstalledVersion(): ?Version
    {
        return $this->installedVersion;
    }

    /**
     * @inheritdoc
     */
    public function setInstalledVersion(?Version $installedVersion): void
    {
        $this->installedVersion = $installedVersion;
    }

    /**
     * @inheritdoc
     */
    public function getMaxInstallableVersion(): ?Version
    {
        return $this->maxInstallableVersion;
    }

    /**
     * @inheritdoc
     */
    public function setMaxInstallableVersion(?Version $maxInstallableVersion): void
    {
        $this->maxInstallableVersion = $maxInstallableVersion;
    }

    /**
     * @inheritdoc
     */
    public function hasUpdate(): bool
    {
        return $this->hasUpdate;
    }

    /**
     * @inheritdoc
     */
    public function setHasUpdate(bool $hasUpdate): void
    {
        $this->hasUpdate = $hasUpdate;
    }

    /**
     * @return bool
     */
    public function canBeUpdated(): bool
    {
        return $this->canBeUpdated;
    }

    /**
     * @param bool $canBeUpdated
     */
    public function setCanBeUpdated(bool $canBeUpdated): void
    {
        $this->canBeUpdated = $canBeUpdated;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @inheritdoc
     */
    public function getInternalID(): int
    {
        return $this->internalID;
    }

    /**
     * @inheritdoc
     */
    public function setInternalID(int $internalID): void
    {
        $this->internalID = $internalID;
    }

    /**
     * @inheritdoc
     */
    public function getDateInstalled(): ?string
    {
        return $this->dateInstalled;
    }

    /**
     * @inheritdoc
     */
    public function setDateInstalled(?string $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @inheritdoc
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @inheritdoc
     */
    public function setInitialized(bool $initialized): void
    {
        $this->initialized = $initialized;
    }

    /**
     * @return bool
     */
    public function isFilesMissing(): bool
    {
        return $this->filesMissing;
    }

    /**
     * @param bool $filesMissing
     */
    public function setFilesMissing(bool $filesMissing): void
    {
        $this->filesMissing = $filesMissing;
    }

    /**
     * @return bool
     */
    public function isShopVersionOK(): bool
    {
        return $this->shopVersionOK;
    }

    /**
     * @param bool $shopVersionOK
     */
    public function setShopVersionOK(bool $shopVersionOK): void
    {
        $this->shopVersionOK = $shopVersionOK;
    }

    /**
     * @return bool
     */
    public function isReleaseAvailable(): bool
    {
        return $this->releaseAvailable;
    }

    /**
     * @param bool $releaseAvailable
     */
    public function setReleaseAvailable(bool $releaseAvailable): void
    {
        $this->releaseAvailable = $releaseAvailable;
    }
}
