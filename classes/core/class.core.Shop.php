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
    public static $kSprache = null;

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
     * @var
     */
    public static $show;

    /**
     * @var
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
     * @var
     */
    public static $MerkmalFilter;

    /**
     * @var
     */
    public static $SuchFilter;

    /**
     * @var
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
    private static $_instance = null;

    /**
     * @var Navigationsfilter
     */
    public static $NaviFilter;

    /**
     * @var string
     */
    public static $fileName = null;

    /**
     * @var
     */
    public static $AktuelleSeite = null;

    /**
     * @var string
     */
    public static $pageType = null;

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
    private static $_logged = null;

    /**
     * @var array
     */
    private static $url = [];

    /**
     * @var Shopsetting
     */
    private static $_settings;

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
        return (self::$_instance === null) ? new self() : self::$_instance;
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
        return (($mapping = self::map($method))!== null)
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
        return (($mapping = self::map($method)) !== null)
            ? call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return (isset($this->registry[$key]))
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
        $mapping = [
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

        return (isset($mapping[$method]))
            ? $mapping[$method]
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
     * @param string $eventName
     * @param array  $arguments
     * @return array|null
     */
    public static function fire($eventName, array $arguments = [])
    {
        return self::Event()->fire($eventName, $arguments);
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
        return ($iso === false) ? (int)self::$kSprache : self::$cISO;
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
        if (($plugins = Shop::Cache()->get($cacheID)) === false) {
            $plugins = self::DB()->executeQuery("
                SELECT kPlugin 
                  FROM tplugin 
                  WHERE nStatus = 2 
                    AND bBootstrap = 1 
                  ORDER BY nPrio ASC", 2) ?: [];
            Shop::Cache()->set($cacheID, $plugins, [CACHING_GROUP_PLUGIN]);
        }

        foreach ($plugins as $plugin) {
            if ($p = Plugin::bootstrapper($plugin->kPlugin)) {
                $p->boot(EventDispatcher::getInstance());
            }
        }
    }

    /**
     *
     */
    public static function run()
    {
        self::$kKonfigPos            = verifyGPCDataInteger('ek');
        self::$kKategorie            = verifyGPCDataInteger('k');
        self::$kArtikel              = verifyGPCDataInteger('a');
        self::$kVariKindArtikel      = verifyGPCDataInteger('a2');
        self::$kSeite                = verifyGPCDataInteger('s');
        self::$kLink                 = verifyGPCDataInteger('s');
        self::$kHersteller           = verifyGPCDataInteger('h');
        self::$kSuchanfrage          = verifyGPCDataInteger('l');
        self::$kMerkmalWert          = verifyGPCDataInteger('m');
        self::$kTag                  = verifyGPCDataInteger('t');
        self::$kSuchspecial          = verifyGPCDataInteger('q');
        self::$kNews                 = verifyGPCDataInteger('n');
        self::$kNewsMonatsUebersicht = verifyGPCDataInteger('nm');
        self::$kNewsKategorie        = verifyGPCDataInteger('nk');
        self::$kUmfrage              = verifyGPCDataInteger('u');

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

        self::$isSeoMainword = !(!isset($oSeo) || !is_object($oSeo) || !isset($oSeo->cSeo) || strlen(trim($oSeo->cSeo)) === 0);

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
        //avoid redirect loops for surveys that require logged in customers
        if (self::$kUmfrage > 0 && verifyGPCDataInteger('r') !== '' && empty($_SESSION['Kunde']->kKunde)) {
            self::$kUmfrage = 0;
        }

        self::$nArtikelProSeite = verifyGPCDataInteger('af');
        if (self::$nArtikelProSeite > 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;

        $redirect = verifyGPDataString('r');
        if (self::$kNews > 0 && self::$kArtikel > 0 && !empty($redirect)) {
            //GET param "n" is often misused as "amount of article"
            self::$kNews    = 0;
            if ((int)$redirect === R_LOGIN_WUNSCHLISTE) {
                //login redirect on wishlist add when not logged in uses get param "n" as amount and "a" for the article ID
                //but we wont to go to the login page, not to the article page
                self::$kArtikel = 0;
            }
        } elseif (self::$kArtikel > 0 && ((int)$redirect === R_LOGIN_BEWERTUNG || (int)$redirect === R_LOGIN_TAG) && empty($_SESSION['Kunde']->kKunde)) {
            //avoid redirect to article page for ratings that require logged in customers
            self::$kArtikel = 0;
        }

        $_SESSION['cTemplate'] = Template::$cTemplate;

        self::Event()->fire('shop.run');
    }

    /**
     * get page parameters
     *
     * @return array
     */
    public static function getParameters()
    {
        self::seoCheck();
        if (self::$kKategorie > 0 && !Kategorie::isVisible(self::$kKategorie, $_SESSION['Kundengruppe']->kKundengruppe)) {
            self::$kKategorie = 0;
        }
        //check variation combination
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
            'kLink'                  => (intval(self::$kSeite) > 0) ? self::$kSeite : self::$kLink,
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
            'TagFilter_arr'          => (isset(self::$TagFilter)) ? self::$TagFilter : [],
            'SuchFilter_arr'         => (isset(self::$SuchFilter)) ? self::$SuchFilter : [],
            'nArtikelProSeite'       => (isset(self::$nArtikelProSeite)) ? self::$nArtikelProSeite : null,
            'cSuche'                 => (isset(self::$cSuche)) ? self::$cSuche : null,
            'seite'                  => (isset(self::$seite)) ? self::$seite : null,
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
        ];
    }

    /**
     * check for seo url
     */
    public static function seoCheck()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_X_REWRITE$'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        self::$uri                       = $uri;
        self::$bSEOMerkmalNotFound       = false;
        self::$bKatFilterNotFound        = false;
        self::$bHerstellerFilterNotFound = false;
        //@todo@todo@todo
        if (true||strpos($uri, 'index.php') === false) {
            executeHook(HOOK_SEOCHECK_ANFANG, array('uri' => &$uri));
            $seite        = 0;
            $hstseo       = '';
            $katseo       = '';
            $xShopurl_arr = parse_url(self::getURL());
            $xBaseurl_arr = parse_url($uri);
            $seo          = (isset($xBaseurl_arr['path']))
                ? substr($xBaseurl_arr['path'], (isset($xShopurl_arr['path']))
                    ? (strlen($xShopurl_arr['path']) + 1)
                    : 1)
                : false;
            //Fremdparameter
            $seo = extFremdeParameter($seo);
            if ($seo) {
                //change Opera Fix
                if (substr($seo, strlen($seo) - 1, 1) === '?') {
                    $seo = substr($seo, 0, strlen($seo) - 1);
                }
                $nMatch = preg_match('/[^_](' . SEP_SEITE . '([0-9]+))/', $seo, $cMatch_arr, PREG_OFFSET_CAPTURE);
                if ($nMatch !== false && $nMatch == 1) {
                    $seite = (int)$cMatch_arr[2][0];
                    $seo   = substr($seo, 0, $cMatch_arr[1][1]);
                }
                //double content work around
                if (strlen($seo) > 0 && $seite === 1) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $seo);
                    exit();
                }
                $cSEOMerkmal_arr = explode(SEP_MERKMAL, $seo);
                $seo             = $cSEOMerkmal_arr[0];
                foreach ($cSEOMerkmal_arr as $i => &$merkmal) {
                    if ($i > 0) {
                        if (($idx = strpos($merkmal, SEP_KAT)) !== false && $idx !== strpos($merkmal, SEP_HST)) {
                            $arr = explode(SEP_KAT, $merkmal);
                            $merkmal = $arr[0];
                            $seo .= SEP_KAT . $arr[1];
                        }
                        if (strpos($merkmal, SEP_HST) !== false) {
                            $arr = explode(SEP_HST, $merkmal);
                            $merkmal = $arr[0];
                            $seo .= SEP_HST . $arr[1];
                        }
                        if (strpos($merkmal, SEP_MM_MMW) !== false) {
                            $arr = explode(SEP_MM_MMW, $merkmal);
                            $merkmal = $arr[0];
                            $seo .= SEP_MM_MMW . $arr[1];
                        }
                        if (strpos($merkmal, SEP_SEITE) !== false) {
                            $arr = explode(SEP_SEITE, $merkmal);
                            $merkmal = $arr[0];
                            $seo .= SEP_SEITE . $arr[1];
                        }
                    }
                }
                $oHersteller_arr = explode(SEP_HST, $seo);
                if (is_array($oHersteller_arr) && count($oHersteller_arr) > 1) {
                    $seo    = $oHersteller_arr[0];
                    $hstseo = $oHersteller_arr[1];
                    if (($idx = strpos($hstseo, SEP_KAT)) !== false && $idx !== strpos($hstseo, SEP_HST)) {
                        $oHersteller_arr = explode(SEP_KAT, $hstseo);
                        $hstseo = $oHersteller_arr[0];
                        $seo .= SEP_KAT . $oHersteller_arr[1];
                    }
                    if (strpos($hstseo, SEP_MERKMAL) !== false) {
                        $arr = explode(SEP_MERKMAL, $hstseo);
                        $hstseo = $arr[0];
                        $seo .= SEP_MERKMAL . $arr[1];
                    }
                    if (strpos($hstseo, SEP_MM_MMW) !== false) {
                        $arr = explode(SEP_MM_MMW, $hstseo);
                        $hstseo = $arr[0];
                        $seo .= SEP_MM_MMW . $arr[1];
                    }
                    if (strpos($hstseo, SEP_SEITE) !== false) {
                        $arr = explode(SEP_SEITE, $hstseo);
                        $hstseo = $arr[0];
                        $seo .= SEP_SEITE . $arr[1];
                    }
                } else {
                    $seo = $oHersteller_arr[0];
                }
                $oKategorie_arr = explode(SEP_KAT, $seo);
                if (is_array($oKategorie_arr) && count($oKategorie_arr) > 1) {
                    $seo    = $oKategorie_arr[0];
                    $katseo = $oKategorie_arr[1];
                    if (strpos($katseo, SEP_HST) !== false) {
                        $arr = explode(SEP_HST, $katseo);
                        $katseo = $arr[0];
                        $seo .= SEP_HST . $arr[1];
                    }
                    if (strpos($katseo, SEP_MERKMAL) !== false) {
                        $arr = explode(SEP_MERKMAL, $katseo);
                        $katseo = $arr[0];
                        $seo .= SEP_MERKMAL . $arr[1];
                    }
                    if (strpos($katseo, SEP_MM_MMW) !== false) {
                        $arr = explode(SEP_MM_MMW, $katseo);
                        $katseo = $arr[0];
                        $seo .= SEP_MM_MMW . $arr[1];
                    }
                    if (strpos($katseo, SEP_SEITE) !== false) {
                        $arr = explode(SEP_SEITE, $katseo);
                        $katseo = $arr[0];
                        $seo .= SEP_SEITE . $arr[1];
                    }
                } else {
                    $seo = $oKategorie_arr[0];
                }
                if (intval($seite) > 0) {
                    $_GET['seite'] = (int)$seite;
                }
                //split attribute/attribute value
                $oMerkmal_arr = explode(SEP_MM_MMW, $seo);
                if (is_array($oMerkmal_arr) && count($oMerkmal_arr) > 1) {
                    $seo = $oMerkmal_arr[1];
                    //$mmseo = $oMerkmal_arr[0];
                }
                //category filter
                if (strlen($katseo) > 0) {
                    $oSeo = self::DB()->select('tseo', 'cKey', 'kKategorie', 'cSeo', $katseo);
                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $katseo) === 0) {
                        self::$kKategorieFilter = (int)$oSeo->kKey;
                    } else {
                        self::$bKatFilterNotFound = true;
                    }
                }
                //manufacturer filter
                if (strlen($hstseo) > 0) {
                    $oSeo = self::DB()->select('tseo', 'cKey', 'kHersteller', 'cSeo', $hstseo);
                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $hstseo) === 0) {
                        self::$kHerstellerFilter = (int)$oSeo->kKey;
                    } else {
                        self::$bHerstellerFilterNotFound = true;
                    }
                }
                //attribute filter
                if (count($cSEOMerkmal_arr) > 1) {
                    $nMerkmalZaehler = 1;
                    $_GET['mf'] = [];
                    foreach ($cSEOMerkmal_arr as $i => $cSEOMerkmal) {
                        if (strlen($cSEOMerkmal) > 0 && $i > 0) {
                            $oSeo = self::DB()->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo', $cSEOMerkmal);
                            if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $cSEOMerkmal) === 0) {
                                //haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                                $_GET['mf'][] = (int)$oSeo->kKey;
                                ++$nMerkmalZaehler;
                                self::$bSEOMerkmalNotFound = false;
                            } else {
                                self::$bSEOMerkmalNotFound = true;
                            }
                        }
                    }
                }
                $oSeo = self::DB()->select('tseo', 'cSeo', $seo);
                //EXPERIMENTAL_MULTILANG_SHOP
                if (isset($oSeo->kSprache) && self::$kSprache !== $oSeo->kSprache &&
                    defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    $oSeo->kSprache = self::$kSprache;
                }
                //EXPERIMENTAL_MULTILANG_SHOP END
                //Link active?
                if (isset($oSeo->cKey) && $oSeo->cKey === 'kLink') {
                    $bIsActive = self::DB()->select('tlink', 'kLink', (int)$oSeo->kKey);
                    if ($bIsActive->bIsActive === '0') {
                        $oSeo = false;
                    }
                }

                //mainwords
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $seo) === 0) {
                    //canonical
                    self::$cCanonicalURL = self::getURL() . '/' . $oSeo->cSeo;
                    $oSeo->kKey = (int)$oSeo->kKey;
                    switch ($oSeo->cKey) {
                        case 'kKategorie':
                            self::$kKategorie = $oSeo->kKey;
                            break;

                        case 'kHersteller':
                            self::$kHersteller = $oSeo->kKey;
                            break;

                        case 'kArtikel':
                            self::$kArtikel = $oSeo->kKey;
                            break;

                        case 'kLink':
                            self::$kLink = $oSeo->kKey;
                            break;

                        case 'kSuchanfrage':
                            self::$kSuchanfrage = $oSeo->kKey;
                            break;

                        case 'kMerkmalWert':
                            self::$kMerkmalWert = $oSeo->kKey;
                            break;

                        case 'kTag':
                            self::$kTag = $oSeo->kKey;
                            break;

                        case 'suchspecial':
                            self::$kSuchspecial = $oSeo->kKey;
                            break;

                        case 'kNews':
                            self::$kNews = $oSeo->kKey;
                            break;

                        case 'kNewsMonatsUebersicht':
                            self::$kNewsMonatsUebersicht = $oSeo->kKey;
                            break;

                        case 'kNewsKategorie':
                            self::$kNewsKategorie = $oSeo->kKey;
                            break;

                        case 'kUmfrage':
                            self::$kUmfrage = $oSeo->kKey;
                            break;

                    }
                }
                if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                    $kSprache = (int)$oSeo->kSprache;
                    $spr      = (class_exists('Sprache'))
                        ? self::Lang()->getIsoFromLangID($kSprache)
                        : self::DB()->select('tsprache', 'kSprache', $kSprache);
                    $cLang = (isset($spr->cISO)) ? $spr->cISO : null;
                    if ($cLang !== $_SESSION['cISOSprache']) {
                        checkeSpracheWaehrung($cLang);
                        setzeSteuersaetze();
                    }
                }
            }
            self::$MerkmalFilter = setzeMerkmalFilter();
            self::$SuchFilter    = setzeSuchFilter();
            self::$TagFilter     = setzeTagFilter();

            executeHook(HOOK_SEOCHECK_ENDE);
        }
    }

    /**
     * decide which page to load
     */
    public static function getEntryPoint()
    {
        $fileName = null;
        self::setPageType(PAGE_UNBEKANNT);
        if (((self::$kArtikel > 0 && !self::$kKategorie) || (self::$kArtikel > 0 && self::$kKategorie > 0 && self::$show == 1))) {
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
                header('Location: ' . self::getURL() . '/navi.php?a=' . $kArtikel . $cRP);
                exit();
            }

            self::setPageType(PAGE_ARTIKEL);
            self::$fileName = 'artikel.php';
        } elseif ((!isset(self::$bSEOMerkmalNotFound) || self::$bSEOMerkmalNotFound === false) &&
            (!isset(self::$bKatFilterNotFound) || self::$bKatFilterNotFound === false) &&
            (!isset(self::$bHerstellerFilterNotFound) || self::$bHerstellerFilterNotFound === false) &&
            ((self::$isSeoMainword || self::$NaviFilter->nAnzahlFilter == 0) || !self::$bSeo) &&
            (self::$kHersteller > 0 || self::$kSuchanfrage > 0 || self::$kMerkmalWert > 0 || self::$kTag > 0 || self::$kKategorie > 0 ||
                (isset(self::$cPreisspannenFilter) && self::$cPreisspannenFilter > 0) ||
                (isset(self::$nBewertungSterneFilter) && self::$nBewertungSterneFilter > 0) || self::$kHerstellerFilter > 0 ||
                self::$kKategorieFilter > 0 || self::$kSuchspecial > 0 || self::$kSuchFilter > 0)
        ) {
            //these are some serious changes! - create 404 if attribute or filtered category is empty
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
            if ($cRequestFile === '/') { //special case: home page is accessible without seo url
                $linkHelper  = LinkHelper::getInstance();
                self::$kLink = $linkHelper->getSpecialPageLinkKey(LINKTYP_STARTSEITE);
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
        return (self::$NaviFilter = (new Navigationsfilter())->initStates($cParameter_arr));
    }

    /**
     * @return Navigationsfilter
     */
    public static function getNaviFilter()
    {
        return self::$NaviFilter;
    }

    /**
     * @param stdClass $NaviFilter
     */
    public static function checkNaviFilter($NaviFilter)
    {
        if ($NaviFilter->nAnzahlFilter > 0) {
            if (empty($NaviFilter->Hersteller->kHersteller) && empty($NaviFilter->Kategorie->kKategorie) &&
                empty($NaviFilter->Tag->kTag) && empty($NaviFilter->Suchanfrage->kSuchanfrage) && empty($NaviFilter->News->kNews) &&
                empty($NaviFilter->Newsmonat->kNewsMonatsUebersicht) && empty($NaviFilter->NewsKategorie->kNewsKategorie) &&
                !isset($NaviFilter->Suche->cSuche) && empty($NaviFilter->MerkmalWert->kMerkmalWert) && empty($NaviFilter->Suchspecial->kKey)) {
                //we have a manufacturer filter that doesn't filter anything
                if (!empty($NaviFilter->HerstellerFilter->cSeo[Shop::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $NaviFilter->HerstellerFilter->cSeo[Shop::$kSprache]);
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if (!empty($NaviFilter->KategorieFilter->cSeo[Shop::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/' . $NaviFilter->KategorieFilter->cSeo[Shop::$kSprache]);
                    exit();
                }
            } elseif (!empty($NaviFilter->Hersteller->kHersteller) && !empty($NaviFilter->HerstellerFilter->kHersteller) &&
                !empty($NaviFilter->Hersteller->cSeo[Shop::$kSprache])) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $NaviFilter->Hersteller->cSeo[Shop::$kSprache]);
                exit();
            } elseif (!empty($NaviFilter->Kategorie->kKategorie) && !empty($NaviFilter->KategorieFilter->kKategorie) &&
                !empty($NaviFilter->Kategorie->cSeo[Shop::$kSprache])) {
                //we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . Shop::getURL() . '/' . $NaviFilter->Kategorie->cSeo[Shop::$kSprache]);
                exit();
            }
        }
    }

    /**
     * @return int
     */
    public static function getShopVersion()
    {
        $oVersion = self::DB()->query("SELECT nVersion FROM tversion", 1);

        return (isset($oVersion->nVersion) && intval($oVersion->nVersion) > 0)
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
        $file = (isset($conf['logo']['shop_logo'])) ? $conf['logo']['shop_logo'] : null;
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

        return ($ret === null) ? null : (($fullUrl === true) ? self::getURL() . '/' : '') . $ret;
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
        $cShopURL = URL_SHOP;
        //EXPERIMENTAL_MULTILANG_SHOP
        if ($bMultilang === true && isset($_SESSION['cISOSprache']) && defined('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']))) {
            $cShopURL = constant('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']));
        }
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
        $cShopURL = static::getURL($bForceSSL, false) . '/' . PFAD_ADMIN;

        return rtrim($cShopURL, '/');
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
        return (self::$pageType !== null) ? self::$pageType : PAGE_UNBEKANNT;
    }

    /**
     * @return string
     */
    public static function getRequestUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }

        $xShopurl_arr = parse_url(self::getURL());
        $xBaseurl_arr = parse_url($uri);

        if (!isset($xShopurl_arr['path']) || strlen($xShopurl_arr['path']) === 0) {
            $xShopurl_arr['path'] = '/';
        }

        $cPath = isset($xBaseurl_arr['path'])
            ? substr($xBaseurl_arr['path'], strlen($xShopurl_arr['path']))
            : '';

        return $cPath;
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
            $oAccount = new AdminAccount(true);

            return $oAccount->logged();
        };
        if (isset($_COOKIE['eSIdAdm'])) {
            if (session_name() !== 'eSIdAdm') {
                $result = $isLogged();
                Session::getInstance(true, true);
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
