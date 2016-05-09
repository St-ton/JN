<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

define('DEFINES_PFAD', '../includes/');
define('FREIDEFINIERBARER_FEHLER', '8');

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
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Shop.php';

$shop = Shop::getInstance();
error_reporting(SYNC_LOG_LEVEL);
if (!is_writable(PFAD_SYNC_TMP)) {
    syncException('Fehler beim Abgleich: Das Shop-Verzeichnis dbeS/' . PFAD_SYNC_TMP . ' ist nicht durch den Web-User beschreibbar!', 8);
}
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ImageCloud.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Path.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.StringHandler.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceMail.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Nice.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Synclogin.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Shopsetting.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Lieferadresse.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Rechnungsadresse.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';
require_once PFAD_ROOT . PFAD_DBES . 'mappings.php';

//datenbankverbindung aufbauen
$DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLCache.php';
$cache = JTLCache::getInstance();
$cache->setJtlCacheConfig();

$GLOBALS['bSeo'] = true; //compatibility!
// Liste aller Hooks, die momentan im Shop gebraucht werden könnten
// An jedem Hook hängt ein Array mit Plugin die diesen Hook benutzen
$oPluginHookListe_arr = Plugin::getHookList();
//globale Sprache
$oSprache = Sprache::getInstance(true);

/**
 * @param      $cacheID
 * @param null $tags
 */
function clearCacheSync($cacheID, $tags = null)
{
    $cache = Shop::Cache();
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
        list($r, $g, $b) = array(
            $color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]);
    } elseif (strlen($color) === 3) {
        list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return false;
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array($r, $g, $b);
}

/**
 *
 */
function checkFile()
{
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('incoming: ' . $_FILES['data']['name'] . ' size:' . $_FILES['data']['size'], JTLLOG_LEVEL_DEBUG, false, 'syncinclude_xml');
    }
    if ($_FILES['data']['error'] || (isset($_FILES['data']['size']) && $_FILES['data']['size'] == 0)) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('ERROR: incoming: ' . $_FILES['data']['name'] . ' size:' . $_FILES['data']['size'] . ' err:' . $_FILES['data']['error'], JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
        }
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

        syncException($cFehler . "\n" . print_r($_FILES, true), 8);
    } else {
        move_uploaded_file($_FILES['data']['tmp_name'], PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']));
        $_FILES['data']['tmp_name'] = PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']);
    }
}

/**
 * @return bool
 */
function auth()
{
    $cName      = $_POST['userID'];
    $cPass      = $_POST['userPWD'];
    $loginDaten = Shop::DB()->query("SELECT * FROM tsynclogin", 1);

    return ($cName === $loginDaten->cName && $cPass === $loginDaten->cPass);
}

/**
 * @param string $tablename
 * @param object $object
 * @return mixed
 */
