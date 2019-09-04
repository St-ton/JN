<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Notification;
use JTL\Backend\Revision;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

if (!isset($bExtern) || !$bExtern) {
    define('DEFINES_PFAD', __DIR__ . '/../../includes/');
    require DEFINES_PFAD . 'config.JTL-Shop.ini.php';
    require DEFINES_PFAD . 'defines.php';
    require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admindefines.php';
    defined('DB_HOST') || die('Kein MySQL-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_NAME') || die('Kein MySQL Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_USER') || die('Kein MySQL-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_PASS') || die('Kein MySQL-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
}

require PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

define(
    'JTL_VERSION',
    (int)sprintf(
        '%d%02d',
        Version::parse(APPLICATION_VERSION)->getMajor(),
        Version::parse(APPLICATION_VERSION)->getMinor()
    )
); // DEPRECATED since 5.0.0
define('JTL_MINOR_VERSION', (int)Version::parse(APPLICATION_VERSION)->getPatch()); // DEPRECATED since 5.0.0

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}
$db         = Shop::Container()->getDB();
$cache      = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
$session    = Backend::getInstance();
$lang       = LanguageHelper::getInstance($db, $cache);
$oAccount   = Shop::Container()->getAdminAccount();
$updater    = new Updater($db);
$hasUpdates = $updater->hasPendingUpdates();
if ($updater->hasPendingUpdates() && $_SERVER['REQUEST_METHOD'] === 'GET' && strpos($_SERVER['SCRIPT_FILENAME'], 'dbupdater') === false) {
    \header('Location: ' . Shop::getURL(true) . '/' . \PFAD_ADMIN . 'dbupdater.php');
    exit;
}

require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';

Shop::Container()->singleton(CaptchaServiceInterface::class, function () {
    return new SimpleCaptchaService(true);
});
if (!$hasUpdates) {
    Shop::bootstrap(false);
}
if ($oAccount->logged()) {
    if (!$session->isValid()) {
        $oAccount->logout();
        $oAccount->redirectOnFailure(AdminLoginStatus::ERROR_SESSION_INVALID);
    }

    Shop::fire('backend.notification', Notification::getInstance()->buildDefault());
    if (isset($_POST['revision-action'], $_POST['revision-type'], $_POST['revision-id']) && Form::validateToken()) {
        $revision = new Revision($db);
        if ($_POST['revision-action'] === 'restore') {
            $revision->restoreRevision(
                $_POST['revision-type'],
                $_POST['revision-id'],
                Request::postInt('revision-secondary') === 1
            );
        } elseif ($_POST['revision-action'] === 'delete') {
            $revision->deleteRevision($_POST['revision-id']);
        }
    }
}

$pageName = basename($_SERVER['PHP_SELF'], '.php');

Shop::Container()->getGetText()->loadAdminLocale("pages/$pageName");
