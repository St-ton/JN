<?php declare(strict_types=1);

use JTL\Debug\DataCollector\Smarty;
use JTL\Filter\Metadata;
use JTL\Language\LanguageHelper;
use JTL\Profiler;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;

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
    handleFatal('Could not load configuration file. For shop installation <a href="install/">click here</a>.');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

defined('DB_HOST') || handleFatal('Kein MySql-Datenbankhost angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_NAME') || handleFatal('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_USER') || handleFatal('Kein MySql-Datenbankbenutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_PASS') || handleFatal('Kein MySql-Datenbankpasswort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

Profiler::start();

/**
 * @deprecated since 5.0.0
 */
define(
    'JTL_VERSION',
    (int)sprintf(
        '%d%02d',
        Version::parse(APPLICATION_VERSION)->getMajor(),
        Version::parse(APPLICATION_VERSION)->getMinor()
    )
);
/**
 * @deprecated since 5.0.0
 */
define('JTL_MINOR_VERSION', Version::parse(APPLICATION_VERSION)->getPatch());

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

try {
    $db = Shop::Container()->getDB();
} catch (Exception $exc) {
    handleFatal($exc->getMessage());
}
if (!defined('CLI_BATCHRUN')) {
    $cache = Shop::Container()->getCache();
    $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
    $lang = LanguageHelper::getInstance($db, $cache);
    if (!JTL_INCLUDE_ONLY_DB) {
        $config   = Shopsetting::getInstance()->getAll();
        $debugbar = Shop::Container()->getDebugBar();
        require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
        Shop::setRouter(new Router($db, $cache, new State(), Shop::Container()->getAlertService(), $config));
        $globalMetaData = Metadata::getGlobalMetaData();
        $session        = (defined('JTLCRON') && JTLCRON === true)
            ? Frontend::getInstance(true, true, 'JTLCRON')
            : Frontend::getInstance();
        Shop::bootstrap(true, $db, $cache);
        executeHook(HOOK_GLOBALINCLUDE_INC);
        $session->deferredUpdate();
        $smarty = JTLSmarty::getInstance();
        $debugbar->addCollector(new Smarty($smarty));
    }
}
