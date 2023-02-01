<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTLShop\SemVer\Version;

/**
 * Class ReferencedItem
 * @package JTL\License\Struct
 */
abstract class ReferencedItem implements ReferencedItemInterface
{
    public const PHP_VERSION_OK = 0;

    public const PHP_VERSION_LOW = -1;

    public const PHP_VERSION_HIGH = 1;

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
     * @var int - 0: OK, -1: too low, 1: too high
     */
    private int $phpVersionOK = self::PHP_VERSION_OK;

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
     * @inheritDoc
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritDoc
     */
    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
    }

    /**
     * @inheritDoc
     */
    public function getInstalledVersion(): ?Version
    {
        return $this->installedVersion;
    }

    /**
     * @inheritDoc
     */
    public function setInstalledVersion(?Version $installedVersion): void
    {
        $this->installedVersion = $installedVersion;
    }

    /**
     * @inheritDoc
     */
    public function getMaxInstallableVersion(): ?Version
    {
        return $this->maxInstallableVersion;
    }

    /**
     * @inheritDoc
     */
    public function setMaxInstallableVersion(?Version $maxInstallableVersion): void
    {
        $this->maxInstallableVersion = $maxInstallableVersion;
    }

    /**
     * @inheritDoc
     */
    public function hasUpdate(): bool
    {
        return $this->hasUpdate;
    }

    /**
     * @inheritDoc
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
        return $this->canBeUpdated && $this->getPhpVersionOK() === self::PHP_VERSION_OK;
    }

    /**
     * @param bool $canBeUpdated
     */
    public function setCanBeUpdated(bool $canBeUpdated): void
    {
        $this->canBeUpdated = $canBeUpdated;
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function getInternalID(): int
    {
        return $this->internalID;
    }

    /**
     * @inheritDoc
     */
    public function setInternalID(int $internalID): void
    {
        $this->internalID = $internalID;
    }

    /**
     * @inheritDoc
     */
    public function getDateInstalled(): ?string
    {
        return $this->dateInstalled;
    }

    /**
     * @inheritDoc
     */
    public function setDateInstalled(?string $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @inheritDoc
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @inheritDoc
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
     * @return int
     */
    public function getPhpVersionOK(): int
    {
        return $this->phpVersionOK;
    }

    /**
     * @param int $phpVersionOK
     */
    public function setPhpVersionOK(int $phpVersionOK): void
    {
        $this->phpVersionOK = $phpVersionOK;
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
}
