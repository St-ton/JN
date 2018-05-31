<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Sprache
 *
 * @method Sprache autoload()
 * @method string get(string $cName, string $cSektion = 'global', mixed ...$arg1)
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
     * @var array
     */
    public $langVars;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array
     */
    public $oSprache_arr;

    /**
     * @var array
     */
    public $oSprachISO = [];

    /**
     * @var array
     */
    public $isoAssociation = [];

    /**
     * @var array
     */
    public $idAssociation = [];

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var Sprache
     */
    private static $instance;

    /**
     * @var array
     */
    private static $mapping = [
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
    ];

    /**
     * @param bool $bAutoload
     * @return Sprache
     */
    public static function getInstance($bAutoload = true)
    {
        return self::$instance ?? new self($bAutoload);
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
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper
     * this allows to call NiceShop::Container()->getDB()->query() etc.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public static function __callStatic($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([self::$instance, $mapping], $arguments)
            : null;
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method)
    {
        return self::$mapping[$method] ?? null;
    }

    /**
     * @return array|mixed
     */
    public function generateLangVars()
    {
        if ($this->cacheID !== null) {
            return ($this->langVars = Shop::Cache()->get($this->cacheID)) === false
                ? []
                : $this->langVars;
        }
        $this->langVars = [];

        return [];
    }

    /**
     * @return bool|mixed
     */
    public function saveLangVars()
    {
        return Shop::Cache()->set($this->cacheID, $this->langVars, [CACHING_GROUP_LANGUAGE]);
    }

    /**
     * @param int $kSprache
     * @return mixed
     */
    public function _getIsoFromLangID($kSprache)
    {
        if (!isset($this->isoAssociation[$kSprache])) {
            $cacheID = 'lang_iso_ks';
            if (($this->isoAssociation = Shop::Cache()->get($cacheID)) === false 
                || !isset($this->isoAssociation[$kSprache])
            ) {
                $this->isoAssociation[$kSprache] = Shop::Container()->getDB()->select(
                    'tsprache',
                    'kSprache',
                    (int)$kSprache,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'cISO'
                );
                Shop::Cache()->set($cacheID, $this->isoAssociation, [CACHING_GROUP_LANGUAGE]);
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
            if (($this->idAssociation = Shop::Cache()->get($cacheID)) === false 
                || !isset($this->idAssociation[$cISO])
            ) {
                $this->idAssociation[$cISO] = Shop::Container()->getDB()->select('tsprachiso', 'cISO', Shop::Container()->getDB()->escape($cISO));
                Shop::Cache()->set($cacheID, $this->idAssociation, [CACHING_GROUP_LANGUAGE]);
            }
        }

        return $this->idAssociation[$cISO];
    }

    /**
     * @param int $kSektion
     * @param mixed null|string $default
     * @return string
     */
    public function getSectionName($kSektion, $default = null)
    {
        $section = Shop::Container()->getDB()->select('tsprachsektion', 'kSprachsektion', (int)$kSektion);

        return $section->cName ?? $default;
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
            $allLangVars = Shop::Container()->getDB()->queryPrepared(
                'SELECT tsprachwerte.kSprachsektion, tsprachwerte.cWert, 
                    tsprachwerte.cName, tsprachsektion.cName AS sectionName
                    FROM tsprachwerte 
                    LEFT JOIN tsprachsektion
                        ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                    WHERE tsprachwerte.kSprachISO = :iso',
                ['iso' => $this->kSprachISO],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $this->langVars[$this->cISOSprache] = [];
            foreach ($allLangVars as $_langVar) {
                if (!isset($this->langVars[$this->cISOSprache][$_langVar->sectionName])) {
                    $this->langVars[$this->cISOSprache][$_langVar->sectionName] = [];
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
                return (int)$this->oSprachISO[$cISO]->kSprachISO;
            }
            $oSprachISO              = $this->getLangIDFromIso($cISO);
            $this->oSprachISO[$cISO] = $oSprachISO;

            return isset($oSprachISO->kSprachISO) ? (int)$oSprachISO->kSprachISO : false;
        }

        return false;
    }

    /**
     * @param string $cName
     * @param string $cSektion
     * @param mixed [$arg1, ...]
     * @return string
     */
    public function gibWert($cName, $cSektion = 'global')
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
            $this->langVars[$this->cISOSprache] = [];
            $save                               = true;
        }
        // Sektion noch nicht vorhanden, alle Werte der Sektion laden
        if (!isset($this->langVars[$this->cISOSprache][$cSektion])) {
            $this->langVars[$this->cISOSprache][$cSektion] = $this->gibSektionsWerte($cSektion);
            $save                                          = true;
        }
        $cValue = $this->langVars[$this->cISOSprache][$cSektion][$cName] ?? null;
        if ($save === true) {
            // only save if values changed
            $this->saveLangVars();
        }
        $argsCount = func_num_args();
        if ($cValue === null) {
            $this->logWert($cSektion, $cName);
            $cValue = '#' . $cSektion . '.' . $cName . '#';
        } elseif ($argsCount > 2) {
            // String formatieren, vsprintf gibt false zurück,
            // sollte die Anzahl der Parameter nicht der Anzahl der Format-Liste entsprechen!
            $cArg_arr = [];
            for ($i = 2; $i < $argsCount; $i++) {
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
        $oWerte_arr       = [];
        $oSprachWerte_arr = [];
        if ($kSektion === null) {
            $oSektion = Shop::Container()->getDB()->select('tsprachsektion', 'cName', $cSektion);
            $kSektion = $oSektion->kSprachsektion ?? 0;
        }
        $kSektion = (int)$kSektion;
        if ($kSektion > 0) {
            $oSprachWerte_arr = Shop::Container()->getDB()->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [$this->kSprachISO, $kSektion],
                'cName, cWert'
            );
        }
        foreach ($oSprachWerte_arr as $oSprachWert) {
            $oWerte_arr[$oSprachWert->cName] = $oSprachWert->cWert;
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
        $cName    = Shop::Container()->getDB()->escape($cName);
        $cSektion = Shop::Container()->getDB()->escape($cSektion);
        $exists   = Shop::Container()->getDB()->select(
            'tsprachlog', 
            'kSprachISO', 
            (int)$this->kSprachISO, 
            'cSektion', 
            $cSektion, 
            'cName', 
            $cName
        );
        if ($exists === null) {
            $oLog             = new stdClass();
            $oLog->kSprachISO = $this->kSprachISO;
            $oLog->cSektion   = $cSektion;
            $oLog->cName      = $cName;
            Shop::Container()->getDB()->insert('tsprachlog', $oLog);
        }

        return $this;
    }

    /**
     * @param bool $currentLang
     * @return int
     */
    public function clearLog($currentLang = true)
    {
        $where = $currentLang === true ? ' WHERE kSprachISO = ' . (int)$this->kSprachISO : '';

        return Shop::Container()->getDB()->query('DELETE FROM tsprachlog' . $where, \DB\ReturnType::AFFECTED_ROWS);
    }

    /**
     * @return array
     */
    public function gibLogWerte()
    {
        return Shop::Container()->getDB()->selectAll('tsprachlog', 'kSprachISO', (int)$this->kSprachISO, '*', 'cName ASC');
    }

    /**
     * @return array
     */
    public function gibAlleWerte()
    {
        $oWerte_arr     = [];
        $oSektionen_arr = $this->gibSektionen();
        foreach ($oSektionen_arr as $oSektion) {
            $oSektion->oWerte_arr = Shop::Container()->getDB()->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [(int)$this->kSprachISO, $oSektion->kSprachsektion]
            );
            $oWerte_arr[] = $oSektion;
        }

        return $oWerte_arr;
    }

    /**
     * @return array
     */
    public function gibInstallierteSprachen()
    {
        return array_filter(
            Shop::Container()->getDB()->query('SELECT * FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS),
            function ($l) {
                return $this->mappekISO($l->cISO) > 0;
            }
        );
    }

    /**
     * @return array
     */
    public function gibVerfuegbareSprachen()
    {
        return Shop::Container()->getDB()->query('SELECT * FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @return array
     */
    public function gibSektionen()
    {
        return Shop::Container()->getDB()->query('SELECT * FROM tsprachsektion ORDER BY cNAME ASC', \DB\ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @return bool
     */
    public function gueltig()
    {
        return $this->kSprachISO > 0;
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
        $_keys       = ['kSprachISO', 'kSprachsektion', 'cName'];
        $_values     = [(int)$this->kSprachISO, (int)$kSprachsektion, $cName];
        $_upd        = new stdClass();
        $_upd->cWert = $cWert;

        return Shop::Container()->getDB()->update('tsprachwerte', $_keys, $_values, $_upd) >= 0;
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

            return Shop::Container()->getDB()->insert('tsprachwerte', $oWert) > 0;
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
        return Shop::Container()->getDB()->delete('tsprachwerte', ['kSprachsektion', 'cName'], [(int)$kSprachsektion, $cName]);
    }

    /**
     * @param string $cSuchwort
     * @return array
     */
    public function suche($cSuchwort)
    {
        return Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT tsprachwerte.kSprachsektion, tsprachwerte.cName, tsprachwerte.cWert, 
                tsprachwerte.cStandard, tsprachwerte.bSystem, tsprachsektion.cName AS cSektionName
                FROM tsprachwerte
                LEFT JOIN tsprachsektion 
                    ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                WHERE (
                    tsprachwerte.cWert LIKE :search 
                    OR tsprachwerte.cName LIKE :search
                )
                AND kSprachISO = :id',
            [
                'search' => '%' . Shop::Container()->getDB()->escape($cSuchwort) . '%',
                'id'     => $this->kSprachISO
            ],
            2
        );
    }

    /**
     * @param int $nTyp
     * @return string
     */
    public function _export($nTyp = 0)
    {
        $cCSVData_arr = [];
        $nTyp         = (int)$nTyp;

        switch ($nTyp) {
            default:
            case 0: // Alle
                $oWerte_arr = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                            ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso',
                    ['iso' => (int)$this->kSprachISO],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                break;

            case 1: // System
                $oWerte_arr = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                            ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso
                            AND bSystem = 1',
                    ['iso' => (int)$this->kSprachISO],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                break;

            case 2: // Eigene
                $oWerte_arr = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                          ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso 
                            AND bSystem = 0',
                    ['iso' => (int)$this->kSprachISO],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                break;
        }

        foreach ($oWerte_arr as $oWert) {
            if (strlen($oWert->cWert) === 0) {
                $oWert->cWert = $oWert->cStandard ?? null;
            }
            $cCSVData_arr[] = [
                $oWert->cSektionName,
                $oWert->cName,
                $oWert->cWert,
                $oWert->bSystem
            ];
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
        if ($kSprachISO === 0 || $kSprachISO === false) {
            // Sprache noch nicht installiert
            $oSprachISO       = new stdClass();
            $oSprachISO->cISO = $cISO;
            $kSprachISO       = Shop::Container()->getDB()->insert('tsprachiso', $oSprachISO);
        }

        while (($cData_arr = fgetcsv($hFile, 4048, ';')) !== false) {
            if (count($cData_arr) === 4) {
                // Sektion holen und ggf neu anlegen
                $cSektion = $cData_arr[0];
                $oSektion = Shop::Container()->getDB()->select('tsprachsektion', 'cName', $cSektion);
                if (isset($oSektion->kSprachsektion)) {
                    $kSprachsektion = $oSektion->kSprachsektion;
                } else {
                    // Sektion hinzufügen
                    $oSektion        = new stdClass();
                    $oSektion->cName = $cSektion;
                    $kSprachsektion  = Shop::Container()->getDB()->insert('tsprachsektion', $oSektion);
                }

                $cName   = $cData_arr[1];
                $cWert   = $cData_arr[2];
                $bSystem = (int)$cData_arr[3];

                switch ($nTyp) {
                    case 0: // Neu importieren
                        // Gültige Zeile, vorhandene Variablen löschen
                        if (!$bDeleteFlag) {
                            Shop::Container()->getDB()->delete('tsprachwerte', 'kSprachISO', $kSprachISO);
                            $bDeleteFlag = true;
                        }
                        $val                 = new stdClass();
                        $val->kSprachISO     = $kSprachISO;
                        $val->kSprachsektion = $kSprachsektion;
                        $val->cName          = $cData_arr[1];
                        $val->cWert          = $cData_arr[2];
                        $val->cStandard      = $cData_arr[2];
                        $val->bSystem        = $bSystem;
                        Shop::Container()->getDB()->insert('tsprachwerte', $val);
                        $nUpdateCount++;
                        break;

                    case 1: // Vorhandene Variablen überschreiben
                        Shop::Container()->getDB()->executeQueryPrepared(
                            'REPLACE INTO tsprachwerte
                                SET kSprachISO = :iso, 
                                    kSprachsektion = :section,
                                    cName = :name, 
                                    cWert = :val, 
                                    cStandard = :val, 
                                    bSystem = :sys',
                            [
                                'iso'     => $kSprachISO,
                                'section' => $kSprachsektion,
                                'name'    => $cName,
                                'val'     => $cWert,
                                'sys'     => $bSystem
                            ],
                            4
                        );
                        $nUpdateCount++;
                        break;

                    case 2: // Vorhandene Variablen beibehalten
                        $oWert = Shop::Container()->getDB()->select(
                            'tsprachwerte',
                            'kSprachISO',
                            $kSprachISO,
                            'kSprachsektion',
                            $kSprachsektion,
                            'cName',
                            $cName
                        );
                        if (!$oWert) {
                            Shop::Container()->getDB()->executeQueryPrepared(
                                'REPLACE INTO tsprachwerte
                                    SET kSprachISO = :iso, 
                                        kSprachsektion = :section,
                                        cName = :name, 
                                        cWert = :val, 
                                        cStandard = :val, 
                                        bSystem = :sys',
                                [
                                    'iso'     => $kSprachISO,
                                    'section' => $kSprachsektion,
                                    'name'    => $cName,
                                    'val'     => $cWert,
                                    'sys'     => $bSystem
                                ],
                                4
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
                $this->oSprache_arr = array_map(
                    function ($e) {
                        $e->kSprache = (int)$e->kSprache;

                        return $e;
                    },
                    Shop::Container()->getDB()->query('SELECT kSprache FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS)
                );
                Shop::Cache()->set($cacheID, $this->oSprache_arr, [CACHING_GROUP_LANGUAGE]);
            }
        }

        return $this->oSprache_arr;
    }

    /**
     * @param int   $kSprache
     * @param array $oShopSpracheAssoc_arr
     * @return bool
     */
    public static function isShopLanguage($kSprache, $oShopSpracheAssoc_arr = [])
    {
        $kSprache = (int)$kSprache;
        if ($kSprache > 0) {
            if (!is_array($oShopSpracheAssoc_arr) || count($oShopSpracheAssoc_arr) === 0) {
                $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            }

            return isset($oShopSpracheAssoc_arr[$kSprache]);
        }

        return false;
    }

    function getLocale() {
        $langs  = array();
        $locale = '';

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all(
                '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $lang_parse);

            if (count($lang_parse[1])) {
                $langs = array_combine($lang_parse[1], $lang_parse[4]);
                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }
                arsort($langs, SORT_NUMERIC);
            }
        }

        foreach ($langs as $lang => $val) {
            if (strpos($lang, 'de') === 0) {
                $locale = 'de_DE.UTF-8';
                break;
            } else if (strpos($lang, 'en') === 0) {
                $locale = 'en_US.UTF-8';
                break;
            } else if (strpos($lang, 'fr') === 0) {
                $locale = 'fr_FR.UTF-8';
                break;
            } else if (strpos($lang, 'ar') === 0) {
                $locale = 'ar_SA.UTF-8';
                break;
            } else if (strpos($lang, 'pt') === 0) {
                $locale = 'pt_PT.UTF-8';
                break;
            } else {
                $locale = 'en_US.UTF-8';
                break;
            }
        }
        return $locale;
    }

    function setLocalization($domain, $path) {
        if (!function_exists('gettext')) {
            exit ("Gettext doesn't exist on your system.");
        }

        $locale  = $this->getLocale();

        Shop::dbg($locale, false,'locale');



        $locale = 'en_GB.UTF-8';



        Shop::dbg($locale, false,'locale');


        putenv("LANG=".$locale);
        putenv("LC_ALL=".$locale);
        bindtextdomain($domain, '/../../Locale/');
        Shop::dbg(bindtextdomain($domain, $path.'/Locale/'));
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);

        $results = setlocale(LC_ALL, $locale);

        if (!$results) {
            exit ('setlocale failed: locale function is not available on this platform, or the given local does not exist in this environment. You can check your locales on the terminal using "locale -a"');
        }

    }


}
