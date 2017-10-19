<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Shop
 * @method static NiceDB DB()
 * @method static JTLCache Cache()
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
     */
    public static $isSeoMainword;

    /**
     * @var null|Shop
     */
    private static $_instance;

    /**
     * @var Navigationsfilter
     */
    public static $NaviFilter;

    /**
     * @var string
     */
    public static $fileName;

    /**
     * @var string
     */
    public static $AktuelleSeite;

    /**
     * @var string
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
     * @var IFilter[]
     */
    public static $customFilters = [];

    /**
     * @var array
     */
    private static $mapping = [
        'DB'       => '_DB',
        'Cache'    => '_Cache',
        'Lang'     => '_Language',
        'Smarty'   => '_Smarty',
        'Media'    => '_Media',
        'Event'    => '_Event',
        'has'      => '_has',
        'set'      => '_set',
        'get'      => '_get'
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
    public static function getInstance()
    {
        return self::$_instance === null ? new self() : self::$_instance;
    }

    /**
     * object wrapper - this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper - this allows to call Shop::DB()->query() etc.
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return isset($this->registry[$key])
            ? $this->registry[$key]
            : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function _set($key, $value)
    {
        $this->registry[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function _has($key)
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
        return isset(self::$mapping[$method])
            ? self::$mapping[$method]
            : null;
    }

    /**
     * get session instance
     *
     * @return Session
     */
    public function Session()
    {
        return Session::getInstance();
    }

    /**
     * get db adapter instance
     *
     * @return NiceDB
     */
    public function _DB()
    {
        return NiceDB::getInstance();
    }

    /**
     * get language instance
     *
     * @return Sprache
     */
    public function _Language()
    {
        return Sprache::getInstance();
    }

    /**
     * get config
     *
     * @return Shopsetting
     */
    public function Config()
    {
        return self::$_settings;
    }

    /**
     * get garbage collector
     *
     * @return GarbageCollector
     */
    public function Gc()
    {
        return new GarbageCollector();
    }

    /**
     * get logger
     *
     * @return Jtllog
     */
    public function Logger()
    {
        return new Jtllog();
    }
    
    /**
     * @return PHPSettingsHelper
     */
    public function PHPSettingsHelper()
    {
        return PHPSettingsHelper::getInstance();
    }

    /**
     * get cache instance
     *
     * @return JTLCache
     */
    public function _Cache()
    {
        return JTLCache::getInstance();
    }

    /**
     * get template engine instance
     *
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty
     */
    public function _Smarty($fast_init = false, $isAdmin = false)
    {
        return JTLSmarty::getInstance($fast_init, $isAdmin);
    }

    /**
     * get media instance
     *
     * @return Media
     */
    public function _Media()
    {
        return Media::getInstance();
    }

    /**
     * get event instance
     *
     * @return EventDispatcher
     */
    public function _Event()
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
     * set language/language ISO
     *
     * @param int $languageID
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
        return self::$_settings->getSettings($config);
    }

    /**
     * @param string $section
     * @param string $option
     * @return string|array|int
     */
    public static function getSettingValue($section, $option)
    {
        return self::getConfigValue($section, $option);
    }

    /**
     * @param string $section
     * @param string $option
     * @return string|array|int
     */
    public static function getConfigValue($section, $option)
    {
        return self::$_settings->getValue($section, $option);
    }

    /**
     * Load plugin event driven system
     */
    public static function bootstrap()
    {
        $cacheID = 'plgnbtsrp';
        if (($plugins = self::Cache()->get($cacheID)) === false) {
            $plugins = self::DB()->query(
                'SELECT kPlugin 
                  FROM tplugin 
                  WHERE nStatus = 2 
                    AND bBootstrap = 1 
                  ORDER BY nPrio ASC', 2) ?: [];
            self::Cache()->set($cacheID, $plugins, [CACHING_GROUP_PLUGIN]);
        }

        foreach ($plugins as $plugin) {
            if (($p = Plugin::bootstrapper($plugin->kPlugin)) !== null) {
                $p->boot(EventDispatcher::getInstance());
            }
        }
    }

    /**
     *
     */
    public static function run()
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
        self::$kSuchspecialFilter     = verifyGPCDataInteger('qf');
        self::$kSuchFilter            = verifyGPCDataInteger('sf');

        self::$nDarstellung = verifyGPCDataInteger('ed');
        self::$nSortierung  = verifyGPCDataInteger('sortierreihenfolge');
        self::$nSort        = verifyGPCDataInteger('Sortierung');

        self::$show            = verifyGPCDataInteger('show');
        self::$vergleichsliste = verifyGPCDataInteger('vla');
        self::$bFileNotFound   = false;
        self::$cCanonicalURL   = '';
        self::$is404           = false;

        self::$nSterne = verifyGPCDataInteger('nSterne');
        // @todo:
        self::$isSeoMainword = !(!isset($oSeo) || !is_object($oSeo) || !isset($oSeo->cSeo) || trim($oSeo->cSeo) === '');

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
        if (self::$nArtikelProSeite > 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;
        $redirect = verifyGPDataString('r');
        if (self::$kArtikel > 0) {
            if (!empty($redirect)
                && (self::$kNews > 0 // get param "n" was used a article amount
                    || (isset($_GET['n']) && (float)$_GET['n'] > 0)) // article amount was a float >0 and <1
            ){
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

        self::$NaviFilter = new Navigationsfilter(self::Lang()->getLangArray(), self::$kSprache, null, NiceDB::getInstance());

        self::seoCheck();
        self::Event()->fire('shop.run');

        return self::buildNaviFilter(self::getParameters());
    }

    /**
     * get page parameters
     *
     * @return array
     */
    public static function getParameters()
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
            'TagFilter_arr'          => self::$TagFilter !== null ? self::$TagFilter : [],
            'SuchFilter_arr'         => self::$SuchFilter !== null ? self::$SuchFilter : [],
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
            'isSeoMainword'          => self::$isSeoMainword,
            'nNewsKat'               => self::$nNewsKat,
            'cDatum'                 => self::$cDatum,
            'nAnzahl'                => self::$nAnzahl,
            'nSterne'                => self::$nSterne,
            'customFilters'          => self::$customFilters
        ];
    }

    /**
     * check for seo url
     */
    public static function seoCheck()
    {
        $uri                             = isset($_SERVER['HTTP_X_REWRITE$'])
            ? $_SERVER['HTTP_X_REWRITE_URL']
            : $_SERVER['REQUEST_URI'];
        self::$uri                       = $uri;
        self::$bSEOMerkmalNotFound       = false;
        self::$bKatFilterNotFound        = false;
        self::$bHerstellerFilterNotFound = false;
        executeHook(HOOK_SEOCHECK_ANFANG, ['uri' => &$uri]);
        $seite        = 0;
        $hstseo       = '';
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
            foreach (self::$NaviFilter->getCustomFilters() as $customFilter) {
                $seoParam = $customFilter->getUrlParamSEO();
                if ($seoParam !== '') {
                    $customFilterArr = explode($seoParam, $seo);
                    if (is_array($customFilterArr) && count($customFilterArr) > 1) {
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
            }
            //change Opera Fix
            if (substr($seo, strlen($seo) - 1, 1) === '?') {
                $seo = substr($seo, 0, strlen($seo) - 1);
            }
            $nMatch = preg_match('/[^_](' . SEP_SEITE . '([0-9]+))/', $seo, $cMatch_arr, PREG_OFFSET_CAPTURE);
            if ($nMatch === 1) {
                $seite = (int)$cMatch_arr[2][0];
                $seo   = substr($seo, 0, $cMatch_arr[1][1]);
            }
            //double content work around
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
                list($seo, $hstseo) = $oHersteller_arr;
                if (($idx = strpos($hstseo, SEP_KAT)) !== false && $idx !== strpos($hstseo, SEP_HST)) {
                    $oHersteller_arr = explode(SEP_KAT, $hstseo);
                    $hstseo          = $oHersteller_arr[0];
                    $seo             .= SEP_KAT . $oHersteller_arr[1];
                }
                if (strpos($hstseo, SEP_MERKMAL) !== false) {
                    $arr    = explode(SEP_MERKMAL, $hstseo);
                    $hstseo = $arr[0];
                    $seo    .= SEP_MERKMAL . $arr[1];
                }
                if (strpos($hstseo, SEP_MM_MMW) !== false) {
                    $arr    = explode(SEP_MM_MMW, $hstseo);
                    $hstseo = $arr[0];
                    $seo    .= SEP_MM_MMW . $arr[1];
                }
                if (strpos($hstseo, SEP_SEITE) !== false) {
                    $arr    = explode(SEP_SEITE, $hstseo);
                    $hstseo = $arr[0];
                    $seo    .= SEP_SEITE . $arr[1];
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
            if (count($customSeo) > 0) {
                foreach ($customSeo as $className => $data) {
                    $oSeo = self::DB()->select($data['table'], 'cSeo', $data['cSeo']);
                    if (isset($oSeo->filterval)) {
                        self::$customFilters[$className] = (int)$oSeo->filterval;
                    } else {
                        self::$bKatFilterNotFound = true;
                    }
                    if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                        self::updateLanguage($oSeo->kSprache);
                    }
                }
            }
            // category filter
            if (strlen($katseo) > 0) {
                $oSeo = self::DB()->select('tseo', 'cKey', 'kKategorie', 'cSeo', $katseo);
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $katseo) === 0) {
                    self::$kKategorieFilter = (int)$oSeo->kKey;
                } else {
                    self::$bKatFilterNotFound = true;
                }
            }
            // manufacturer filter
            if (strlen($hstseo) > 0) {
                $oSeo = self::DB()->select('tseo', 'cKey', 'kHersteller', 'cSeo', $hstseo);
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $hstseo) === 0) {
                    self::$kHerstellerFilter = (int)$oSeo->kKey;
                } else {
                    self::$bHerstellerFilterNotFound = true;
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
                        $oSeo = self::DB()->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo', $cSEOMerkmal);
                        if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $cSEOMerkmal) === 0) {
                            //haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                            $_GET['mf'][] = (int)$oSeo->kKey;
                        } else {
                            self::$bSEOMerkmalNotFound = true;
                        }
                    }
                }
            }
            $oSeo = self::DB()->select('tseo', 'cSeo', $seo);
            // EXPERIMENTAL_MULTILANG_SHOP
            if (isset($oSeo->kSprache) && self::$kSprache !== $oSeo->kSprache &&
                defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                $oSeo->kSprache = self::$kSprache;
            }
            // EXPERIMENTAL_MULTILANG_SHOP END
            // Link active?
            if (isset($oSeo->cKey) && $oSeo->cKey === 'kLink') {
                $bIsActive = self::DB()->select('tlink', 'kLink', (int)$oSeo->kKey);
                if (isset($bIsActive->bIsActive) && $bIsActive->bIsActive === '0') {
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
        if (self::$NaviFilter->getLanguageID() !== $languageID) {
            self::$NaviFilter->setLanguageID($languageID);
        }
        $spr   = self::Lang()->getIsoFromLangID($languageID);
        $cLang = isset($spr->cISO) ? $spr->cISO : null;
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
                header('Location: ' . self::getURL() . '/index.php?a=' . $kArtikel . $cRP);
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
            && (self::$isSeoMainword || self::$NaviFilter->getFilterCount() === 0 || !self::$bSeo)
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
                //special case: home page is accessible without seo url
                $link        = null;
                $linkHelper  = LinkHelper::getInstance();
                if (Session::CustomerGroup()->getID() > 0) {
                    $cKundengruppenSQL = " AND (FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(cKundengruppen, ';', ',')) > 0
                        OR cKundengruppen IS NULL 
                        OR cKundengruppen = 'NULL' 
                        OR tlink.cKundengruppen = '')";
                    $link              = self::DB()->query(
                        'SELECT kLink 
                            FROM tlink
                            WHERE nLinkart = ' . LINKTYP_STARTSEITE . $cKundengruppenSQL, 1
                    );
                }
                self::$kLink = isset($link->kLink)
                    ? (int)$link->kLink
                    : $linkHelper->getSpecialPageLinkKey(LINKTYP_STARTSEITE);
            } elseif (self::Media()->isValidRequest($cPath)) {
                self::Media()->handleRequest($cPath);
            } else {
                self::$is404         = true;
                self::$AktuelleSeite = '404';
                self::setPageType(PAGE_404);
            }
        } else {
            if (!empty(self::$kLink)) {
                $linkHelper = LinkHelper::getInstance();
                $link       = $linkHelper->getPageLink(self::$kLink);
                $oSeite     = null;
                if (isset($link->nLinkart)) {
                    $oSeite = self::DB()->select('tspezialseite', 'nLinkart', (int)$link->nLinkart);
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
            }
            if (self::$fileName === null) {
                self::$fileName      = 'seite.php';
                self::$AktuelleSeite = 'SEITE';
                self::setPageType(PAGE_EIGENE);
            }
        }
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array $cParameter_arr
     * @param object|null|Navigationsfilter $NaviFilter
     * @todo: use NaviFilter param
     * @return Navigationsfilter
     */
    public static function buildNaviFilter($cParameter_arr, $NaviFilter = null)
    {
        if (self::$NaviFilter === null) {
            self::$NaviFilter = new Navigationsfilter(self::Lang()->getLangArray(), self::$kSprache);
        }

        return self::$NaviFilter->initStates($cParameter_arr);
    }

    /**
     * @return Navigationsfilter
     */
    public static function getNaviFilter()
    {
        return self::$NaviFilter;
    }

    /**
     * @param null|Navigationsfilter $NaviFilter
     */
    public static function checkNaviFilter($NaviFilter = null)
    {
        if ($NaviFilter === null) {
            $NaviFilter = self::$NaviFilter;
        }
        if ($NaviFilter->getFilterCount() > 0) {
            if (!$NaviFilter->hasManufacturer()
                && !$NaviFilter->hasCategory()
                && !$NaviFilter->hasTag()
                && !$NaviFilter->hasSearchQuery()
                && !$NaviFilter->hasSearch()
                && !$NaviFilter->hasAttributeValue()
                && !$NaviFilter->hasSearchSpecial()
            ) {
                $manufacturerFilter = $NaviFilter->getManufacturerFilter();
                // we have a manufacturer filter that doesn't filter anything
                if (!empty($manufacturerFilter->cSeo[self::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $manufacturerFilter->cSeo[self::$kSprache]);
                    exit();
                }
                $categoryFilter = $NaviFilter->getCategoryFilter();
                // we have a category filter that doesn't filter anything
                if (!empty($categoryFilter->cSeo[self::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $categoryFilter->cSeo[self::$kSprache]);
                    exit();
                }
            } elseif (($NaviFilter->hasManufacturer() && $NaviFilter->hasManufacturerFilter())
                || ($NaviFilter->hasCategory() && $NaviFilter->hasCategoryFilter())
            ) {
                $manufacturer = $NaviFilter->getManufacturer();
                if (!empty($manufacturer->cSeo[self::$kSprache])) {
                    //we have a manufacturer page with some manufacturer filter
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $manufacturer->cSeo[self::$kSprache]);
                    exit();
                }
                $category = $NaviFilter->getCategory();
                if (!empty($category->cSeo[self::$kSprache])) {
                    //we have a category page with some category filter
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $category->cSeo[self::$kSprache]);
                    exit();
                }
            }
        }
    }

    /**
     * @return int
     */
    public static function getShopVersion()
    {
        $oVersion = self::DB()->query('SELECT nVersion FROM tversion', 1);

        return (isset($oVersion->nVersion) && (int)$oVersion->nVersion > 0)
            ? (int)$oVersion->nVersion
            : 0;
    }

    /**
     * Return version of files
     *
     * @return int
     */
    public static function getVersion()
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
        $file = isset($conf['logo']['shop_logo']) ? $conf['logo']['shop_logo'] : null;
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
                ? self::getURL() . '/'
                : '') . $ret;
    }

    /**
     * @param bool $bForceSSL
     * @param bool $bMultilang
     * @return string - the shop URL without trailing slash
     */
    public static function getURL($bForceSSL = false, $bMultilang = true)
    {
        $idx = (int)$bForceSSL;
        if (isset(self::$url[self::$kSprache][$idx])) {
            return self::$url[self::$kSprache][$idx];
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        $cShopURL = ($bMultilang === true && isset($_SESSION['cISOSprache'])
            && defined('URL_SHOP_' . strtoupper($_SESSION['cISOSprache'])))
            ? constant('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']))
            : URL_SHOP;
        $sslStatus = pruefeSSL();
        if ($sslStatus === 2) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        } elseif ($sslStatus === 4 || ($sslStatus === 3 && $bForceSSL)) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        }

        $url = rtrim($cShopURL, '/');
        self::$url[self::$kSprache][$idx] = $url;

        return $url;
    }

    /**
     * @param bool $bForceSSL
     * @return string - the shop Admin URL without trailing slash
     */
    public static function getAdminURL($bForceSSL = false)
    {
        return rtrim(static::getURL($bForceSSL, false) . '/' . PFAD_ADMIN, '/');
    }

    /**
     * @param int $pageType
     */
    public static function setPageType($pageType)
    {
        self::$pageType        = $pageType;
        $GLOBALS['nSeitenTyp'] = $pageType;
        executeHook(HOOK_SHOP_SET_PAGE_TYPE, ['pageType' => $pageType]);
    }

    /**
     * @return int
     */
    public static function getPageType()
    {
        return self::$pageType !== null ? self::$pageType : PAGE_UNBEKANNT;
    }

    /**
     * @return string
     */
    public static function getRequestUri()
    {
        $uri          = isset($_SERVER['HTTP_X_REWRITE_URL'])
            ? $_SERVER['HTTP_X_REWRITE_URL']
            : $_SERVER['REQUEST_URI'];
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
     */
    public static function isAdmin()
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
    public static function isBrandfree()
    {
        return Nice::getInstance()->checkErweiterung(SHOP_ERWEITERUNG_BRANDFREE);
    }
}
