<?php declare(strict_types=1);

namespace JTL\License;

use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Smarty\JTLSmarty;
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
    private $cache;

    /**
     * Admin constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(Manager $manager, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->manager = $manager;
        $this->db      = $db;
        $this->cache   = $cache;
    }

    /**
     * @param JTLSmarty $smarty
     * @throws DownloadValidationException
     * @throws \SmartyException
     */
    public function handle(JTLSmarty $smarty): void
    {
        \ob_start();
        $action = Request::postVar('action');
        if ($action === null || !Form::validateToken()) {
            return;
        }
        $response         = new AjaxResponse();
        $response->action = $action;
        if ($action === 'update') {
            $itemID       = Request::postVar('item-id', '');
            $response->id = $itemID;
            try {
                $updateRes = $this->updateItem($itemID, $response);
                if ($updateRes !== InstallCode::OK) {
                    $smarty->assign('licenseErrorMessage', $response->error);
                }
            } catch (ClientException $e) {
                $response->status = 'FAILED';
                $msg              = $e->getMessage();
                if (\strpos($msg, 'response:') !== false) {
                    $msg = \substr($msg, 0, \strpos($msg, 'response:'));
                }
                $smarty->assign('licenseErrorMessage', $msg);
            }
            $smarty->assign('license', $this->manager->getLicenseByItemID($itemID));
            $response->html = $smarty->fetch('tpl_inc/licenses_referenced_item.tpl');
            $this->sendResponse($response);
        }
    }

    /**
     * @param AjaxResponse $response
     */
    private function sendResponse(AjaxResponse $response): void
    {
        \ob_clean();
        \ob_start();
        echo \json_encode($response);
        echo \ob_get_clean();
        exit;
    }

    /**
     * @param string       $itemID
     * @param AjaxResponse $response
     * @return bool|int
     * @throws DownloadValidationException
     */
    private function updateItem(string $itemID, AjaxResponse $response)
    {
        $res         = false;
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }
        $downloader        = new Downloader();
        $downloadedArchive = $downloader->downloadRelease($available);
        if ($licenseData->getType() === ExsLicense::TYPE_PLUGIN) {
            $res = $this->updatePlugin($itemID, $downloadedArchive, $response);
        }

        return $res;
    }

    /**
     * @param string       $itemID
     * @param string       $downloadedArchive
     * @param AjaxResponse $response
     * @return int
     */
    private function updatePlugin(string $itemID, string $downloadedArchive, AjaxResponse $response): int
    {
        $parser           = new XMLParser();
        $uninstaller      = new Uninstaller($this->db, $this->cache);
        $legacyValidator  = new LegacyPluginValidator($this->db, $parser);
        $pluginValidator  = new PluginValidator($this->db, $parser);
        $installer        = new Installer($this->db, $uninstaller, $legacyValidator, $pluginValidator);
        $updater          = new Updater($this->db, $installer);
        $extractor        = new Extractor(new XMLParser());
        $installResponse  = $extractor->extractPlugin($downloadedArchive);
        $response->status = $installResponse->getStatus();
        if ($response->status === InstallationResponse::STATUS_FAILED) {
            $response->error      = $installResponse->getError() ?? \implode(', ', $installResponse->getMessages());
            $response->additional = $installResponse;

            return 0;
        }

        return $updater->update(Helper::getIDByPluginID($itemID));
    }
}
