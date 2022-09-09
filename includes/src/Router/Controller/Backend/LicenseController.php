<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use JTL\Backend\AuthToken;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\License\AjaxResponse;
use JTL\License\Checker;
use JTL\License\Exception\AuthException;
use JTL\License\Installer\Helper;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\License\Struct\ExsLicense;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;
use JTL\Router\Route;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LicenseController
 * @package JTL\Router\Controller\Backend
 */
class LicenseController extends AbstractBackendController
{
    private const ACTION_EXTEND = 'extendLicense';

    private const ACTION_UPGRADE = 'upgradeLicense';

    private const ACTION_SET_BINDING = 'setbinding';

    private const ACTION_CLEAR_BINDING = 'clearbinding';

    private const ACTION_ENTER_TOKEN = 'entertoken';

    private const ACTION_SAVE_TOKEN = 'savetoken';

    private const ACTION_RECHECK = 'recheck';

    private const ACTION_REVOKE = 'revoke';

    private const ACTION_REDIRECT = 'redirect';

    private const ACTION_UPDATE = 'update';

    private const ACTION_INSTALL = 'install';

    private const STATE_APPROVED = 'approved';

    private const STATE_CREATED = 'created';

    private const STATE_FAILED = 'failed';

    /**
     * @var AuthToken
     */
    private AuthToken $auth;

    /**
     * @var string[]
     */
    private array $validActions = [
        self::ACTION_EXTEND,
        self::ACTION_UPGRADE,
        self::ACTION_SET_BINDING,
        self::ACTION_CLEAR_BINDING,
        self::ACTION_RECHECK,
        self::ACTION_REVOKE,
        self::ACTION_REDIRECT,
        self::ACTION_UPDATE,
        self::ACTION_ENTER_TOKEN,
        self::ACTION_SAVE_TOKEN,
        self::ACTION_INSTALL
    ];

    /**
     * @var Checker
     */
    private Checker $checker;

    /**
     * @var Manager
     */
    private Manager $manager;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty->assign('route', $this->route);
        $this->auth   = AuthToken::getInstance($this->db);
        $this->getText->loadAdminLocale('pages/licenses');
        $this->getText->loadAdminLocale('pages/pluginverwaltung');
        $this->checker = new Checker(Shop::Container()->getLogService(), $this->db, $this->cache);
        $this->manager = new Manager($this->db, $this->cache);
        if (Request::postVar('action') === 'code') {
            $this->handleAuth();
            exit();
        }
        $this->checkPermissions(Permissions::LICENSE_MANAGER);

