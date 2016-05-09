<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Sprache
 *
 * @method Sprache autoload()
 * @method string get(string $cName, string $cSektion = 'global')
 * @method bool set(int $kSprachsektion, string $cName, string $cWert)
 * @method bool insert(string $cSprachISO, int $kSprachsektion, string $cName, string $cWert)
 * @method bool delete(int $kSprachsektion, string $cName)
 * @method mixed search(string $cSuchwort)
 * @method bool|int import(string $cFileName, string $cISO, int $nTyp)
 * @method string export(int $nTyp = 0)
 * @method Sprache reset()
 * @method Sprache log(string $cSektion, string $cName)
 * @method array|mixed generate()
 * @method array getAll()
 * @method array getLogs()
 * @method array getSections()
 * @method array getSectionValues(string $cSektion, int|null $kSektion = null)
 * @method array getInstalled()
 * @method array getAvailable()
 * @method string getIso()
 * @method bool valid()
 * @method bool isValid()
 * @method array|mixed|null getLangArray()
 * @method mixed getIsoFromLangID(int $kSprache)
 */
class Sprache
{
    /**
     * @var array
     */
    protected static $mappings;

    /**
     * @var string
     */
    public $cISOSprache = '';

    /**
     * @var int
     */
    public $kSprachISO = 0;

    /**
     * @var array|null
     */
    public $langVars = null;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array|null
     */
    public $oSprache_arr = null;

    /**
     * @var array
     */
    public $oSprachISO = array();

    /**
     * @var array
     */
    public $isoAssociation = array();

    /**
     * @var array
     */
    public $idAssociation = array();

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var Sprache
     */
    private static $instance = null;

    /**
     * @param bool $bAutoload
     * @return Sprache
     */
    public static function getInstance($bAutoload = true)
    {
        return (self::$instance !== null) ? self::$instance : new self($bAutoload);
    }

    /**
     * @param bool $bAutoload
     */
    public function __construct($bAutoload = true)
    {
        if ($bAutoload) {
            $this->_autoload();
        }
        self::$instance = $this;
    }

    /**
     * object wrapper
     * this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public function __call($method, $arguments)
    {
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array(array($this, $mapping), $arguments) : null;
    }

    /**
     * static wrapper
     * this allows to call NiceShop::DB()->query() etc.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public static function __callStatic($method, $arguments)
    {
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array(array(self::$instance, $mapping), $arguments) : null;
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method)
    {
        $mapping = array(
            'autoload'         => '_autoload',
            'get'              => 'gibWert',
            'set'              => 'setzeWert',
            'insert'           => 'fuegeEin',
            'delete'           => 'loesche',
            'search'           => 'suche',
            'import'           => '_import',
            'export'           => '_export',
            'reset'            => '_reset',
            'log'              => 'logWert',
            'generate'         => 'generateLangVars',
            'getAll'           => 'gibAlleWerte',
            'getLogs'          => 'gibLogWerte',
            'getSections'      => 'gibSektionen',
            'getSectionValues' => 'gibSektionsWerte',
            'getInstalled'     => 'gibInstallierteSprachen',
            'getAvailable'     => 'gibVerfuegbareSprachen',
            'getIso'           => 'gibISO',
            'valid'            => 'gueltig',
            'isValid'          => 'gueltig',
            'change'           => 'changeDatabase',
            'update'           => 'updateRow',
            'getLangArray'     => '_getLangArray',
            'getIsoFromLangID' => '_getIsoFromLangID'
        );

        return (isset($mapping[$method])) ? $mapping[$method] : null;
    }

    /**
     * @return array|mixed
     */
    public function generateLangVars()
    {
        if ($this->cacheID !== null) {
            return (($this->langVars = Shop::Cache()->get($this->cacheID)) === false) ? array() : $this->langVars;
        }
        $this->langVars = array();

        return array();
    }

    /**
     * @return bool|mixed
     */
    public function saveLangVars()
    {
        return Shop::Cache()->set($this->cacheID, $this->langVars, array(CACHING_GROUP_LANGUAGE));
    }

    /**
     * @param int $kSprache
     * @return mixed
     */
    public function _getIsoFromLangID($kSprache)
    {
        if (!isset($this->isoAssociation[$kSprache])) {
            $cacheID = 'lang_iso_ks';
            if (($this->isoAssociation = Shop::Cache()->get($cacheID)) === false || !isset($this->isoAssociation[$kSprache])) {
                $lang                            = Shop::DB()->query("SELECT cISO FROM tsprache WHERE kSprache = " . (int)$kSprache, 1);
                $this->isoAssociation[$kSprache] = $lang;
                Shop::Cache()->set($cacheID, $this->isoAssociation, array(CACHING_GROUP_LANGUAGE));
            }
        }

        return $this->isoAssociation[$kSprache];
    }

