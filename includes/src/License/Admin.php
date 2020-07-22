<?php declare(strict_types=1);

namespace JTL\License;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Backend\AuthToken;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Installer\PluginInstaller;
use JTL\License\Installer\TemplateInstaller;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\InstallCode;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

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
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->db      = $manager->getDB();
        $this->cache   = $manager->getCache();
    }

    public function handleAuth(): void
    {
        AuthToken::getInstance($this->db)->responseToken();
    }

    /**
     * @param JTLSmarty $smarty
     */
    public function handle(JTLSmarty $smarty): void
    {
        \ob_start();
        $token  = AuthToken::getInstance($this->db);
        $action = Request::postVar('action');
        $valid  = Form::validateToken();
        if ($action === 'setbinding' && $valid) {
            $this->setBinding($smarty);
        }
        if ($action === 'clearbinding' && $valid) {
            $this->clearBinding($smarty);
        }
        if ($action === 'recheck' && $valid) {
            $this->getLicenses(true);
            $this->getList($smarty);
            \header('Location: ' . Shop::getAdminURL() . '/licenses.php', true, 303);
            exit();

        }
        if ($action === 'revoke' && $valid) {
            $token->revoke();
            $action = null;
        }
        if ($action === null || !$valid) {
            $this->getLicenses(true);
            $this->getList($smarty);
            return;
        }
        if ($action === 'redirect') {
            $token->requestToken(
                Backend::get('jtl_token'),
                Shop::getURL(true) . $_SERVER['SCRIPT_NAME'] . '?action=code'
            );
        }
        if ($action === 'update' || $action === 'install') {
            $this->installUpdate($action, $smarty);
        }
    }

    /**
     * @param string    $action
     * @param JTLSmarty $smarty
     */
    private function installUpdate(string $action, JTLSmarty $smarty): void
    {
        $response         = new AjaxResponse();
        $response->action = $action;
        $itemID           = Request::postVar('item-id', '');
        $response->id     = $itemID;
        try {
            $installer = $this->getInstaller($itemID);
            $download  = $this->getDownload($itemID);
            $result    = $action === 'update'
                ? $installer->update($itemID, $download, $response)
                : $installer->install($itemID, $download, $response);
            $this->cache->flushTags([\CACHING_GROUP_LICENSES]);
            if ($result !== InstallCode::OK) {
                $smarty->assign('licenseErrorMessage', $response->error)
                    ->assign('resultCode', $result);
            }
        } catch (ClientException
        | ConnectException
        | FilePermissionException
        | ApiResultCodeException
        | DownloadValidationException
        | ChecksumValidationException
        | InvalidArgumentException $e
        ) {
            $response->status = 'FAILED';
            $msg              = $e->getMessage();
            if (\strpos($msg, 'response:') !== false) {
                $msg = \substr($msg, 0, \strpos($msg, 'response:'));
            }
            $smarty->assign('licenseErrorMessage', $msg);
        }
        $this->getList($smarty);
        $smarty->assign('license', $this->manager->getLicenseByItemID($itemID));
        $response->html         = $smarty->fetch('tpl_inc/licenses_referenced_item.tpl');
        $response->notification = $smarty->fetch('tpl_inc/updates_drop.tpl');
        $this->sendResponse($response);
    }

    /**
     * @param bool      $up
     * @param JTLSmarty $smarty
     */
    private function updateBinding(bool $up, JTLSmarty $smarty)
    {
        $apiResponse      = '';
        $response         = new AjaxResponse();
        $response->action = $up === true ? 'setbinding' : 'clearbinding';
        try {
            $apiResponse = $up === true
                ? $this->manager->setBinding(Request::postVar('url'))
                : $this->manager->clearBinding(Request::postVar('url'));
        } catch (ClientException | GuzzleException $e) {
            $response->error = $e->getMessage();
            $smarty->assign('bindErrorMessage', $e->getMessage());
        }
        $this->getLicenses(true);
        $this->getList($smarty);
        $response->replaceWith['#unbound-licenses'] = $smarty->fetch('tpl_inc/licenses_unbound.tpl');
        $response->replaceWith['#bound-licenses']   = $smarty->fetch('tpl_inc/licenses_bound.tpl');
        $response->html                             = $apiResponse;
        $this->sendResponse($response);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function setBinding(JTLSmarty $smarty): void
    {
        $this->updateBinding(true, $smarty);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function clearBinding(JTLSmarty $smarty): void
    {
        $this->updateBinding(false, $smarty);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function setOverviewData(JTLSmarty $smarty): void
    {
        $token = AuthToken::getInstance($this->db);
        $data  = $this->manager->getLicenseData();
        $smarty->assign('hasAuth', $token->isValid())
            ->assign('lastUpdate', $data->timestamp ?? null);
    }

    /**
     * @param bool $force
     */
    private function getLicenses(bool $force = false): void
    {
        try {
            $this->manager->update($force);
        } catch (RequestException | Exception | ClientException $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                __('errorFetchLicenseAPI') . '' . $e->getMessage(),
                'errorFetchLicenseAPI'
            );
        }
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function getList(JTLSmarty $smarty): void
    {
        $this->setOverviewData($smarty);
        $mapper     = new Mapper($this->manager);
        $collection = $mapper->getCollection();
        $smarty->assign('licenses', $collection)
            ->assign('licenseItemUpdates', $collection->getUpdateableItems());
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
     * @param string $itemID
     * @return PluginInstaller|TemplateInstaller
     */
    private function getInstaller(string $itemID)
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }
        switch ($licenseData->getType()) {
            case ExsLicense::TYPE_PLUGIN:
                return new PluginInstaller($this->db, $this->cache);
            case ExsLicense::TYPE_TEMPLATE:
                return new TemplateInstaller($this->db, $this->cache);
            case ExsLicense::TYPE_PORTLET:
                // @todo
                throw new InvalidArgumentException('Cannot update portlets yet');
            default:
                throw new InvalidArgumentException('Cannot update type ' . $licenseData->getType());
        }
    }

    /**
     * @param string $itemID
     * @return ResponseInterface|string
     * @throws DownloadValidationException
     * @throws InvalidArgumentException
     * @throws ApiResultCodeException
     * @throws FilePermissionException
     * @throws ChecksumValidationException
     */
    private function getDownload(string $itemID)
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }
        $downloader = new Downloader();

        return $downloader->downloadRelease($available);
    }
}
