<?php declare(strict_types=1);

namespace JTL\License\Installer;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Downloader;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Manager;
use JTL\License\Struct\ExsLicense;

/**
 * Class Helper
 * @package JTL\License\Installer
 */
class Helper
{
    /**
     * Helper constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(private Manager $manager, private DbInterface $db, private JTLCacheInterface $cache)
    {
    }

    /**
     * @param string $itemID
     * @return InstallerInterface
     */
    public function getInstaller(string $itemID): InstallerInterface
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find release for item with ID ' . $itemID);
        }
        return match ($licenseData->getType()) {
            ExsLicense::TYPE_PLUGIN, ExsLicense::TYPE_PORTLET => new PluginInstaller($this->db, $this->cache),
            ExsLicense::TYPE_TEMPLATE => new TemplateInstaller($this->db, $this->cache),
            default => throw new InvalidArgumentException('Cannot update type ' . $licenseData->getType()),
        };
    }

    /**
     * @param string $itemID
     * @return string
     * @throws DownloadValidationException
     * @throws InvalidArgumentException
     * @throws ApiResultCodeException
     * @throws FilePermissionException
     * @throws ChecksumValidationException
     */
    public function getDownload(string $itemID): string
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }

        return (new Downloader())->downloadRelease($available);
    }
}
