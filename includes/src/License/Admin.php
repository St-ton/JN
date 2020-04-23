<?php declare(strict_types=1);

namespace JTL\License;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Shop;
use JTL\XMLParser;

/**
 * Class Admin
 * @package JTL\License
 */
class Admin
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache

    /**
     * Admin constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(Manager $manager, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->manager = $manager;
        $this->db = $db;
        $this->cache = $cache;
    }

    /**
     * @param string $itemID
     * @return bool|InstallationResponse
     * @throws DownloadValidationException
     */
    public function updateItem(string $itemID)
    {
        $res = false;
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }
        $downloader = new Downloader();
        $downloadedArchive = $downloader->downloadRelease($available);
        if ($licenseData->getType() === ExsLicense::TYPE_PLUGIN) {
            $res = $this->updatePlugin($itemID, $downloadedArchive);
        }
        Shop::dbg($res, true, 'update plugin res:');


        return $res;
    }

    /**
     * @param string $itemID
     * @param string $downloadedArchive
     * @return int
     */
    private function updatePlugin(string $itemID, string $downloadedArchive)
    {
        $parser          = new XMLParser();
        $uninstaller     = new Uninstaller($this->db, $this->cache);
        $legacyValidator = new LegacyPluginValidator($this->db, $parser);
        $pluginValidator = new PluginValidator($this->db, $parser);
        $installer       = new Installer($this->db, $uninstaller, $legacyValidator, $pluginValidator);
        $updater         = new Updater($this->db, $installer);

        $extractor = new Extractor(new XMLParser());
        $res = $extractor->extractPlugin($downloadedArchive);
        Shop::dbg($res, false, 'extracted:');
        return $updater->update(Helper::getIDByPluginID($itemID));
    }
}
