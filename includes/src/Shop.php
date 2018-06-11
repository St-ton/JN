<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Services\Container;
use DB\Services as DbService;

use JTL\ProcessingHandler\NiceDBHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use \Services\JTL\Validation\ValidationServiceInterface;
use \Services\JTL\Validation\ValidationService;
use Services\JTL\Validation\RuleSet;
use Filter\ProductFilter;

/**
 * Class Shop
 * @method static \Cache\JTLCacheInterface Cache()
 * @method static Sprache Lang()
 * @method static JTLSmarty Smarty(bool $fast_init = false, bool $isAdmin = false)
 * @method static Media Media()
 * @method static EventDispatcher Event()
 * @method static bool has(string $key)
 * @method static Shop set(string $key, mixed $value)
 * @method static null|mixed get($key)
 */
final class Shop
{
    /**
     * @var int
     */
    public static $kSprache;

    /**
     * @var string
     */
    public static $cISO;

    /**
     * @var int
     */
    public static $kKonfigPos;

    /**
     * @var int
     */
    public static $kKategorie;

    /**
     * @var int
     */
    public static $kArtikel;

    /**
     * @var int
     */
    public static $kVariKindArtikel;

    /**
     * @var int
     */
    public static $kSeite;

    /**
     * @var int
     */
    public static $kLink;

    /**
     * @var int
     */
    public static $kHersteller;

    /**
     * @var int
     */
    public static $kSuchanfrage;

    /**
     * @var int
     */
    public static $kMerkmalWert;

    /**
     * @var int
     */
    public static $kTag;

    /**
     * @var int
     */
    public static $kSuchspecial;

    /**
     * @var int
     */
    public static $kNews;

    /**
     * @var int
     */
    public static $kNewsMonatsUebersicht;

    /**
     * @var int
     */
    public static $kNewsKategorie;

    /**
     * @var int
     */
    public static $kUmfrage;

    /**
     * @var int
     */
    public static $nBewertungSterneFilter;

    /**
     * @var string
     */
    public static $cPreisspannenFilter;

    /**
     * @var int
     */
    public static $kHerstellerFilter;

    /**
     * @var int
     */
    public static $kKategorieFilter;

    /**
     * @var int
     */
    public static $kSuchspecialFilter;

    /**
     * @var array
     */
    public static $searchSpecialFilterIDs;

    /**
     * @var int
     */
    public static $kSuchFilter;

    /**
     * @var int
     */
    public static $nDarstellung;

    /**
     * @var int
     */
    public static $nSortierung;

    /**
     * @var int
     */
    public static $nSort;

    /**
     * @var int
     */
    public static $show;

    /**
     * @var int
     */
    public static $vergleichsliste;

    /**
     * @var bool
     */
    public static $bFileNotFound;

    /**
     * @var string
     */
    public static $cCanonicalURL;

    /**
     * @var bool
     */
    public static $is404;

    /**
     * @var array
     */
    public static $MerkmalFilter;

    /**
     * @var array
     */
    public static $SuchFilter;

    /**
     * @var array
     */
    public static $TagFilter;

    /**
     * @var int
     */
    public static $kWunschliste;

    /**
     * @var bool
     */
    public static $bSEOMerkmalNotFound;

    /**
     * @var bool
     */
    public static $bKatFilterNotFound;

    /**
     * @var bool
     */
    public static $bHerstellerFilterNotFound;

    /**
     * @var bool
     * @deprecated since 5.0
     */
    public static $isSeoMainword = false;

    /**
     * @var null|Shop
     */
    private static $_instance;

    /**
     * @var ProductFilter
     */
    public static $productFilter;

    /**
     * @var string
     */
    public static $fileName;

    /**
     * @var string
     */
    public static $AktuelleSeite;

    /**
     * @var int
     */
    public static $pageType;

    /**
     * @var bool
     */
    public static $directEntry = true;

    /**
     * @var bool
     */
    public static $bSeo = false;

    /**
     * @var bool
     */
    public static $isInitialized = false;

    /**
     * @var int
     */
    public static $nArtikelProSeite;

    /**
     * @var string
     */
    public static $cSuche;

    /**
     * @var
     */
    public static $seite;

    /**
     * @var int
     */
    public static $nSterne;

    /**
     * @var int
     */
    public static $nNewsKat;

    /**
     * @var string
     */
    public static $cDatum;

    /**
     * @var int
     */
    public static $nAnzahl;

    /**
     * @var string
     */
    public static $uri;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var bool
     */
    private static $_logged;

    /**
     * @var array
     */
    private static $url = [];

    /**
     * @var Shopsetting
     */
    private static $_settings;

    /**
     * @var \Filter\FilterInterface[]
     */
    public static $customFilters = [];

    /**
     * @var \Services\DefaultServicesInterface
     */
    private static $container;

    /**
     * @var string
     */
    private static $imageBaseURL;

    /**
     * @var array
     */
    private static $mapping = [
        'DB'     => '_DB',
        'Cache'  => '_Cache',
        'Lang'   => '_Language',
        'Smarty' => '_Smarty',
        'Media'  => '_Media',
        'Event'  => '_Event',
        'has'    => '_has',
        'set'    => '_set',
        'get'    => '_get'
    ];

    /**
     *
     */
    private function __construct()
    {
        self::$_instance = $this;
        self::$_settings = Shopsetting::getInstance();
    }

