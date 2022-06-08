<?php declare(strict_types=1);

use JTL\Backend\AdminLoginStatus;
use JTL\Language\LanguageHelper;
use JTL\Profiler;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Update\Updater;

if (!isset($bExtern) || !$bExtern) {
    if (isset($_REQUEST['safemode'])) {
        $GLOBALS['plgSafeMode'] = in_array(strtolower($_REQUEST['safemode']), ['1', 'on', 'ein', 'true', 'wahr']);
    }
    define('DEFINES_PFAD', __DIR__ . '/../../includes/');
    require DEFINES_PFAD . 'config.JTL-Shop.ini.php';
    require DEFINES_PFAD . 'defines.php';
    require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admindefines.php';
    defined('DB_HOST') || die('Kein MySQL-Datenbankhost angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_NAME') || die('Kein MySQL Datenbankname angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_USER') || die('Kein MySQL-Datenbankbenutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_PASS') || die('Kein MySQL-Datenbankpasswort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
}

require PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

if (!function_exists('Shop')) {
    /**
     * @return Shop
     * @deprecated since 5.2.0
     */
    function Shop(): Shop
    {
        trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
        return Shop::getInstance();
    }
}

/**
 * @param string $route
 * @return void
 */
function routeRedirect(string $route): void
{
    header('Location: ' . Shop::getAdminURL() . '/' . $route, true, 308);
    exit();
}

Profiler::start();
Shop::setIsFrontend(false);
$db         = Shop::Container()->getDB();
$cache      = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
$session    = Backend::getInstance();
$lang       = LanguageHelper::getInstance($db, $cache);
$oAccount   = Shop::Container()->getAdminAccount();
$loggedIn   = $oAccount->logged();
if ($loggedIn && isset($GLOBALS['plgSafeMode'])) {
    if ($GLOBALS['plgSafeMode']) {
        touch(SAFE_MODE_LOCK);
    } elseif (file_exists(SAFE_MODE_LOCK)) {
        unlink(SAFE_MODE_LOCK);
    }
}

if (!empty($_COOKIE['JTLSHOP']) && empty($_SESSION['frontendUpToDate'])) {
    $adminToken   = $_SESSION['jtl_token'];
    $adminLangTag = $_SESSION['AdminAccount']->language;
    $eSIdAdm      = session_id();
    session_write_close();
    session_name('JTLSHOP');
    session_id($_COOKIE['JTLSHOP']);
    session_start();
    $_SESSION['loggedAsAdmin'] = $loggedIn;
    $_SESSION['adminToken']    = $adminToken;
    $_SESSION['adminLangTag']  = $adminLangTag;
    session_write_close();
    session_name('eSIdAdm');
    session_id($eSIdAdm);
    $session = new Backend();
    $session::set('frontendUpToDate', true);
}
Shop::setRouter(new Router(
    $db,
    $cache,
    new State(),
    Shop::Container()->getAlertService(),
    Shopsetting::getInstance()->getAll()
));
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';

Shop::Container()->singleton(CaptchaServiceInterface::class, static function () {
    return new SimpleCaptchaService(true);
});
if ((new Updater($db))->hasPendingUpdates() === false) {
    Shop::bootstrap(false);
}
if ($loggedIn && !$session->isValid()) {
    $oAccount->logout();
    $oAccount->redirectOnFailure(AdminLoginStatus::ERROR_SESSION_INVALID);
}
