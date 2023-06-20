<?php

namespace JTL\Language;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Mapper\PageTypeToLinkType;
use JTL\News\Category;
use JTL\News\Item;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\map;
use function Functional\reindex;

/**
 * Class LanguageHalper
 * @package JTL\Language
 * @method string get(string $cName, string $cSektion = 'global', mixed ...$arg1)
 * @method bool set(int $kSprachsektion, string $cName, string $cWert)
 * @method bool insert(string $cSprachISO, int $kSprachsektion, string $cName, string $cWert)
 * @method bool delete(int $kSprachsektion, string $cName)
 * @method mixed search(string $cSuchwort)
 * @method bool|int import(string $cFileName, string $cISO, int $nTyp)
 * @method string export(int $nTyp = 0)
 * @method self reset()
 * @method self log(string $cSektion, string $cName)
 * @method array|mixed generate()
 * @method array getAll()
 * @method array getLogs()
 * @method array getSections()
 * @method array getSectionValues(string $cSektion, int | null $kSektion = null)
 * @method LanguageModel[] getInstalled()
 * @method LanguageModel[] getAvailable()
 * @method string getIso()
 * @method bool valid()
 * @method bool isValid()
 * @method array|mixed|null getLangArray()
 * @method stdClass|null getIsoFromLangID(int $languageID)
 * @method static stdClass|null getLangIDFromIso(string $cISO)
 * @method static bool|int|string getLanguageDataByType(string $cISO = '', int $languageID = 0)
 * @method static string getIsoCodeByCountryName(string $country)
 * @method static string getCountryCodeByCountryName(string $iso)
 * @method static LanguageModel getDefaultLanguage(bool $shop = true)
 * @method static LanguageModel[] getAllLanguages(int $returnType = 0, bool $force = false, bool $onlyActive = false)
 * @method static bool isShopLanguage(int $languageID, array $languages = [])
 */
class LanguageHelper
{
    /**
     * compatibility only
     *
     * @var int
     */
    public int $kSprachISO = 0;

    /**
     * compatibility only
     *
     * @var string
     */
    public string $cISOSprache = '';

    /**
     * @var string
     */
    private string $currentISOCode = '';

    /**
     * @var int
     */
    public int $currentLanguageID = 0;

    /**
     * @var array
     */
    public array $langVars = [];

    /**
     * @var string
     */
    public string $cacheID = 'cr_lng_dta';

    /**
     * @var stdClass[]
     */
    public array $availableLanguages;

    /**
     * @var array
     */
    public array $byISO = [];

    /**
     * @var array
     */
    public array $byLangID = [];

    /**
     * @var int
     */
    public int $kSprache = 0;

    /**
     * @var LanguageHelper|null
     */
    private static ?LanguageHelper $instance;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var JTLCacheInterface
     */
    private JTLCacheInterface $cache;

    /**
     * @var array
     */
    private static array $mapping = [
        'gibWert'                     => 'getTranslation',
        'get'                         => 'getTranslation',
        'set'                         => 'setzeWert',
        'insert'                      => 'fuegeEin',
        'delete'                      => 'loesche',
        'search'                      => 'suche',
        'import'                      => 'mappedImport',
        'export'                      => 'mappedExport',
        'reset'                       => 'mappedReset',
        'log'                         => 'logWert',
        'generate'                    => 'generateLangVars',
        'getAll'                      => 'gibAlleWerte',
        'getLogs'                     => 'gibLogWerte',
        'getSections'                 => 'gibSektionen',
        'getSectionValues'            => 'gibSektionsWerte',
        'getInstalled'                => 'gibInstallierteSprachen',
        'getAvailable'                => 'gibVerfuegbareSprachen',
        'getIso'                      => 'gibISO',
        'valid'                       => 'gueltig',
        'isValid'                     => 'gueltig',
        'change'                      => 'changeDatabase',
        'update'                      => 'updateRow',
        'isShopLanguage'              => 'mappedIsShopLanguage',
        'getLangArray'                => 'mappedGetLangArray',
        'getIsoFromLangID'            => 'mappedGetIsoFromLangID',
        'getLangIDFromIso'            => 'mappedGetLangIDFromIso',
        'getLanguageDataByType'       => 'mappedGetLanguageDataByType',
        'getAllLanguages'             => 'mappedGetAllLanguages',
        'getDefaultLanguage'          => 'mappedGetDefaultLanguage',
        'getCountryCodeByCountryName' => 'mappedGetCountryCodeByCountryName',
        'getIsoCodeByCountryName'     => 'mappedGetIsoCodeByCountryName',
    ];

