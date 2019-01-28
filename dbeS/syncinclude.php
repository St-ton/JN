<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\Synclogin;
use Helpers\FileSystem;

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

$db          = new \DB\NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$cache       = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
$pluginHooks = \Plugin\Helper::getHookList();
$oSprache    = Sprache::getInstance($db, $cache);

/**
 * @param string     $cacheID
 * @param array|null $tags
 */
function clearCacheSync($cacheID, $tags = null)
{
    $cache = Shop::Container()->getCache();
    $cache->flush($cacheID);
    if ($tags !== null) {
        $cache->flushTags($tags);
    }
}

/**
 * @param string $color
 * @return array|bool
 */
function html2rgb($color)
{
    if ($color[0] === '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) === 6) {
        [$r, $g, $b] = [
            $color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]
        ];
    } elseif (strlen($color) === 3) {
        [$r, $g, $b] = [
            $color[0] . $color[0],
            $color[1] . $color[1],
            $color[2] . $color[2]
        ];
    } else {
        return false;
    }

    return [hexdec($r), hexdec($g), hexdec($b)];
}

/**
 * @return bool|string
 */
function checkFile()
{
    if ($_FILES['data']['error'] || (isset($_FILES['data']['size']) && $_FILES['data']['size'] === 0)) {
        Shop::Container()->getLogService()->error(
            'ERROR: incoming: ' . $_FILES['data']['name'] . ' size:' . $_FILES['data']['size'] .
            ' err:' . $_FILES['data']['error']
        );
        $cFehler = 'Fehler beim Datenaustausch - Datei kam nicht an oder Größe 0!';
        switch ($_FILES['data']['error']) {
            case 0:
                $cFehler = 'Datei kam an, aber Dateigröße 0 [0]';
                break;
            case 1:
                $cFehler = 'Dateigröße > upload_max_filesize directive in php.ini [1]';
                break;
            case 2:
                $cFehler = 'Dateigröße > MAX_FILE_SIZE [2]';
                break;
            case 3:
                $cFehler = 'Datei wurde nur zum Teil hochgeladen [3]';
                break;
            case 4:
                $cFehler = 'Es wurde keine Datei hochgeladen [4]';
                break;
            case 6:
                $cFehler = 'Es fehlt ein TMP-Verzeichnis für HTTP Datei-Uploads! Bitte an Hoster wenden! [6]';
                break;
            case 7:
                $cFehler = 'Datei konnte nicht auf Datenträger gespeichert werden! [7]';
                break;
            case 8:
                $cFehler = 'Dateiendung nicht akzeptiert, bitte an Hoster werden! [8]';
                break;
        }
        syncException($cFehler . "\n" . print_r($_FILES, true), FREIDEFINIERBARER_FEHLER);

        return false;
    }
    $tmpDir = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP;
    move_uploaded_file($_FILES['data']['tmp_name'], $tmpDir . basename($_FILES['data']['tmp_name']));
    $_FILES['data']['tmp_name'] = $tmpDir . basename($_FILES['data']['tmp_name']);

    return $tmpDir . basename($_FILES['data']['tmp_name']);
}

/**
 * @return bool
 * @throws Exception
 */
function auth()
{
    if (!isset($_POST['userID'], $_POST['userPWD'])) {
        return false;
    }

    return (new Synclogin())->checkLogin(utf8_encode($_POST['userID']), utf8_encode($_POST['userPWD'])) === true;
}

/**
 * @param string $tablename
 * @param object $object
 * @return mixed
 */
function DBinsert($tablename, $object)
{
    $key = Shop::Container()->getDB()->insert($tablename, $object);
    if (!$key) {
        Shop::Container()->getLogService()->error(
            'DBinsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
            print_r($object, true)
        );
    }

    return $key;
}

/**
 * @param string   $tablename
 * @param array    $objects
 * @param int|bool $del
 */
function DBDelInsert($tablename, $objects, $del)
{
    if (!is_array($objects)) {
        return;
    }
    $db = Shop::Container()->getDB();
    if ($del) {
        $db->query('DELETE FROM ' . $tablename, \DB\ReturnType::DEFAULT);
    }
    foreach ($objects as $object) {
        //hack? unset arrays/objects that would result in nicedb exceptions
        foreach (get_object_vars($object) as $key => $var) {
            if (is_array($var) || is_object($var)) {
                unset($object->$key);
            }
        }
        $key = $db->insert($tablename, $object);
        if (!$key) {
            Shop::Container()->getLogService()->error(
                'DBDelInsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
                print_r($object, true)
            );
        }
    }
}

/**
 * @param string     $tablename
 * @param array      $objects
 * @param string     $pk1
 * @param string|int $pk2
 */
