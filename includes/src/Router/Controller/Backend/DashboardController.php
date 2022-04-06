<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Alert\Alert;
use JTL\Backend\AdminAccount;
use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Profiler;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Updater;
use JTL\Widgets\AbstractWidget;
use JTLShop\SemVer\Version;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DashboardController
 * @package JTL\Router\Controller\Backend
 */
class DashboardController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $oUpdater     = new Updater($this->db);
        if (Request::postInt('adminlogin') === 1) {
            $csrfOK = true;
            // Check if shop version is new enough for csrf validation
            if (Shop::getShopDatabaseVersion()->equals(Version::parse('4.0.0'))
                || Shop::getShopDatabaseVersion()->greaterThan(Version::parse('4.0.0'))
            ) {
                $csrfOK = Form::validateToken();
            }
            if ($csrfOK === true) {
                switch ($this->account->login($_POST['benutzer'], $_POST['passwort'])) {
                    case AdminLoginStatus::ERROR_LOCKED:
                    case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                        $lockTime = $this->account->getLockedMinutes();
                        $this->alertService->addError(\sprintf(\__('lockForMinutes'), $lockTime), 'errorFillRequired');
                        break;

                    case AdminLoginStatus::ERROR_USER_NOT_FOUND:
                    case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                        if (empty(Request::verifyGPDataString('TwoFA_code'))) {
                            $this->alertService->addError(\__('errorWrongPasswordUser'), 'errorWrongPasswordUser');
                        }
                        break;

                    case AdminLoginStatus::ERROR_USER_DISABLED:
                        $this->alertService->addError(\__('errorLoginTemporaryNotPossible'), 'errorLoginTemporaryNotPossible');
                        break;

                    case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                        $this->alertService->addError(\__('errorLoginDataExpired'), 'errorLoginDataExpired');
                        break;

                    case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                        if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                            && $_SESSION['AdminAccount']->TwoFA_expired === true
                        ) {
                            $this->alertService->addError(\__('errorTwoFactorExpired'), 'errorTwoFactorExpired');
                        }
                        break;

                    case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                        $this->alertService->addError(\__('errorNoPermission'), 'errorNoPermission');
                        break;

                    case AdminLoginStatus::LOGIN_OK:
                        Status::getInstance($this->db, $cache, true);
                        Backend::getInstance()->reHash();
                        $_SESSION['loginIsValid'] = true; // "enable" the "header.tpl"-navigation again
                        $this->redirectLogin($this->account, $oUpdater);

                        break;
                }
            } elseif (isset($_COOKIE['eSIdAdm'])) {
                $this->alertService->addError(\__('errorCSRF'), 'errorCSRF');
            } else {
                $this->alertService->addError(\__('errorCookieSettings'), 'errorCookieSettings');
            }
        }
        $type          = '';
        $profilerState = Profiler::getIsActive();
        switch ($profilerState) {
            case 0:
            default:
                break;
            case 1:
                $type = 'Datenbank';
                break;
            case 2:
                $type = 'XHProf';
                break;
            case 3:
                $type = 'Plugin';
                break;
            case 4:
                $type = 'Plugin- und XHProf';
                break;
            case 5:
                $type = 'Datenbank- und Plugin';
                break;
            case 6:
                $type = 'Datenbank- und XHProf';
                break;
            case 7:
                $type = 'Datenbank-, XHProf und Plugin';
                break;
        }
        $smarty->assign('bProfilerActive', $profilerState !== 0)
            ->assign('profilerType', $type)
            ->assign('pw_updated', Request::getVar('pw_updated') === 'true')
            ->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
            ->assign('alertList', $this->alertService)
            ->assign('plgSafeMode', $GLOBALS['plgSafeMode'] ?? false);

        if (!$this->account->getIsAuthenticated()) {
            $this->account->redirectOnUrl();
            if (Request::getInt('errCode', null) === AdminLoginStatus::ERROR_SESSION_INVALID) {
                $this->alertService->addError(\__('errorSessionExpired'), 'errorSessionExpired');
            }
            $this->getText->loadAdminLocale('pages/login');

            return $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(\trim($_REQUEST['uri'])) > 0
                ? \trim($_REQUEST['uri'])
                : '')
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
                    $this->redirectLogin($this->account, $oUpdater);
                } else {
                    $this->alertService->addError(\__('errorTwoFactorFaultyExpired'), 'errorTwoFactorFaultyExpired');
                    $smarty->assign('alertError', true);
                }
            } else {
                $_SESSION['AdminAccount']->TwoFA_expired = true;
            }
            $this->getText->loadAdminLocale('pages/login');
            $this->account->redirectOnUrl();

            return $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(\trim($_REQUEST['uri'])) > 0
                ? \trim($_REQUEST['uri'])
                : '')
                ->getResponse('login.tpl');
        }
        if (isset($_REQUEST['uri']) && mb_strlen(\trim($_REQUEST['uri'])) > 0) {
            $this->redirectToURI($_REQUEST['uri']);
        }
        $_SESSION['loginIsValid'] = true;

        if ($this->hasPermissions('DASHBOARD_VIEW')) {
            $smarty->assign('bDashboard', true)
                ->assign('bUpdateError', (Request::postInt('shopupdate') === 1 ? '1' : false))
                ->assign('oActiveWidget_arr', $this->getWidgets())
                ->assign('oAvailableWidget_arr', $this->getWidgets(false))
                ->assign('bInstallExists', \is_dir(PFAD_ROOT . 'install'));
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
        if (!$getAll && !$this->hasPermissions('DASHBOARD_VIEW')) {
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
     */
    public function redirectToURI($uri): void
    {
        \header('Location: ' . Shop::getAdminURL(true) . '/' . \base64_decode($uri));
        exit;
    }

    /**
     * @param AdminAccount $account
     * @param Updater      $updater
     * @return void
     * @throws Exception
     */
    public function redirectLogin(AdminAccount $account, Updater $updater): void
    {
        unset($_SESSION['frontendUpToDate']);
        $safeMode = isset($GLOBALS['plgSafeMode'])
            ? '?safemode=' . ($GLOBALS['plgSafeMode'] ? 'on' : 'off')
            : '';
        if (Shop::getSettingValue(\CONF_GLOBAL, 'global_wizard_done') === 'N') {
            \header('Location: ' . Shop::getAdminURL(true) . '/wizard.php' . $safeMode);
            exit;
        }
        if ($account->permission('SHOP_UPDATE_VIEW') && $updater->hasPendingUpdates()) {
            \header('Location: ' . Shop::getAdminURL(true) . '/dbupdater' . $safeMode);
            exit;
        }
        if (isset($_REQUEST['uri']) && mb_strlen(\trim($_REQUEST['uri'])) > 0) {
            $this->redirectToURI($_REQUEST['uri']);
        }

        \header('Location: ' . Shop::getAdminURL(true) . '/' . $safeMode);
        exit;
    }
}