    /**
     * @return Shop
     */
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     * object wrapper - this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper - this allows to call Shop::Container()->getDB()->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param string $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return $this->registry[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function _set($key, $value): self
    {
        $this->registry[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function _has($key): bool
    {
        return isset($this->registry[$key]);
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
     * @param string $url
     */
    public static function setImageBaseURL(string $url)
    {
        self::$imageBaseURL = rtrim($url, '/') . '/';
    }

    /**
     * @return string
     */
    public static function getImageBaseURL(): string
    {
        if (self::$imageBaseURL === null) {
            self::setImageBaseURL(defined('IMAGE_BASE_URL') ? IMAGE_BASE_URL : self::getURL());
        }

        return self::$imageBaseURL;
    }

    /**
     * get remote service instance
     *
     * @return \Network\JTLApi
     * @deprecated since Shop 5.0 use Shop::Container()->get(JTLApi::class) instead
     * @throws
     */
    public function RS(): \Network\JTLApi
    {
        return self::Container()->get(\Network\JTLApi::class);
    }

    /**
     * get session instance
     *
     * @return Session
     * @throws Exception
     */
    public function Session(): Session
    {
        return Session::getInstance();
    }

    /**
     * get db adapter instance
     *
     * @return \DB\DbInterface
     * @deprecated since Shop 5 use Shop::Container()->getDB() instead
     */
    public function _DB(): \DB\DbInterface
    {
        return self::Container()->getDB();
    }

    /**
     * @return \DB\DbInterface
     * @deprecated since Shop 5 use Shop::Container()->getDB() instead
     */
    public static function DB(): \DB\DbInterface
    {
        return self::Container()->getDB();
    }

    /**
     * get language instance
     *
     * @return Sprache
     */
    public function _Language(): Sprache
    {
        return Sprache::getInstance();
    }

    /**
     * get config
     *
     * @return Shopsetting
     */
    public function Config(): Shopsetting
    {
        return self::$_settings;
    }

    /**
     * get garbage collector
     *
     * @return DbService\GcServiceInterface
     * @deprecated since 5.0 -> use Shop::Container()->getGc() instead
     */
    public function Gc(): DbService\GcServiceInterface
    {
        return static::Container()->getDBServiceGC();
    }

    /**
     * get logger
     *
     * @return Jtllog
     */
    public function Logger(): Jtllog
    {
        return new Jtllog();
    }

    /**
     * @return PHPSettingsHelper
     */
    public function PHPSettingsHelper(): PHPSettingsHelper
    {
        return PHPSettingsHelper::getInstance();
    }

    /**
     * get cache instance
     *
     * @return \Cache\JTLCacheInterface
     * @deprecated since shop 5.0
     */
    public function _Cache(): \Cache\JTLCacheInterface
    {
        return self::Container()->getCache();
    }

    /**
     * get template engine instance
     *
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty
     */
    public function _Smarty($fast_init = false, $isAdmin = false): JTLSmarty
    {
        return JTLSmarty::getInstance($fast_init, $isAdmin);
    }

    /**
     * get media instance
     *
     * @return Media
     */
    public function _Media(): Media
    {
        return Media::getInstance();
    }

    /**
     * get event instance
     *
     * @return EventDispatcher
     */
    public function _Event(): EventDispatcher
    {
        return EventDispatcher::getInstance();
    }

    /**
     * @param string       $eventName
     * @param array|object $arguments
     */
    public static function fire($eventName, $arguments = [])
    {
        self::Event()->fire($eventName, $arguments);
    }

    /**
     * quick&dirty debugging
     *
     * @param mixed       $var - the variable to debug
     * @param bool        $die - set true to die() afterwards
     * @param null|string $beforeString - a prefix string
     * @param int         $backtrace - backtrace depth
     */
    public static function dbg($var, $die = false, $beforeString = null, $backtrace = 0)
    {
        if ($beforeString !== null) {
            echo $beforeString . '<br />';
        }
        echo '<pre>';
        var_dump($var);
        if ($backtrace > 0) {
            echo '<br />Backtrace:<br />';
            var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace));
        }
        echo '</pre>';
        if ($die === true) {
            die();
        }
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return int|string
     */
    public static function getLanguage($iso = false)
    {
        return $iso === false ? (int)self::$kSprache : self::$cISO;
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return int
     */
    public static function getLanguageID(): int
    {
        return (int)self::$kSprache;
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return string
     */
    public static function getLanguageCode()
    {
        return self::$cISO;
    }

    /**
     * set language/language ISO
     *
     * @param int    $languageID
     * @param string $cISO
     */
    public static function setLanguage($languageID, $cISO = null)
    {
        self::$kSprache = (int)$languageID;
        if ($cISO !== null) {
            self::$cISO = $cISO;
        }
    }

    /**
     * @param array $config
     * @return array
     */
    public static function getConfig($config)
    {
        return self::getSettings($config);
    }

    /**
     * @param array|int $config
     * @return array
     */
    public static function getSettings($config)
    {
        return (self::$_settings ?? Shopsetting::getInstance())->getSettings($config);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public static function getSettingValue(int $section, $option)
    {
        return self::getConfigValue($section, $option);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public static function getConfigValue(int $section, $option)
    {
        return (self::$_settings ?? Shopsetting::getInstance())->getValue($section, $option);
    }

    /**
     * Load plugin event driven system
     */
    public static function bootstrap()
    {
        $cacheID = 'plgnbtsrp';
        if (($plugins = self::Cache()->get($cacheID)) === false) {
            $plugins = self::Container()->getDB()->query(
                'SELECT kPlugin 
                  FROM tplugin 
                  WHERE nStatus = 2 
                    AND bBootstrap = 1 
                  ORDER BY nPrio ASC', \DB\ReturnType::ARRAY_OF_OBJECTS) ?: [];
            self::Cache()->set($cacheID, $plugins, [CACHING_GROUP_PLUGIN]);
        }

        foreach ($plugins as $plugin) {
            if (($p = Plugin::bootstrapper($plugin->kPlugin)) !== null) {
                $p->boot(EventDispatcher::getInstance());
            }
        }
    }

    /**
     * @return ProductFilter
     */
    public static function run(): ProductFilter
    {
        self::$kKonfigPos             = verifyGPCDataInteger('ek');
        self::$kKategorie             = verifyGPCDataInteger('k');
        self::$kArtikel               = verifyGPCDataInteger('a');
        self::$kVariKindArtikel       = verifyGPCDataInteger('a2');
        self::$kSeite                 = verifyGPCDataInteger('s');
        self::$kLink                  = verifyGPCDataInteger('s');
        self::$kHersteller            = verifyGPCDataInteger('h');
        self::$kSuchanfrage           = verifyGPCDataInteger('l');
        self::$kMerkmalWert           = verifyGPCDataInteger('m');
        self::$kTag                   = verifyGPCDataInteger('t');
        self::$kSuchspecial           = verifyGPCDataInteger('q');
        self::$kNews                  = verifyGPCDataInteger('n');
        self::$kNewsMonatsUebersicht  = verifyGPCDataInteger('nm');
        self::$kNewsKategorie         = verifyGPCDataInteger('nk');
        self::$kUmfrage               = verifyGPCDataInteger('u');
        self::$nBewertungSterneFilter = verifyGPCDataInteger('bf');
        self::$cPreisspannenFilter    = verifyGPDataString('pf');
        self::$kHerstellerFilter      = verifyGPCDataInteger('hf');
        self::$kKategorieFilter       = verifyGPCDataInteger('kf');
        self::$searchSpecialFilterIDs = verifyGPDataIntegerArray('qf');
        self::$kSuchFilter            = verifyGPCDataInteger('sf');
        self::$kSuchspecialFilter     = count(self::$searchSpecialFilterIDs) > 0
            ? self::$searchSpecialFilterIDs[0]
            : 0;

        self::$nDarstellung = verifyGPCDataInteger('ed');
        self::$nSortierung  = verifyGPCDataInteger('sortierreihenfolge');
        self::$nSort        = verifyGPCDataInteger('Sortierung');

        self::$show            = verifyGPCDataInteger('show');
        self::$vergleichsliste = verifyGPCDataInteger('vla');
        self::$bFileNotFound   = false;
        self::$cCanonicalURL   = '';
        self::$is404           = false;

        self::$nSterne = verifyGPCDataInteger('nSterne');

        self::$kWunschliste = checkeWunschlisteParameter();

        self::$nNewsKat = verifyGPCDataInteger('nNewsKat');
        self::$cDatum   = verifyGPDataString('cDatum');
        self::$nAnzahl  = verifyGPCDataInteger('nAnzahl');

        if (strlen(verifyGPDataString('qs')) > 0) {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('qs'));
        } elseif (strlen(verifyGPDataString('suchausdruck')) > 0) {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('suchausdruck'));
        } else {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('suche'));
        }
        // avoid redirect loops for surveys that require logged in customers
        if (self::$kUmfrage > 0 && empty($_SESSION['Kunde']->kKunde) && verifyGPCDataInteger('r') !== 0) {
            self::$kUmfrage = 0;
        }

        self::$nArtikelProSeite = verifyGPCDataInteger('af');
        if (self::$nArtikelProSeite !== 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;
        $redirect            = verifyGPDataString('r');
        if (self::$kArtikel > 0) {
            if (!empty($redirect)
                && (self::$kNews > 0 // get param "n" was used a article amount
                    || (isset($_GET['n']) && (float)$_GET['n'] > 0)) // article amount was a float >0 and <1
            ) {
                // GET param "n" is often misused as "amount of article"
                self::$kNews = 0;
                if ((int)$redirect === R_LOGIN_WUNSCHLISTE) {
                    // login redirect on wishlist add when not logged in uses get param "n" as amount
                    // and "a" for the article ID - but we want to go to the login page, not to the article page
                    self::$kArtikel = 0;
                }
            } elseif (((int)$redirect === R_LOGIN_BEWERTUNG || (int)$redirect === R_LOGIN_TAG)
                && empty($_SESSION['Kunde']->kKunde)
            ) {
                // avoid redirect to article page for ratings that require logged in customers
                self::$kArtikel = 0;
            }
        }
        $_SESSION['cTemplate'] = Template::$cTemplate;

        if (self::$kWunschliste === 0
            && verifyGPDataString('error') === ''
            && strlen(verifyGPDataString('wlid')) > 0
        ) {
            header(
                'Location: ' . LinkHelper::getInstance()->getStaticRoute('wunschliste.php') .
                '?wlid=' . StringHandler::filterXSS(verifyGPDataString('wlid')) . '&error=1',
                true,
                303
            );
            exit();
        }
        if ((self::$kArtikel > 0 || self::$kKategorie > 0) && !Session::CustomerGroup()->mayViewCategories()) {
            // falls Artikel/Kategorien nicht gesehen werden duerfen -> login
            header('Location: ' . LinkHelper::getInstance()->getStaticRoute('jtl.php') . '?li=1', true, 303);
            exit;
        }
        self::$productFilter = new ProductFilter(self::Lang()->getLangArray(), self::$kSprache);
        self::seoCheck();
        self::setImageBaseURL(defined('IMAGE_BASE_URL') ? IMAGE_BASE_URL : self::getURL());
        self::Event()->fire('shop.run');

        self::$productFilter->initStates(self::getParameters());

        return self::$productFilter;
    }

    /**
     * get page parameters
     *
     * @return array
     */
    public static function getParameters(): array
    {
        if (self::$kKategorie > 0 && !Kategorie::isVisible(self::$kKategorie, Session::CustomerGroup()->getID())) {
            self::$kKategorie = 0;
        }
        // check variation combination
        if (ArtikelHelper::isVariChild(self::$kArtikel)) {
            self::$kVariKindArtikel = self::$kArtikel;
            self::$kArtikel         = ArtikelHelper::getParent(self::$kArtikel);
        }

        return [
            'kKategorie'             => self::$kKategorie,
            'kKonfigPos'             => self::$kKonfigPos,
            'kHersteller'            => self::$kHersteller,
            'kArtikel'               => self::$kArtikel,
            'kVariKindArtikel'       => self::$kVariKindArtikel,
            'kSeite'                 => self::$kSeite,
            'kLink'                  => self::$kSeite > 0 ? self::$kSeite : self::$kLink,
            'kSuchanfrage'           => self::$kSuchanfrage,
            'kMerkmalWert'           => self::$kMerkmalWert,
            'kTag'                   => self::$kTag,
            'kSuchspecial'           => self::$kSuchspecial,
            'kNews'                  => self::$kNews,
            'kNewsMonatsUebersicht'  => self::$kNewsMonatsUebersicht,
            'kNewsKategorie'         => self::$kNewsKategorie,
            'kUmfrage'               => self::$kUmfrage,
            'kKategorieFilter'       => self::$kKategorieFilter,
            'kHerstellerFilter'      => self::$kHerstellerFilter,
            'nBewertungSterneFilter' => self::$nBewertungSterneFilter,
            'cPreisspannenFilter'    => self::$cPreisspannenFilter,
            'kSuchspecialFilter'     => self::$kSuchspecialFilter,
            'nSortierung'            => self::$nSortierung,
            'nSort'                  => self::$nSort,
            'MerkmalFilter_arr'      => self::$MerkmalFilter,
            'TagFilter_arr'          => self::$TagFilter ?? [],
            'SuchFilter_arr'         => self::$SuchFilter ?? [],
            'nArtikelProSeite'       => self::$nArtikelProSeite,
            'cSuche'                 => self::$cSuche,
            'seite'                  => self::$seite,
            'show'                   => self::$show,
            'is404'                  => self::$is404,
            'kSuchFilter'            => self::$kSuchFilter,
            'kWunschliste'           => self::$kWunschliste,
            'MerkmalFilter'          => self::$MerkmalFilter,
            'SuchFilter'             => self::$SuchFilter,
            'TagFilter'              => self::$TagFilter,
            'vergleichsliste'        => self::$vergleichsliste,
            'nDarstellung'           => self::$nDarstellung,
            'isSeoMainword'          => false,
            'nNewsKat'               => self::$nNewsKat,
            'cDatum'                 => self::$cDatum,
            'nAnzahl'                => self::$nAnzahl,
            'nSterne'                => self::$nSterne,
            'customFilters'          => self::$customFilters,
            'searchSpecialFilters'   => self::$searchSpecialFilterIDs
        ];
    }

    /**
     * check for seo url
     */
    public static function seoCheck()
    {
        $uri                             = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'];
        self::$uri                       = $uri;
        self::$bSEOMerkmalNotFound       = false;
        self::$bKatFilterNotFound        = false;
        self::$bHerstellerFilterNotFound = false;
        executeHook(HOOK_SEOCHECK_ANFANG, ['uri' => &$uri]);
        $seite        = 0;
        $manufSeo     = [];
        $katseo       = '';
        $customSeo    = [];
        $xShopurl_arr = parse_url(self::getURL());
        $xBaseurl_arr = parse_url($uri);
        $seo          = isset($xBaseurl_arr['path'])
            ? substr($xBaseurl_arr['path'], isset($xShopurl_arr['path'])
                ? (strlen($xShopurl_arr['path']) + 1)
                : 1)
            : false;
        // Fremdparameter
        $seo = extFremdeParameter($seo);
        if ($seo) {
            foreach (self::$productFilter->getCustomFilters() as $customFilter) {
                $seoParam = $customFilter->getUrlParamSEO();
                if (empty($seoParam)) {
                    continue;
                }
                $customFilterArr = explode($seoParam, $seo);
                if (count($customFilterArr) > 1) {
                    list($seo, $customFilterSeo) = $customFilterArr;
                    if (strpos($customFilterSeo, SEP_HST) !== false) {
                        $arr             = explode(SEP_HST, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo             .= SEP_HST . $arr[1];
                    }
                    if (($idx = strpos($customFilterSeo, SEP_KAT)) !== false
                        && $idx !== strpos($customFilterSeo, SEP_HST)
                    ) {
                        $oHersteller_arr = explode(SEP_KAT, $customFilterSeo);
                        $customFilterSeo = $oHersteller_arr[0];
                        $seo             .= SEP_KAT . $oHersteller_arr[1];
                    }
                    if (strpos($customFilterSeo, SEP_MERKMAL) !== false) {
                        $arr             = explode(SEP_MERKMAL, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo             .= SEP_MERKMAL . $arr[1];
                    }
                    if (strpos($customFilterSeo, SEP_MM_MMW) !== false) {
                        $arr             = explode(SEP_MM_MMW, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo             .= SEP_MM_MMW . $arr[1];
                    }
                    if (strpos($customFilterSeo, SEP_SEITE) !== false) {
                        $arr             = explode(SEP_SEITE, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo             .= SEP_SEITE . $arr[1];
                    }

                    $customSeo[$customFilter->getClassName()] = [
                        'cSeo'  => $customFilterSeo,
                        'table' => $customFilter->getTableName()
                    ];
                }
            }
            // change Opera Fix
            if (substr($seo, strlen($seo) - 1, 1) === '?') {
                $seo = substr($seo, 0, -1);
            }
            $nMatch = preg_match('/[^_](' . SEP_SEITE . '([0-9]+))/', $seo, $cMatch_arr, PREG_OFFSET_CAPTURE);
            if ($nMatch === 1) {
                $seite = (int)$cMatch_arr[2][0];
                $seo   = substr($seo, 0, $cMatch_arr[1][1]);
            }
            // duplicate content work around
            if ($seite === 1 && strlen($seo) > 0) {
                http_response_code(301);
                header('Location: ' . self::getURL() . '/' . $seo);
                exit();
            }
            $cSEOMerkmal_arr = explode(SEP_MERKMAL, $seo);
            $seo             = $cSEOMerkmal_arr[0];
            foreach ($cSEOMerkmal_arr as $i => &$merkmal) {
                if ($i === 0) {
                    continue;
                }
                if (($idx = strpos($merkmal, SEP_KAT)) !== false && $idx !== strpos($merkmal, SEP_HST)) {
                    $arr     = explode(SEP_KAT, $merkmal);
                    $merkmal = $arr[0];
                    $seo     .= SEP_KAT . $arr[1];
                }
                if (strpos($merkmal, SEP_HST) !== false) {
                    $arr     = explode(SEP_HST, $merkmal);
                    $merkmal = $arr[0];
                    $seo     .= SEP_HST . $arr[1];
                }
                if (strpos($merkmal, SEP_MM_MMW) !== false) {
                    $arr     = explode(SEP_MM_MMW, $merkmal);
                    $merkmal = $arr[0];
                    $seo     .= SEP_MM_MMW . $arr[1];
                }
                if (strpos($merkmal, SEP_SEITE) !== false) {
                    $arr     = explode(SEP_SEITE, $merkmal);
                    $merkmal = $arr[0];
                    $seo     .= SEP_SEITE . $arr[1];
                }
            }
            unset($merkmal);
            $oHersteller_arr = explode(SEP_HST, $seo);
            if (is_array($oHersteller_arr) && count($oHersteller_arr) > 1) {
                foreach ($oHersteller_arr as $i => $manufacturer) {
                    if ($i === 0) {
                        $seo = $manufacturer;
                    } else {
                        $manufSeo[] = $manufacturer;
                    }
                }
                foreach ($manufSeo as $i => $hstseo) {
                    if (($idx = strpos($hstseo, SEP_KAT)) !== false && $idx !== strpos($hstseo, SEP_HST)) {
                        $oHersteller_arr[] = explode(SEP_KAT, $hstseo);
                        $manufSeo[$i]      = $oHersteller_arr[0];
                        $seo               .= SEP_KAT . $oHersteller_arr[1];
                    }
                    if (strpos($hstseo, SEP_MERKMAL) !== false) {
                        $arr          = explode(SEP_MERKMAL, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo          .= SEP_MERKMAL . $arr[1];
                    }
                    if (strpos($hstseo, SEP_MM_MMW) !== false) {
                        $arr          = explode(SEP_MM_MMW, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo          .= SEP_MM_MMW . $arr[1];
                    }
                    if (strpos($hstseo, SEP_SEITE) !== false) {
                        $arr          = explode(SEP_SEITE, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo          .= SEP_SEITE . $arr[1];
                    }
                }
            } else {
                $seo = $oHersteller_arr[0];
            }
            $oKategorie_arr = explode(SEP_KAT, $seo);
            if (is_array($oKategorie_arr) && count($oKategorie_arr) > 1) {
                list($seo, $katseo) = $oKategorie_arr;
                if (strpos($katseo, SEP_HST) !== false) {
                    $arr    = explode(SEP_HST, $katseo);
                    $katseo = $arr[0];
                    $seo    .= SEP_HST . $arr[1];
                }
                if (strpos($katseo, SEP_MERKMAL) !== false) {
                    $arr    = explode(SEP_MERKMAL, $katseo);
                    $katseo = $arr[0];
                    $seo    .= SEP_MERKMAL . $arr[1];
                }
                if (strpos($katseo, SEP_MM_MMW) !== false) {
                    $arr    = explode(SEP_MM_MMW, $katseo);
                    $katseo = $arr[0];
                    $seo    .= SEP_MM_MMW . $arr[1];
                }
                if (strpos($katseo, SEP_SEITE) !== false) {
                    $arr    = explode(SEP_SEITE, $katseo);
                    $katseo = $arr[0];
                    $seo    .= SEP_SEITE . $arr[1];
                }
            } else {
                $seo = $oKategorie_arr[0];
            }
            if ($seite > 0) {
                $_GET['seite'] = $seite;
                self::$kSeite  = $seite;
            }
            // split attribute/attribute value
            $oMerkmal_arr = explode(SEP_MM_MMW, $seo);
            if (is_array($oMerkmal_arr) && count($oMerkmal_arr) > 1) {
                $seo = $oMerkmal_arr[1];
                //$mmseo = $oMerkmal_arr[0];
            }
            // custom filter
            foreach ($customSeo as $className => $data) {
                $oSeo = self::Container()->getDB()->select($data['table'], 'cSeo', $data['cSeo']);
                if (isset($oSeo->filterval)) {
                    self::$customFilters[$className] = (int)$oSeo->filterval;
                } else {
                    self::$bKatFilterNotFound = true;
                }
                if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                    self::updateLanguage($oSeo->kSprache);
                }
            }
            // category filter
            if (strlen($katseo) > 0) {
                $oSeo = self::Container()->getDB()->select('tseo', 'cKey', 'kKategorie', 'cSeo', $katseo);
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $katseo) === 0) {
                    self::$kKategorieFilter = (int)$oSeo->kKey;
                } else {
                    self::$bKatFilterNotFound = true;
                }
            }
            // manufacturer filter
            if (($seoCount = count($manufSeo)) > 0) {
                if ($seoCount === 1) {
                    $oSeo = self::Container()->getDB()->selectAll('tseo', ['cKey', 'cSeo'],
                        ['kHersteller', $manufSeo[0]], 'kKey');
                } else {
                    $bindValues = [];
                    // PDO::bindValue() is 1-based
                    foreach ($manufSeo as $i => $t) {
                        $bindValues[$i + 1] = $t;
                    }
                    $oSeo = self::Container()->getDB()->queryPrepared(
                        "SELECT kKey 
                            FROM tseo 
                            WHERE cKey = 'kHersteller' 
                            AND cSeo IN (" . implode(',', array_fill(0, $seoCount, '?')) . ")",
                        $bindValues,
                        2);
                }
                $results = count($oSeo);
                if ($results === 1) {
                    self::$kHerstellerFilter = (int)$oSeo[0]->kKey;
                } elseif ($results === 0) {
                    self::$bHerstellerFilterNotFound = true;
                } else {
                    self::$kHerstellerFilter = array_map(function ($e) {
                        return (int)$e->kKey;
                    }, $oSeo);
                }
            }
            // attribute filter
            if (count($cSEOMerkmal_arr) > 1) {
                if (!isset($_GET['mf'])) {
                    $_GET['mf'] = [];
                } elseif (!is_array($_GET['mf'])) {
                    $_GET['mf'] = [(int)$_GET['mf']];
                }
                self::$bSEOMerkmalNotFound = false;
                foreach ($cSEOMerkmal_arr as $i => $cSEOMerkmal) {
                    if ($i > 0 && strlen($cSEOMerkmal) > 0) {
                        $oSeo = self::Container()->getDB()->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo',
                            $cSEOMerkmal);
                        if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $cSEOMerkmal) === 0) {
                            //haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                            $_GET['mf'][] = (int)$oSeo->kKey;
                        } else {
                            self::$bSEOMerkmalNotFound = true;
                        }
                    }
                }
            }
            $oSeo = self::Container()->getDB()->select('tseo', 'cSeo', $seo);
            // EXPERIMENTAL_MULTILANG_SHOP
            if (isset($oSeo->kSprache) && self::$kSprache !== $oSeo->kSprache &&
                defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                $oSeo->kSprache = self::$kSprache;
            }
            // EXPERIMENTAL_MULTILANG_SHOP END
            // Link active?
            if (isset($oSeo->cKey) && $oSeo->cKey === 'kLink') {
                $bIsActive = self::Container()->getDB()->select('tlink', 'kLink', (int)$oSeo->kKey);
                if ($bIsActive !== null && (int)$bIsActive->bIsActive === 0) {
                    $oSeo = false;
                }
            }
            // mainwords
            if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $seo) === 0) {
                // canonical
                self::$cCanonicalURL = self::getURL() . '/' . $oSeo->cSeo;
                $oSeo->kKey          = (int)$oSeo->kKey;
                switch ($oSeo->cKey) {
                    case 'kKategorie':
                        self::$kKategorie = (int)$oSeo->kKey;
                        break;

                    case 'kHersteller':
                        self::$kHersteller = (int)$oSeo->kKey;
                        break;

                    case 'kArtikel':
                        self::$kArtikel = (int)$oSeo->kKey;
                        break;

                    case 'kLink':
                        self::$kLink = (int)$oSeo->kKey;
                        break;

                    case 'kSuchanfrage':
                        self::$kSuchanfrage = (int)$oSeo->kKey;
                        break;

                    case 'kMerkmalWert':
                        self::$kMerkmalWert = (int)$oSeo->kKey;
                        break;

                    case 'kTag':
                        self::$kTag = (int)$oSeo->kKey;
                        break;

                    case 'suchspecial':
                        self::$kSuchspecial = (int)$oSeo->kKey;
                        break;

                    case 'kNews':
                        self::$kNews = (int)$oSeo->kKey;
                        break;

                    case 'kNewsMonatsUebersicht':
                        self::$kNewsMonatsUebersicht = (int)$oSeo->kKey;
                        break;

                    case 'kNewsKategorie':
                        self::$kNewsKategorie = (int)$oSeo->kKey;
                        break;

                    case 'kUmfrage':
                        self::$kUmfrage = (int)$oSeo->kKey;
                        break;

                }
            }
            if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                self::updateLanguage($oSeo->kSprache);
            }
        }
        self::$MerkmalFilter = setzeMerkmalFilter();
        self::$SuchFilter    = setzeSuchFilter();
        self::$TagFilter     = setzeTagFilter();

        executeHook(HOOK_SEOCHECK_ENDE);
    }

    /**
     * @param int $languageID
     */
    private static function updateLanguage($languageID)
    {
        $languageID = (int)$languageID;
        if (self::$productFilter->getLanguageID() !== $languageID) {
            self::$productFilter->setLanguageID($languageID);
        }
        $spr   = self::Lang()->getIsoFromLangID($languageID);
        $cLang = $spr->cISO ?? null;
        if ($cLang !== $_SESSION['cISOSprache']) {
            checkeSpracheWaehrung($cLang);
            setzeSteuersaetze();
        }
    }

    /**
     * decide which page to load
     */
    public static function getEntryPoint()
    {
        self::setPageType(PAGE_UNBEKANNT);
        if ((self::$kArtikel > 0 && !self::$kKategorie)
            || (self::$kArtikel > 0 && self::$kKategorie > 0 && self::$show === 1)
        ) {
            $kVaterArtikel = ArtikelHelper::getParent(self::$kArtikel);
            if ($kVaterArtikel > 0) {
                $kArtikel = $kVaterArtikel;
                //save data from child article POST and add to redirect
                $cRP = '';
                if (is_array($_POST) && count($_POST) > 0) {
                    $cMember_arr = array_keys($_POST);
                    foreach ($cMember_arr as $cMember) {
                        $cRP .= '&' . $cMember . '=' . $_POST[$cMember];
                    }
                    // Redirect POST
                    $cRP = '&cRP=' . base64_encode($cRP);
                }
                http_response_code(301);
                header('Location: ' . self::getURL() . '/?a=' . $kArtikel . $cRP);
                exit();
            }

            self::setPageType(PAGE_ARTIKEL);
            self::$fileName = 'artikel.php';
        } elseif ((self::$bSEOMerkmalNotFound === null || self::$bSEOMerkmalNotFound === false)
            && (self::$bKatFilterNotFound === null || self::$bKatFilterNotFound === false)
            && (self::$bHerstellerFilterNotFound === null || self::$bHerstellerFilterNotFound === false)
            && ((self::$kHersteller > 0
                    || self::$kSuchanfrage > 0
                    || self::$kMerkmalWert > 0
                    || self::$kTag > 0
                    || self::$kKategorie > 0
                    || self::$nBewertungSterneFilter > 0
                    || self::$kHerstellerFilter > 0
                    || self::$kKategorieFilter > 0
                    || self::$kSuchspecial > 0
                    || self::$kSuchFilter > 0)
                || (self::$cPreisspannenFilter !== null && self::$cPreisspannenFilter > 0))
            && (self::$productFilter->getFilterCount() === 0 || !self::$bSeo)
        ) {
            self::$fileName      = 'filter.php';
            self::$AktuelleSeite = 'ARTIKEL';
            self::setPageType(PAGE_ARTIKELLISTE);
        } elseif (self::$kWunschliste > 0) {
            self::$fileName      = 'wunschliste.php';
            self::$AktuelleSeite = 'WUNSCHLISTE';
            self::setPageType(PAGE_WUNSCHLISTE);
        } elseif (self::$vergleichsliste > 0) {
            self::$fileName      = 'vergleichsliste.php';
            self::$AktuelleSeite = 'VERGLEICHSLISTE';
            self::setPageType(PAGE_VERGLEICHSLISTE);
        } elseif (self::$kNews > 0 || self::$kNewsMonatsUebersicht > 0 || self::$kNewsKategorie > 0) {
            self::$fileName      = 'news.php';
            self::$AktuelleSeite = 'NEWS';
            self::setPageType(PAGE_NEWS);
        } elseif (self::$kUmfrage > 0) {
            self::$fileName      = 'umfrage.php';
            self::$AktuelleSeite = 'UMFRAGE';
            self::setPageType(PAGE_UMFRAGE);
        } elseif (!empty(self::$cSuche)) {
            self::$fileName      = 'filter.php';
            self::$AktuelleSeite = 'ARTIKEL';
            self::setPageType(PAGE_ARTIKELLISTE);
        } elseif (!self::$kLink) {
            //check path
            $cPath        = self::getRequestUri();
            $cRequestFile = '/' . ltrim($cPath, '/');
            if ($cRequestFile === '/index.php') {
                // special case: /index.php shall be redirected to Shop-URL
                header('Location: ' . self::getURL(), true, 301);
                exit;
            }
            if ($cRequestFile === '/') {
                // special case: home page is accessible without seo url
                $link       = null;
                $linkHelper = LinkHelper::getInstance();
                self::setPageType(PAGE_STARTSEITE);
                self::$fileName = 'seite.php';
                if (Session::CustomerGroup()->getID() > 0) {
                    $cKundengruppenSQL = " AND (FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(cKundengruppen, ';', ',')) > 0
                        OR cKundengruppen IS NULL 
                        OR cKundengruppen = 'NULL' 
                        OR tlink.cKundengruppen = '')";
                    $link              = self::Container()->getDB()->query(
                        'SELECT kLink 
                            FROM tlink
                            WHERE nLinkart = ' . LINKTYP_STARTSEITE . $cKundengruppenSQL,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                }
                self::$kLink = isset($link->kLink)
                    ? (int)$link->kLink
                    : $linkHelper->getSpecialPageLinkKey(LINKTYP_STARTSEITE);
            } elseif (self::Media()->isValidRequest($cPath)) {
                self::Media()->handleRequest($cPath);
            } else {
                self::$is404         = true;
                self::$fileName      = null;
                self::$AktuelleSeite = '404';
                self::setPageType(PAGE_404);
            }
        } elseif (!empty(self::$kLink)) {
            $linkHelper = LinkHelper::getInstance();
            $link       = $linkHelper->getPageLink(self::$kLink);
            $oSeite     = null;
            if (isset($link->nLinkart)) {
                if ($link->nLinkart === LINKTYP_EXTERNE_URL) {
                    header('Location: ' . $link->cURL, true, 303);
                    exit;
                }
                self::$fileName = 'seite.php';
                self::setPageType(PAGE_EIGENE);
                $oSeite = self::Container()->getDB()->select('tspezialseite', 'nLinkart', (int)$link->nLinkart);
                if ($link->nLinkart === LINKTYP_STARTSEITE) {
                    self::setPageType(PAGE_STARTSEITE);
                } elseif ($link->nLinkart === LINKTYP_DATENSCHUTZ) {
                    self::setPageType(PAGE_DATENSCHUTZ);
                } elseif ($link->nLinkart === LINKTYP_AGB) {
                    self::setPageType(PAGE_AGB);
                } elseif ($link->nLinkart === LINKTYP_WRB) {
                    self::setPageType(PAGE_WRB);
                } elseif ($link->nLinkart === LINKTYP_VERSAND) {
                    self::setPageType(PAGE_VERSAND);
                } elseif ($link->nLinkart === LINKTYP_LIVESUCHE) {
                    self::setPageType(PAGE_LIVESUCHE);
                } elseif ($link->nLinkart === LINKTYP_TAGGING) {
                    self::setPageType(PAGE_TAGGING);
                } elseif ($link->nLinkart === LINKTYP_HERSTELLER) {
                    self::setPageType(PAGE_HERSTELLER);
                } elseif ($link->nLinkart === LINKTYP_NEWSLETTERARCHIV) {
                    self::setPageType(PAGE_NEWSLETTERARCHIV);
                } elseif ($link->nLinkart === LINKTYP_SITEMAP) {
                    self::setPageType(PAGE_SITEMAP);
                } elseif ($link->nLinkart === LINKTYP_GRATISGESCHENK) {
                    self::setPageType(PAGE_GRATISGESCHENK);
                } elseif ($link->nLinkart === LINKTYP_AUSWAHLASSISTENT) {
                    self::setPageType(PAGE_AUSWAHLASSISTENT);
                } elseif ($link->nLinkart === LINKTYP_404) {
                    self::setPageType(PAGE_404);
                }
            }
            if (!empty($oSeite->cDateiname)) {
                self::$fileName = $oSeite->cDateiname;
                switch ($oSeite->cDateiname) {
                    case 'news.php' :
                        self::$AktuelleSeite = 'NEWS';
                        self::setPageType(PAGE_NEWS);
                        break;
                    case 'jtl.php' :
                        self::$AktuelleSeite = 'MEIN KONTO';
                        self::setPageType(PAGE_MEINKONTO);
                        break;
                    case 'kontakt.php' :
                        self::$AktuelleSeite = 'KONTAKT';
                        self::setPageType(PAGE_KONTAKT);
                        break;
                    case 'newsletter.php' :
                        self::$AktuelleSeite = 'NEWSLETTER';
                        self::setPageType(PAGE_NEWSLETTER);
                        break;
                    case 'pass.php' :
                        self::$AktuelleSeite = 'PASSWORT VERGESSEN';
                        self::setPageType(PAGE_PASSWORTVERGESSEN);
                        break;
                    case 'registrieren.php' :
                        self::$AktuelleSeite = 'REGISTRIEREN';
                        self::setPageType(PAGE_REGISTRIERUNG);
                        break;
                    case 'umfrage.php' :
                        self::$AktuelleSeite = 'UMFRAGE';
                        self::setPageType(PAGE_UMFRAGE);
                        break;
                    case 'warenkorb.php' :
                        self::$AktuelleSeite = 'WARENKORB';
                        self::setPageType(PAGE_WARENKORB);
                        break;
                    case 'wunschliste.php' :
                        self::$AktuelleSeite = 'WUNSCHLISTE';
                        self::setPageType(PAGE_WUNSCHLISTE);
                        break;
                    default :
                        break;
                }
            }
        } elseif (self::$fileName === null) {
            self::$fileName      = 'seite.php';
            self::$AktuelleSeite = 'SEITE';
            self::setPageType(PAGE_EIGENE);
        }
        self::check404();
    }

    /**
     * @return bool
     */
    public static function check404(): bool
    {
        if (self::$is404 !== true) {
            return false;
        }
        executeHook(HOOK_INDEX_SEO_404, ['seo' => self::getRequestUri()]);
        if (!self::$kLink) {
            $hookInfos     = urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => self::$kLink
            ]);
            $kLink         = $hookInfos['value'];
            $bFileNotFound = $hookInfos['isFileNotFound'];
            if (!$kLink) {
                self::$kLink = LinkHelper::getInstance()->getSpecialPageLinkKey(LINKTYP_404);
            }
        }

        return true;
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array                     $cParameter_arr
     * @param object|null|ProductFilter $productFilter
     * @return ProductFilter
     * @deprecated since 5.0
     */
    public static function buildNaviFilter($cParameter_arr, $productFilter = null): ProductFilter
    {
        trigger_error(__METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::buildProductFilter() instead',
            E_USER_DEPRECATED);

        return self::buildProductFilter($cParameter_arr, $productFilter);
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array                       $cParameter_arr
     * @param stdClass|null|ProductFilter $productFilter
     * @return ProductFilter
     */
    public static function buildProductFilter($cParameter_arr, $productFilter = null): ProductFilter
    {
        $pf = new ProductFilter(self::Lang()->getLangArray(), self::getLanguageID());
        if ($productFilter !== null) {
            foreach (get_object_vars($productFilter) as $k => $v) {
                $pf->$k = $v;
            }
        }

        return $pf->initStates($cParameter_arr);
    }

    /**
     * @return ProductFilter
     * @deprecated since 5.0
     */
    public static function getNaviFilter(): ProductFilter
    {
        trigger_error(__METHOD__ . 'is deprecated. Use ' . __CLASS__ . '::getProductFilter() instead',
            E_USER_DEPRECATED);

        return self::getProductFilter();
    }

    /**
     * @return ProductFilter
     */
    public static function getProductFilter(): ProductFilter
    {
        if (self::$productFilter === null) {
            self::$productFilter = self::buildProductFilter([]);
        }

        return self::$productFilter;
    }

    /**
     * @param ProductFilter $productFilter
     */
    public static function setProductFilter(ProductFilter $productFilter)
    {
        self::$productFilter = $productFilter;
    }

    /**
     * @param null|ProductFilter $productFilter
     * @deprecated since 5.0 - this is done in ProductFilter:validate()
     */
    public static function checkNaviFilter($productFilter = null)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    }

    /**
     * @return int
     */
    public static function getShopVersion(): int
    {
        $oVersion = self::Container()->getDB()->query('SELECT nVersion FROM tversion', \DB\ReturnType::SINGLE_OBJECT);

        return (isset($oVersion->nVersion) && (int)$oVersion->nVersion > 0)
            ? (int)$oVersion->nVersion
            : 0;
    }

    /**
     * Return version of files
     *
     * @return int
     */
    public static function getVersion(): int
    {
        return JTL_VERSION;
    }

    /**
     * @return int
     */
    public function _getVersion(): int
    {
        return JTL_VERSION;
    }

    /**
     * get logo from db, fallback to first file in logo dir
     *
     * @var bool $fullURL - prepend shop url if set to true
     * @return string|null - image path/null if no logo was found
     */
    public static function getLogo($fullUrl = false)
    {
        $ret  = null;
        $conf = self::getSettings([CONF_LOGO]);
        $file = $conf['logo']['shop_logo'] ?? null;
        if ($file !== null && $file !== '') {
            $ret = PFAD_SHOPLOGO . $file;
        } elseif (is_dir(PFAD_ROOT . PFAD_SHOPLOGO)) {
            $dir = opendir(PFAD_ROOT . PFAD_SHOPLOGO);
            if (!$dir) {
                return '';
            }
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== '.' && $cDatei !== '..' && strpos($cDatei, SHOPLOGO_NAME) !== false) {
                    $ret = PFAD_SHOPLOGO . $cDatei;
                    break;
                }
            }
        }

        return $ret === null
            ? null
            : ($fullUrl === true
                ? self::getImageBaseURL()
                : '') . $ret;
    }

    /**
     * @param bool $bForceSSL
     * @param bool $bMultilang
     * @return string - the shop URL without trailing slash
     */
    public static function getURL($bForceSSL = false, $bMultilang = true): string
    {
        $idx = (int)$bForceSSL;
        if (isset(self::$url[self::$kSprache][$idx])) {
            return self::$url[self::$kSprache][$idx];
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        $cShopURL  = ($bMultilang === true && isset($_SESSION['cISOSprache'])
            && defined('URL_SHOP_' . strtoupper($_SESSION['cISOSprache'])))
            ? constant('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']))
            : URL_SHOP;
        $sslStatus = pruefeSSL();
        if ($sslStatus === 2) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        } elseif ($sslStatus === 4 || ($sslStatus === 3 && $bForceSSL)) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        }

