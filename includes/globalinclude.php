<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$nStartzeit = microtime(true);

if (file_exists(__DIR__ . '/config.JTL-Shop.ini.php')) {
    require_once __DIR__ . '/config.JTL-Shop.ini.php';
}

if (defined('PFAD_ROOT')) {
    require_once PFAD_ROOT . 'includes/defines.php';
} else {
    die('Die Konfigurationsdatei des Shops konnte nicht geladen werden! ' .
        'Bei einer Neuinstallation bitte <a href="install/index.php">hier</a> klicken.');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
// existiert Konfiguration?
defined('DB_HOST') || die('Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_NAME') || die('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_USER') || die('Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_PASS') || die('Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

Profiler::start();

$shop = Shop::getInstance();

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}
// PHP memory_limit work around
if (!Shop()->PHPSettingsHelper()->hasMinLimit(64 * 1024 * 1024)) {
    ini_set('memory_limit', '64M');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';

try {
    $GLOBALS['DB'] = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (Exception $exc) {
    die($exc->getMessage());
}
$GLOBALS['bSeo'] = true; //seo module is always available, keep global for compatibility reasons
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
$cache = JTLCache::getInstance()->setJtlCacheConfig();
$conf  = Shop::getSettings([CONF_GLOBAL]);

if (PHP_SAPI !== 'cli'
    && $conf['global']['kaufabwicklung_ssl_nutzen'] === 'P'
    && (!isset($_SERVER['HTTPS']) || (strtolower($_SERVER['HTTPS']) !== 'on' && (int)$_SERVER['HTTPS'] !== 1))
) {
    $https = ((isset($_SERVER['HTTP_X_FORWARDED_HOST']) && $_SERVER['HTTP_X_FORWARDED_HOST'] === 'ssl.webpack.de')
        || (isset($_SERVER['SCRIPT_URI']) && preg_match('/^ssl-id/', $_SERVER['SCRIPT_URI']))
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && preg_match('/^ssl/', $_SERVER['HTTP_X_FORWARDED_HOST']))
    );
    if (!$https) {
        $lang = '';
        if (!standardspracheAktiv(true)) {
            $lang = strpos($_SERVER['REQUEST_URI'], '?')
                ? '&lang=' . $_SESSION['cISOSprache']
                : '?lang=' . $_SESSION['cISOSprache'];
        }
        header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $lang, true, 301);
        exit();
    }
}

if (!JTL_INCLUDE_ONLY_DB) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'parameterhandler.php';
    require_once PFAD_ROOT . PFAD_XAJAX . 'xajax_core/xajax.inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'auswahlassistent_ext_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikelsuchspecial_inc.php';
    $oPluginHookListe_arr = Plugin::getHookList();
    $nSystemlogFlag       = Jtllog::getSytemlogFlag();
    $template             = Template::getInstance();
    $template->check(true);
    $GlobaleEinstellungen         = Shop::getSettings([
        CONF_GLOBAL,
        CONF_RSS,
        CONF_METAANGABEN,
        CONF_KUNDENWERBENKUNDEN,
        CONF_BILDER
    ]);
    $oGlobaleMetaAngabenAssoc_arr = Metadata::getGlobalMetaData();
    executeHook(HOOK_GLOBALINCLUDE_INC);
    $oBoxen              = Boxen::getInstance();
    $session             = (defined('JTLCRON') && JTLCRON === true)
        ? Session::getInstance(true, true, 'JTLCRON')
        : Session::getInstance();
    $bAdminWartungsmodus = false;
    if ($GlobaleEinstellungen['global']['wartungsmodus_aktiviert'] === 'Y'
        && basename($_SERVER['SCRIPT_FILENAME']) !== 'wartung.php'
    ) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
        if (!Shop::isAdmin()) {
            http_response_code(503);
            require_once PFAD_ROOT . 'wartung.php';
            exit;
        }
        $bAdminWartungsmodus = true;
    }
    $GLOBALS['oSprache'] = Sprache::getInstance();
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    Shop::bootstrap();
}