function DBUpdateInsert($tablename, $objects, $pk1, $pk2 = 0)
{
    if (!is_array($objects)) {
        return;
    }
    $db = Shop::Container()->getDB();
    foreach ($objects as $object) {
        if (isset($object->$pk1) && !$pk2 && $pk1 && $object->$pk1) {
            $db->delete($tablename, $pk1, $object->$pk1);
        }
        if (isset($object->$pk2) && $pk1 && $pk2 && $object->$pk1 && $object->$pk2) {
            $db->delete($tablename, [$pk1, $pk2], [$object->$pk1, $object->$pk2]);
        }
        $key = $db->insert($tablename, $object);
        if (!$key) {
            Shop::Container()->getLogService()->error(
                'DBinsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
                print_r($object, true)
            );
        }
    }
}

/**
 * @param array  $elements
 * @param string $child
 * @return array
 */
function getObjectArray($elements, $child)
{
    $obj_arr = [];
    if (is_array($elements) && (is_array($elements[$child]) || is_array($elements[$child . ' attr']))) {
        $cnt = count($elements[$child]);
        if (is_array($elements[$child . ' attr'])) {
            $obj  = new stdClass();
            $keys = array_keys($elements[$child . ' attr']);
            foreach ($keys as $key) {
                if (!$elements[$child . ' attr'][$key]) {
                    Shop::Container()->getLogService()->error(
                        $child . '->' . $key . ' fehlt! XML:' .
                        $elements[$child]
                    );
                }
                $obj->$key = $elements[$child . ' attr'][$key];
            }
            if (is_array($elements[$child])) {
                $keys = array_keys($elements[$child]);
                foreach ($keys as $key) {
                    $obj->$key = $elements[$child][$key];
                }
            }
            $obj_arr[] = $obj;
        } elseif ($cnt > 1) {
            for ($i = 0; $i < $cnt / 2; $i++) {
                unset($obj);
                $obj = new stdClass();
                if (is_array($elements[$child][$i . ' attr'])) {
                    $keys = array_keys($elements[$child][$i . ' attr']);
                    foreach ($keys as $key) {
                        if (!$elements[$child][$i . ' attr'][$key]) {
                            Shop::Container()->getLogService()->error(
                                $child . '[' . $i . ']->' . $key .
                                ' fehlt! XML:' . $elements[$child]
                            );
                        }

                        $obj->$key = $elements[$child][$i . ' attr'][$key];
                    }
                }
                if (is_array($elements[$child][$i])) {
                    $keys = array_keys($elements[$child][$i]);
                    foreach ($keys as $key) {
                        $obj->$key = $elements[$child][$i][$key];
                    }
                }
                $obj_arr[] = $obj;
            }
        }
    }

    return $obj_arr;
}

/**
 * @param string $file
 * @param bool   $isDir
 * @return bool
 */
function removeTemporaryFiles(string $file, bool $isDir = false)
{
    return KEEP_SYNC_FILES
        ? false
        : ($isDir ? FileSystem::delDirRecursively($file) : unlink($file));
}

/**
 * @param array $arr
 * @param array $cExclude_arr
 * @return array
 */
function buildAttributes(&$arr, $cExclude_arr = [])
{
    $attr_arr = [];
    if (is_array($arr)) {
        $keys     = array_keys($arr);
        $keyCount = count($keys);
        for ($i = 0; $i < $keyCount; $i++) {
            if (!in_array($keys[$i], $cExclude_arr) && $keys[$i]{0} === 'k') {
                $attr_arr[$keys[$i]] = $arr[$keys[$i]];
                unset($arr[$keys[$i]]);
            }
        }
    }

    return $attr_arr;
}

/**
 * @param string       $zip
 * @param object|array $xml_obj
 */