    /**
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     * @return LanguageHelper
     */
    public static function getInstance(DbInterface $db = null, JTLCacheInterface $cache = null): self
    {
        return self::$instance ?? new self($db, $cache);
    }

    /**
     * LanguageHelper constructor.
     *
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     */
    public function __construct(DbInterface $db = null, JTLCacheInterface $cache = null)
    {
        self::$instance = $this;
        $this->cache    = $cache ?? Shop::Container()->getCache();
        $this->db       = $db ?? Shop::Container()->getDB();
        $this->autoload();
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
            ? \call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed|null
     */
    public static function __callStatic($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? \call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map(string $method): ?string
    {
        return self::$mapping[$method] ?? null;
    }

    /**
     * @return array
     */
    private function loadLangVars(): array
    {
        if (\count($this->langVars) > 0) {
            return $this->langVars;
        }

        return ($langVars = $this->cache->get($this->cacheID)) === false
            ? []
            : $langVars;
    }

    /**
     * @return bool
     */
    private function saveLangVars(): bool
    {
        return $this->cache->set($this->cacheID, $this->langVars, [\CACHING_GROUP_LANGUAGE]);
    }

    /**
     * generate all available lang vars for the current language
     * this saves some sql statements and is called by JTLCache only if the objekct cache is available
     *
     * @return $this
     */
    public function initLangVars(): self
    {
        $this->langVars = $this->loadLangVars();
        if (\count($this->langVars) > 0) {
            return $this;
        }
        $collection = $this->db->getCollection(
            'SELECT tsprachwerte.cWert AS val, tsprachwerte.cName AS name, 
                tsprachsektion.cName AS sectionName, tsprache.kSprache AS langID
                FROM tsprachwerte
                INNER JOIN tsprachiso iso 
                    ON tsprachwerte.kSprachISO = iso.kSprachISO
                INNER JOIN tsprache 
                    ON iso.cISO = tsprache.cISO
                LEFT JOIN tsprachsektion
                    ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion'
        );

        $this->langVars = [];
        foreach ($collection as $data) {
            $this->langVars[$data->langID][$data->sectionName][$data->name] = $data->val;
        }
        $this->getPluginLangVars();
        $this->saveLangVars();

        return $this;
    }

    private function getPluginLangVars(): void
    {
        $all = $this->db->getCollection(
            'SELECT tplugin.cPluginID, l.cName AS name,
                COALESCE(c.cName, tpluginsprachvariablesprache.cName) AS val,
                tsprache.kSprache
                FROM tplugin
                    JOIN tpluginsprachvariable AS l
                        ON tplugin.kPlugin = l.kPlugin
                    LEFT JOIN tpluginsprachvariablecustomsprache AS c
                        ON c.kPluginSprachvariable = l.kPluginSprachvariable
                    LEFT JOIN tpluginsprachvariablesprache
                        ON tpluginsprachvariablesprache.kPluginSprachvariable = l.kPluginSprachvariable
                        AND tpluginsprachvariablesprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
                    JOIN tsprache
                        ON tsprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
                WHERE tplugin.nStatus = :sts
                ORDER BY l.kPluginSprachvariable',
            ['sts' => State::ACTIVATED]
        )->groupBy(['kSprache', 'cPluginID'])->toArray();
        foreach ($all as $langID => $sections) {
            $langID = (int)$langID;
            foreach ($sections as $section => $data) {
                $variables = [];
                foreach ($data as $variable) {
                    $variables[$variable->name] = $variable->val;
                }
                $this->langVars[$langID][$section] = $variables;
            }
        }
    }

    private function initLangData(): void
    {
        $data = $this->cache->get('lng_dta_lst', function ($cache, $cacheID, &$content, &$tags): bool {
            $content = $this->db->getCollection(
                'SELECT tsprache.*, tsprachiso.kSprachISO FROM tsprache 
                    LEFT JOIN tsprachiso
                        ON tsprache.cISO = tsprachiso.cISO
                    ORDER BY tsprache.kSprache ASC'
            );
            $tags    = [\CACHING_GROUP_LANGUAGE];

            return true;
        });
        /** @var Collection $data */
        $this->availableLanguages = $data->map(static function (stdClass $e): stdClass {
            return (object)['kSprache' => (int)$e->kSprache];
        })->toArray();

        $this->byISO = $data->groupBy('cISO')->transform(static function (Collection $e): stdClass {
            $e = $e->first();

            return (object)[
                'kSprachISO' => (int)$e->kSprachISO,
                'kSprache'   => (int)$e->kSprache,
                'cISO'       => $e->cISO
            ];
        })->toArray();

        $this->byLangID = $data->groupBy('kSprache')->transform(static function (Collection $e): stdClass {
            $e = $e->first();

            return (object)['cISO' => $e->cISO];
        })->toArray();
    }

    /**
     * @param int $languageID
     * @return stdClass|null
     */
    private function mappedGetIsoFromLangID(int $languageID): ?stdClass
    {
        return $this->byLangID[$languageID] ?? null;
    }

    /**
     * @param string $isoCode
     * @return stdClass|null
     */
    private function mappedGetLangIDFromIso(string $isoCode): ?stdClass
    {
        return $this->byISO[$isoCode] ?? null;
    }

    /**
     * @return $this
     */
    public function autoload(): self
    {
        $this->initLangVars();
        $this->initLangData();
        if (isset($_SESSION['cISOSprache']) && \mb_strlen($_SESSION['cISOSprache']) > 0) {
            $this->currentISOCode = $_SESSION['cISOSprache'];
            $this->kSprache       = (int)$_SESSION['kSprache'];
        } else {
            $language = $this->mappedGetDefaultLanguage();
            if (isset($language->cISO) && \mb_strlen($language->cISO) > 0) {
                $this->currentISOCode = $language->getCode();
                $this->kSprache       = $language->getId();
            }
        }
        $this->currentLanguageID = $this->kSprache;
        $this->kSprachISO        = $this->mappekISO($this->currentISOCode);
        if (isset($_SESSION)) {
            $_SESSION['kSprachISO'] = $this->kSprachISO;
        }

        return $this;
    }

    /**
     * @param string $isoCode
     * @return $this
     */
    public function setzeSprache(string $isoCode): self
    {
        $this->currentISOCode    = $isoCode;
        $this->currentLanguageID = $this->mappekISO($this->currentISOCode);

        return $this;
    }

    /**
     * @param string $isoCode
     * @return int
     */
    public function mappekISO(string $isoCode): int
    {
        if (\mb_strlen($isoCode) === 0) {
            return 0;
        }
        if (isset($this->byISO[$isoCode]->kSprachISO)) {
            return (int)$this->byISO[$isoCode]->kSprachISO;
        }
        $langISO               = $this->mappedGetLangIDFromIso($isoCode);
        $this->byISO[$isoCode] = $langISO;

        return (int)($langISO->kSprachISO ?? 0);
    }

    /**
     * @param string $name
     * @param string $sectionName
     * @return string
     */
    public function getTranslation(string $name, string $sectionName = 'global'): string
    {
        if ($this->currentLanguageID === 0) {
            return '';
        }
        if ($this->langVars) {
            $this->langVars = $this->loadLangVars();
        }
        $save = false;
        if (!isset($this->langVars[$this->currentLanguageID])) {
            $this->langVars[$this->currentLanguageID] = [];
            $save                                     = true;
        }
        // Sektion noch nicht vorhanden, alle Werte der Sektion laden
        if (!isset($this->langVars[$this->currentLanguageID][$sectionName])) {
            $this->langVars[$this->currentLanguageID][$sectionName] = $this->gibSektionsWerte($sectionName);
            $save                                                   = true;
        }
        $value = $this->langVars[$this->currentLanguageID][$sectionName][$name] ?? null;
        if ($save === true) {
            // only save if values changed
            $this->saveLangVars();
        }
        $argsCount = \func_num_args();
        if ($value === null) {
            $this->logWert($sectionName, $name);
            $value = '#' . $sectionName . '.' . $name . '#';
        } elseif ($argsCount > 2) {
            $args = [];
            for ($i = 2; $i < $argsCount; $i++) {
                $args[] = \func_get_arg($i);
            }
            try {
                $res = \vsprintf($value, $args);
                if ($res !== false) { // php < 8.0
                    $value = $res;
                }
            } catch (\ValueError) {
            }
        }

        return $value;
    }

    /**
     * @param string   $sectionName
     * @param int|null $sectionID
     * @return array
     */
    public function gibSektionsWerte(string $sectionName, ?int $sectionID = null): array
    {
        $values = [];
        if ($sectionID === null) {
            $section   = $this->db->select('tsprachsektion', 'cName', $sectionName);
            $sectionID = (int)($section->kSprachsektion ?? 0);
        }
        if ($sectionID > 0) {
            $localizations = $this->db->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [$this->currentLanguageID, $sectionID],
                'cName, cWert'
            );
        } else {
            $localizations = $this->getPluginLocalizations($sectionName);
        }
        foreach ($localizations as $translation) {
            $values[$translation->cName] = $translation->cWert;
        }

        return $values;
    }

    /**
     * @param string $pluginID
     * @return stdClass[]
     */
    private function getPluginLocalizations(string $pluginID): array
    {
        return $this->db->getObjects(
            'SELECT l.cName, COALESCE(c.cName, tpluginsprachvariablesprache.cName) AS cWert
                FROM tplugin
                    JOIN tpluginsprachvariable AS l
                        ON tplugin.kPlugin = l.kPlugin
                    LEFT JOIN tpluginsprachvariablecustomsprache AS c
                        ON c.kPluginSprachvariable = l.kPluginSprachvariable
                    LEFT JOIN tpluginsprachvariablesprache
                        ON tpluginsprachvariablesprache.kPluginSprachvariable = l.kPluginSprachvariable
                        AND tpluginsprachvariablesprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
                    JOIN tsprache
                        ON tsprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
                WHERE tplugin.nStatus = :sts
                    AND tplugin.cPluginID = :pid
                    AND tsprache.kSprache = :lid
                ORDER BY l.cName, cWert',
            ['sts' => State::ACTIVATED, 'pid' => $pluginID, 'lid' => $this->currentLanguageID]
        );
    }

    /**
     * Nicht gesetzte Werte loggen
     *
     * @param string $sectionName
     * @param string $varName
     * @return $this
     */
    public function logWert(string $sectionName, string $varName): self
    {
        $exists = $this->db->select(
            'tsprachlog',
            'kSprachISO',
            $this->currentLanguageID,
            'cSektion',
            $sectionName,
            'cName',
            $varName
        );
        if ($exists === null && $this->currentLanguageID > 0) {
            $ins             = new stdClass();
            $ins->kSprachISO = $this->currentLanguageID;
            $ins->cSektion   = $sectionName;
            $ins->cName      = $varName;
            $this->db->insert('tsprachlog', $ins);
        }

        return $this;
    }

    /**
     * @param bool $currentLang
     * @return int
     */
    public function clearLog(bool $currentLang = true): int
    {
        $where = $currentLang === true ? ' WHERE kSprachISO = ' . (int)$this->currentLanguageID : '';

        return $this->db->getAffectedRows('DELETE FROM tsprachlog' . $where);
    }

    /**
     * @return stdClass[]
     */
    public function gibLogWerte(): array
    {
        return $this->db->selectAll(
            'tsprachlog',
            'kSprachISO',
            $this->currentLanguageID,
            '*',
            'cName ASC'
        );
    }

    /**
     * @return stdClass[]
     */
    public function gibAlleWerte(): array
    {
        $values = [];
        foreach ($this->gibSektionen() as $section) {
            $section->kSprachsektion = (int)$section->kSprachsektion;
            $section->oWerte_arr     = map($this->db->selectAll(
                'tsprachwerte',
                ['kSprachISO', 'kSprachsektion'],
                [$this->currentLanguageID, $section->kSprachsektion]
            ), static function ($e) {
                $e->kSprachISO     = (int)$e->kSprachISO;
                $e->kSprachsektion = (int)$e->kSprachsektion;
                $e->bSystem        = (int)$e->bSystem;

                return $e;
            });
            $values[]                = $section;
        }

        return $values;
    }

    /**
     * @return LanguageModel[]
     */
    public function gibInstallierteSprachen(): array
    {
        return LanguageModel::loadAll($this->db, [], [])->filter(function (LanguageModel $model): bool {
            return $model->getActive() === 1 && $this->mappekISO($model->getIso()) > 0;
        })->toArray();
    }

    /**
     * @return LanguageModel[]
     */
    public function gibVerfuegbareSprachen(): array
    {
        return LanguageModel::loadAll($this->db, [], [])->toArray();
    }

    /**
     * @return stdClass[]
     */
    private function gibSektionen(): array
    {
        return $this->db->getObjects('SELECT * FROM tsprachsektion ORDER BY cNAME ASC');
    }

    /**
     * @return bool
     */
    public function gueltig(): bool
    {
        return $this->currentLanguageID > 0;
    }

    /**
     * @return string
     */
    public function gibISO(): string
    {
        return $this->currentISOCode;
    }

    /**
     * @return $this
     */
    private function mappedReset(): self
    {
        unset($_SESSION['Sprache']);

        return $this;
    }

    /**
     * @param int    $sectionID
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function setzeWert(int $sectionID, $name, $value): bool
    {
        $_keys       = ['kSprachISO', 'kSprachsektion', 'cName'];
        $_values     = [$this->currentLanguageID, $sectionID, $name];
        $_upd        = new stdClass();
        $_upd->cWert = $value;

        return $this->db->update('tsprachwerte', $_keys, $_values, $_upd) >= 0;
    }

    /**
     * @param string $isoCode
     * @param int    $sectionID
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function fuegeEin(string $isoCode, int $sectionID, $name, $value): bool
    {
        $isoID = $this->mappekISO($isoCode);
        if ($isoID > 0) {
            $ins                 = new stdClass();
            $ins->kSprachISO     = $isoID;
            $ins->kSprachsektion = $sectionID;
            $ins->cName          = $name;
            $ins->cWert          = $value;
            $ins->cStandard      = $value;
            $ins->bSystem        = 0;

            return $this->db->insert('tsprachwerte', $ins) > 0;
        }

        return false;
    }

    /**
     * @param int    $sectionID
     * @param string $name
     * @return int
     */
    public function loesche(int $sectionID, string $name): int
    {
        return $this->db->delete(
            'tsprachwerte',
            ['kSprachsektion', 'cName'],
            [$sectionID, $name]
        );
    }

    /**
     * @param string $query
     * @return stdClass[]
     */
    public function suche(string $query): array
    {
        return $this->db->getObjects(
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
                'search' => '%' . $query . '%',
                'id'     => $this->currentLanguageID
            ]
        );
    }

