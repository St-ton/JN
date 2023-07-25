<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Alert\Alert;
use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Permissions;
use JTL\Backend\Status;
use JTL\Exceptions\LoginException;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Session\Backend;
use JTL\Smarty\JTLSmarty;
use JTL\Widgets\AbstractWidget;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DashboardController
 * @package JTL\Router\Controller\Backend
 */
class DashboardController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        if (Request::postInt('adminlogin') === 1) {
            try {
                return $this->actionLogin();
            } catch (LoginException $e) {
                $this->alertService->addError($e->getMessage(), 'errLogin', ['dismissable' => false]);
            }
        }
        $uri = Text::filterXSS(Request::verifyGPDataString('uri'));
        $this->smarty->assign('pw_updated', Request::getVar('pw_updated') === 'true')
            ->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
            ->assign('alertList', $this->alertService)
            ->assign('plgSafeMode', (bool)($GLOBALS['plgSafeMode'] ?? false));
        if (!$this->account->getIsAuthenticated()) {
            $this->account->redirectOnUrl();
            if (Request::getInt('errCode', null) === AdminLoginStatus::ERROR_SESSION_INVALID) {
                $this->alertService->addError(\__('errorSessionExpired'), 'errorSessionExpired');
            }
            $this->getText->loadAdminLocale('pages/login');

            return $smarty->assign('uri', $uri)
                ->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
                ->assign('alertList', $this->alertService)
                ->getResponse('login.tpl');
        }
        $this->getText->loadAdminLocale('widgets');
        if (!$this->account->getIsTwoFaAuthenticated()) {
            $_SESSION['AdminAccount']->TwoFA_active = true;
            // restore first generated token from POST
            $_SESSION['jtl_token'] = $_POST['jtl_token'] ?? '';
            if (Request::postVar('TwoFA_code', '') !== '') {
                if ($this->account->doTwoFA()) {
                    Backend::getInstance()->reHash();
                    $_SESSION['AdminAccount']->TwoFA_expired = false;
                    $_SESSION['AdminAccount']->TwoFA_valid   = true;
                    $_SESSION['loginIsValid']                = true;

                    return $this->redirectLogin();
                }
                $this->alertService->addError(\__('errorTwoFactorFaultyExpired'), 'errorTwoFactorFaultyExpired');
                $smarty->assign('alertError', true);
            } else {
                $_SESSION['AdminAccount']->TwoFA_expired = true;
            }
            $this->getText->loadAdminLocale('pages/login');
            $this->account->redirectOnUrl();

            return $smarty->assign('uri', $uri)
                ->getResponse('login.tpl');
        }
        if ($uri !== '') {
            return $this->redirectToURI($uri);
        }
        $_SESSION['loginIsValid'] = true;
        if (isset($GLOBALS['plgSafeMode'])) {
            if ($GLOBALS['plgSafeMode']) {
                \touch(\SAFE_MODE_LOCK);
            } elseif (\file_exists(\SAFE_MODE_LOCK)) {
                \unlink(\SAFE_MODE_LOCK);
            }
        }

        if ($this->hasPermissions(Permissions::DASHBOARD_VIEW)) {
            $smarty->assign('bDashboard', true)
                ->assign('bUpdateError', (Request::postInt('shopupdate') === 1 ? '1' : false))
                ->assign('oActiveWidget_arr', $this->getWidgets())
                ->assign('oAvailableWidget_arr', $this->getWidgets(false))
                ->assign('bInstallExists', \is_dir(\PFAD_ROOT . 'install'));
        }

        return $smarty->getResponse('dashboard.tpl');
    }

    /**
     * @param bool $active
     * @param bool $getAll
     * @return array
     */
    public function getWidgets(bool $active = true, bool $getAll = false): array
    {
        if (!$getAll && !$this->hasPermissions(Permissions::DASHBOARD_VIEW)) {
            return [];
        }

        $loaderLegacy = Helper::getLoader(false, $this->db, $this->cache);
        $loaderExt    = Helper::getLoader(true, $this->db, $this->cache);
        $plugins      = [];

        $widgets = $this->db->getObjects(
            'SELECT tadminwidgets.*, tplugin.cPluginID, tplugin.bExtension
            FROM tadminwidgets
            LEFT JOIN tplugin 
                ON tplugin.kPlugin = tadminwidgets.kPlugin
            WHERE bActive = :active
                AND (tplugin.nStatus IS NULL OR tplugin.nStatus = :activated)
            ORDER BY eContainer ASC, nPos ASC',
            ['active' => (int)$active, 'activated' => State::ACTIVATED]
        );

        foreach ($widgets as $widget) {
            $widget->kWidget    = (int)$widget->kWidget;
            $widget->kPlugin    = (int)$widget->kPlugin;
            $widget->nPos       = (int)$widget->nPos;
            $widget->bExpanded  = (int)$widget->bExpanded;
            $widget->bActive    = (int)$widget->bActive;
            $widget->bExtension = (int)$widget->bExtension;
            $widget->plugin     = null;

            if ($widget->cPluginID !== null && \SAFE_MODE === false) {
                if (\array_key_exists($widget->cPluginID, $plugins)) {
                    $widget->plugin = $plugins[$widget->cPluginID];
                } else {
                    if ($widget->bExtension === 1) {
                        $widget->plugin = $loaderExt->init((int)$widget->kPlugin);
                    } else {
                        $widget->plugin = $loaderLegacy->init((int)$widget->kPlugin);
                    }

                    $plugins[$widget->cPluginID] = $widget->plugin;
                }

                if ($widget->bExtension) {
                    $this->getText->loadPluginLocale('widgets/' . $widget->cClass, $widget->plugin);
                }
            } else {
                $this->getText->loadAdminLocale('widgets/' . $widget->cClass);
                $widget->plugin = null;
            }

            $msgid  = $widget->cClass . '_title';
            $msgstr = \__($msgid);

            if ($msgid !== $msgstr) {
                $widget->cTitle = $msgstr;
            }

            $msgid  = $widget->cClass . '_desc';
            $msgstr = \__($msgid);

            if ($msgid !== $msgstr) {
                $widget->cDescription = $msgstr;
            }
        }

        if (!$active) {
            return $widgets;
        }
        foreach ($widgets as $key => $widget) {
            $widget->cContent = '';
            $className        = '\JTL\Widgets\\' . $widget->cClass;
            $classPath        = null;

            if ($widget->plugin !== null) {
                $hit = $widget->plugin->getWidgets()->getWidgetByID($widget->kWidget);

                if ($hit !== null) {
                    $className = $hit->className;
                    $classPath = $hit->classFile;

                    if (\file_exists($classPath)) {
                        require_once $classPath;
                    }
                }
            }
            if (\class_exists($className)) {
                /** @var AbstractWidget $instance */
                $instance = new $className($this->smarty, $this->db, $widget->plugin);
                if ($getAll
                    || \in_array($instance->getPermission(), ['DASHBOARD_ALL', ''], true)
                    || $this->hasPermissions($instance->getPermission())
                ) {
                    $widget->cContent = $instance->getContent();
                    $widget->hasBody  = $instance->hasBody;
                } else {
                    unset($widgets[$key]);
                }
            }
        }

        return $widgets;
    }

    /**
     * redirects to a given (base64-encoded) URI
     * (prevents code duplication)
     * @param string $uri
     * @return ResponseInterface
     */
    public function redirectToURI(string $uri): ResponseInterface
    {
        return new RedirectResponse($this->baseURL . '/' . \base64_decode($uri));
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function redirectLogin(): ResponseInterface
    {
        unset($_SESSION['frontendUpToDate']);
        $uri      = Text::filterXSS(Request::verifyGPDataString('uri'));
        $safeMode = isset($GLOBALS['plgSafeMode'])
            ? '?safemode=' . ($GLOBALS['plgSafeMode'] ? 'on' : 'off')
            : '';
        if ($uri !== '') {
            return $this->redirectToURI($uri);
        }

        return new RedirectResponse($this->baseURL . '/' . $safeMode);
    }

    /**
     * @return ResponseInterface
     * @throws LoginException
     */
    private function actionLogin(): ResponseInterface
    {
        $csrfOK = Form::validateToken();
        if ($csrfOK !== true) {
            throw new LoginException(isset($_COOKIE['eSIdAdm']) ? \__('errorCSRF') : \__('errorCookieSettings'));
        }
        $res = $this->account->login($_POST['benutzer'], $_POST['passwort']);
        switch ($res) {
            case AdminLoginStatus::ERROR_LOCKED:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                $lockTime = $this->account->getLockedMinutes();
                throw new LoginException(\sprintf(\__('lockForMinutes'), $lockTime));

            case AdminLoginStatus::ERROR_USER_NOT_FOUND:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                if (empty(Request::verifyGPDataString('TwoFA_code'))) {
                    throw new LoginException(\__('errorWrongPasswordUser'));
                }
                throw new LoginException('');

            case AdminLoginStatus::ERROR_USER_DISABLED:
                throw new LoginException(\__('errorLoginTemporaryNotPossible'));

            case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                throw new LoginException(\__('errorLoginDataExpired'));

            case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                if (($_SESSION['AdminAccount']->TwoFA_expired ?? false) === true) {
                    throw new LoginException(\__('errorTwoFactorExpired'));
                }
                throw new LoginException('');

            case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                throw new LoginException(\__('errorNoPermission'));

            case AdminLoginStatus::LOGIN_OK:
                Status::getInstance($this->db, $this->cache, true);
                Backend::getInstance()->reHash();
                $_SESSION['loginIsValid'] = true;

                return $this->redirectLogin();
            default:
                throw new LoginException(\__('???'));
        }
    }
}