function zipRedirect($zip, $xml_obj)
{
    $xmlfile = fopen(PFAD_SYNC_TMP . FILENAME_XML, 'w');
    fwrite($xmlfile, strtr(StringHandler::convertISO(XML_serialize($xml_obj)), "\0", ' '));
    fclose($xmlfile);
    if (file_exists(PFAD_SYNC_TMP . FILENAME_XML)) {
        if (class_exists('ZipArchive')) {
            $archive = new ZipArchive();
            if ($archive->open(PFAD_SYNC_TMP . $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== false
                && $archive->addFile(PFAD_SYNC_TMP . FILENAME_XML)
            ) {
                $archive->close();
                readfile(PFAD_SYNC_TMP . $zip);
                exit;
            }
            $archive->close();
            syncException($archive->getStatusString());
        } else {
            $archive = new PclZip(PFAD_SYNC_TMP . $zip);
            if ($archive->create(PFAD_SYNC_TMP . FILENAME_XML, PCLZIP_OPT_REMOVE_ALL_PATH)) {
                readfile(PFAD_SYNC_TMP . $zip);
                exit;
            }
            syncException($archive->errorInfo(true));
        }
    }
}

/**
 * @param stdClass $obj
 * @param array    $xml
 */
function mapAttributes(&$obj, $xml)
{
    if (is_array($xml)) {
        $keys = array_keys($xml);
        if (is_array($keys)) {
            if ($obj === null) {
                $obj = new stdClass();
            }
            foreach ($keys as $key) {
                $obj->$key = $xml[$key];
            }
        }
    } else {
        Shop::Container()->getLogService()->error(
            'mapAttributes kein Array: XML:' .
            print_r($xml, true)
        );
    }
}

/**
 * @param array $array
 * @return bool
 */
function is_assoc(array $array): bool
{
    return count(array_filter(array_keys($array), '\is_string')) > 0;
}

/**
 * @param stdClass|object $obj
 * @param array           $xml
 * @param array           $map
 */
function mappe(&$obj, $xml, $map)
{
    if ($obj === null) {
        $obj = new stdClass();
    }

    if (!is_assoc($map)) {
        foreach ($map as $key) {
            $obj->$key = $xml[$key] ?? null;
        }
    } else {
        foreach ($map as $key => $value) {
            $val = null;
            if (isset($value) && empty($xml[$key])) {
                $val = $value;
            } elseif (isset($xml[$key])) {
                $val = $xml[$key];
            }
            $obj->$key = $val;
        }
    }
}

/**
 * @param array  $xml
 * @param string $name
 * @param array  $map
 * @return array
 */
function mapArray($xml, $name, $map)
{
    $objects = [];
    $idx     = $name . ' attr';
    if ((isset($xml[$name]) && is_array($xml[$name])) || (isset($xml[$idx]) && is_array($xml[$idx]))) {
        if (isset($xml[$idx]) && is_array($xml[$idx])) {
            $obj = new stdClass();
            mapAttributes($obj, $xml[$idx]);
            mappe($obj, $xml[$name], $map);

            return [$obj];
        }
        if (count($xml[$name]) > 2) {
            $cnt = count($xml[$name]) / 2;
            for ($i = 0; $i < $cnt; $i++) {
                if (!isset($objects[$i]) || $objects[$i] === null) {
                    $objects[$i] = new stdClass();
                }
                mapAttributes($objects[$i], $xml[$name][$i . ' attr']);
                mappe($objects[$i], $xml[$name][$i], $map);
            }
        }
    }

    return $objects;
}

/**
 * @param object $oXmlTree
 * @param array  $mappings
 * @return stdClass
 */
function JTLMapArr($oXmlTree, array $mappings)
{
    $mapped = new stdClass();
    foreach ($oXmlTree->Attributes() as $key => $val) {
        $mapped->{$key} = (string)$val;
    }
    foreach ($mappings as $mapping) {
        if (isset($oXmlTree->{$mapping})) {
            $mapped->{$mapping} = (string)$oXmlTree->{$mapping};
        }
    }

    return $mapped;
}

/**
 * @param array  $xml
 * @param string $table
 * @param array  $map
 * @param int    $del
 */
function XML2DB($xml, $table, $map, $del = 1)
{
    if (isset($xml[$table]) && is_array($xml[$table])) {
        $obj_arr = mapArray($xml, $table, $map);
        DBDelInsert($table, $obj_arr, $del);
    }
}

/**
 * @param array      $xml
 * @param string     $table
 * @param array      $map
 * @param string     $pk1
 * @param int|string $pk2
 */
function updateXMLinDB($xml, $table, $map, $pk1, $pk2 = 0)
{
    $idx = $table . ' attr';
    if ((isset($xml[$table]) && is_array($xml[$table])) || (isset($xml[$idx]) && is_array($xml[$idx]))) {
        DBUpdateInsert($table, mapArray($xml, $table, $map), $pk1, $pk2);
    }
}

/**
 * @param object $product
 * @param array  $customerGroups
 * @return array
 */
function fuelleArtikelKategorieRabatt($product, $customerGroups): array
{
    $affectedProductIDs = [];
    $db                 = Shop::Container()->getDB();
    $db->delete('tartikelkategorierabatt', 'kArtikel', (int)$product->kArtikel);
    if (!is_array($customerGroups) || count($customerGroups) === 0) {
        return $affectedProductIDs;
    }
    foreach ($customerGroups as $oKundengruppe) {
        $oMaxRabatt = $db->queryPrepared(
            'SELECT tkategoriekundengruppe.fRabatt, tkategoriekundengruppe.kKategorie
                FROM tkategoriekundengruppe
                JOIN tkategorieartikel 
                    ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategorieartikel.kArtikel = :kArtikel
                LEFT JOIN tkategoriesichtbarkeit
                    ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :kKundengruppe
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                    AND tkategoriekundengruppe.kKundengruppe = :kKundengruppe
                ORDER BY tkategoriekundengruppe.fRabatt DESC
                LIMIT 1',
            [
                'kArtikel'      => $product->kArtikel,
                'kKundengruppe' => $oKundengruppe->kKundengruppe,
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oMaxRabatt->fRabatt) && $oMaxRabatt->fRabatt > 0) {
            $discount                = new stdClass();
            $discount->kArtikel      = $product->kArtikel;
            $discount->kKundengruppe = $oKundengruppe->kKundengruppe;
            $discount->kKategorie    = $oMaxRabatt->kKategorie;
            $discount->fRabatt       = $oMaxRabatt->fRabatt;
            $db->insert('tartikelkategorierabatt', $discount);
            $affectedProductIDs[] = $product->kArtikel;
        }
    }

    return $affectedProductIDs;
}

/**
 * @param int $categoryID
 * @return void
 */
function setCategoryDiscount(int $categoryID)
{
    $db = Shop::Container()->getDB();
    $db->delete('tartikelkategorierabatt', 'kKategorie', $categoryID);
    $db->queryPrepared(
        'INSERT INTO tartikelkategorierabatt (
            SELECT tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie,
                   MAX(tkategoriekundengruppe.fRabatt) fRabatt
            FROM tkategoriekundengruppe
            INNER JOIN tkategorieartikel 
                ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
            LEFT JOIN tkategoriesichtbarkeit 
                ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                AND tkategoriesichtbarkeit.kKundengruppe = tkategoriekundengruppe.kKundengruppe
            WHERE tkategoriekundengruppe.kKategorie = :categoryID
                AND tkategoriesichtbarkeit.kKategorie IS NULL
            GROUP BY tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie
            HAVING MAX(tkategoriekundengruppe.fRabatt) > 0
        )',
        [
            'categoryID' => $categoryID,
        ],
        \DB\ReturnType::DEFAULT
    );
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY . '_' . $categoryID]);
}

