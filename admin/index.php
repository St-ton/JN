<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
/** @global AdminAccount $oAccount */
$oUpdater = new Updater();
$cFehler  = '';

//Work Around => Update 300 => 308
if ($oUpdater->getCurrentDatabaseVersion() < 308) {
    $oAdmin = Shop::DB()->query("SELECT * FROM tadminlogin LIMIT 1", 1);
    if (is_object($oAdmin) && !isset($oAdmin->kAdminlogingruppe)) {
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD `dLetzterLogin` DATETIME NOT NULL", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` CHANGE  `cName`  `cLogin` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `cName` VARCHAR( 255 ) NOT NULL AFTER  `cPass` ;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `bAktiv` BOOL NOT NULL DEFAULT  '1';", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `kAdminlogin` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` CHANGE  `dLetzterLogin`  `dLetzterLogin` DATETIME NULL;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `dGueltigBis` DATETIME NULL AFTER  `dLetzterLogin` ;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `cMail` VARCHAR( 255 ) NOT NULL AFTER  `cName` ;", 3);
        Shop::DB()->query("ALTER TABLE `tadminlogin` ADD  `kAdminlogingruppe` INT( 10 ) UNSIGNED NOT NULL AFTER  `cMail` ;", 3);
        Shop::DB()->query("UPDATE `tadminlogin` SET `kAdminlogingruppe`=1;", 3);
    }
}
// Login
if (isset($_POST['adminlogin']) && (int)$_POST['adminlogin'] === 1) {
    $ret['captcha'] = 0;
    $ret['csrf']    = 0;
    if (file_exists(CAPTCHA_LOCKFILE)) {
        if (!isset($_POST['captcha']) || !$_POST['captcha']) {
            $ret['captcha'] = 1;
        }
        if (!isset($_POST['md5']) || !$_POST['md5'] || ($_POST['md5'] !== md5(PFAD_ROOT . strtoupper($_POST['captcha'])))) {
            $ret['captcha'] = 2;
        }
    }
    // Check if shop version is new enough for csrf validation
    if (version_compare(Shop::getShopVersion(), 400, '>=') === true) {
        // Check if template version is new enough for csrf validation
        $tpl = AdminTemplate::getInstance();
        if ($tpl::$cTemplate === 'bootstrap' && !validateToken()) {
            $ret['csrf'] = 1;
        }
    }
    $loginName = isset($_POST['benutzer'])
        ? StringHandler::filterXSS(Shop::DB()->escape($_POST['benutzer']))
        : '---';
    if ($ret['captcha'] === 0 && $ret['csrf'] === 0) {
        $cLogin  = $_POST['benutzer'];
        $cPass   = $_POST['passwort'];
        $nReturn = $oAccount->login($cLogin, $cPass);
        switch ($nReturn) {
            case AdminAccount::ERROR_INVALID_PASSWORD_LOCKED:
                @touch(CAPTCHA_LOCKFILE);
                break;

            case AdminAccount::ERROR_USER_NOT_FOUND:
            case AdminAccount::ERROR_INVALID_PASSWORD:
                $cFehler = 'Benutzername oder Passwort falsch';
                if (isset($_SESSION['AdminAccount']->TwoFA_expired) && true === $_SESSION['AdminAccount']->TwoFA_expired) {
                    $cFehler = '2-Faktor-Auth-Code abgelaufen';
                }
                Jtllog::writeLog(
                    'Login für Nutzer ' . $loginName . ' fehlgeschlagen.',
                    JTLLOG_LEVEL_ERROR,
                    true
                );
                break;

            case AdminAccount::ERROR_USER_DISABLED:
                $cFehler = 'Anmeldung zur Zeit nicht m&ouml;glich';
                break;

            case AdminAccount::ERROR_LOGIN_EXPIRED:
                $cFehler = 'Anmeldedaten nicht mehr g&uuml;ltig';
                Jtllog::writeLog(
                    'Login für Nutzer ' . $loginName . ' fehlgeschlagen - abgelaufene Anmeldedaten.',
                    JTLLOG_LEVEL_ERROR,
                    true
                );
                break;

            case AdminAccount::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                if (isset($_SESSION['AdminAccount']->TwoFA_expired) && true === $_SESSION['AdminAccount']->TwoFA_expired) {
                    $cFehler = '2-Faktor-Authentifizierungs-Code abgelaufen';
                    Jtllog::writeLog(
                        'Login für Nutzer ' . $loginName . ' fehlgeschlagen - 2-Faktor-Authentifizierung abgelaufen.',
                        JTLLOG_LEVEL_ERROR,
                        true
                    );
                }
                break;

            case AdminAccount::ERROR_NOT_AUTHORIZED:
                $cFehler = 'Keine Berechtigungen vorhanden';
                break;

            case AdminAccount::LOGIN_OK:
                $_SESSION['loginIsValid'] = true; // "enable" the "header.tpl"-navigation again
                if (file_exists(CAPTCHA_LOCKFILE)) {
                    unlink(CAPTCHA_LOCKFILE);
                }
                Jtllog::writeLog(
                    'Nutzer ' . $loginName . ' hat sich erfolgreich angemeldet.',
                    JTLLOG_LEVEL_NOTICE
                );
                if ($oAccount->permission('SHOP_UPDATE_VIEW') && $oUpdater->hasPendingUpdates()) {
                    header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'dbupdater.php');
                    exit;
                }
                if (isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0) {
                    redirectToURI($_REQUEST['uri']);
                }
                header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'index.php');
                exit;

                break;
        }
    } elseif ($ret['captcha'] !== 0) {
        $cFehler = 'Captcha-Code falsch';
        Jtllog::writeLog(
            'Login für Nutzer ' . $loginName . ' fehlgeschlagen - fehlerhafter Captcha-Code.',
            JTLLOG_LEVEL_ERROR,
            true
        );
    } elseif ($ret['csrf'] !== 0) {
        $cFehler = 'Cross site request forgery! Sind Cookies aktiviert?';
        Jtllog::writeLog(
            'Login für Nutzer ' . $loginName . ' fehlgeschlagen - kein CSRF-Token.',
            JTLLOG_LEVEL_ERROR,
            true
        );
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
if (file_exists(CAPTCHA_LOCKFILE)) {
    $smarty->assign('code_adminlogin', generiereCaptchaCode(3));
}
$smarty->assign('bProfilerActive', $profilerState !== 0)
       ->assign('profilerType', $type)
       ->assign('pw_updated', isset($_GET['pw_updated']) && $_GET['pw_updated'] === 'true')
       ->assign('cFehler', $cFehler)
       ->assign('updateMessage', (isset($updateMessage) ? $updateMessage : null));


/**
 * opens the dashboard
 * (prevents code duplication)
 */
function openDashboard()
{
    global $oAccount, $smarty;

    if (isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0) {
        redirectToURI($_REQUEST['uri']);
    }
    $_SESSION['loginIsValid'] = true;
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';

        $oFsCheck = new Systemcheck_Platform_Filesystem(PFAD_ROOT);
        $oFsCheck->getFoldersChecked();

        $smarty->assign('bDashboard', true)
               ->assign('oPermissionStat', $oFsCheck->getFolderStats())
               ->assign('bUpdateError', ((isset($_POST['shopupdate']) && $_POST['shopupdate'] === '1') ? '1' : false))
               ->assign('bTemplateDiffers', JTL_VERSION !== Template::getInstance()->getShopVersion())
               ->assign('oActiveWidget_arr', getWidgets(true))
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
    // at this point, the user is logged in with his regular credentials
    if (!$oAccount->getIsTwoFaAuthenticated()) {
        // activate the 2FA-code input-field in the login-template(-page)
        $_SESSION['AdminAccount']->TwoFA_active = true;
        $_SESSION['jtl_token']                  = isset($_POST['jtl_token'])
            ? $_POST['jtl_token']
            : ''; // restore first generated token from POST!
        // if our check failed, we redirect to login
        if (isset($_POST['TwoFA_code']) && '' !== $_POST['TwoFA_code']) {
            if ($oAccount->doTwoFA()) {
                $_SESSION['AdminAccount']->TwoFA_expired = false;
                $_SESSION['AdminAccount']->TwoFA_valid   = true;
                $_SESSION['loginIsValid']                = true; // "enable" the "header.tpl"-navigation again
                $smarty->assign('cFehler', ''); // reset a previously (falsely arised) error-message

                openDashboard(); // and exit here
            }
        } else {
            $_SESSION['AdminAccount']->TwoFA_expired = true;
        }
        // "redirect" to the "login not valid"
        // (we've received a wrong code and give the user the chance to retry)
        $oAccount->redirectOnUrl();
        $smarty->assign('uri', isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0
            ? trim($_REQUEST['uri'])
            : '')
               ->display('login.tpl');
        exit();
    }
    openDashboard();
} else {
    $oAccount->redirectOnUrl();
    $smarty->assign('uri', isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0
        ? trim($_REQUEST['uri'])
        : '')
           ->display('login.tpl');
}