    /**
     * @param int $type
     * @return string
     */
    private function mappedExport(int $type = 0): string
    {
        $csvData = [];
        switch ($type) {
            default:
            case 0: // Alle
                $values = $this->db->getObjects(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                            ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso',
                    ['iso' => $this->currentLanguageID]
                );
                break;

            case 1: // System
                $values = $this->db->getObjects(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                            ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso
                            AND bSystem = 1',
                    ['iso' => $this->currentLanguageID]
                );
                break;

            case 2: // Eigene
                $values = $this->db->getObjects(
                    'SELECT tsprachsektion.cName AS cSektionName, tsprachwerte.cName, 
                        tsprachwerte.cWert, tsprachwerte.bSystem
                        FROM tsprachwerte
                        LEFT JOIN tsprachsektion 
                          ON tsprachwerte.kSprachsektion = tsprachsektion.kSprachsektion
                        WHERE kSprachISO = :iso 
                            AND bSystem = 0',
                    ['iso' => $this->currentLanguageID]
                );
                break;
        }

        foreach ($values as $value) {
            if (\mb_strlen($value->cWert) === 0) {
                $value->cWert = $value->cStandard ?? null;
            }
            $csvData[] = [
                $value->cSektionName,
                $value->cName,
                $value->cWert,
                $value->bSystem
            ];
        }
        $fileName = \tempnam('../' . \PFAD_DBES_TMP, 'csv');
        $handle   = \fopen($fileName, 'wb');
        foreach ($csvData as $csv) {
            \fputcsv($handle, $csv, ';');
        }
        \fclose($handle);

        return $fileName;
    }