/**
 * @param object $product
 */
function versendeVerfuegbarkeitsbenachrichtigung($product)
{
    if (!($product->fLagerbestand > 0 && $product->kArtikel)) {
        return;
    }
    $db            = Shop::Container()->getDB();
    $subscriptions = $db->selectAll(
        'tverfuegbarkeitsbenachrichtigung',
        ['nStatus', 'kArtikel'],
        [0, $product->kArtikel]
    );
    if (count($subscriptions) === 0) {
        return;
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $product = (new Artikel())->fuelleArtikel($product->kArtikel, Artikel::getDefaultOptions());
    if ($product === null) {
        return;
    }
    $campaign = new Kampagne(KAMPAGNE_INTERN_VERFUEGBARKEIT);
    if ($campaign->kKampagne > 0) {
        $cSep           = strpos($product->cURL, '.php') === false ? '?' : '&';
        $product->cURL .= $cSep . $campaign->cParameter . '=' . $campaign->cWert;
    }
    foreach ($subscriptions as $msg) {
        $obj                                   = new stdClass();
        $obj->tverfuegbarkeitsbenachrichtigung = $msg;
        $obj->tartikel                         = $product;
        $obj->tartikel->cName                  = StringHandler::htmlentitydecode($obj->tartikel->cName);
        $mail                                  = new stdClass();
        $mail->toEmail                         = $msg->cMail;
        $mail->toName                          = ($msg->cVorname || $msg->cNachname)
            ? ($msg->cVorname . ' ' . $msg->cNachname)
            : $msg->cMail;
        $obj->mail                             = $mail;
        sendeMail(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $obj);

        $upd                    = new stdClass();
        $upd->nStatus           = 1;
        $upd->dBenachrichtigtAm = 'NOW()';
        $upd->cAbgeholt         = 'N';
        $db->update(
            'tverfuegbarkeitsbenachrichtigung',
            'kVerfuegbarkeitsbenachrichtigung',
            $msg->kVerfuegbarkeitsbenachrichtigung,
            $upd
        );
    }
}

/**
 * @param int   $productID
 * @param int   $customerGroupID
 * @param float $fVKNetto
 */
function setzePreisverlauf(int $productID, int $customerGroupID, float $fVKNetto)
{
    $db      = Shop::Container()->getDB();
    $history = $db->queryPrepared(
        'SELECT kPreisverlauf, fVKNetto, dDate, IF(dDate = CURDATE(), 1, 0) bToday
            FROM tpreisverlauf
            WHERE kArtikel = :kArtikel
	            AND kKundengruppe = :kKundengruppe
            ORDER BY dDate DESC LIMIT 2',
        [
            'kArtikel'      => $productID,
            'kKundengruppe' => $customerGroupID,
        ],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    if (!empty($history[0]) && (int)$history[0]->bToday === 1) {
        // price for today exists
        if (round($history[0]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // return if there is no difference
            return;
        }
        if (!empty($history[1]) && round($history[1]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // delete todays price if the new price for today is the same as the latest price
            $db->delete('tpreisverlauf', 'kPreisverlauf', (int)$history[0]->kPreisverlauf);
        } else {
            // update if prices are different
            $db->update(
                'tpreisverlauf',
                'kPreisverlauf',
                (int)$history[0]->kPreisverlauf,
                (object)['fVKNetto' => $fVKNetto]
            );
        }
    } else {
        // no price for today exists
        if (!empty($history[0]) && round($history[0]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // return if there is no difference
            return;
        }
        $db->insert('tpreisverlauf', (object)[
            'kArtikel'      => $productID,
            'kKundengruppe' => $customerGroupID,
            'fVKNetto'      => $fVKNetto,
            'dDate'         => 'NOW()',
        ]);
    }
}

/**
 * @param string $error
 */
function unhandledError($error)
{
    syncException($error, FREIDEFINIERBARER_FEHLER);
}

/**
 * @param int $size
 * @return string
 */
function convert($size)
{
    $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

    return @round($size / pow(1024, ($i = (int)floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

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

    return $error;
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
 * @param null|stdClass $image
 * @param int           $productID
 * @param int           $kArtikelPict
 */
function deleteArticleImage($image = null, int $productID = 0, int $kArtikelPict = 0)
{
    $db = Shop::Container()->getDB();
    if ($image === null && $kArtikelPict > 0) {
        $image     = $db->select('tartikelpict', 'kArtikelPict', $kArtikelPict);
        $productID = isset($image->kArtikel) ? (int)$image->kArtikel : 0;
    }
    // Das Bild ist eine Verknüpfung
    if (isset($image->kMainArtikelBild) && $image->kMainArtikelBild > 0 && $productID > 0) {
        // Existiert der Artikel vom Mainbild noch?
        $main = $db->query(
            'SELECT kArtikel
                FROM tartikel
                WHERE kArtikel = (
                    SELECT kArtikel
                        FROM tartikelpict
                        WHERE kArtikelPict = ' . (int)$image->kMainArtikelBild . ')',
            \DB\ReturnType::SINGLE_OBJECT
        );
        // Main Artikel existiert nicht mehr
        if (!isset($main->kArtikel) || (int)$main->kArtikel === 0) {
            // Existiert noch eine andere aktive Verknüpfung auf das Mainbild?
            $productImages = $db->query(
                'SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kMainArtikelBild = ' . (int)$image->kMainArtikelBild . '
                        AND kArtikel != ' . $productID,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            // Lösche das MainArtikelBild
            if (count($productImages) === 0) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $image->cPfad);
                // Bild vom Main aus DB löschen
                $db->delete('tartikelpict', 'kArtikelPict', (int)$image->kMainArtikelBild);
            }
        }
        // Bildverknüpfung aus DB löschen
        $db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
    } elseif (isset($image->kMainArtikelBild) && $image->kMainArtikelBild == 0) {
        // Das Bild ist ein Hauptbild
        // Gibt es Artikel die auf Bilder des zu löschenden Artikel verknüpfen?
        $childProducts = $db->queryPrepared(
            'SELECT kArtikelPict
                FROM tartikelpict
                WHERE kMainArtikelBild = :img',
            ['img' => (int)$image->kArtikelPict],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($childProducts) === 0) {
            // Gibt ein neue Artikel die noch auf den physikalischen Pfad zeigen?
            $oObj = $db->queryPrepared(
                'SELECT COUNT(*) AS nCount
                    FROM tartikelpict
                    WHERE cPfad = :pth',
                ['pth' => $image->cPfad],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oObj->nCount) && $oObj->nCount < 2) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $image->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $image->cPfad);
            }
        } else {
            // Reorder linked images because master imagelink will be deleted
            $kArtikelPictNext = $childProducts[0]->kArtikelPict;
            // this will be the next masterimage
            $db->update(
                'tartikelpict',
                'kArtikelPict',
                (int)$kArtikelPictNext,
                (object)['kMainArtikelBild' => 0]
            );
            // now link other images to the new masterimage
            $db->update(
                'tartikelpict',
                'kMainArtikelBild',
                (int)$image->kArtikelPict,
                (object)['kMainArtikelBild' => (int)$kArtikelPictNext]
            );
        }
        $db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
    }
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $productID]);
}

/**
 * @param object $object
 */
function extractStreet(&$object)
{
    $data = explode(' ', $object->cStrasse);
    if (count($data) > 1) {
        $object->cHausnummer = $data[count($data) - 1];
        unset($data[count($data) - 1]);
        $object->cStrasse = implode(' ', $data);
    }
}

/**
 * @param string $oldSeo
 * @param string $newSeo
 * @return bool
 */
function checkDbeSXmlRedirect($oldSeo, $newSeo)
{
    // Insert into tredirect weil sich das SEO von der Kategorie geändert hat
    if ($oldSeo !== $newSeo && strlen($oldSeo) > 0 && strlen($newSeo) > 0) {
        $oRedirect = new Redirect();
        $xPath_arr = parse_url(Shop::getURL());
        if (isset($xPath_arr['path'])) {
            $cSource = "{$xPath_arr['path']}/{$oldSeo}";
        } else {
            $cSource = '/' . $oldSeo;
        }

        return $oRedirect->saveExt($cSource, $newSeo, true);
    }

    return false;
}

/**
 * @param int         $kKey
 * @param string      $cKey
 * @param int|null    $kSprache
 * @param string|null $cAssoc
 * @return array|null|stdClass
 */
function getSeoFromDB(int $kKey, $cKey, $kSprache = null, $cAssoc = null)
{
    if (!($kKey > 0 && strlen($cKey) > 0)) {
        return null;
    }
    if ($kSprache !== null && (int)$kSprache > 0) {
        $kSprache = (int)$kSprache;
        $oSeo     = Shop::Container()->getDB()->select('tseo', 'kKey', $kKey, 'cKey', $cKey, 'kSprache', $kSprache);
        if (isset($oSeo->kKey) && (int)$oSeo->kKey > 0) {
            return $oSeo;
        }
    } else {
        $seo = Shop::Container()->getDB()->selectAll('tseo', ['kKey', 'cKey'], [$kKey, $cKey]);
        if (is_array($seo) && count($seo) > 0) {
            if ($cAssoc !== null && strlen($cAssoc) > 0) {
                $seoData = [];
                foreach ($seo as $oSeo) {
                    if (isset($oSeo->{$cAssoc})) {
                        $seoData[$oSeo->{$cAssoc}] = $oSeo;
                    }
                }
                if (count($seoData) > 0) {
                    $seo = $seoData;
                }
            }

            return $seo;
        }
    }

    return null;
}

/**
 * @param int      $kArtikel
 * @param int      $kKundengruppe
 * @param int|null $kKunde
 * @return mixed
 */
function handlePriceFormat(int $kArtikel, int $kKundengruppe, int $kKunde = null)
{
    $ins                = new stdClass();
    $ins->kArtikel      = $kArtikel;
    $ins->kKundengruppe = $kKundengruppe;
    if ($kKunde !== null && $kKunde > 0) {
        $ins->kKunde = $kKunde;
        flushCustomerPriceCache($ins->kKunde);
    }

    return Shop::Container()->getDB()->insert('tpreis', $ins);
}

/**
 * Handle new PriceFormat (Wawi >= v.1.00):
 *
 * Sample XML:
 *  <tpreis kPreis="8" kArtikel="15678" kKundenGruppe="1" kKunde="0">
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>100</nAnzahlAb>
 *          <fNettoPreis>0.756303</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>250</nAnzahlAb>
 *          <fNettoPreis>0.714286</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>500</nAnzahlAb>
 *          <fNettoPreis>0.672269</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>750</nAnzahlAb>
 *          <fNettoPreis>0.630252</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>1000</nAnzahlAb>
 *          <fNettoPreis>0.588235</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>2000</nAnzahlAb>
 *          <fNettoPreis>0.420168</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>0</nAnzahlAb>
 *          <fNettoPreis>0.798319</fNettoPreis>
 *      </tpreisdetail>
 *  </tpreis>
 *
 * @param array $xml
 */
function handleNewPriceFormat($xml)
{
    if (!is_array($xml) || !isset($xml['tpreis'])) {
        return;
    }
    $prices = mapArray($xml, 'tpreis', \dbeS\TableMapper::getMapping('mPreis'));
    if (count($prices) === 0) {
        return;
    }
    $db        = Shop::Container()->getDB();
    $productID = (int)$prices[0]->kArtikel;
    $customers = $db->selectAll(
        'tpreis',
        ['kArtikel', 'kKundengruppe'],
        [$productID, 0],
        'kKunde'
    );
    foreach ($customers as $customer) {
        $customerID = (int)$customer->kKunde;
        if ($customerID > 0) {
            flushCustomerPriceCache($customerID);
        }
    }
    $db->query(
        'DELETE p, d
            FROM tpreis AS p
            LEFT JOIN tpreisdetail AS d 
                ON d.kPreis = p.kPreis
            WHERE p.kArtikel = ' . $productID,
        \DB\ReturnType::DEFAULT
    );
    $customerGroupHandled = [];
    foreach ($prices as $i => $price) {
        $priceID         = handlePriceFormat($price->kArtikel, $price->kKundenGruppe, (int)$price->kKunde);
        $details         = empty($xml['tpreis'][$i])
            ? mapArray($xml['tpreis'], 'tpreisdetail', \dbeS\TableMapper::getMapping('mPreisDetail'))
            : mapArray($xml['tpreis'][$i], 'tpreisdetail', \dbeS\TableMapper::getMapping('mPreisDetail'));
        $hasDefaultPrice = false;
        foreach ($details as $preisdetail) {
            $ins = (object)[
                'kPreis'    => $priceID,
                'nAnzahlAb' => $preisdetail->nAnzahlAb,
                'fVKNetto'  => $preisdetail->fNettoPreis
            ];
            $db->insert('tpreisdetail', $ins);
            if ((int)$ins->nAnzahlAb === 0) {
                $hasDefaultPrice = true;
            }
        }
        // default price for customergroup set?
        if (!$hasDefaultPrice && isset($xml['fStandardpreisNetto'])) {
            $ins = (object)[
                'kPreis'    => $priceID,
                'nAnzahlAb' => 0,
                'fVKNetto'  => $xml['fStandardpreisNetto']
            ];
            $db->insert('tpreisdetail', $ins);
        }
        $customerGroupHandled[] = (int)$price->kKundenGruppe;
    }
    // any customergroups with missing tpreis node left?
    foreach (Kundengruppe::getGroups() as $customergroup) {
        $id = $customergroup->getID();
        if (isset($xml['fStandardpreisNetto']) && !in_array($id, $customerGroupHandled, true)) {
            $priceID = handlePriceFormat($productID, $id);
            $ins     = (object)[
                'kPreis'    => $priceID,
                'nAnzahlAb' => 0,
                'fVKNetto'  => $xml['fStandardpreisNetto']
            ];
            $db->insert('tpreisdetail', $ins);
        }
    }
}

/**
 * @param array $objs
 */
function handleOldPriceFormat($objs)
{
    if (!is_array($objs) || count($objs) === 0) {
        return;
    }
    $productID = (int)$objs[0]->kArtikel;
    $customers = Shop::Container()->getDB()->selectAll(
        'tpreis',
        ['kArtikel', 'kKundengruppe'],
        [$productID, 0],
        'kKunde'
    );
    foreach ($customers as $customer) {
        flushCustomerPriceCache((int)$customer->kKunde);
    }
    Shop::Container()->getDB()->query(
        'DELETE p, d
            FROM tpreis AS p
            LEFT JOIN tpreisdetail AS d 
                ON d.kPreis = p.kPreis
            WHERE p.kArtikel = ' . $productID,
        \DB\ReturnType::DEFAULT
    );
    foreach ($objs as $obj) {
        $priceID = handlePriceFormat((int)$obj->kArtikel, (int)$obj->kKundengruppe);
        insertPriceDetail($obj, 0, $priceID);
        for ($i = 1; $i <= 5; $i++) {
            insertPriceDetail($obj, $i, $priceID);
        }
    }
}

/**
 * @param int[] $productIDs
 */
function handlePriceRange(array $productIDs)
{
    $db = Shop::Container()->getDB();
    $db->executeQuery(
        'DELETE FROM tpricerange
            WHERE kArtikel IN (' . implode(',', $productIDs) . ')',
        \DB\ReturnType::DEFAULT
    );
    $uniqueProductIDs = implode(',', array_unique($productIDs));
    $db->executeQuery(
        'INSERT INTO tpricerange
            (kArtikel, kKundengruppe, kKunde, nRangeType, fVKNettoMin, fVKNettoMax, nLagerAnzahlMax, dStart, dEnde)
            SELECT baseprice.kArtikel,
                COALESCE(baseprice.kKundengruppe, 0) AS kKundengruppe,
                COALESCE(baseprice.kKunde, 0) AS kKunde,
                baseprice.nRangeType,
                MIN(IF(varaufpreis.fMinAufpreisNetto IS NULL,
                    baseprice.fVKNetto, baseprice.fVKNetto + varaufpreis.fMinAufpreisNetto)) fVKNettoMin,
                MAX(IF(varaufpreis.fMaxAufpreisNetto IS NULL,
                    baseprice.fVKNetto, baseprice.fVKNetto + varaufpreis.fMaxAufpreisNetto)) fVKNettoMax,
                baseprice.nLagerAnzahlMax,
                baseprice.dStart,
                baseprice.dEnde
            FROM (
                SELECT IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) kArtikel,
                    tartikel.kArtikel kKindArtikel,
                    tartikel.nIstVater,
                    tpreis.kKundengruppe,
                    tpreis.kKunde,
                    IF (tpreis.kKundengruppe > 0, 9, 1) nRangeType,
                    null nLagerAnzahlMax,
                    tpreisdetail.fVKNetto,
                    null dStart, null dEnde
                FROM tartikel
                INNER JOIN tpreis 
                    ON tpreis.kArtikel = tartikel.kArtikel
                INNER JOIN tpreisdetail 
                    ON tpreisdetail.kPreis = tpreis.kPreis
                WHERE IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) IN ('
                    . $uniqueProductIDs . ')

                UNION ALL

                SELECT IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) kArtikel,
                    tartikel.kArtikel kKindArtikel,
                    tartikel.nIstVater,
                    tsonderpreise.kKundengruppe,
                    null kKunde,
                    IF(tartikelsonderpreis.nIstAnzahl = 0 AND tartikelsonderpreis.nIstDatum = 0, 5, 3) nRangeType,
                    IF(tartikelsonderpreis.nIstAnzahl = 0, null, tartikelsonderpreis.nAnzahl) nLagerAnzahlMax,
                    IF(tsonderpreise.fNettoPreis < tpreisdetail.fVKNetto, 
                        tsonderpreise.fNettoPreis, tpreisdetail.fVKNetto) fVKNetto,
                    tartikelsonderpreis.dStart dStart,
                    IF(tartikelsonderpreis.nIstDatum = 0, null, tartikelsonderpreis.dEnde) dEnde
                FROM tartikel
                INNER JOIN tpreis 
                    ON tpreis.kArtikel = tartikel.kArtikel
	            INNER JOIN tpreisdetail 
	                ON tpreisdetail.kPreis = tpreis.kPreis
                INNER JOIN tartikelsonderpreis 
                    ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                INNER JOIN tsonderpreise 
                    ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                WHERE tartikelsonderpreis.cAktiv = \'Y\'
                    AND IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) IN ('
                        . $uniqueProductIDs . ')
            ) baseprice
            LEFT JOIN (
                SELECT variations.kArtikel, variations.kKundengruppe,
                    SUM(variations.fMinAufpreisNetto) fMinAufpreisNetto,
                    SUM(variations.fMaxAufpreisNetto) fMaxAufpreisNetto
                FROM (
                    SELECT teigenschaft.kArtikel,
                        tkundengruppe.kKundengruppe,
                        teigenschaft.kEigenschaft,
                        MIN(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto, 
                            teigenschaftwert.fAufpreisNetto)) fMinAufpreisNetto,
                        MAX(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto, 
                            teigenschaftwert.fAufpreisNetto)) fMaxAufpreisNetto
                    FROM teigenschaft
                    INNER JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    JOIN tkundengruppe
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = tkundengruppe.kKundengruppe
                    WHERE teigenschaft.kArtikel IN (' . $uniqueProductIDs . ')
                    GROUP BY teigenschaft.kArtikel, tkundengruppe.kKundengruppe, teigenschaft.kEigenschaft
                ) variations
                GROUP BY variations.kArtikel, variations.kKundengruppe
            ) varaufpreis 
                ON varaufpreis.kArtikel = baseprice.kKindArtikel 
                AND baseprice.nIstVater = 0
            WHERE baseprice.kArtikel IN (' . $uniqueProductIDs . ')
            GROUP BY baseprice.kArtikel,
                baseprice.kKundengruppe,
                baseprice.kKunde,
                baseprice.nRangeType,
                baseprice.nLagerAnzahlMax,
                baseprice.dStart,
                baseprice.dEnde',
        \DB\ReturnType::DEFAULT
    );
}