        $url                              = rtrim($cShopURL, '/');
        self::$url[self::$kSprache][$idx] = $url;

        return $url;
    }

    /**
     * @param bool $bForceSSL
     * @return string - the shop Admin URL without trailing slash
     */
    public static function getAdminURL($bForceSSL = false): string
    {
        return rtrim(static::getURL($bForceSSL, false) . '/' . PFAD_ADMIN, '/');
    }

    /**
     * @param int $pageType
     */
    public static function setPageType($pageType)
    {
        self::$pageType        = (int)$pageType;
        $GLOBALS['nSeitenTyp'] = (int)$pageType;
        executeHook(HOOK_SHOP_SET_PAGE_TYPE, ['pageType' => $pageType]);
    }

    /**
     * @return int
     */
    public static function getPageType()
    {
        return self::$pageType ?? PAGE_UNBEKANNT;
    }

    /**
     * @return string
     */
    public static function getRequestUri(): string
    {
        $uri          = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'];
        $xShopurl_arr = parse_url(self::getURL());
        $xBaseurl_arr = parse_url($uri);

        if (empty($xShopurl_arr['path'])) {
            $xShopurl_arr['path'] = '/';
        }

        return isset($xBaseurl_arr['path'])
            ? substr($xBaseurl_arr['path'], strlen($xShopurl_arr['path']))
            : '';
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function isAdmin(): bool
    {
        if (is_bool(self::$_logged)) {
            return self::$_logged;
        }
        $result   = false;
        $isLogged = function () {
            return (new AdminAccount(true))->logged();
        };
        if (isset($_COOKIE['eSIdAdm'])) {
            if (session_name() !== 'eSIdAdm') {
                $oldID = session_id();
                session_write_close();
                session_id($_COOKIE['eSIdAdm']);
                $result = $isLogged();
                session_write_close();
                session_id($oldID);
                new Session();
            } else {
                $result = $isLogged();
            }
        }
        self::$_logged = $result;

        return $result;
    }

    /**
     * @return bool
     */
    public static function isBrandfree(): bool
    {
        return Nice::getInstance()->checkErweiterung(SHOP_ERWEITERUNG_BRANDFREE);
    }

    /**
     * Get the default container of the jtl shop
     *
     * @return \Services\DefaultServicesInterface
     */
    public static function Container(): \Services\DefaultServicesInterface
    {
        if (!static::$container) {
            static::createContainer();
        }

        return static::$container;
    }

    /**
     * Get the default container of the jtl shop
     *
     * @return \Services\DefaultServicesInterface
     */
    public function _Container(): \Services\DefaultServicesInterface
    {
        return self::Container();
    }

    /**
     * Create the default container of the jtl shop
     */
    private static function createContainer()
    {
        $container         = new \Services\Container();
        static::$container = $container;
        // BASE
        $container->setSingleton(\DB\DbInterface::class, function () {
            return new DB\NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        });
        $container->setSingleton(\Cache\JTLCacheInterface::class, function () {
            return new \Cache\JTLCache();
        });
        // SECURITY
        $container->setSingleton(\Services\JTL\CryptoServiceInterface::class, function () {
            return new \Services\JTL\CryptoService();
        });

        $container->setSingleton(\Services\JTL\PasswordServiceInterface::class, function (Container $container) {
            return new \Services\JTL\PasswordService($container->get(\Services\JTL\CryptoServiceInterface::class));
        });
        $container->setSingleton('BackendAuthLogger', function (Container $container) {
            $loggingConf = self::getConfig([CONF_GLOBAL])['global']['admin_login_logger_mode'] ?? [];
            $handlers    = [];
            foreach ($loggingConf as $value) {
                if ($value === AdminLoginConfig::CONFIG_DB) {
                    $handlers[] = (new NiceDBHandler(self::Container()->getDB(), Logger::INFO))
                        ->setFormatter(new LineFormatter('%message%', null, false, true));
                } elseif ($value === AdminLoginConfig::CONFIG_FILE) {
                    $handlers[] = (new StreamHandler(PFAD_LOGFILES . 'auth.log', Logger::INFO))
                        ->setFormatter(new LineFormatter(null, null, false, true));
                }
            }

            return new Logger('auth', $handlers, [new PsrLogMessageProcessor()]);
        });
        $container->setSingleton(ValidationServiceInterface::class, function () {
            $vs = new ValidationService($_GET, $_POST, $_COOKIE);
            $vs->setRuleSet('identity', (new RuleSet())->integer()->gt(0));

            return $vs;
        });
        // NETWORK & API
        $container->setFactory(\Network\JTLApi::class, function () {
            return new \Network\JTLApi($_SESSION, Nice::getInstance(), self::getInstance());
        });
        // DB SERVICES
        $container->setSingleton(DbService\GcServiceInterface::class, function (Container $container) {
            return new DbService\GcService($container->getDB());
        });
        // Captcha
        $container->setSingleton(\Services\JTL\CaptchaServiceInterface::class, function (Container $container) {
            return new \Services\JTL\CaptchaService(new \Services\JTL\SimpleCaptchaService(
                // Captcha Prüfung ist bei eingeloggtem Kunden, bei bereits erfolgter Prüfung
                // oder ausgeschaltetem Captcha nicht notwendig
                (self::getConfigValue(CONF_GLOBAL, 'anti_spam_method') !== 'N')
                && !Session::get('bAnti_spam_already_checked', false)
                && !Session::Customer()->isLoggedIn()
            ));
        });
    }
}
