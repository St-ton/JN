<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Sprache;
use JTL\Plugin\Helper;
use JTL\DB\NiceDB;

define('DEFINES_PFAD', '../includes/');
define('FREIDEFINIERBARER_FEHLER', 8);

define('FILENAME_XML', 'data.xml');
define('FILENAME_KUNDENZIP', 'kunden.jtl');
define('FILENAME_BESTELLUNGENZIP', 'bestellungen.jtl');

define('LIMIT_KUNDEN', 100);
define('LIMIT_VERFUEGBARKEITSBENACHRICHTIGUNGEN', 100);
define('LIMIT_UPLOADQUEUE', 100);
define('LIMIT_BESTELLUNGEN', 100);

define('AUTO_SITEMAP', 1);
define('AUTO_RSS', 1);

require_once DEFINES_PFAD . 'config.JTL-Shop.ini.php';
require_once DEFINES_PFAD . 'defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'parameterhandler.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

$shop = Shop::getInstance();
error_reporting(SYNC_LOG_LEVEL);
if (!is_writable(PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP)) {
    syncException(
        'Fehler beim Abgleich: Das Verzeichnis ' .
        PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . ' ist nicht beschreibbar!',
        FREIDEFINIERBARER_FEHLER
    );
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_DBES . 'mappings.php';

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}

$db          = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$cache       = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
$pluginHooks = Helper::getHookList();
$oSprache    = Sprache::getInstance($db, $cache);

/**
 * @param string $error
 * @return string
 */
function translateError($error)
{
    if (preg_match('/Maximum execution time of (\d+) second.? exceeded/', $error, $matches)) {
        $seconds = (int)$matches[1];
        $error   = 'Maximale Ausführungszeit von ' . $seconds . ' Sekunden überschritten';
    } elseif (preg_match('/Allowed memory size of (\d+) bytes exhausted/', $error, $matches)) {
        $limit = (int)$matches[1];
        $error = 'Erlaubte Speichergröße von ' . $limit . ' Bytes erschöpft';
    }

    return utf8_decode($error);
}

/**
 * @param mixed $output
 * @return string
 */
function handleError($output)
{
    $error = error_get_last();
    if ($error['type'] === 1) {
        $error  = translateError($error['message']) . "\n";
        $error .= 'Datei: ' . $error['file'];
        Shop::Container()->getLogService()->error($error);

        return $error;
    }

    return $output;
}

/**
 * prints fatal sync exception and exits with die()
 *
 * wawi codes:
 * 0: HTTP_NOERROR
 * 1: HTTP_DBERROR
 * 2: AUTH OK, ZIP CORRUPT
 * 3: HTTP_LOGIN
 * 4: HTTP_AUTH
 * 5: HTTP_BADINPUT
 * 6: HTTP_AUTHINVALID
 * 7: HTTP_AUTHCLOSED
 * 8: HTTP_CUSTOMERR
 * 9: HTTP_EBAYERROR
 *
 * @param string $msg Exception Message
 * @param int    $wawiExceptionCode int code (0-9)
 */
function syncException(string $msg, int $wawiExceptionCode = null)
{
    $output = '';
    if ($wawiExceptionCode !== null) {
        $output .= $wawiExceptionCode . "\n";
    }
    $output .= $msg;
    Shop::Container()->getLogService()->error('SyncException: ' . $output);
    die(mb_convert_encoding($output, 'ISO-8859-1', 'auto'));
}

ob_start('handleError');