/**
 * @param object $obj
 * @param int    $index
 * @param int    $priceId
 */
function insertPriceDetail($obj, $index, $priceId)
{
    $count = 'nAnzahl' . $index;
    $price = 'fPreis' . $index;

    if ((isset($obj->{$count}) && (int)$obj->{$count} > 0) || $index === 0) {
        $ins            = new stdClass();
        $ins->kPreis    = $priceId;
        $ins->nAnzahlAb = $index === 0 ? 0 : $obj->{$count};
        $ins->fVKNetto  = $index === 0 ? $obj->fVKNetto : $obj->{$price};

        Shop::Container()->getDB()->insert('tpreisdetail', $ins);
    }
}

/**
 * @param string $salutation
 * @return string
 */
function mappeWawiAnrede2ShopAnrede($salutation)
{
    $salutation = strtolower($salutation);
    if ($salutation === 'w' || $salutation === 'm') {
        return $salutation;
    }
    if ($salutation === 'frau' || $salutation === 'mrs' || $salutation === 'mrs.') {
        return 'w';
    }

    return 'm';
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

/**
 * flush object cache for category tree
 *
 * @return int
 */
function flushCategoryTreeCache()
{
    return Shop::Container()->getCache()->flushTags(['jtl_category_tree']);
}

/**
 * @param int $kKunde
 * @return bool|int
 */
function flushCustomerPriceCache(int $kKunde)
{
    return Shop::Container()->getCache()->flush('custprice_' . $kKunde);
}

/**
 * @param string $zipFile
 * @param string $targetPath
 * @param string $source
 * @return array|bool
 */
function unzipSyncFiles($zipFile, $targetPath, $source = '')
{
    if ($zipFile === false) {
        return false;
    }
    if (class_exists('ZipArchive')) {
        $archive = new ZipArchive();
        $open    = $archive->open($zipFile);
        if (!$open) {
            Shop::Container()->getLogService()->error('unzipSyncFiles: Kann Datei nicht öffnen: ' . $zipFile);

            return false;
        }
        $filenames = [];
        if (is_dir($targetPath) || (mkdir($targetPath) && is_dir($targetPath))) {
            for ($i = 0; $i < $archive->numFiles; ++$i) {
                $filenames[] = $targetPath . $archive->getNameIndex($i);
            }
            if ($archive->numFiles > 0 && !$archive->extractTo($targetPath)) {
                return false;
            }
            $archive->close();

            return array_filter(array_map(function ($e) {
                return file_exists($e)
                    ? $e
                    : null;
            }, $filenames));
        }
    } else {
        Shop::Container()->getLogService()->notice('Achtung: Klasse ZipArchive wurde nicht gefunden - ' .
            ' bitte PHP-Konfiguration überprüfen.');
        $archive = new PclZip($zipFile);
        if (($list = $archive->listContent()) !== 0 && $archive->extract(PCLZIP_OPT_PATH, $targetPath)) {
            $filenames = [];
            foreach ($list as $file) {
                $filenames[] = $targetPath . $file['filename'];
            }

            return $filenames;
        }
    }

    return false;
}

ob_start('handleError');