    /**
     * @param string $fileName
     * @param string $iso
     * @param int    $type
     * @return bool|int
     */
    private function mappedImport(string $fileName, string $iso, int $type)
    {
        $handle = \fopen($fileName, 'rb');
        if (!$handle) {
            return false;
        }

        $deleteFlag  = false;
        $updateCount = 0;
        $kSprachISO  = $this->mappekISO($iso);
        if ($kSprachISO === 0) {
            // Sprache noch nicht installiert
            $langIso       = new stdClass();
            $langIso->cISO = $iso;
            $kSprachISO    = $this->db->insert('tsprachiso', $langIso);
        }

        while (($data = \fgetcsv($handle, 4048, ';')) !== false) {
            if (\count($data) === 4) {
                // Sektion holen und ggf neu anlegen
                $cSektion = $data[0];
                $oSektion = $this->db->select('tsprachsektion', 'cName', $cSektion);
                if (isset($oSektion->kSprachsektion)) {
                    $kSprachsektion = $oSektion->kSprachsektion;
                } else {
                    // Sektion hinzufügen
                    $oSektion        = new stdClass();
                    $oSektion->cName = $cSektion;
                    $kSprachsektion  = $this->db->insert('tsprachsektion', $oSektion);
                }
                $name   = $data[1];
                $value  = $data[2];
                $system = (int)$data[3];

                switch ($type) {
                    case 0: // Neu importieren
                        // Gültige Zeile, vorhandene Variablen löschen
                        if (!$deleteFlag) {
                            $this->db->delete('tsprachwerte', 'kSprachISO', $kSprachISO);
                            $deleteFlag = true;
                        }
                        $val                 = new stdClass();
                        $val->kSprachISO     = $kSprachISO;
                        $val->kSprachsektion = $kSprachsektion;
                        $val->cName          = $data[1];
                        $val->cWert          = $data[2];
                        $val->cStandard      = $data[2];
                        $val->bSystem        = $system;
                        $this->db->insert('tsprachwerte', $val);
                        $updateCount++;
                        break;

                    case 1: // Vorhandene Variablen überschreiben
                        $this->db->queryPrepared(
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
                                'name'    => $name,
                                'val'     => $value,
                                'sys'     => $system
                            ]
                        );
                        $updateCount++;
                        break;

                    case 2: // Vorhandene Variablen beibehalten
                        $dbval = $this->db->select(
                            'tsprachwerte',
                            'kSprachISO',
                            $kSprachISO,
                            'kSprachsektion',
                            $kSprachsektion,
                            'cName',
                            $name
                        );
                        if (!$dbval) {
                            $this->db->queryPrepared(
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
                                    'name'    => $name,
                                    'val'     => $value,
                                    'sys'     => $system
                                ]
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
     * @return stdClass[]
     */
    private function mappedGetLangArray()
    {
        return $this->availableLanguages;
    }

    /**
     * @param int   $languageID
     * @param array $languages
     * @return bool
     */
    private function mappedIsShopLanguage(int $languageID, array $languages = []): bool
    {
        if ($languageID <= 0) {
            return false;
        }
        if (\count($languages) === 0) {
            $languages = $this->mappedGetAllLanguages(1);
        }

        return isset($languages[$languageID]);
    }

    /**
     * @param string $iso
     * @param int    $languageID
     * @return int|string|bool
     */
    private function mappedGetLanguageDataByType(string $iso = '', int $languageID = 0)
    {
        if (\mb_strlen($iso) > 0) {
            $data = $this->mappedGetLangIDFromIso($iso);

            return $data->kSprachISO ?? false;
        }
        if ($languageID > 0) {
            $data = $this->mappedGetIsoFromLangID($languageID);

            return $data->cISO ?? false;
        }

        return false;
    }

    /**
     * gibt alle Sprachen zurück
     *
     * @param int  $returnType
     * 0 = Normales Array
     * 1 = Gib ein Assoc mit Key = kSprache
     * 2 = Gib ein Assoc mit Key = cISO
     * @param bool $force
     * @param bool $onlyActive
     * @return LanguageModel[]
     * @throws \Exception
     * @former gibAlleSprachen()
     * @since  5.0.0
     */
    private function mappedGetAllLanguages(int $returnType = 0, bool $force = false, bool $onlyActive = false): array
    {
        $languages = Frontend::getLanguages();
        if ($force || \count($languages) === 0 || \get_class(\array_values($languages)[0]) === stdClass::class) {
            $languages = $onlyActive === true
                ? LanguageModel::loadAll($this->db, ['active'], [1])->toArray()
                : LanguageModel::loadAll($this->db, [], [])->toArray();
        }

        return match ($returnType) {
            2 => reindex($languages, static function (LanguageModel $e): string {
                return $e->getCode();
            }),
            1 => reindex($languages, static function (LanguageModel $e): int {
                return $e->getId();
            }),
            default => $languages,
        };
    }

    /**
     * @param bool     $shop
     * @param int|null $languageID - optional lang id to check against instead of session value
     * @return bool
     * @former standardspracheAktiv()
     * @since  5.0.0
     */
    public static function isDefaultLanguageActive(bool $shop = false, int $languageID = null): bool
    {
        if (Shop::$forceHost[0]['id'] > 0) {
            return false;
        }
        $languageID = $languageID ?? Shop::getLanguageID();
        if ($languageID <= 0) {
            return true;
        }
        foreach (Frontend::getLanguages() as $language) {
            if (!$shop && $language->isDefault() && $language->getId() === $languageID) {
                return true;
            }
            if ($shop && $language->isShopDefault() && $language->getId() === $languageID) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $shop
     * @return LanguageModel
     * @former gibStandardsprache()
     * @since  5.0.0
     */
    private function mappedGetDefaultLanguage(bool $shop = true): LanguageModel
    {
        foreach (Frontend::getLanguages() as $language) {
            if (!$shop && $language->isDefault()) {
                return $language;
            }
            if ($shop && $language->isShopDefault()) {
                return $language;
            }
        }

        $cacheID = 'shp_lng_' . (($shop === true) ? 'b' : '');
        if (($lang = $this->cache->get($cacheID)) !== false && $lang !== null) {
            return $lang;
        }
        $lang = LanguageModel::loadByAttributes($shop ? ['shopDefault' => 'Y'] : ['default' => 'Y'], $this->db);
        $this->cache->set($cacheID, $lang, [\CACHING_GROUP_LANGUAGE]);

        return $lang;
    }

    /**
     * @former setzeSpracheUndWaehrungLink()
     * @since  5.0.0
     */
    public function generateLanguageAndCurrencyLinks(): void
    {
        global $AktuellerArtikel;
        $linkID        = Shop::$kLink;
        $pageID        = Shop::$kSeite;
        $shopURL       = Shop::getURL() . '/';
        $linkService   = Shop::Container()->getLinkService();
        $productFilter = Shop::getProductFilter();
        $pageType      = Shop::getPageType();
        $state         = Shop::getState();
        if ($pageID !== null && $pageID > 0 && $pageType !== \PAGE_ARTIKELLISTE) {
            $linkID = $pageID;
        }
        $mapper = new PageTypeToLinkType();
        $mapped = $mapper->map($pageType);
        try {
            $specialPage = $mapped > 0 ? $linkService->getSpecialPage($mapped) : null;
        } catch (SpecialPageNotFoundException) {
            $specialPage = null;
        }
        $page          = $linkID > 0 ? $linkService->getPageLink($linkID) : null;
        $languages     = Frontend::getLanguages();
        $currencies    = Frontend::getCurrencies();
        $currentLangID = Shop::getLanguageID();
        $currentLocale = null;
        if (\count($languages) > 1) {
            foreach ($languages as $lang) {
                /** @var Artikel $AktuellerArtikel */
                $langID  = $lang->getId();
                $langISO = $lang->getIso();
                if ($currentLangID === $langID) {
                    $currentLocale = $lang->getIso639();
                }
                if ($state->currentRouteName !== null && $state->routeData !== null) {
                    $url = Shop::getRouter()->getURLByType(
                        $state->currentRouteName,
                        \array_merge($state->routeData, ['lang' => $lang->getIso639()]),
                        true,
                        true
                    );
                    $lang->setUrl($url);
                } elseif (isset($AktuellerArtikel->cSprachURL_arr[$langISO])) {
                    $lang->setUrl($AktuellerArtikel->cSprachURL_arr[$langISO]);
                } elseif ($page !== null) {
                    $url = $page->getURL($langID);
                    if (\str_contains($url, '/?s=')) {
                        $lang->setUrl(\rtrim($shopURL, '/') . $url);
                    } else {
                        $lang->setUrl($url);
                    }
                } elseif ($specialPage !== null) {
                    if ($pageType === \PAGE_STARTSEITE) {
                        $url = $shopURL . '?lang=' . $langISO;
                    } elseif ($specialPage->getFileName() !== '') {
                        if (Shop::$kNews > 0) {
                            $newsItem = new Item($this->db);
                            $newsItem->load(Shop::$kNews);
                            $url = $newsItem->getURL($langID);
                        } elseif (Shop::$kNewsKategorie > 0) {
                            $newsCategory = new Category($this->db);
                            $newsCategory->load(Shop::$kNewsKategorie);
                            $url = $newsCategory->getURL($langID);
                        } else {
                            $url = $linkService->getStaticRoute($specialPage->getFileName(), false, false, $langISO);
                            // check if there is a SEO link for the given file
                            if ($url === $specialPage->getFileName()) {
                                // no SEO link - fall back to php file with GET param
                                $url = $shopURL . $specialPage->getFileName() . '?lang=' . $langISO;
                            } else { //there is a SEO link - make it a full URL
                                $url = $linkService->getStaticRoute($specialPage->getFileName(), true, false, $langISO);
                            }
                        }
                    } else {
                        $url = $specialPage->getURL($langID);
                    }
                    $lang->setUrl($url);
                    \executeHook(\HOOK_TOOLSGLOBAL_INC_SWITCH_SETZESPRACHEUNDWAEHRUNG_SPRACHE);
                } else {
                    $config           = $productFilter->getFilterConfig();
                    $originalLanguage = $config->getLanguageID();
                    $originalBase     = $config->getBaseURL();
                    $config->setLanguageID($langID);
                    $config->setBaseURL(Shop::getURL(false, $langID) . '/');
                    $url = $productFilter->getFilterURL()->getURL();
                    // reset
                    $config->setLanguageID($originalLanguage);
                    $config->setBaseURL($originalBase);
                    if ($productFilter->getPage() > 1) {
                        if (\str_contains($url, '?') || \str_contains($url, 'navi.php')) {
                            $url .= '&amp;seite=' . $productFilter->getPage();
                        } else {
                            $url .= \SEP_SEITE . $productFilter->getPage();
                        }
                    }
                    $lang->setUrl($url);
                }
            }
        }
        if (\count($currencies) > 1) {
            $currentCurrencyCode = Frontend::getCurrency()->getID();
            foreach ($currencies as $currency) {
                $code       = $currency->getCode();
                $additional = $currency->getID() === $currentCurrencyCode
                    ? []
                    : ['currency' => $code];
                if ($currentLocale !== null && $state->currentRouteName !== null && $state->routeData !== null) {
                    $url = Shop::getRouter()->getURLByType(
                        $state->currentRouteName,
                        \array_merge($state->routeData, ['lang' => $currentLocale, 'currency' => $code]),
                        true,
                        true
                    );
                    $currency->setURL($url);
                    $currency->setURLFull($url);
                    continue;
                }
                if (isset($AktuellerArtikel)) {
                    $original = $AktuellerArtikel->getURLs();
                    $AktuellerArtikel->createBySlug($AktuellerArtikel->kArtikel, $additional);
                    $url = $AktuellerArtikel->getURL($currentLangID);
                    $currency->setURL($url);
                    $currency->setURLFull($url);
                    $AktuellerArtikel->setURLs($original);
                    continue;
                }
                if ($page !== null) {
                    $original = $page->getURLs();
                    $page->createBySlug($page->getID(), $additional);
                    $url = $page->getURL($currentLangID);
                    $currency->setURL($url);
                    $currency->setURLFull($url);
                    $page->setURLs($original);
                    continue;
                }
                if ($specialPage !== null) {
                    $original = $specialPage->getURLs();
                    $specialPage->createBySlug($specialPage->getID(), $additional);
                    $url = $specialPage->getURL($currentLangID);
                    $currency->setURL($url);
                    $currency->setURLFull($url);
                    $specialPage->setURLs($original);
                    continue;
                }
                $url = $productFilter->getFilterURL()->getURL(null, false, $additional);
                if ($currency->getID() !== $currentCurrencyCode && !\str_contains($url, '/' . $code . '/')) {
                    $url .= (!\str_contains($url, '?') ? '?' : '&') . 'curr=' . $code;
                }
                $currency->setURL($url);
                $currency->setURLFull(!\str_contains($url, Shop::getURL())
                    ? ($shopURL . $url)
                    : $url);
            }
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_SETZESPRACHEUNDWAEHRUNG_WAEHRUNG, [
            'oNaviFilter'       => &$productFilter,
            'oZusatzFilter'     => null,
            'cSprachURL'        => [],
            'oAktuellerArtikel' => &$AktuellerArtikel,
            'kSeite'            => &$pageID,
            'kLink'             => &$linkID,
            'AktuelleSeite'     => &Shop::$AktuelleSeite
        ]);
    }

    /**
     * @param string $iso
     * @return string
     * @former ISO2land()
     * @since  5.0.0
     */
    private function mappedGetCountryCodeByCountryName(string $iso): string
    {
        if (\mb_strlen($iso) > 2) {
            return $iso;
        }
        $country = Shop::Container()->getCountryService()->getCountry($iso);

        return $country !== null ? $country->getName() : $iso;
    }

    /**
     * @param string $country
     * @return string
     * @former landISO()
     * @since  5.0.0
     */
    private function mappedGetIsoCodeByCountryName(string $country): string
    {
        return Shop::Container()->getCountryService()->getIsoByCountryName($country) ?? 'noISO';
    }

    /**
     * @param int $langID
     * @return LanguageModel
     * @throws \Exception
     */
    public function getLanguageByID(int $langID): LanguageModel
    {
        foreach (Frontend::getLanguages() as $language) {
            if ($language->getId() === $langID) {
                return $language;
            }
        }

        return LanguageModel::loadByAttributes(['id' => $langID], $this->db);
    }
}
