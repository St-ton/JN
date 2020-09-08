<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\License\AjaxResponse;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Installer\Helper;
use JTL\License\Manager as LicenseManager;
use JTL\Plugin\InstallCode;
use JTL\Recommendation\Recommendation;
use JTL\Shop;

/**
 * Class ExtensionInstaller
 * @package JTL\Backend\Wizard
 */
class ExtensionInstaller
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Collection
     */
    private $recommendations;

    /**
     * @var LicenseManager
     */
    private $manager;

    /**
     * ExtensionInstaller constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $cache                 = Shop::Container()->getCache();
        $this->recommendations = new Collection();
        $this->manager         = new LicenseManager($db, $cache);
        $this->helper          = new Helper($this->manager, $db, $cache);
    }

    /**
     * @param string $id
     * @return Recommendation|null
     */
    private function getRecommendationByID(string $id): ?Recommendation
    {
        return $this->recommendations->first(static function (Recommendation $rec) use ($id) {
            return $rec->getId() === $id;
        });
    }

    /**
     * @return Collection
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    /**
     * @param Collection $recommendations
     */
    public function setRecommendations(Collection $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    /**
     * @param array $requested
     * @throws GuzzleException
     * @throws ApiResultCodeException
     * @throws ChecksumValidationException
     * @throws DownloadValidationException
     * @throws FilePermissionException
     */
    public function onSaveStep(array $requested): void
    {
        $createdLicenseKeys = [];
        foreach ($requested as $id) {
            Shop::dbg($id, false, 'requested ID:');
            $recom = $this->getRecommendationByID($id);
            if ($recom !== null) {
                foreach ($recom->getLinks() as $link) {
                    if ($link->getRel() === 'createLicense') {
                        try {
                            $res  = $this->manager->createLicense($link->getHref());
                            $data = \json_decode($res);
                            Shop::dbg($res, false, 'created:');
                            if (isset($data->meta)) {
                                $createdLicenseKeys[] = $data->meta->exs_key;
                            }
                        } catch (ClientException | GuzzleException $e) {
                            // @todo
                        }
                    }
                }
            }
        }
        $this->manager->update(true);

        foreach ($createdLicenseKeys as $key) {
            $ajaxResponse = new AjaxResponse();
            $key          = 'LIC-123456789';
            $license      = $this->manager->getLicenseByLicenseKey($key);
            if ($license !== null) {
                $itemID    = $license->getID();
                $installer = $this->helper->getInstaller($itemID);
                try {
                    $download    = $this->helper->getDownload($itemID);
                    $installCode = $installer->install($itemID, $download, $ajaxResponse);
                } catch (InvalidArgumentException $e) {
                    // @todo catch: throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
                    // @todo catch: throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID)
                    $installCode = 0;
                }
                Shop::dbg($installCode, false, 'RETURNCODE:');
                if ($installCode !== InstallCode::OK) {
                    // @todo: fail
                }
            }
        }
        die('DONE.');
    }
}
