<?php declare(strict_types=1);

use JTL\Debug\DataCollector\Smarty;
use JTL\Filter\Metadata;
use JTL\Helpers\PHPSettings;
use JTL\Language\LanguageHelper;
use JTL\Profiler;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

$nStartzeit = microtime(true);

if (file_exists(__DIR__ . '/config.JTL-Shop.ini.php')) {
    require_once __DIR__ . '/config.JTL-Shop.ini.php';
}

/**
 * @param string $message
 */
function handleFatal(string $message): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache', true, 500);
    die($message);
}

if (defined('PFAD_ROOT')) {
    require_once PFAD_ROOT . 'includes/defines.php';
} else {
    handleFatal('Could not get configuration from config file. ' .
        'For shop installation <a href="install/">click here</a>.');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

defined('DB_HOST') || handleFatal('Kein MySql-Datenbankhost angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_NAME') || handleFatal('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_USER') || handleFatal('Kein MySql-Datenbankbenutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_PASS') || handleFatal('Kein MySql-Datenbankpasswort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

Profiler::start();

$db    = null;
$cache = null;
$shop  = Shop::getInstance();

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
// PHP memory_limit work around
if (!PHPSettings::getInstance()->hasMinLimit(64 * 1024 * 1024)) {
    ini_set('memory_limit', '64M');
}

try {
    $db = Shop::Container()->getDB();
} catch (Exception $exc) {
    handleFatal($exc->getMessage());
}
if (!defined('CLI_BATCHRUN')) {
    $cache = Shop::Container()->getCache();
    $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
    $lang = LanguageHelper::getInstance($db, $cache);
}
$config = Shopsetting::getInstance()->getAll();
if (PHP_SAPI !== 'cli'
    && $config['global']['kaufabwicklung_ssl_nutzen'] === 'P'
    && (!isset($_SERVER['HTTPS'])
        || (mb_convert_case($_SERVER['HTTPS'], MB_CASE_LOWER) !== 'on' && (int)$_SERVER['HTTPS'] !== 1))
) {
    $https = ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '' === 'ssl.webpack.de')
        || str_starts_with($_SERVER['SCRIPT_URI'] ?? '', 'ssl-id')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' === 'https')
        || str_starts_with($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '', 'ssl');
    if (!$https && isset($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'])) {
        $lang = '';
        if (!LanguageHelper::isDefaultLanguageActive(true)) {
            $lang = mb_strpos($_SERVER['REQUEST_URI'], '?')
                ? '&lang=' . $_SESSION['cISOSprache']
                : '?lang=' . $_SESSION['cISOSprache'];
        }
        header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $lang, true, 301);
        exit();
    }
}
if (!JTL_INCLUDE_ONLY_DB && !defined('CLI_BATCHRUN')) {
    $debugbar = Shop::Container()->getDebugBar();
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
    Shop::setRouter(new Router($db, $cache, new State(), Shop::Container()->getAlertService(), $config));
    $globalMetaData = Metadata::getGlobalMetaData();
    $session        = (defined('JTLCRON') && JTLCRON === true)
        ? Frontend::getInstance(true, true, 'JTLCRON')
        : Frontend::getInstance();
    Shop::bootstrap();
    executeHook(HOOK_GLOBALINCLUDE_INC);
    $session->deferredUpdate();
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    $debugbar->addCollector(new Smarty(Shop::Smarty()));
}
