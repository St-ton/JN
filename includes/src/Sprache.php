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
 * @method array getSectionValues(string $cSektion, int | null $kSektion = null)
 * @method array getInstalled()
 * @method array getAvailable()
 * @method string getIso()
 * @method bool valid()
 * @method bool isValid()
 * @method array|mixed|null getLangArray()
 * @method stdClass|null getIsoFromLangID(int $kSprache)
 * @method static stdClass|null getLangIDFromIso(string $cISO)
 * @method static bool|int|string getLanguageDataByType(string $cISO = '', int $kSprache = 0)
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
        'autoload'              => '_autoload',
        'get'                   => 'gibWert',
        'set'                   => 'setzeWert',
        'insert'                => 'fuegeEin',
        'delete'                => 'loesche',
        'search'                => 'suche',
        'import'                => '_import',
        'export'                => '_export',
        'reset'                 => '_reset',
        'log'                   => 'logWert',
        'generate'              => 'generateLangVars',
        'getAll'                => 'gibAlleWerte',
        'getLogs'               => 'gibLogWerte',
        'getSections'           => 'gibSektionen',
        'getSectionValues'      => 'gibSektionsWerte',
        'getInstalled'          => 'gibInstallierteSprachen',
        'getAvailable'          => 'gibVerfuegbareSprachen',
        'getIso'                => 'gibISO',
        'valid'                 => 'gueltig',
        'isValid'               => 'gueltig',
        'change'                => 'changeDatabase',
        'update'                => 'updateRow',
        'getLangArray'          => '_getLangArray',
        'getIsoFromLangID'      => '_getIsoFromLangID',
        'getLangIDFromIso'      => '_getLangIDFromIso',
        'getLanguageDataByType' => '_getLanguageDataByType'
    ];

    /**
     * @param bool $bAutoload
     * @return Sprache
     */
    public static function getInstance(bool $bAutoload = true): self
    {
        return self::$instance ?? new self($bAutoload);
    }

    /**
     * @param bool $bAutoload
     */
    public function __construct(bool $bAutoload = true)
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
     * @param array  $arguments
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
     * @param array  $arguments
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
    private static function map($method): ?string
    {
        return self::$mapping[$method] ?? null;
    }

    /**
     * @return array|mixed
     */
    public function generateLangVars()
    {
        if ($this->cacheID !== null) {
            return ($this->langVars = Shop::Container()->getCache()->get($this->cacheID)) === false
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
        return Shop::Container()->getCache()->set($this->cacheID, $this->langVars, [CACHING_GROUP_LANGUAGE]);
    }

    /**
     * @param int $kSprache
     * @return stdClass|null
     */
    public function _getIsoFromLangID(int $kSprache)
    {
        if (!isset($this->isoAssociation[$kSprache])) {
            $cacheID = 'lang_iso_ks';
            if (($this->isoAssociation = Shop::Container()->getCache()->get($cacheID)) === false
                || !isset($this->isoAssociation[$kSprache])
            ) {
                $this->isoAssociation[$kSprache] = Shop::Container()->getDB()->select(
                    'tsprache',
                    'kSprache',
                    $kSprache,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'cISO'
                );
                Shop::Container()->getCache()->set($cacheID, $this->isoAssociation, [CACHING_GROUP_LANGUAGE]);
            }
        }

        return $this->isoAssociation[$kSprache];
    }

    /**
     * @param string $cISO
     * @return stdClass|null
     */
    public function _getLangIDFromIso(string $cISO)
    {
        if (!isset($this->idAssociation[$cISO])) {
            $cacheID = 'lang_id_ks';
            if (($this->idAssociation = Shop::Container()->getCache()->get($cacheID)) === false
                || !isset($this->idAssociation[$cISO])
            ) {
                $res = Shop::Container()->getDB()->select('tsprachiso', 'cISO', $cISO);
                if (isset($res->kSprachISO)) {
                    $res->kSprachISO = (int)$res->kSprachISO;
                }
                $this->idAssociation[$cISO] = $res;
                Shop::Container()->getCache()->set($cacheID, $this->idAssociation, [CACHING_GROUP_LANGUAGE]);
            }
        }

        return $this->idAssociation[$cISO];
    }

    /**
     * @param int $kSektion
     * @param mixed null|string $default
     * @return string|null
     */
    public function getSectionName(int $kSektion, $default = null): ?string
    {
        $section = Shop::Container()->getDB()->select('tsprachsektion', 'kSprachsektion', $kSektion);

        return $section->cName ?? $default;
    }

    /**
     * generate all available lang vars for the current language
     * this saves some sql statements and is called by JTLCache only if the objekct cache is available
     *
     * @return $this
     */
    public function preLoad(): self
    {
        $_lv = $this->generateLangVars();
        if ($this->kSprachISO > 0 && $this->cISOSprache !== '' && !isset($_lv[$this->cISOSprache])) {
            $allLangVars                        = Shop::Container()->getDB()->queryPrepared(
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
    public function _autoload(): self
    {
        // cISOSprache aus Session
        if (isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache']) > 0) {
            $this->cISOSprache = $_SESSION['cISOSprache'];
        } else {
            $oSprache = self::getDefaultLanguage();
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
    public function setzeSprache(string $cISO): self
    {
        $this->cISOSprache = $cISO;
        $this->kSprachISO  = $this->mappekISO($this->cISOSprache);

        return $this;
    }

    /**
     * @param string $cISO
     * @return int|bool
     */
    public function mappekISO(string $cISO)
    {
        if (strlen($cISO) > 0) {
            if (isset($this->oSprachISO[$cISO]->kSprachISO)) {
                return (int)$this->oSprachISO[$cISO]->kSprachISO;
            }
            $oSprachISO              = $this->_getLangIDFromIso($cISO);
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
    public function gibWert($cName, $cSektion = 'global'): string
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
        $value = $this->langVars[$this->cISOSprache][$cSektion][$cName] ?? null;
        if ($save === true) {
            // only save if values changed
            $this->saveLangVars();
        }
        $argsCount = func_num_args();
        if ($value === null) {
            $this->logWert($cSektion, $cName);
            $value = '#' . $cSektion . '.' . $cName . '#';
        } elseif ($argsCount > 2) {
            // String formatieren, vsprintf gibt false zurück,
            // sollte die Anzahl der Parameter nicht der Anzahl der Format-Liste entsprechen!
            $args = [];
            for ($i = 2; $i < $argsCount; $i++) {
                $args[] = func_get_arg($i);
            }
            if (vsprintf($value, $args) !== false) {
                $value = vsprintf($value, $args);
            }
        }

        return $value;
    }

    /**
     * @param string   $sectionName
     * @param int|null $sectionID
     * @return array
     */
    public function gibSektionsWerte($sectionName, $sectionID = null): array
    {
        $values        = [];
        $localizations = [];
        if ($sectionID === null) {
            $oSektion  = Shop::Container()->getDB()->select('tsprachsektion', 'cName', $sectionName);
            $sectionID = $oSektion->kSprachsektion ?? 0;
        }
        $sectionID = (int)$sectionID;
        if ($sectionID > 0) {
            $localizations = Shop::Container()->getDB()->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [$this->kSprachISO, $sectionID],
                'cName, cWert'
            );
        }
        foreach ($localizations as $translation) {
            $values[$translation->cName] = $translation->cWert;
        }

        return $values;
    }

    /**
     * Nicht gesetzte Werte loggen
     *
     * @param string $sectionName
     * @param string $varName
     * @return $this
     */
    public function logWert($sectionName, $varName): self
    {
        $varName     = Shop::Container()->getDB()->escape($varName);
        $sectionName = Shop::Container()->getDB()->escape($sectionName);
        $exists      = Shop::Container()->getDB()->select(
            'tsprachlog',
            'kSprachISO',
            (int)$this->kSprachISO,
            'cSektion',
            $sectionName,
            'cName',
            $varName
        );
        if ($exists === null) {
            $ins             = new stdClass();
            $ins->kSprachISO = $this->kSprachISO;
            $ins->cSektion   = $sectionName;
            $ins->cName      = $varName;
            Shop::Container()->getDB()->insert('tsprachlog', $ins);
        }

        return $this;
    }

    /**
     * @param bool $currentLang
     * @return int
     */
    public function clearLog(bool $currentLang = true): int
    {
        $where = $currentLang === true ? ' WHERE kSprachISO = ' . (int)$this->kSprachISO : '';

        return Shop::Container()->getDB()->query('DELETE FROM tsprachlog' . $where, \DB\ReturnType::AFFECTED_ROWS);
    }

    /**
     * @return array
     */
    public function gibLogWerte(): array
    {
        return Shop::Container()->getDB()->selectAll('tsprachlog', 'kSprachISO', $this->kSprachISO, '*', 'cName ASC');
    }

    /**
     * @return array
     */
    public function gibAlleWerte(): array
    {
        $oWerte_arr = [];
        foreach ($this->gibSektionen() as $section) {
            $section->kSprachsektion = (int)$section->kSprachsektion;
            $section->oWerte_arr     = \Functional\map(Shop::Container()->getDB()->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [$this->kSprachISO, $section->kSprachsektion]
            ), function ($e) {
                $e->kSprachISO     = (int)$e->kSprachISO;
                $e->kSprachsektion = (int)$e->kSprachsektion;
                $e->bSystem        = (int)$e->bSystem;

                return $e;
            });
            $oWerte_arr[]            = $section;
        }

        return $oWerte_arr;
    }

    /**
     * @return array
     */
    public function gibInstallierteSprachen(): array
    {
        return \Functional\map(
            array_filter(
                Shop::Container()->getDB()->query('SELECT * FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS),
                function ($l) {
                    return $this->mappekISO($l->cISO) > 0;
                }
            ),
            function ($e) {
                $e->kSprache = (int)$e->kSprache;

                return $e;
            }
        );
    }

    /**
     * @return array
     */
    public function gibVerfuegbareSprachen(): array
    {
        return \Functional\map(
            Shop::Container()->getDB()->query('SELECT * FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS),
            function ($e) {
                $e->kSprache = (int)$e->kSprache;

                return $e;
            }
        );
    }

    /**
     * @return array
     */
    public function gibSektionen(): array
    {
        return Shop::Container()->getDB()->query(
            'SELECT * FROM tsprachsektion ORDER BY cNAME ASC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return bool
     */
    public function gueltig(): bool
    {
        return $this->kSprachISO > 0;
    }

    /**
     * @return string
     */
    public function gibISO(): string
    {
        return $this->cISOSprache;
    }

    /**
     * @return $this
     */
    public function _reset(): self
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
    public function setzeWert(int $kSprachsektion, $cName, $cWert): bool
    {
        $_keys       = ['kSprachISO', 'kSprachsektion', 'cName'];
        $_values     = [(int)$this->kSprachISO, $kSprachsektion, $cName];
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
    public function fuegeEin($cSprachISO, int $kSprachsektion, $cName, $cWert): bool
    {
        $kSprachISO = $this->mappekISO($cSprachISO);
        if ($kSprachISO > 0) {
            $oWert                 = new stdClass();
            $oWert->kSprachISO     = (int)$kSprachISO;
            $oWert->kSprachsektion = $kSprachsektion;
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
     * @return int
     */
    public function loesche(int $kSprachsektion, $cName): int
    {
        return Shop::Container()->getDB()->delete(
            'tsprachwerte',
            ['kSprachsektion', 'cName'],
            [$kSprachsektion, $cName]
        );
    }

    /**
     * @param string $cSuchwort
     * @return array
     */
    public function suche(string $cSuchwort): array
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int $type
     * @return string
     */
    public function _export(int $type = 0): string
    {
        $csvData = [];
        switch ($type) {
            default:
            case 0: // Alle
                $values = Shop::Container()->getDB()->queryPrepared(
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
                $values = Shop::Container()->getDB()->queryPrepared(
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
                $values = Shop::Container()->getDB()->queryPrepared(
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

        foreach ($values as $value) {
            if (strlen($value->cWert) === 0) {
                $value->cWert = $value->cStandard ?? null;
            }
            $csvData[] = [
                $value->cSektionName,
                $value->cName,
                $value->cWert,
                $value->bSystem
            ];
        }
        $fileName = tempnam('../' . PFAD_DBES_TMP, 'csv');
        $handle   = fopen($fileName, 'w');
        foreach ($csvData as $csv) {
            fputcsv($handle, $csv, ';');
        }
        fclose($handle);

        return $fileName;
    }

    /**
     * @param string $fileName
     * @param string $iso
     * @param int    $type
     * @return bool|int
     */
    public function _import(string $fileName, string $iso, int $type)
    {
        $handle = fopen($fileName, 'r');
        if (!$handle) {
            return false;
        }

        $deleteFlag  = false;
        $updateCount = 0;
        $kSprachISO  = $this->mappekISO($iso);
        if ($kSprachISO === 0 || $kSprachISO === false) {
            // Sprache noch nicht installiert
            $oSprachISO       = new stdClass();
            $oSprachISO->cISO = $iso;
            $kSprachISO       = Shop::Container()->getDB()->insert('tsprachiso', $oSprachISO);
        }

        while (($data = fgetcsv($handle, 4048, ';')) !== false) {
            if (count($data) === 4) {
                // Sektion holen und ggf neu anlegen
                $cSektion = $data[0];
                $oSektion = Shop::Container()->getDB()->select('tsprachsektion', 'cName', $cSektion);
                if (isset($oSektion->kSprachsektion)) {
                    $kSprachsektion = $oSektion->kSprachsektion;
                } else {
                    // Sektion hinzufügen
                    $oSektion        = new stdClass();
                    $oSektion->cName = $cSektion;
                    $kSprachsektion  = Shop::Container()->getDB()->insert('tsprachsektion', $oSektion);
                }

                $cName   = $data[1];
                $cWert   = $data[2];
                $bSystem = (int)$data[3];

                switch ($type) {
                    case 0: // Neu importieren
                        // Gültige Zeile, vorhandene Variablen löschen
                        if (!$deleteFlag) {
                            Shop::Container()->getDB()->delete('tsprachwerte', 'kSprachISO', $kSprachISO);
                            $deleteFlag = true;
                        }
                        $val                 = new stdClass();
                        $val->kSprachISO     = $kSprachISO;
                        $val->kSprachsektion = $kSprachsektion;
                        $val->cName          = $data[1];
                        $val->cWert          = $data[2];
                        $val->cStandard      = $data[2];
                        $val->bSystem        = $bSystem;
                        Shop::Container()->getDB()->insert('tsprachwerte', $val);
                        $updateCount++;
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
                            \DB\ReturnType::DEFAULT
                        );
                        $updateCount++;
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
                                \DB\ReturnType::DEFAULT
                            );
                            $updateCount++;
                        }
                        break;
                }
            }
        }

        return $updateCount;
    }

    /**
     * @return array|mixed|null
     */
    public function _getLangArray()
    {
        if ($this->oSprache_arr === null || $this->oSprache_arr === false) {
            $cacheID = 'langobj';
            if (($this->oSprache_arr = Shop::Container()->getCache()->get($cacheID)) === false) {
                $this->oSprache_arr = array_map(
                    function ($e) {
                        $e->kSprache = (int)$e->kSprache;

                        return $e;
                    },
                    Shop::Container()->getDB()->query('SELECT kSprache FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS)
                );
                Shop::Container()->getCache()->set($cacheID, $this->oSprache_arr, [CACHING_GROUP_LANGUAGE]);
            }
        }

        return $this->oSprache_arr;
    }

    /**
     * @param int   $languageID
     * @param array $languages
     * @return bool
     */
    public static function isShopLanguage(int $languageID, array $languages = []): bool
    {
        if ($languageID > 0) {
            if (!is_array($languages) || count($languages) === 0) {
                $languages = self::getAllLanguages(1);
            }

            return isset($languages[$languageID]);
        }

        return false;
    }

    /**
     * @param string $iso
     * @param int    $languageID
     * @return int|string|bool
     */
    public function _getLanguageDataByType(string $iso = '', int $languageID = 0)
    {
        if (strlen($iso) > 0) {
            $data = $this->_getLangIDFromIso($iso);

            return $data === null
                ? false
                : $data->kSprachISO;
        }
        if ($languageID > 0) {
            $data = $this->_getIsoFromLangID($languageID);

            return $data === null
                ? false
                : $data->cISO;
        }

        return false;
    }

    /**
     * gibt alle Sprachen zurück
     *
     * @param int $returnType
     * 0 = Normales Array
     * 1 = Gib ein Assoc mit Key = kSprache
     * 2 = Gib ein Assoc mit Key = cISO
     * @return array
     * @former gibAlleSprachen()
     * @since 5.0.0
     */
    public static function getAllLanguages(int $returnType = 0)
    {
        $languages = \Session\Frontend::getLanguages();
        if (count($languages) > 0) {
            switch ($returnType) {
                case 2:
                    return \Functional\reindex($languages, function ($e) {
                        return $e->cISO;
                    });

                case 1:
                    return \Functional\reindex($languages, function ($e) {
                        return $e->kSprache;
                    });

                case 0:
                default:
                    return $languages;
            }
        }
        $languages = array_map(
            function ($s) {
                $s->kSprache = (int)$s->kSprache;

                return $s;
            },
            Shop::Container()->getDB()->query(
                'SELECT *
                    FROM tsprache
                    ORDER BY cShopStandard DESC, cNameDeutsch',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            )
        );
        switch ($returnType) {
            case 2:
                return \Functional\reindex($languages, function ($e) {
                    return $e->cISO;
                });

            case 1:
                return \Functional\reindex($languages, function ($e) {
                    return $e->kSprache;
                });

            case 0:
            default:
                return $languages;
        }
    }

    /**
     * @param bool     $shop
     * @param int|null $languageID - optional lang id to check against instead of session value
     * @return bool
     * @former standardspracheAktiv()
     * @since 5.0.0
     */
    public static function isDefaultLanguageActive(bool $shop = false, int $languageID = null): bool
    {
        if ($languageID === null && !isset($_SESSION['kSprache'])) {
            return true;
        }
        $langToCheckAgainst = $languageID !== null ? (int)$languageID : Shop::getLanguageID();
        if ($langToCheckAgainst > 0) {
            foreach (\Session\Frontend::getLanguages() as $language) {
                if ($language->cStandard === 'Y' && (int)$language->kSprache === $langToCheckAgainst && !$shop) {
                    return true;
                }
                if ($language->cShopStandard === 'Y' && (int)$language->kSprache === $langToCheckAgainst && $shop) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param bool $shop
     * @return stdClass|Sprache
     * @former gibStandardsprache()
     * @since 5.0.0
     */
    public static function getDefaultLanguage(bool $shop = true)
    {
        foreach (\Session\Frontend::getLanguages() as $language) {
            if ($language->cStandard === 'Y' && !$shop) {
                return $language;
            }
            if ($language->cShopStandard === 'Y' && $shop) {
                return $language;
            }
        }

        $cacheID = 'shop_lang_' . (($shop === true) ? 'b' : '');
        if (($lang = Shop::Container()->getCache()->get($cacheID)) !== false && $lang !== null) {
            return $lang;
        }
        $lang           = Shop::Container()->getDB()->select('tsprache', $shop ? 'cShopStandard' : 'cStandard', 'Y');
        $lang->kSprache = (int)$lang->kSprache;
        Shop::Container()->getCache()->set($cacheID, $lang, [CACHING_GROUP_LANGUAGE]);

        return $lang;
    }

    /**
     * @former setzeSpracheUndWaehrungLink()
     * @since 5.0.0
     */
    public static function generateLanguageAndCurrencyLinks(): void
    {
        global $oZusatzFilter, $AktuellerArtikel;
        $kLink         = Shop::$kLink;
        $kSeite        = Shop::$kSeite;
        $shopURL       = Shop::getURL() . '/';
        $helper        = Shop::Container()->getLinkService();
        $productFilter = Shop::getProductFilter();
        if ($kSeite !== null && $kSeite > 0) {
            $kLink = $kSeite;
        }
        $ls          = Shop::Container()->getLinkService();
        $mapper      = new \Mapper\PageTypeToLinkType();
        $mapped      = $mapper->map(Shop::getPageType());
        $specialPage = $mapped > 0 ? $ls->getSpecialPage($mapped) : null;
        $page        = $kLink > 0 ? $ls->getPageLink($kLink) : null;
        if (count(\Session\Frontend::getLanguages()) > 1) {
            /** @var Artikel $AktuellerArtikel */
            if ($AktuellerArtikel !== null
                && $AktuellerArtikel->kArtikel > 0
                && empty($AktuellerArtikel->cSprachURL_arr)
            ) {
                $AktuellerArtikel->baueArtikelSprachURL();
            }
            foreach (\Session\Frontend::getLanguages() as $lang) {
                if (isset($AktuellerArtikel->cSprachURL_arr[$lang->cISO])) {
                    $lang->cURL     = $AktuellerArtikel->cSprachURL_arr[$lang->cISO];
                    $lang->cURLFull = $shopURL . $AktuellerArtikel->cSprachURL_arr[$lang->cISO];
                } elseif ($specialPage !== null) {
                    if (Shop::getPageType() === PAGE_STARTSEITE) {
                        $url = $shopURL . '?lang=' . $lang->cISO;
                    } elseif ($specialPage->getFileName() !== '') {
                        if (Shop::$kNews > 0) {
                            $newsItem = new News\Item(Shop::Container()->getDB());
                            $newsItem->load(Shop::$kNews);
                            $url = $newsItem->getURL($lang->kSprache);
                        } elseif (Shop::$kNewsKategorie > 0) {
                            $newsCategory = new \News\Category(Shop::Container()->getDB());
                            $newsCategory->load(Shop::$kNewsKategorie);
                            $url = $newsCategory->getURL($lang->kSprache);
                        } else {
                            $url = $helper->getStaticRoute($specialPage->getFileName(), false, false, $lang->cISO);
                            // check if there is a SEO link for the given file
                            if ($url === $specialPage->getFileName()) {
                                // no SEO link - fall back to php file with GET param
                                $url = $shopURL . $specialPage->getFileName() . '?lang=' . $lang->cISO;
                            } else { //there is a SEO link - make it a full URL
                                $url = $helper->getStaticRoute($specialPage->getFileName(), true, false, $lang->cISO);
                            }
                        }
                    } else {
                        $url = $specialPage->getURL($lang->kSprache);
                    }
                    $lang->cURL     = $url;
                    $lang->cURLFull = $url;
                    executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_SETZESPRACHEUNDWAEHRUNG_SPRACHE);
                } elseif ($page !== null) {
                    $lang->cURL = $page->getURL($lang->kSprache);
                    if (strpos($lang->cURL, '/?s=') !== false) {
                        $lang->cURL    .= '&amp;lang=' . $lang->cISO;
                        $lang->cURLFull = rtrim($shopURL, '/') . $lang->cURL;
                    } else {
                        $lang->cURLFull = $lang->cURL;
                    }
                } else {
                    $originalLanguage = $productFilter->getFilterConfig()->getLanguageID();
                    $productFilter->getFilterConfig()->setLanguageID($lang->kSprache);
                    $url = $productFilter->getFilterURL()->getURL($oZusatzFilter);
                    $productFilter->getFilterConfig()->setLanguageID($originalLanguage);
                    if ($productFilter->getPage() > 1) {
                        if (strpos($url, 'navi.php') !== false) {
                            $url .= '&amp;seite=' . $productFilter->getPage();
                        } else {
                            $url .= SEP_SEITE . $productFilter->getPage();
                        }
                    }
                    $lang->cURL     = $url;
                    $lang->cURLFull = $url;
                }
            }
        }
        if (count(\Session\Frontend::getCurrencies()) > 1) {
            if ($AktuellerArtikel !== null
                && $AktuellerArtikel->kArtikel > 0
                && empty($AktuellerArtikel->cSprachURL_arr)
            ) {
                $AktuellerArtikel->baueArtikelSprachURL(false);
            }
            $currentCurrencyCode = Session\Frontend::getCurrency()->getID();
            foreach (\Session\Frontend::getCurrencies() as $currency) {
                if (isset($AktuellerArtikel->cSprachURL_arr[Shop::getLanguageCode()])) {
                    $url = $AktuellerArtikel->cSprachURL_arr[Shop::getLanguageCode()];
                } elseif ($specialPage !== null) {
                    $url = $specialPage->getURL();
                    if (empty($url)) {
                        if (Shop::getPageType() === PAGE_STARTSEITE) {
                            $url = '';
                        } elseif ($specialPage->getFileName() !== null) {
                            $url = $helper->getStaticRoute($specialPage->getFileName(), false);
                            // check if there is a SEO link for the given file
                            if ($url === $specialPage->getFileName()) {
                                // no SEO link - fall back to php file with GET param
                                $url = $shopURL . $specialPage->getFileName();
                            } else {
                                // there is a SEO link - make it a full URL
                                $url = $helper->getStaticRoute($specialPage->getFileName(), true);
                            }
                        }
                    }
                } elseif ($page !== null) {
                    $url = $page->getURL();
                } else {
                    $url = $productFilter->getFilterURL()->getURL($oZusatzFilter);
                }
                if ($currency->getID() !== $currentCurrencyCode) {
                    $url = $url . (strpos($url, '?') === false ? '?' : '&') . 'curr=' . $currency->getCode();
                }
                $currency->setURL($url);
                $currency->setURLFull(strpos($url, Shop::getURL()) === false
                    ? ($shopURL . $url)
                    : $url);
            }
        }
        executeHook(HOOK_TOOLSGLOBAL_INC_SETZESPRACHEUNDWAEHRUNG_WAEHRUNG, [
            'oNaviFilter'       => &$productFilter,
            'oZusatzFilter'     => &$oZusatzFilter,
            'cSprachURL'        => [],
            'oAktuellerArtikel' => &$AktuellerArtikel,
            'kSeite'            => &$kSeite,
            'kLink'             => &$kLink,
            'AktuelleSeite'     => &Shop::$AktuelleSeite
        ]);
    }
    /**
     * @param string $cISO
     * @return string
     * @former ISO2land()
     * @since 5.0.0
     */
    public static function getCountryCodeByCountryName(string $cISO): string
    {
        if (strlen($cISO) > 2) {
            return $cISO;
        }
        $cSpalte = Shop::getLanguageCode() === 'ger' ? 'cDeutsch' : 'cEnglisch';
        $land    = Shop::Container()->getDB()->select('tland', 'cISO', $cISO);

        return $land->$cSpalte ?? $cISO;
    }

    /**
     * @param string $country
     * @return string
     * @former landISO()
     * @since 5.0.0
     */
    public static function getIsoCodeByCountryName(string $country): string
    {
        $iso = Shop::Container()->getDB()->select('tland', 'cDeutsch', $country);
        if (!empty($iso->cISO)) {
            return $iso->cISO;
        }
        $iso = Shop::Container()->getDB()->select('tland', 'cEnglisch', $country);
        if (!empty($iso->cISO)) {
            return $iso->cISO;
        }

        return 'noISO';
    }
}
