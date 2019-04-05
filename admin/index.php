<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Profiler;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\Template;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;
use JTL\Backend\AdminLoginStatus;
use JTL\Session\Backend;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty     $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();
$oUpdater    = new Updater($db);
if (isset($_POST['adminlogin']) && (int)$_POST['adminlogin'] === 1) {
    $csrfOK = true;
    // Check if shop version is new enough for csrf validation
    if (Shop::getShopDatabaseVersion()->equals(Version::parse('4.0.0'))
        || Shop::getShopDatabaseVersion()->greaterThan(Version::parse('4.0.0'))
    ) {
        $csrfOK = Form::validateToken();
    }
    $loginName = isset($_POST['benutzer'])
        ? Text::filterXSS($db->escape($_POST['benutzer']))
        : '---';
    if ($csrfOK === true) {
        switch ($oAccount->login($_POST['benutzer'], $_POST['passwort'])) {
            case AdminLoginStatus::ERROR_LOCKED:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                $lockTime = $oAccount->getLockedMinutes();
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    sprintf(__('lockForMinutes'), $lockTime),
                    'errorFillRequired'
                );
                break;

            case AdminLoginStatus::ERROR_USER_NOT_FOUND:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorWrongPasswordUser'), 'errorWrongPasswordUser');
                if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                    && $_SESSION['AdminAccount']->TwoFA_expired === true
                ) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTwoFactorExpired'), 'errorTwoFactorExpired');
                }
                break;

            case AdminLoginStatus::ERROR_USER_DISABLED:
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorLoginTemporaryNotPossible'),
                    'errorLoginTemporaryNotPossible'
                );
                break;

            case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLoginDataExpired'), 'errorLoginDataExpired');
                break;

            case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                    && $_SESSION['AdminAccount']->TwoFA_expired === true
                ) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTwoFactorExpired'), 'errorTwoFactorExpired');
                }
                break;

            case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoPermission'), 'errorNoPermission');
                break;

            case AdminLoginStatus::LOGIN_OK:
                Backend::getInstance()->reHash();
                $_SESSION['loginIsValid'] = true; // "enable" the "header.tpl"-navigation again
                if ($oAccount->permission('SHOP_UPDATE_VIEW') && $oUpdater->hasPendingUpdates()) {
                    header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'dbupdater.php');
                    exit;
                }
                if (isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0) {
                    redirectToURI($_REQUEST['uri']);
                }
                header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'index.php');
                exit;

                break;
        }
    } elseif ($csrfOK !== true) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCSRF'), 'errorCSRF');
    }
}
$type          = '';
$profilerState = Profiler::getIsActive();
switch ($profilerState) {
    case 0:
    default:
        $type = '';
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
       ->assign('pw_updated', isset($_GET['pw_updated']) && $_GET['pw_updated'] === 'true')
       ->assign('alertError', $alertHelper->alertTypeExists(Alert::TYPE_ERROR))
       ->assign('alertList', $alertHelper)
       ->assign('updateMessage', $updateMessage ?? null);


/**
 * opens the dashboard
 * (prevents code duplication)
 */
function openDashboard()
{
    global $oAccount;

    $smarty = Shop::Smarty();
    if (isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0) {
        redirectToURI($_REQUEST['uri']);
    }
    $_SESSION['loginIsValid'] = true;
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';

        $fsCheck = new Systemcheck_Platform_Filesystem(PFAD_ROOT);
        $fsCheck->getFoldersChecked();

        $smarty->assign('bDashboard', true)
               ->assign('oPermissionStat', $fsCheck->getFolderStats())
               ->assign('bUpdateError', ((isset($_POST['shopupdate']) && $_POST['shopupdate'] === '1') ? '1' : false))
               ->assign('bTemplateDiffers', Template::getInstance()->getVersion() !== APPLICATION_VERSION)
               ->assign('oActiveWidget_arr', getWidgets())
               ->assign('oAvailableWidget_arr', getWidgets(false))
               ->assign('bInstallExists', is_dir(PFAD_ROOT . 'install'));
    }
    $smarty->display('dashboard.tpl');
    exit();
}

/**
 * redirects to a given (base64-encoded) URI
 * (prevents code duplication)
 * @param string $szURI
 */
function redirectToURI($szURI)
{
    header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . base64_decode($szURI));
    exit;
}

unset($_SESSION['AdminAccount']->TwoFA_active);
if ($oAccount->getIsAuthenticated()) {
    Shop::Container()->getGetText()->loadAdminLocale('widgets');
    if (!$oAccount->getIsTwoFaAuthenticated()) {
        $_SESSION['AdminAccount']->TwoFA_active = true;
        // restore first generated token from POST
        $_SESSION['jtl_token'] = $_POST['jtl_token'] ?? '';
        if (isset($_POST['TwoFA_code']) && $_POST['TwoFA_code'] !== '') {
            if ($oAccount->doTwoFA()) {
                Backend::getInstance()->reHash();
                $_SESSION['AdminAccount']->TwoFA_expired = false;
                $_SESSION['AdminAccount']->TwoFA_valid   = true;
                $_SESSION['loginIsValid']                = true;
                openDashboard();
            }
        } else {
            $_SESSION['AdminAccount']->TwoFA_expired = true;
        }
        Shop::Container()->getGetText()->loadAdminLocale('pages/login');
        $oAccount->redirectOnUrl();
        $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0
            ? trim($_REQUEST['uri'])
            : '')
               ->display('login.tpl');
        exit();
    }
    openDashboard();
} else {
    $oAccount->redirectOnUrl();
    if (isset($_GET['errCode']) && (int)$_GET['errCode'] === AdminLoginStatus::ERROR_SESSION_INVALID) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSessionExpired'), 'errorSessionExpired');
    }
    Shop::Container()->getGetText()->loadAdminLocale('pages/login');
    $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0
        ? trim($_REQUEST['uri'])
        : '')
           ->assign('alertError', $alertHelper->alertTypeExists(Alert::TYPE_ERROR))
           ->assign('alertList', $alertHelper)
           ->display('login.tpl');
}