function DBinsert($tablename, $object)
{
    $key = Shop::DB()->insert($tablename, $object);
    if (!$key && Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('DBinsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' . print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
    }

    return $key;
}

/**
 * @param string   $tablename
 * @param array    $object_arr
 * @param int|bool $del
 */
function DBDelInsert($tablename, $object_arr, $del)
{
    if (is_array($object_arr)) {
        if ($del) {
            Shop::DB()->query("DELETE FROM $tablename", 4);
        }
        foreach ($object_arr as $object) {
            //hack? unset arrays/objects that would result in nicedb exceptions
            foreach(get_object_vars($object) as $key=>$var) {
                if (is_array($var) || is_object($var)) {
                    unset($object->$key);
                }
            }
            $key = Shop::DB()->insert($tablename, $object);
            if (!$key && Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
                Jtllog::writeLog('DBDelInsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' . print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
            }
        }
    }
}

/**
 * @param string     $tablename
 * @param array      $object_arr
 * @param string     $pk1
 * @param string|int $pk2
 */
function DBUpdateInsert($tablename, $object_arr, $pk1, $pk2 = 0)
{
    if (is_array($object_arr)) {
        foreach ($object_arr as $object) {
            if (isset($object->$pk1) && !$pk2 && $pk1 && $object->$pk1) {
                Shop::DB()->query("DELETE FROM $tablename WHERE $pk1=" . $object->$pk1, 4);
            }
            if (isset($object->$pk2) && $pk1 && $pk2 && $object->$pk1 && $object->$pk2) {
                Shop::DB()->query("DELETE FROM $tablename WHERE $pk1=" . $object->$pk1 . " AND $pk2=" . $object->$pk2, 4);
            }
            $key = Shop::DB()->insert($tablename, $object);
            if (!$key && Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
                Jtllog::writeLog('DBUpdateInsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' . print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
            }
        }
    }
}

/**
 * @param array $elements
 * @param string $child
 * @return array
 */
function getObjectArray($elements, $child)
{
    $obj_arr = array();
    if (is_array($elements) && (is_array($elements[$child]) || is_array($elements[$child . ' attr']))) {
        $cnt = count($elements[$child]);
        if (is_array($elements[$child . ' attr'])) {
            $obj = new stdClass();
            if (is_array($elements[$child . ' attr'])) {
                $keys = array_keys($elements[$child . ' attr']);
                foreach ($keys as $key) {
                    if (!$elements[$child . ' attr'][$key]) {
                        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
                            Jtllog::writeLog($child . '->' . $key . ' fehlt! XML:' . $elements[$child], JTLLOG_LEVEL_ERROR, false, 'syncinclude');
                        }
                    }
                    $obj->$key = $elements[$child . ' attr'][$key];
                }
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
                            if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
                                Jtllog::writeLog($child . '[' . $i . ']->' . $key . ' fehlt! XML:' . $elements[$child], JTLLOG_LEVEL_ERROR, false, 'syncinclude');
                            }
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
function removeTemporaryFiles($file, $isDir = false)
{
    if (!KEEP_SYNC_FILES) {
        return $isDir ? @rmdir($file) : @unlink($file);
    }

    return false;
}

/**
 * @param array $arr
 * @param array $cExclude_arr
 * @return array
 */
function buildAttributes(&$arr, $cExclude_arr = array())
{
    $attr_arr = array();
    if (is_array($arr)) {
        $keys     = array_keys($arr);
        $keyCount = count($keys);
        for ($i = 0; $i < $keyCount; $i++) {
            if (!in_array($keys[$i], $cExclude_arr)) {
                if ($keys[$i]{0} === 'k') {
                    $attr_arr[$keys[$i]] = $arr[$keys[$i]];
                    unset($arr[$keys[$i]]);
                }
            }
        }
    }

    return $attr_arr;
}

/**
 * @param string $zip
 * @param object $xml_obj
 */
function zipRedirect($zip, $xml_obj)
{
    $xmlfile = fopen(PFAD_SYNC_TMP . FILENAME_XML, 'w');
    fwrite($xmlfile, strtr(XML_serialize($xml_obj), "\0", ' '));
    fclose($xmlfile);
    if (file_exists(PFAD_SYNC_TMP . FILENAME_XML)) {
        $archive = new PclZip(PFAD_SYNC_TMP . $zip);
        if ($archive->create(PFAD_SYNC_TMP . FILENAME_XML, PCLZIP_OPT_REMOVE_ALL_PATH)) {
            //unlink(PFAD_SYNC_TMP . FILENAME_XML);
            readfile(PFAD_SYNC_TMP . $zip);
            exit;
        } else {
            syncException($archive->errorInfo(true));
        }
    }
}

/**
 * @param object $obj
 * @param array  $xml
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
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('mapAttributes kein Array: XML:' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude');
    }
}

/**
 * @param array $array
 * @return bool
 */
function is_assoc(array $array) {
    return count(array_filter(array_keys($array), 'is_string')) > 0;
}

/**
 * @param object $obj
 * @param array $xml
 * @param array $map
 */
function mappe(&$obj, $xml, $map)
{
    if ($obj === null) {
        $obj = new stdClass();
    }

    if (!is_assoc($map)) {
        foreach ($map as $key) {
            $obj->$key = (isset($xml[$key])) ? $xml[$key] : null;
        }
    } else {
        foreach ($map as $key => $value) {
            $val = null;
            if (empty($xml[$key]) && isset($value)) {
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
    $obj_arr = array();
    if ((isset($xml[$name]) && is_array($xml[$name])) || (isset($xml[$name . ' attr']) && is_array($xml[$name . ' attr']))) {
        if (isset($xml[$name . ' attr']) && is_array($xml[$name . ' attr'])) {
            $obj = new stdClass();
            mapAttributes($obj, $xml[$name . ' attr']);
            mappe($obj, $xml[$name], $map);

            return array($obj);
        }
        if (count($xml[$name]) > 2) {
            $cnt = count($xml[$name]) / 2;
            for ($i = 0; $i < $cnt; $i++) {
                if (!isset($obj_arr[$i]) || $obj_arr[$i] === null) {
                    $obj_arr[$i] = new stdClass();
                }
                mapAttributes($obj_arr[$i], $xml[$name][$i . ' attr']);
                mappe($obj_arr[$i], $xml[$name][$i], $map);
            }
        }
    }

    return $obj_arr;
}

/**
 * @param object $oXmlTree
 * @param array  $cMapping_arr
 * @return stdClass
 */
function JTLMapArr($oXmlTree, $cMapping_arr)
{
    $oMapped = new stdClass();
    foreach ($oXmlTree->Attributes() as $cKey => $cVal) {
        $oMapped->{$cKey} = utf8_decode((string)$cVal);
    }
    foreach ($cMapping_arr as $cMap) {
        if (isset($oXmlTree->{$cMap})) {
            $oMapped->{$cMap} = utf8_decode((string)$oXmlTree->{$cMap});
        }
    }

    return $oMapped;
}

/**
 * @param array  $xml
 * @param string $tabelle
 * @param array  $map
 * @param int $del
 */
function XML2DB($xml, $tabelle, $map, $del = 1)
{
    if (isset($xml[$tabelle]) && is_array($xml[$tabelle])) {
        $obj_arr = mapArray($xml, $tabelle, $map);
        DBDelInsert($tabelle, $obj_arr, $del);
    }
}

/**
 * @param array      $xml
 * @param string     $tabelle
 * @param array      $map
 * @param string     $pk1
 * @param int|string $pk2
 */
function updateXMLinDB($xml, $tabelle, $map, $pk1, $pk2 = 0)
{
    if ((isset($xml[$tabelle]) && is_array($xml[$tabelle])) || (isset($xml[$tabelle . ' attr']) && is_array($xml[$tabelle . ' attr']))) {
        $obj_arr = mapArray($xml, $tabelle, $map);

        DBUpdateInsert($tabelle, $obj_arr, $pk1, $pk2);
    }
}

/**
 * @param object $oArtikel
 * @param array  $oKundengruppe_arr
 */
function fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr)
{
    Shop::DB()->query(
        "DELETE FROM tartikelkategorierabatt
          WHERE kArtikel = " . intval($oArtikel->kArtikel), 3
    );
    if (is_array($oKundengruppe_arr) && count($oKundengruppe_arr) > 0) {
        foreach ($oKundengruppe_arr as $oKundengruppe) {
            $oMaxRabatt = Shop::DB()->query(
                "SELECT tkategoriekundengruppe.fRabatt, tkategoriekundengruppe.kKategorie
                    FROM tkategoriekundengruppe
                    JOIN tkategorieartikel ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                        AND tkategorieartikel.kArtikel = {$oArtikel->kArtikel}
                    LEFT JOIN tkategoriesichtbarkeit
                        ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = {$oKundengruppe->kKundengruppe}
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND tkategoriekundengruppe.kKundengruppe = {$oKundengruppe->kKundengruppe}
                    ORDER BY tkategoriekundengruppe.fRabatt DESC
                    LIMIT 1", 1
            );

            if (isset($oMaxRabatt->fRabatt) && $oMaxRabatt->fRabatt > 0) {
                $oArtikelKategorieRabatt                = new stdClass();
                $oArtikelKategorieRabatt->kArtikel      = $oArtikel->kArtikel;
                $oArtikelKategorieRabatt->kKundengruppe = $oKundengruppe->kKundengruppe;
                $oArtikelKategorieRabatt->kKategorie    = $oMaxRabatt->kKategorie;
                $oArtikelKategorieRabatt->fRabatt       = $oMaxRabatt->fRabatt;

                Shop::DB()->insert('tartikelkategorierabatt', $oArtikelKategorieRabatt);
                // Clear Artikel Cache
                $cache = Shop::Cache();
                $cache->flushTags(array(CACHING_GROUP_ARTICLE . '_' . $oArtikel->kArtikel));
                if ($cache->isPageCacheEnabled()) {
                    if (!isset($smarty)) {
                        $smarty = Shop::Smarty();
                    }
                    $smarty->clearCache(null, 'jtlc|article|aid' . $oArtikel->kArtikel);
                }
            }
        }
    }
}

/**
 * @param object $oArtikel
 */
function versendeVerfuegbarkeitsbenachrichtigung($oArtikel)
{
    if ($oArtikel->fLagerbestand > 0 && $oArtikel->kArtikel) {
        $Benachrichtigungen = Shop::DB()->query(
            "SELECT *
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE nStatus=0
                    AND kArtikel=" . $oArtikel->kArtikel, 2
        );
        if (is_array($Benachrichtigungen) && count($Benachrichtigungen) > 0) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';

            $Artikel = new Artikel();
            $Artikel->fuelleArtikel($oArtikel->kArtikel, Artikel::getDefaultOptions());
            // Kampagne
            $oKampagne = new Kampagne(KAMPAGNE_INTERN_VERFUEGBARKEIT);
            if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                $cSep = (strpos($Artikel->cURL, '.php') === false) ?
                    '?' :
                    '&';
                $Artikel->cURL .= $cSep . $oKampagne->cParameter . '=' . $oKampagne->cWert;
            }
            foreach ($Benachrichtigungen as $Benachrichtigung) {
                $obj                                   = new stdClass();
                $obj->tverfuegbarkeitsbenachrichtigung = $Benachrichtigung;
                $obj->tartikel                         = $Artikel;
                $obj->tartikel->cName                  = StringHandler::htmlentitydecode($obj->tartikel->cName);
                $mail                                  = new stdClass();
                $mail->toEmail                         = $Benachrichtigung->cMail;
                $mail->toName                          = ($Benachrichtigung->cVorname || $Benachrichtigung->cNachname) ?
                    ($Benachrichtigung->cVorname . ' ' . $Benachrichtigung->cNachname) :
                    $Benachrichtigung->cMail;
                $obj->mail = $mail;
                sendeMail(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $obj);
                Shop::DB()->query(
                    "UPDATE tverfuegbarkeitsbenachrichtigung
                        SET nStatus=1, dBenachrichtigtAm=now(), cAbgeholt = 'N'
                        WHERE kVerfuegbarkeitsbenachrichtigung=" . $Benachrichtigung->kVerfuegbarkeitsbenachrichtigung, 4
                );
            }
        }
    }
}

/**
 * @param int   $kArtikel
 * @param int   $kKundengruppe
 * @param float $fVKNetto
 */
function setzePreisverlauf($kArtikel, $kKundengruppe, $fVKNetto)
{
    $nReihen = Shop::DB()->query(
        "UPDATE tpreisverlauf
            SET fVKNetto=" . $fVKNetto . "
            WHERE kArtikel=" . $kArtikel . "
                AND kKundengruppe=" . $kKundengruppe . "
                AND dDate=DATE(NOW())", 3
    );
    if ($nReihen == 0) {
        $oPreisverlauf                = new stdClass();
        $oPreisverlauf->kArtikel      = $kArtikel;
        $oPreisverlauf->kKundengruppe = $kKundengruppe;
        $oPreisverlauf->fVKNetto      = $fVKNetto;
        $oPreisverlauf->dDate         = 'now()';

        $oPreis = Shop::DB()->query(
            "SELECT fVKNetto
                FROM tpreisverlauf
                WHERE kArtikel=" . $kArtikel . "
                    AND kKundengruppe=" . $kKundengruppe . "
                ORDER BY dDate DESC
                LIMIT 1", 1
        );
        //no pricehistory or price changed?
        if ( !isset($oPreis->fVKNetto) || isset($oPreis->fVKNetto) && intval($oPreis->fVKNetto * 100) !== intval($fVKNetto * 100)) {
            Shop::DB()->insert('tpreisverlauf', $oPreisverlauf);
            // Clear Artikel Cache
            $cache = Shop::Cache();
            $cache->flushTags(array(CACHING_GROUP_ARTICLE . '_' . $kArtikel));
            if ($cache->isPageCacheEnabled()) {
                if (!isset($smarty)) {
                    $smarty = Shop::Smarty();
                }
                $smarty->clearCache(null, 'jtlc|article|aid' . $kArtikel);
            }
        }
    }
}

/**
 * @param string $cFehler
 */
function unhandledError($cFehler)
{
    if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog($cFehler, JTLLOG_LEVEL_ERROR);
    }
    syncException($cFehler, FREIDEFINIERBARER_FEHLER);
}

/**
 * @param int $size
 * @return string
 */
function convert($size)
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

    return @round($size / pow(1024, ($i = (int)floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

/**
 * @param string $cMessage
 * @return string
 */
function translateError($cMessage)
{
    if (preg_match('/Maximum execution time of (\d+) second.? exceeded/', $cMessage, $cMatch_arr)) {
        $nSeconds = intval($cMatch_arr[1]);
        $cMessage = utf8_decode("Maximale Ausführungszeit von $nSeconds Sekunden überschritten");
    } elseif (preg_match("/Allowed memory size of (\d+) bytes exhausted/", $cMessage, $cMatch_arr)) {
        $nLimit   = intval($cMatch_arr[1]);
        $cMessage = utf8_decode("Erlaubte Speichergröße von $nLimit Bytes erschöpft");
    }

    return $cMessage;
}

/**
 * @param mixed $output
 * @return string
 */
function handleError($output)
{
    if (function_exists('error_get_last')) {
        $error = error_get_last();
        if ($error['type'] == 1) {
            $cError = translateError($error['message']) . "\n";
            $cError .= 'Datei: ' . $error['file'];
            if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
                Jtllog::writeLog($cError, JTLLOG_LEVEL_ERROR);
            }

            return ($cError);
        }

        return $output;
    }

    return $output;
}

/**
 * @param null|stdClass $oArtikelPict
 * @param int           $kArtikel
 * @param int           $kArtikelPict
 */
function deleteArticleImage($oArtikelPict = null, $kArtikel = 0, $kArtikelPict = 0)
{
    $kArtikelPict = (int)$kArtikelPict;
    if ($oArtikelPict === null && $kArtikelPict > 0) {
        $oArtikelPict = Shop::DB()->query(
            "SELECT *
                FROM tartikelpict
                WHERE kArtikelPict = " . $kArtikelPict, 1
        );
        $kArtikel = (isset($oArtikelPict->kArtikel)) ? (int)$oArtikelPict->kArtikel : 0;
    }
    // Das Bild ist eine Verknüpfung
    if (isset($oArtikelPict->kMainArtikelBild) && $oArtikelPict->kMainArtikelBild > 0 && $kArtikel > 0) {
        // Existiert der Artikel vom Mainbild noch?
        $oMainArtikel = Shop::DB()->query(
            "SELECT kArtikel
                FROM tartikel
                WHERE kArtikel =
                (
                    SELECT kArtikel
                    FROM tartikelpict
                    WHERE kArtikelPict = " . (int)$oArtikelPict->kMainArtikelBild . "
                )", 1
        );
        // Main Artikel existiert nicht mehr
        if (!isset($oMainArtikel->kArtikel) || $oMainArtikel->kArtikel == 0) {
            // Existiert noch eine andere aktive Verknüpfung auf das Mainbild?
            $oArtikelPictPara_arr = Shop::DB()->query(
                "SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kMainArtikelBild = " . (int)$oArtikelPict->kMainArtikelBild . "
                        AND kArtikel != " . (int)$kArtikel, 2
            );
            // Lösche das MainArtikelBild
            if (count($oArtikelPictPara_arr) === 0) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $oArtikelPict->cPfad);
                // Bild vom Main aus DB löschen
                Shop::DB()->query(
                    "DELETE FROM tartikelpict
                        WHERE kArtikelPict = " . (int)$oArtikelPict->kMainArtikelBild, 3
                );
            }
        }
        // Bildverknüpfung aus DB löschen
        Shop::DB()->delete('tartikelpict', 'kArtikelPict', (int)$oArtikelPict->kArtikelPict);
    } elseif (isset($oArtikelPict->kMainArtikelBild) && $oArtikelPict->kMainArtikelBild == 0) { // Das Bild ist ein Hauptbild
        // Gibt es Artikel die auf Bilder des zu löschenden Artikel verknüpfen?
        $oVerknuepfteArtikel_arr = Shop::DB()->query(
            "SELECT *
                FROM tartikelpict
                WHERE kMainArtikelBild =
                (
                    SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kArtikelPict = " . (int)$oArtikelPict->kArtikelPict . "
                )", 2
        );
        if (count($oVerknuepfteArtikel_arr) === 0) {
            // Gibt ein neue Artikel die noch auf den physikalischen Pfad zeigen?
            $oObj = Shop::DB()->query(
                "SELECT count(*) AS nCount
                    FROM tartikelpict
                    WHERE cPfad = '{$oArtikelPict->cPfad}'", 1
            );
            if (isset($oObj->nCount) && $oObj->nCount < 2) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $oArtikelPict->cPfad);
            }
        } else {
            //Reorder linked images because master imagelink will be deleted
            $kArtikelPictNext = $oVerknuepfteArtikel_arr[0]->kArtikelPict;
            //this will be the next masterimage
            Shop::DB()->query("UPDATE tartikelpict SET kMainArtikelBild = 0
                                WHERE kArtikelPict = " . (int)$kArtikelPictNext, 3);
            //now link other images to the new masterimage
            Shop::DB()->query("UPDATE tartikelpict SET kMainArtikelBild = " . (int)$kArtikelPictNext . "
                                WHERE kMainArtikelBild = " . (int)$oArtikelPict->kArtikelPict, 3);
        }
        // Bild aus DB löschen
        Shop::DB()->delete('tartikelpict', 'kArtikelPict', (int)$oArtikelPict->kArtikelPict);
    }
    // Clear Artikel Cache
    $cache = Shop::Cache();
    $cache->flushTags(array(CACHING_GROUP_ARTICLE . '_' . (int)$kArtikel));
    if ($cache->isPageCacheEnabled()) {
        if (!isset($smarty)) {
            $smarty = Shop::Smarty();
        }
        $smarty->clearCache(null, 'jtlc|article|aid' . (int)$kArtikel);
    }
}

/**
 * @param stdClass $oObject
 */
function extractStreet(&$oObject)
{
    $cData_arr = explode(' ', $oObject->cStrasse);
    if (count($cData_arr) > 1) {
        $oObject->cHausnummer = $cData_arr[count($cData_arr) - 1];
        unset($cData_arr[count($cData_arr) - 1]);
        $oObject->cStrasse = implode(' ', $cData_arr);
    }
}

/**
 * @param string $cSeoOld
 * @param string $cSeoNew
 * @return bool
 */
function checkDbeSXmlRedirect($cSeoOld, $cSeoNew)
{
    // Insert into tredirect weil sich das SEO von der Kategorie geändert hat
    if (strlen($cSeoOld) > 0 && strlen($cSeoNew) > 0 && $cSeoOld != $cSeoNew) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Redirect.php';
        $oRedirect = new Redirect();
        $xPath_arr = parse_url(Shop::getURL());
        if (isset($xPath_arr['path'])) {
            $cSource = "{$xPath_arr['path']}/{$cSeoOld}";
        } else {
            $cSource = '/' . $cSeoOld;
        }

        return $oRedirect->saveExt($cSource, $cSeoNew, true);
    }

    return false;
}

/**
 * @param int         $kKey
 * @param string      $cKey
 * @param int|null    $kSprache
 * @param string|null $cAssoc
 * @return array|null
 */
function getSeoFromDB($kKey, $cKey, $kSprache = null, $cAssoc = null)
{
    $kKey = (int)$kKey;
    if ($kKey > 0 && strlen($cKey) > 0) {
        if ($kSprache !== null && intval($kSprache) > 0) {
            $kSprache = (int)$kSprache;
            $oSeo     = Shop::DB()->query(
                "SELECT *
                    FROM tseo
                    WHERE kKey = {$kKey}
                        AND cKey = '" . Shop::DB()->escape($cKey) . "'
                        AND kSprache = {$kSprache}", 1
            );

            if (isset($oSeo->kKey) && intval($oSeo->kKey) > 0) {
                return $oSeo;
            }
        } else {
            $oSeo_arr = Shop::DB()->query(
                "SELECT *
                    FROM tseo
                    WHERE kKey = {$kKey}
                        AND cKey = '" . Shop::DB()->escape($cKey) . "'", 2
            );

            if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                if ($cAssoc !== null && strlen($cAssoc) > 0) {
                    $oAssoc_arr = array();
                    foreach ($oSeo_arr as $oSeo) {
                        if (isset($oSeo->{$cAssoc})) {
                            $oAssoc_arr[$oSeo->{$cAssoc}] = $oSeo;
                        }
                    }
                    if (count($oAssoc_arr) > 0) {
                        $oSeo_arr = $oAssoc_arr;
                    }
                }

                return $oSeo_arr;
            }
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
function handlePriceFormat($kArtikel, $kKundengruppe, $kKunde = null)
{
    $kArtikel      = (int)$kArtikel;
    $kKundengruppe = (int)$kKundengruppe;
    $price         = Shop::DB()->query(
        "SELECT kPreis
            FROM tpreis AS p
            WHERE p.kArtikel = {$kArtikel}
                AND p.kKundengruppe = {$kKundengruppe}", 1);

    if ($price && isset($price->kPreis)) {
        Shop::DB()->query(
            "DELETE p, d
                FROM tpreis AS p
                LEFT JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                WHERE p.kPreis = {$price->kPreis}", 3);
    }
    // tpreis
    $o                = new stdClass();
    $o->kArtikel      = $kArtikel;
    $o->kKundengruppe = $kKundengruppe;

    if ($kKunde !== null && intval($kKunde) > 0) {
        $o->kKunde = (int)$kKunde;
    }

    return Shop::DB()->insert('tpreis', $o);
}

/**
 * Handle new PriceFormat (Wawi >= v.1.00): 
 * 
 * Sample XML:
 * 	<tpreis kPreis="8" kArtikel="15678" kKundenGruppe="1" kKunde="0">
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>100</nAnzahlAb>
 *			<fNettoPreis>0.756303</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>250</nAnzahlAb>
 *			<fNettoPreis>0.714286</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>500</nAnzahlAb>
 *			<fNettoPreis>0.672269</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>750</nAnzahlAb>
 *			<fNettoPreis>0.630252</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>1000</nAnzahlAb>
 *			<fNettoPreis>0.588235</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>2000</nAnzahlAb>
 *			<fNettoPreis>0.420168</fNettoPreis>
 *		</tpreisdetail>
 *		<tpreisdetail kPreis="8">
 *			<nAnzahlAb>0</nAnzahlAb>
 *			<fNettoPreis>0.798319</fNettoPreis>
 *		</tpreisdetail>
 *	</tpreis>
 * 
 * @param array $xml
 */
function handleNewPriceFormat($xml)
{
    if (is_array($xml) && isset($xml['tpreis'])) {
        $preise = mapArray($xml, 'tpreis', $GLOBALS['mPreis']);
        if (is_array($preise) && count($preise) > 0) {
            $customerGroupHandled = array();
            foreach ($preise as $i => $preis) {
                $kPreis       = handlePriceFormat($preis->kArtikel, $preis->kKundenGruppe, $preis->kKunde);
                if (!empty($xml['tpreis'][$i])) {
                    $preisdetails = mapArray($xml['tpreis'][$i], 'tpreisdetail', $GLOBALS['mPreisDetail']);
                } else {
                    $preisdetails = mapArray($xml['tpreis'], 'tpreisdetail', $GLOBALS['mPreisDetail']);
                }
                $hasDefaultPrice = false;
                foreach ($preisdetails as $preisdetail) {
                    $o            = (object) array(
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => $preisdetail->nAnzahlAb,
                        'fVKNetto'  => $preisdetail->fNettoPreis
                    );
                    Shop::DB()->insert('tpreisdetail', $o);
                    if ($o->nAnzahlAb == 0) {
                        $hasDefaultPrice = true;
                    }
                }
                // default price for customergroup set?
                if (!$hasDefaultPrice && isset($xml['fStandardpreisNetto'])) { 
                    $o            = (object) array(
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => 0,
                        'fVKNetto'  => $xml['fStandardpreisNetto']
                    );
                    Shop::DB()->insert('tpreisdetail', $o);
                }
                $customerGroupHandled[] = $preis->kKundenGruppe;
            }
            //any customergroups with missing tpreis node left? 
            $kKundengruppen_arr = Kundengruppe::getGroups();
            foreach ($kKundengruppen_arr as $customergroup) {
            	$kKundengruppe = $customergroup->getKundengruppe();
                if (!in_array($kKundengruppe, $customerGroupHandled) && isset($xml['fStandardpreisNetto'])) {
                    $kPreis       = handlePriceFormat($preis->kArtikel, $kKundengruppe, 0);
                    $o            = (object) array(
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => 0,
                        'fVKNetto'  => $xml['fStandardpreisNetto']
                    );
                    Shop::DB()->insert('tpreisdetail', $o);
                }
            }
            
            
        }
    }
}

/**
 * @param array $objs
 */
function handleOldPriceFormat($objs)
{
    if (is_array($objs) && count($objs) > 0) {
        foreach ($objs as $obj) {
            $kPreis = handlePriceFormat($obj->kArtikel, $obj->kKundengruppe);
            // tpreisdetail
            insertPriceDetail($obj, 0, $kPreis);
            for ($i = 1; $i <= 5; $i++) {
                insertPriceDetail($obj, $i, $kPreis);
            }
        }
    }
}

/**
 * @param object $obj
 * @param int    $index
 * @param int    $priceId
 */
function insertPriceDetail($obj, $index, $priceId)
{
    $count = "nAnzahl{$index}";
    $price = "fPreis{$index}";

    if ((isset($obj->{$count}) && intval($obj->{$count}) > 0) || $index === 0) {
        $o            = new stdClass();
        $o->kPreis    = $priceId;
        $o->nAnzahlAb = ($index === 0) ? 0 : $obj->{$count};
        $o->fVKNetto  = ($index === 0) ? $obj->fVKNetto : $obj->{$price};

        Shop::DB()->insert('tpreisdetail', $o);
    }
}

/**
 * @param string $cAnrede
 * @return string
 */
function mappeWawiAnrede2ShopAnrede($cAnrede)
{
    $cAnrede = strtolower($cAnrede);
    if ($cAnrede === 'w' || $cAnrede === 'm') {
        return $cAnrede;
    }
    if ($cAnrede === 'frau' || $cAnrede === 'mrs' || $cAnrede === 'mrs.') {
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
 * @param int $wawiExceptionCode int code (0-9)
 */
function syncException($msg, $wawiExceptionCode = null) {
    $output = '';
    if (isset($wawiExceptionCode)) {
        $output .= $wawiExceptionCode . '\n';
    }
    $output .= $msg;
    die(mb_convert_encoding($output, 'ISO-8859-1', 'auto'));
}

/**
 * flush object cache for category tree
 *
 * @return int
 */
function flushCategoryTreeCache()
{
    error_log('######FLUSHING!!!!');
    return Shop::Cache()->flushTags('jtl_category_tree');
}

ob_start('handleError');