        return $this->handle() ?? $this->smarty->getResponse('licenses.tpl');
    }

    /**
     * @return void
     */
    public function handleAuth(): void
    {
        $this->auth->responseToken();
    }

    /**
     * @return ResponseInterface|null
     */
    public function handle(): ?ResponseInterface
    {
        \ob_start();
        $action = Request::postVar('action');
        $valid  = Form::validateToken();
        if ($valid) {
            if ($action === self::ACTION_SAVE_TOKEN) {
                $this->saveToken();
                $action = null;
            }
            if ($action === self::ACTION_ENTER_TOKEN) {
                $this->setToken();

                return null;
            }
            if ($action === self::ACTION_SET_BINDING) {
                return $this->setBinding();
            }
            if ($action === self::ACTION_CLEAR_BINDING) {
                return $this->clearBinding();
            }
            if ($action === self::ACTION_RECHECK) {
                $this->getLicenses(true);
                $this->getList();

                return new RedirectResponse(Shop::getAdminURL() . '/' . Route::LICENSE, 303);
            }
            if ($action === self::ACTION_REVOKE) {
                $this->auth->revoke();
                $action = null;
            }
            if ($action === self::ACTION_EXTEND || $action === self::ACTION_UPGRADE) {
                return $this->extendUpgrade($action);
            }
        }
        if ($action === null || !$valid || !\in_array($action, $this->validActions, true)) {
            $this->getLicenses();
            $this->getList();

            return null;
        }
        if ($action === self::ACTION_REDIRECT) {
            $this->auth->requestToken(
                Backend::get('jtl_token'),
                Shop::getAdminURL(true) . '/' . Route::CODE . '/license'
            );
        }
        if ($action === self::ACTION_UPDATE || $action === self::ACTION_INSTALL) {
            return $this->installUpdate($action);
        }

        return null;
    }

    /**
     * @param string $action
     * @return JsonResponse
     */
    private function installUpdate(string $action): JsonResponse
    {
        $itemID           = Request::postVar('item-id', '');
        $exsID            = Request::postVar('exs-id', '');
        $type             = Request::postVar('license-type', '');
        $response         = new AjaxResponse();
        $response->action = $action;
        $response->id     = $itemID;
        if ($type !== '') {
            $response->id .= '-' . $type;
        }
        try {
            $helper    = new Helper($this->manager, $this->db, $this->cache);
            $installer = $helper->getInstaller($itemID);
            $download  = $helper->getDownload($itemID);
            $result    = $action === self::ACTION_UPDATE
                ? $installer->update($exsID, $download, $response)
                : $installer->install($itemID, $download, $response);
            if ($result === InstallCode::DUPLICATE_PLUGIN_ID && $action !== self::ACTION_UPDATE) {
                $download = $helper->getDownload($itemID);
                $result   = $installer->forceUpdate($download, $response);
            }
            $this->cache->flushTags([\CACHING_GROUP_LICENSES]);
            if ($result !== InstallCode::OK) {
                $errorCode      = $result;
                $mappedErrorMsg = (new PluginValidation())->map($result);
                if (empty($response->error)) {
                    $response->error = \__('Error code: %d', $errorCode) . ' - ' . $mappedErrorMsg;
                }
                $this->smarty->assign('licenseErrorMessage', $response->error)
                    ->assign('mappedErrorMessage', $mappedErrorMsg)
                    ->assign('resultCode', $result);
            }
        } catch (Exception $e) {
            $response->status = 'FAILED';
            $msg              = $e->getMessage();
            if (\str_contains($msg, 'response:')) {
                $msg = \substr($msg, 0, \strpos($msg, 'response:'));
            }
            $this->smarty->assign('licenseErrorMessage', $msg);
        }
        $this->getList();
        $license = $this->manager->getLicenseByItemID($itemID);
        if ($license === null || $license->getReferencedItem() === null) {
            $license = $this->manager->getLicenseByExsID($exsID);
        }
        if ($license !== null && $license->getReferencedItem() !== null) {
            $this->smarty->assign('license', $license);
            $response->html         = $this->smarty->fetch('tpl_inc/licenses_referenced_item.tpl');
            $response->notification = $this->smarty->fetch('tpl_inc/updates_drop.tpl');
        }

        return $this->sendResponse($response);
    }

    /**
     * @param bool $up
     * @return JsonResponse
     * @throws ResponseInterface
     */
    private function updateBinding(bool $up): ResponseInterface
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
            if ($e->getResponse()->getStatusCode() === 400) {
                $body = \json_decode((string)$e->getResponse()->getBody(), false, 512, \JSON_THROW_ON_ERROR);
                if (isset($body->code, $body->message) && $body->code === 422) {
                    $response->error = $body->message;
                }
            }
            $this->smarty->assign('bindErrorMessage', $response->error);
        } catch (AuthException $e) {
            $this->smarty->assign('bindErrorMessage', $e->getMessage());
        }
        $this->getLicenses(true);
        $this->getList();
        $response->replaceWith['#unbound-licenses'] = $this->smarty->fetch('tpl_inc/licenses_unbound.tpl');
        $response->replaceWith['#bound-licenses']   = $this->smarty->fetch('tpl_inc/licenses_bound.tpl');
        $response->html                             = $apiResponse;

        return $this->sendResponse($response);
    }

    /**
     * @param string $action
     * @return JsonResponse
     */
    private function extendUpgrade(string $action): JsonResponse
    {
        $responseData     = null;
        $apiResponse      = '';
        $response         = new AjaxResponse();
        $response->action = $action;
        try {
            $apiResponse  = $this->manager->extendUpgrade(
                Request::postVar('url'),
                Request::postVar('exsid'),
                Request::postVar('key')
            );
            $responseData = \json_decode($apiResponse, false, 512, \JSON_THROW_ON_ERROR);
        } catch (ClientException | GuzzleException | AuthException | JsonException $e) {
            $response->error = $e->getMessage();
            $this->smarty->assign('extendErrorMessage', $e->getMessage());
        }
        if (isset($responseData->state)) {
            if ($responseData->state === self::STATE_APPROVED) {
                if ($action === self::ACTION_EXTEND) {
                    $this->smarty->assign('extendSuccessMessage', 'Successfully extended.');
                } elseif ($action === self::ACTION_UPGRADE) {
                    $this->smarty->assign('extendSuccessMessage', 'Successfully executed.');
                }
            } elseif ($responseData->state === self::STATE_FAILED && isset($responseData->failure_reason)) {
                $this->smarty->assign('extendErrorMessage', $responseData->failure_reason);
            } elseif ($responseData->state === self::STATE_CREATED
                && isset($responseData->links)
                && \is_array($responseData->links)
            ) {
                foreach ($responseData->links as $link) {
                    if (isset($link->rel) && $link->rel === 'redirect_url') {
                        $response->redirect = $link->href;
                        $response->status   = 'OK';

                        return $this->sendResponse($response);
                    }
                }
            }
        }
        $this->getLicenses(true);
        $this->getList();
        $response->replaceWith['#bound-licenses'] = $this->smarty->fetch('tpl_inc/licenses_bound.tpl');
        $response->html                           = $apiResponse;

        return $this->sendResponse($response);
    }

    /**
     * @return void
     */
    private function setToken(): void
    {
        $this->smarty->assign('setToken', true)
            ->assign('hasAuth', false);
    }

    /**
     * @return void
     */
    private function saveToken(): void
    {
        $code  = \trim(Request::postVar('code', ''));
        $token = \trim(Request::postVar('token', ''));
        $this->auth->reset($code);
        AuthToken::getInstance($this->db)->set($code, $token);
    }

    /**
     * @return ResponseInterface
     */
    private function setBinding(): ResponseInterface
    {
        return $this->updateBinding(true);
    }

    /**
     * @return ResponseInterface
     */
    private function clearBinding(): ResponseInterface
    {
        return $this->updateBinding(false);
    }

    /**
     * @return void
     */
    private function setOverviewData(): void
    {
        $data = $this->manager->getLicenseData();
        $this->smarty->assign('hasAuth', $this->auth->isValid())
            ->assign('tokenOwner', $data->owner ?? null)
            ->assign('lastUpdate', $data->timestamp ?? null);
    }

    /**
     * @param bool $force
     */
    private function getLicenses(bool $force = false): void
    {
        if (!$this->auth->isValid()) {
            return;
        }
        try {
            $this->manager->update($force, $this->getInstalledExtensionPostData());
            $this->checker->handleExpiredLicenses($this->manager);
        } catch (Exception $e) {
            $this->alertService->addError(\__('errorFetchLicenseAPI') . ' ' . $e->getMessage(), 'errorFetchLicenseAPI');
        }
    }

    /**
     * @return array
     */
    private function getInstalledExtensionPostData(): array
    {
        return (new Mapper($this->manager))->getCollection()->map(function (ExsLicense $exsLicense) {
            $item      = (object)[
                'active'  => false,
                'id'      => $exsLicense->getID(),
                'exsid'   => $exsLicense->getExsID(),
                'version' => (string)($exsLicense->getReleases()->getAvailable()?->getVersion() ?? '0.0.0')
            ];
            $reference = $exsLicense->getReferencedItem();
            if ($reference !== null && $reference->getInstalledVersion() !== null) {
                $item->active  = $reference->isActive();
                $item->version = (string)$reference->getInstalledVersion();
                if ($reference->getDateInstalled() !== null) {
                    $item->enabled = $reference->getDateInstalled();
                }
            }

            return $item;
        })->toArray();
    }

    /**
     * @return void
     */
    private function getList(): void
    {
        $this->setOverviewData();
        $collection = (new Mapper($this->manager))->getCollection();
        $this->smarty->assign('licenses', $collection)
            ->assign('authToken', $this->auth->get())
            ->assign('rawData', isset($_GET['debug']) ? $this->manager->getLicenseData() : null)
            ->assign('licenseItemUpdates', $collection->getUpdateableItems());
    }

    /**
     * @param AjaxResponse $response
     * @return JsonResponse
     */
    private function sendResponse(AjaxResponse $response): JsonResponse
    {
        \ob_clean();
        return new JsonResponse($response);
    }
}