    /**
     * @param string $cISO
     * @return mixed
     */
    public function getLangIDFromIso($cISO)
    {
        if (!isset($this->idAssociation[$cISO])) {
            $cacheID = 'lang_id_ks';
            if (($this->idAssociation = Shop::Cache()->get($cacheID)) === false || !isset($this->idAssociation[$cISO])) {
                $lang                       = Shop::DB()->query("SELECT kSprachISO FROM tsprachiso WHERE cISO = '" . Shop::DB()->escape($cISO) . "'", 1);
                $this->idAssociation[$cISO] = $lang;
                Shop::Cache()->set($cacheID, $this->idAssociation, array(CACHING_GROUP_LANGUAGE));
            }
        }

        return $this->idAssociation[$cISO];
    }

    /**
     * generate all available lang vars for the current language
     * this saves some sql statements and is called by JTLCache only if the objekct cache is available
     *
     * @return $this
     */
    public function preLoad()
    {
        $_lv = $this->generateLangVars();
        if ($this->kSprachISO > 0 && $this->cISOSprache !== '' && !isset($_lv[$this->cISOSprache])) {
            $allLangVars = Shop::DB()->query("
                SELECT tsprachwerte.kSprachsektion, tsprachwerte.cWert, tsprachwerte.cName, tsprachsektion.cName AS sectionName
                    FROM tsprachwerte 
                        LEFT JOIN tsprachsektion
                        ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                    WHERE tsprachwerte.kSprachISO = " . $this->kSprachISO, 2
            );
            $this->langVars[$this->cISOSprache] = array();
            foreach ($allLangVars as $_langVar) {
                if (!isset($this->langVars[$this->cISOSprache][$_langVar->sectionName])) {
                    $this->langVars[$this->cISOSprache][$_langVar->sectionName] = array();
                }
                $this->langVars[$this->cISOSprache][$_langVar->sectionName][$_langVar->cName] = $_langVar->cWert;
            }
            $this->saveLangVars();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function _autoload()
    {
        // cISOSprache aus Session
        if (isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache']) > 0) {
            $this->cISOSprache = $_SESSION['cISOSprache'];
        } else {
            $oSprache = gibStandardsprache();
            if (isset($oSprache->cISO) && strlen($oSprache->cISO) > 0) {
                $this->cISOSprache = $oSprache->cISO;
            }
        }

        $this->kSprachISO       = $this->mappekISO($this->cISOSprache);
        $_SESSION['kSprachISO'] = $this->kSprachISO;
        $this->cacheID          = 'lang_' . $this->kSprachISO;
        $this->oSprache_arr     = $this->_getLangArray();

        return $this;
    }

    /**
     * @param string $cISO
     * @return $this
     */
    public function setzeSprache($cISO)
    {
        $this->cISOSprache = $cISO;
        $this->kSprachISO  = $this->mappekISO($this->cISOSprache);

        return $this;
    }

    /**
     * @param string $cISO
     * @return int|bool
     */
    public function mappekISO($cISO)
    {
        if (strlen($cISO) > 0) {
            if (isset($this->oSprachISO[$cISO]->kSprachISO)) {
                return (int) $this->oSprachISO[$cISO]->kSprachISO;
            }
            $oSprachISO              = $this->getLangIDFromIso($cISO);
            $this->oSprachISO[$cISO] = $oSprachISO;

            return (isset($oSprachISO->kSprachISO)) ? (int) $oSprachISO->kSprachISO : false;
        }

        return false;
    }

    /**
     * @param string $cName
     * @param string $cSektion
     * @return string
     * @todo: HTML verhindern
     */
    public function gibWert($cName, $cSektion = 'global' /* ... */)
    {
        if ($this->kSprachISO === 0) {
            return '';
        }
        if ($this->langVars === null) {
            //dirty workaround since at __construct time, there is no $_SESSION yet..
            $this->generateLangVars();
        }
        $save = false;
        if (!isset($this->langVars[$this->cISOSprache])) {
            $this->langVars[$this->cISOSprache] = array();
            $save                               = true;
        }
        // Sektion noch nicht vorhanden, alle Werte der Sektion laden
        if (!isset($this->langVars[$this->cISOSprache][$cSektion])) {
            $this->langVars[$this->cISOSprache][$cSektion] = $this->gibSektionsWerte($cSektion);
            $save                                          = true;
        }
        $cValue = isset($this->langVars[$this->cISOSprache][$cSektion][$cName]) ? $this->langVars[$this->cISOSprache][$cSektion][$cName] : null;
        if ($save === true) {
            //only save if values changed
            $this->saveLangVars();
        }
        if ($cValue === null) {
            $this->logWert($cSektion, $cName);
            $cValue = '#' . $cSektion . '.' . $cName . '#';
        } elseif (func_num_args() > 2) { // String formatieren, vsprintf gibt false zurück, sollte die Anzahl der Parameter nicht der Anzahl der Format-Liste entsprechen!
            $cArg_arr = array();
            for ($i = 2; $i < func_num_args(); $i++) {
                $cArg_arr[] = func_get_arg($i);
            }
            if (vsprintf($cValue, $cArg_arr) !== false) {
                $cValue = vsprintf($cValue, $cArg_arr);
            }
        }

        return $cValue;
    }

    /**
     * @param string $cSektion
     * @param int|null $kSektion
     * @return array
     */
    public function gibSektionsWerte($cSektion, $kSektion = null)
    {
        $oWerte_arr       = array();
        $oSprachWerte_arr = array();
        if ($kSektion === null) {
            $oSektion = Shop::DB()->select('tsprachsektion', 'cName', $cSektion);
            $kSektion = isset($oSektion->kSprachsektion) ? $oSektion->kSprachsektion : 0;
        }
        $kSektion = (int)$kSektion;
        if ($kSektion > 0) {
            $oSprachWerte_arr = Shop::DB()->query("
                SELECT cName, cWert
                    FROM tsprachwerte
                    WHERE kSprachISO = " . $this->kSprachISO . "
                        AND kSprachsektion = " . $kSektion, 2);
        }

        if (count($oSprachWerte_arr) > 0) {
            foreach ($oSprachWerte_arr as $oSprachWert) {
                $oWerte_arr[$oSprachWert->cName] = $oSprachWert->cWert;
            }
        }

        return $oWerte_arr;
    }

    /**
     * Nicht gesetzte Werte loggen
     *
     * @param string $cSektion
     * @param string $cName
     * @return $this
     */
    public function logWert($cSektion, $cName)
    {
        $cName    = Shop::DB()->escape($cName);
        $cSektion = Shop::DB()->escape($cSektion);
        $nCount   = Shop::DB()->query("
            SELECT kSprachISO
                FROM tsprachlog
                WHERE kSprachISO = " . (int)$this->kSprachISO . "
                    AND cSektion = '" . $cSektion . "' AND cName = '" . $cName . "'", 3);
        if ($nCount == 0) {
            $oLog             = new stdClass();
            $oLog->kSprachISO = $this->kSprachISO;
            $oLog->cSektion   = $cSektion;
            $oLog->cName      = $cName;
            Shop::DB()->insert('tsprachlog', $oLog);
        }

        return $this;
    }

    /**
     * @param bool $currentLang
     * @return int
     */
    public function clearLog($currentLang = true)
    {
        $where = ($currentLang === true) ?
            " WHERE kSprachISO = " . (int)$this->kSprachISO :
            '';

        return Shop::DB()->query("DELETE FROM tsprachlog" . $where, 3);
    }

    /**
     * @return array
     */
    public function gibLogWerte()
    {
        return Shop::DB()->query("
          SELECT * 
            FROM tsprachlog 
            WHERE kSprachISO = " . (int)$this->kSprachISO . " 
            ORDER BY cName ASC", 2
        );
    }

    /**
     * @return array
     */
    public function gibAlleWerte()
    {
        $oWerte_arr     = array();
        $oSektionen_arr = $this->gibSektionen();
        foreach ($oSektionen_arr as $oSektion) {
            $oSektion->oWerte_arr = Shop::DB()->query("
                SELECT *
                    FROM tsprachwerte
                    WHERE kSprachISO = " . (int)$this->kSprachISO . "
                        AND kSprachsektion = " . $oSektion->kSprachsektion, 2);
            $oWerte_arr[] = $oSektion;
        }

        return $oWerte_arr;
    }

    /**
     * @return array
     */
    public function gibInstallierteSprachen()
    {
        $oInstallierteSprachen_arr = array();
        $oSprachen_arr             = Shop::DB()->query("SELECT * FROM tsprache", 2);
        if (is_array($oSprachen_arr)) {
            foreach ($oSprachen_arr as $oSprache) {
                $kSprachISO = $this->mappekISO($oSprache->cISO);
                if ($kSprachISO > 0) {
                    $oInstallierteSprachen_arr[] = $oSprache;
                }
            }
        }

        return $oInstallierteSprachen_arr;
    }

    /**
     * @return array
     */
    public function gibVerfuegbareSprachen()
    {
        return Shop::DB()->query("SELECT * FROM tsprache", 2);
    }

    /**
     * @return array
     */
    public function gibSektionen()
    {
        return Shop::DB()->query("SELECT * FROM tsprachsektion ORDER BY cNAME ASC", 2);
    }

    /**
     * @return bool
     */
    public function gueltig()
    {
        return ($this->kSprachISO > 0);
    }

    /**
     * @return string
     */
    public function gibISO()
    {
        return $this->cISOSprache;
    }

    /**
     * @return $this
     */
    public function _reset()
    {
        unset($_SESSION['Sprache']);

        return $this;
    }

    /**
     * @param int    $kSprachsektion
     * @param string $cName
     * @param string $cWert
     * @return bool
     */
    public function setzeWert($kSprachsektion, $cName, $cWert)
    {
        $_keys       = array('kSprachISO', 'kSprachsektion', 'cName');
        $_values     = array((int)$this->kSprachISO, (int)$kSprachsektion, $cName);
        $_upd        = new stdClass();
        $_upd->cWert = $cWert;

        return Shop::DB()->update('tsprachwerte', $_keys, $_values, $_upd) >= 0;
    }

    /**
     * @param string $cSprachISO
     * @param int    $kSprachsektion
     * @param string $cName
     * @param string $cWert
     * @return bool
     */
    public function fuegeEin($cSprachISO, $kSprachsektion, $cName, $cWert)
    {
        $kSprachISO = $this->mappekISO($cSprachISO);
        if ($kSprachISO > 0) {
            $oWert                 = new stdClass();
            $oWert->kSprachISO     = (int)$kSprachISO;
            $oWert->kSprachsektion = (int)$kSprachsektion;
            $oWert->cName          = $cName;
            $oWert->cWert          = $cWert;
            $oWert->cStandard      = $cWert;
            $oWert->bSystem        = 0;

            return Shop::DB()->insert('tsprachwerte', $oWert) > 0;
        }

        return false;
    }

    /**
     * @param int    $kSprachsektion
     * @param string $cName
     * @return bool
     */
    public function loesche($kSprachsektion, $cName)
    {
        return Shop::DB()->delete('tsprachwerte', array('kSprachsektion', 'cName'), array((int)$kSprachsektion, $cName));
    }

    /**
     * @param string $cSuchwort
     * @return mixed
     */
    public function suche($cSuchwort)
    {
        $cSuchwort = Shop::DB()->escape($cSuchwort);

        return Shop::DB()->query(
            "SELECT tsprachwerte.kSprachsektion, tsprachwerte.cName, tsprachwerte.cWert, tsprachwerte.cStandard,
                tsprachwerte.bSystem, tsprachsektion.cName AS cSektionName
                FROM tsprachwerte
                LEFT JOIN tsprachsektion ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                WHERE (tsprachwerte.cWert LIKE '%" . $cSuchwort . "%' OR tsprachwerte.cName LIKE '%" . $cSuchwort . "%')
                    AND kSprachISO = " . (int)$this->kSprachISO, 2
        );
    }

    /**
     * @param int $nTyp
     * @return string
     */
    public function _export($nTyp = 0)
    {
        $cCSVData_arr = array();
        $nTyp         = (int) $nTyp;

        switch ($nTyp) {
            default:
            case 0: // Alle
                $oWerte_arr = Shop::DB()->query(
                    "SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = " . (int)$this->kSprachISO, 2
                );
                break;

            case 1: // System
                $oWerte_arr = Shop::DB()->query(
                    "SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = " . (int)$this->kSprachISO . " AND bSystem = 1", 2
                );
                break;

            case 2: // Eigene
                $oWerte_arr = Shop::DB()->query(
                    "SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = " . (int)$this->kSprachISO . " AND bSystem = 0", 2
                );
                break;
        }

        foreach ($oWerte_arr as $oWert) {
            if (strlen($oWert->cWert) === 0) {
                $oWert->cWert = (isset($oWert->cStandard)) ? $oWert->cStandard : null;
            }
            $cCSVData_arr[] = array($oWert->cSektionName, $oWert->cName, $oWert->cWert, $oWert->bSystem);
        }

        $cFileName = tempnam('../' . PFAD_DBES_TMP, 'csv');
        $hFile     = fopen($cFileName, 'w');

        foreach ($cCSVData_arr as $cCSVData) {
            fputcsv($hFile, $cCSVData, ';');
        }

        fclose($hFile);

        return $cFileName;
    }

    /**
     * @param string $cFileName
     * @param string $cISO
     * @param int    $nTyp
     * @return bool|int
     */
    public function _import($cFileName, $cISO, $nTyp)
    {
        $hFile = fopen($cFileName, 'r');
        if (!$hFile) {
            return false;
        }

        $bDeleteFlag  = false;
        $nUpdateCount = 0;
        $kSprachISO   = $this->mappekISO($cISO);
        if ($kSprachISO == 0 || $kSprachISO === false) {
            // Sprache noch nicht installiert
            $oSprachISO       = new stdClass();
            $oSprachISO->cISO = $cISO;
            $kSprachISO       = Shop::DB()->insert('tsprachiso', $oSprachISO);
        }

        while (($cData_arr = fgetcsv($hFile, 4048, ';')) !== false) {
            if (count($cData_arr) === 4) {
                // Sektion holen und ggf neu anlegen
                $cSektion = $cData_arr[0];
                $oSektion = Shop::DB()->select('tsprachsektion', 'cName', $cSektion);
                if (isset($oSektion->kSprachsektion)) {
                    $kSprachsektion = $oSektion->kSprachsektion;
                } else {
                    // Sektion hinzufügen
                    $oSektion        = new stdClass();
                    $oSektion->cName = $cSektion;
                    $kSprachsektion  = Shop::DB()->insert('tsprachsektion', $oSektion);
                }

                $cName   = Shop::DB()->escape($cData_arr[1]);
                $cWert   = Shop::DB()->escape($cData_arr[2]);
                $bSystem = intval($cData_arr[3]);

                switch ($nTyp) {
                    case 0: // Neu importieren
                        // Gültige Zeile, vorhandene Variablen löschen
                        if (!$bDeleteFlag) {
                            Shop::DB()->delete('tsprachwerte', 'kSprachISO', $kSprachISO);
                            $bDeleteFlag = true;
                        }
                        $val                 = new stdClass();
                        $val->kSprachISO     = $kSprachISO;
                        $val->kSprachsektion = $kSprachsektion;
                        $val->cName          = $cData_arr[1];
                        $val->cWert          = $cData_arr[2];
                        $val->cStandard      = $cData_arr[2];
                        $val->bSystem        = $bSystem;
                        Shop::DB()->insert('tsprachwerte', $val);
                        $nUpdateCount++;
                        break;

                    case 1: // Vorhandene Variablen überschreiben
                        Shop::DB()->query(
                            "REPLACE INTO tsprachwerte
                                SET kSprachISO=" . $kSprachISO . ", kSprachsektion = " . $kSprachsektion . ",
                                cName = '" . $cName . "', cWert='" . $cWert . "', cStandard = '" . $cWert . "', bSystem = " . $bSystem, 4
                        );
                        $nUpdateCount++;
                        break;

                    case 2: // Vorhandene Variablen beibehalten
                        $oWert = Shop::DB()->query(
                            "SELECT *
                                FROM tsprachwerte
                                WHERE kSprachISO = '" . $kSprachISO . "'
                                    AND kSprachsektion = '" . $kSprachsektion . "'
                                    AND cName = '" . $cName . "'", 1
                        );
                        if (!$oWert) {
                            Shop::DB()->query(
                                "REPLACE INTO tsprachwerte
                                    SET kSprachISO = " . $kSprachISO . ", kSprachsektion = " . $kSprachsektion . ",
                                    cName = '" . $cName . "', cWert = '" . $cWert . "', cStandard = '" . $cWert . "', bSystem = " . $bSystem, 4
                            );
                            $nUpdateCount++;
                        }
                        break;
                }
            }
        }

        return $nUpdateCount;
    }

    /**
     * @return array|mixed|null
     */
    public function _getLangArray()
    {
        if ($this->oSprache_arr === null || $this->oSprache_arr === false) {
            $cacheID = 'langobj';
            if (($this->oSprache_arr = Shop::Cache()->get($cacheID)) === false) {
                $this->oSprache_arr = Shop::DB()->query("SELECT kSprache FROM tsprache", 2);
                Shop::Cache()->set($cacheID, $this->oSprache_arr, array(CACHING_GROUP_LANGUAGE));
            }
        }

        return $this->oSprache_arr;
    }

    /**
     * @param int   $kSprache
     * @param array $oShopSpracheAssoc_arr
     * @return bool
     */
    public static function isShopLanguage($kSprache, $oShopSpracheAssoc_arr = array())
    {
        $kSprache = intval($kSprache);
        if ($kSprache > 0) {
            if (!is_array($oShopSpracheAssoc_arr) || count($oShopSpracheAssoc_arr) === 0) {
                $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            }

            return (isset($oShopSpracheAssoc_arr[$kSprache]));
        }

        return false;
    }
}
