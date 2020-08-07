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
use stdClass;

/**
 * Class Admin
 * @package JTL\License
 */
class Admin
{
    public const ACTION_EXTEND = 'extendLicense';

    public const ACTION_SET_BINDING = 'setbinding';

    public const ACTION_CLEAR_BINDING = 'clearbinding';

    public const ACTION_RECHECK = 'recheck';

    public const ACTION_REVOKE = 'revoke';

    public const ACTION_REDIRECT = 'redirect';

    public const ACTION_UPDATE = 'update';

    public const ACTION_INSTALL = 'install';

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
     * @var Checker
     */
    private $checker;

    /**
     * @var string[]
     */
    private $validActions = [
        self::ACTION_EXTEND,
        self::ACTION_SET_BINDING,
        self::ACTION_CLEAR_BINDING,
        self::ACTION_RECHECK,
        self::ACTION_REVOKE,
        self::ACTION_REDIRECT,
        self::ACTION_UPDATE,
        self::ACTION_INSTALL
    ];

    /**
     * Admin constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param Checker           $checker
     */
    public function __construct(Manager $manager, DbInterface $db, JTLCacheInterface $cache, Checker $checker)
    {
        $this->manager = $manager;
        $this->db      = $db;
        $this->cache   = $cache;
        $this->checker = $checker;
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
        if ($valid) {
            if ($action === self::ACTION_SET_BINDING) {
                $this->setBinding($smarty);
            }
            if ($action === self::ACTION_CLEAR_BINDING) {
                $this->clearBinding($smarty);
            }
            if ($action === self::ACTION_RECHECK) {
                $this->getLicenses(true);
                $this->getList($smarty);
                \header('Location: ' . Shop::getAdminURL() . '/licenses.php', true, 303);
                exit();
            }
            if ($action === self::ACTION_REVOKE) {
                $token->revoke();
                $action = null;
            }
            if ($action === self::ACTION_EXTEND) {
                $this->extend($smarty);
            }
        }
        if ($action === null || !\in_array($action, $this->validActions, true) || !$valid) {
            $this->getLicenses(true);
            $this->getList($smarty);
            return;
        }
        if ($action === self::ACTION_REDIRECT) {
            $token->requestToken(
                Backend::get('jtl_token'),
                Shop::getAdminURL(true) . '/licenses.php?action=code'
            );
        }
        if ($action === self::ACTION_UPDATE || $action === self::ACTION_INSTALL) {
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
    private function updateBinding(bool $up, JTLSmarty $smarty): void
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
     * @throws \SmartyException
     */
    private function extend(JTLSmarty $smarty): void
    {
        $apiResponse      = '';
        $response         = new AjaxResponse();
        $response->action = 'extendLicense';
        try {
            $apiResponse = $this->manager->extend(
                Request::postVar('url'),
                Request::postVar('exsid'),
                Request::postVar('key')
            );
        } catch (ClientException | GuzzleException $e) {
            $response->error = $e->getMessage();
            $smarty->assign('extendErrorMessage', $e->getMessage());
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
        $data = $this->manager->getLicenseData();
        $smarty->assign('hasAuth', AuthToken::getInstance($this->db)->isValid())
            ->assign('lastUpdate', $data->timestamp ?? null);
    }

    /**
     * @param bool $force
     */
    private function getLicenses(bool $force = false): void
    {
        if (!AuthToken::getInstance($this->db)->isValid()) {
            return;
        }
        try {
            $this->manager->update($force, $this->getInstalledExtensionPostData());
            $this->checker->handleExpiredLicenses($this->manager);
        } catch (RequestException | Exception | ClientException $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                __('errorFetchLicenseAPI') . '' . $e->getMessage(),
                'errorFetchLicenseAPI'
            );
        }
    }

    /**
     * @return array
     */
    private function getInstalledExtensionPostData(): array
    {
        $mapper     = new Mapper($this->manager);
        $collection = $mapper->getCollection();
        $data       = [];
        foreach ($collection as $exsLicense) {
            /** @var ExsLicense $exsLicense */
            $avail         = $exsLicense->getReleases()->getAvailable();
            $item          = new stdClass();
            $item->active  = false;
            $item->id      = $exsLicense->getID();
            $item->exsid   = $exsLicense->getExsID();
            $item->version = $avail !== null ? (string)$avail->getVersion() : '0.0.0';
            $reference     = $exsLicense->getReferencedItem();
            if ($reference !== null && $reference->getInstalledVersion() !== null) {
                $item->active  = $reference->isActive();
                $item->version = (string)$reference->getInstalledVersion();
                if ($reference->getDateInstalled() !== null) {
                    $item->enabled = $reference->getDateInstalled();
                }
            }
            $data[] = $item;
        }

        return $data;
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
            case ExsLicense::TYPE_PORTLET:
                return new PluginInstaller($this->db, $this->cache);
            case ExsLicense::TYPE_TEMPLATE:
                return new TemplateInstaller($this->db, $this->cache);
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
