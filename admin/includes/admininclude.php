<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Plugin\Admin\StateChanger;
use JTL\Profiler;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\BackendSmarty;
use JTL\Update\Updater;

if (isset($_REQUEST['safemode'])) {
    $GLOBALS['plgSafeMode'] = in_array(strtolower($_REQUEST['safemode']), ['1', 'on', 'ein', 'true', 'wahr']);
}
const DEFINES_PFAD = __DIR__ . '/../../includes/';
require DEFINES_PFAD . 'config.JTL-Shop.ini.php';
require DEFINES_PFAD . 'defines.php';

error_reporting(ADMIN_LOG_LEVEL);
date_default_timezone_set(SHOP_TIMEZONE);

defined('DB_HOST') || die('Kein MySQL-Datenbankhost angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_NAME') || die('Kein MySQL Datenbankname angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_USER') || die('Kein MySQL-Datenbankbenutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_PASS') || die('Kein MySQL-Datenbankpasswort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

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
$db       = Shop::Container()->getDB();
$cache    = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
$session  = Backend::getInstance();
$lang     = LanguageHelper::getInstance($db, $cache);
$oAccount = Shop::Container()->getAdminAccount();
$updates  = collect([]);
$expired  = collect([]);
Shop::setRouter(new Router(
    $db,
    $cache,
    new State(),
    Shop::Container()->getAlertService(),
    Shopsetting::getInstance()->getAll()
));
$smarty = new BackendSmarty($db);

Shop::Container()->singleton(CaptchaServiceInterface::class, static function () {
    return new SimpleCaptchaService(true);
});
$hasUpdates = (new Updater($db))->hasPendingUpdates();
if ($hasUpdates === false) {
    if (Request::getVar('licensenoticeaccepted') === 'true') {
        $_SESSION['licensenoticeaccepted'] = 0;
    }
    if (Request::postVar('action') === 'disable-expired-plugins' && Form::validateToken()) {
        $sc = new StateChanger($db, $cache);
        foreach ($_POST['pluginID'] as $pluginID) {
            $sc->deactivate((int)$pluginID);
        }
    }
    $mapper         = new Mapper(new Manager($db, $cache));
    $checker        = new Checker(Shop::Container()->getBackendLogService(), $db, $cache);
    $updates        = $checker->getUpdates($mapper);
    $noticeAccepted = (int)($_SESSION['licensenoticeaccepted'] ?? -1);
    if ($noticeAccepted === -1 && SAFE_MODE === false) {
        $expired = $checker->getLicenseViolations($mapper);
    } else {
        $noticeAccepted++;
    }
    if ($noticeAccepted > 5) {
        $noticeAccepted = -1;
    }
    $_SESSION['licensenoticeaccepted'] = $noticeAccepted;
    Shop::bootstrap(false, $db, $cache);
}
$smarty->assign('account', $oAccount->account())
    ->assign('favorites', $oAccount->favorites())
    ->assign('licenseItemUpdates', $updates)
    ->assign('expiredLicenses', $expired)
    ->assign('hasPendingUpdates', $hasUpdates);
