<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

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
if (isset($_POST['adminlogin']) && intval($_POST['adminlogin']) === 1) {
    $ret['captcha'] = 0;
    $ret['csrf']    = 0;
    if (file_exists(CAPTCHA_LOCKFILE)) {
        if (!isset($_POST['captcha']) || !$_POST['captcha']) {
            $ret['captcha'] = 1;
        }
        if ((!isset($_POST['md5']) || !$_POST['md5'] || ($_POST['md5'] !== md5(PFAD_ROOT . strtoupper($_POST['captcha']))))) {
            $ret['captcha'] = 2;
        }
    }
    // Check if shop version is new enough for csrf validation
    if (version_compare(Shop::getShopVersion(), 400, '>=') === true) {
        // Check if template version is new enough for csrf validation
        $tpl = Template::getInstance();
        if (version_compare($tpl->getVersion(), 400, '>=') === true) {
            if (!validateToken()) {
                $ret['csrf'] = 1;
            }
        }
    }
    if ($ret['captcha'] == 0 && $ret['csrf'] === 0) {
        $cLogin  = $_POST['benutzer'];
        $cPass   = $_POST['passwort'];
        $nReturn = $oAccount->login($cLogin, $cPass);
        switch ($nReturn) {
            case -2:
                @touch(CAPTCHA_LOCKFILE);
                break;

            case -3:
            case -1:
                $cFehler = 'Benutzername oder Passwort falsch';
                break;

            case -4:
                $cFehler = 'Anmeldung zur Zeit nicht m&ouml;glich';
                break;

            case -5:
                $cFehler = 'Anmeldedaten nicht mehr g&uuml;ltig';
                break;

            case 0:
                $cFehler = 'Keine Berechtigungen vorhanden';
                break;

            case 1:
                if (file_exists(CAPTCHA_LOCKFILE)) {
                    unlink(CAPTCHA_LOCKFILE);
                }
                if ($oAccount->permission('SHOP_UPDATE_VIEW')) {
                    if ($oUpdater->hasPendingUpdates()) {
                        header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'dbupdater.php');
                        exit;
                    }
                }
                if (isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0) {
                    $url = base64_decode(trim($_REQUEST['uri']));
                    header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . $url);
                    exit;
                }
                header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'index.php');
                exit;

                break;
        }
    } elseif ($ret['captcha'] != 0) {
        $cFehler = 'Captcha-Code falsch';
    } elseif ($ret['csrf'] !== 0) {
        $cFehler = 'Cross site request forgery!';
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
       ->assign('pw_updated', (isset($_GET['pw_updated']) && $_GET['pw_updated'] === 'true'))
       ->assign('cFehler', $cFehler)
       ->assign('updateMessage', (isset($updateMessage) ? $updateMessage : null));
if ($oAccount->logged()) {
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'permissioncheck_inc.php';
        $cDirAssoc_arr = checkWriteables();
        $oTpl          = Template::getInstance();
        $nTplVersion   = $oTpl->getShopVersion();
        $smarty->assign('bDashboard', true)
               ->assign('oPermissionStat', getPermissionStats($cDirAssoc_arr))
               ->assign('bUpdateError', ((isset($_POST['shopupdate']) && $_POST['shopupdate'] === '1') ? '1' : false))
               ->assign('bTemplateDiffers', JTL_VERSION != $nTplVersion)
               ->assign('oActiveWidget_arr', getWidgets(true))
               ->assign('oAvailableWidget_arr', getWidgets(false))
               ->assign('bInstallExists', is_dir(PFAD_ROOT . 'install'));
    }
    $smarty->display('dashboard.tpl');
} else {
    $oAccount->redirectOnUrl();
    if (isset($_REQUEST['uri']) && strlen(trim($_REQUEST['uri'])) > 0) {
        $smarty->assign('uri', trim($_REQUEST['uri']));
    }
    $smarty->display('login.tpl');
}
