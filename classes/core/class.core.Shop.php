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
    public static $isSeoMainword;

    /**
     * @var null|Shop
     */
    private static $_instance = null;

    /**
     * @var object
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
    private $registry = array();

    /**
     * @var bool
     */
    private static $_logged = null;

    /**
     *
     */
    private function __construct()
    {
        self::$_instance = $this;
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
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array(array($this, $mapping), $arguments) : null;
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
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array(array(self::getInstance(), $mapping), $arguments) : null;
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return (isset($this->registry[$key]) ? $this->registry[$key] : null);
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
        $mapping = array(
            'DB'       => '_DB',
            'Cache'    => '_Cache',
            'Lang'     => '_Language',
            'Smarty'   => '_Smarty',
            'Media'    => '_Media',
            'has'      => '_has',
            'set'      => '_set',
            'get'      => '_get'
        );

        return (isset($mapping[$method])) ? $mapping[$method] : null;
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
        return Shopsetting::getInstance();
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
        return ($iso === false) ? self::$kSprache : self::$cISO;
    }

    /**
     * set language/language ISO
     *
     * @param int $languageID
     * @param string $cISO
     */
    public static function setLanguage($languageID, $cISO = null)
    {
        self::$kSprache = intval($languageID);
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
     * @param array $config
     * @return array
     */
    public static function getSettings($config)
    {
        $settings = Shopsetting::getInstance();

        return $settings->getSettings($config);
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

        self::$isSeoMainword = (!isset($oSeo) || !is_object($oSeo) || !isset($oSeo->cSeo) || strlen(trim($oSeo->cSeo)) === 0) ? false : true;

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

        self::$nArtikelProSeite = verifyGPCDataInteger('af');
        if (self::$nArtikelProSeite > 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;

        $_SESSION['cTemplate'] = Template::$cTemplate;
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

        return array(
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
            'TagFilter_arr'          => (isset(self::$TagFilter)) ? self::$TagFilter : array(),
            'SuchFilter_arr'         => (isset(self::$SuchFilter)) ? self::$SuchFilter : array(),
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
        );
    }

    /**
     * check for seo url
     */
    public static function seoCheck()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        self::$uri                 = $uri;
        self::$bSEOMerkmalNotFound = false;
        self::$bKatFilterNotFound  = false;

        if (strpos($uri, 'index.php') === false) {
            executeHook(HOOK_SEOCHECK_ANFANG, array('uri' => &$uri));
            $seite        = 0;
            $hstseo       = '';
            $katseo       = '';
            $xShopurl_arr = parse_url(self::getURL());
            $xBaseurl_arr = parse_url($uri);
            $seo          = (isset($xBaseurl_arr['path'])) ? substr($xBaseurl_arr['path'], (isset($xShopurl_arr['path'])) ? (strlen($xShopurl_arr['path']) + 1) : 1) : false;
            //Fremdparameter
            $seo = extFremdeParameter($seo);
            if ($seo) {
                //change Opera Fix
                if (substr($seo, strlen($seo) - 1, 1) === '?') {
                    $seo = substr($seo, 0, strlen($seo) - 1);
                }
                $nMatch = preg_match('/[^_](' . SEP_SEITE . '([0-9]+))/', $seo, $cMatch_arr, PREG_OFFSET_CAPTURE);
                if ($nMatch !== false && $nMatch == 1) {
                    $seite = intval($cMatch_arr[2][0]);
                    $seo   = substr($seo, 0, $cMatch_arr[1][1]);
                }
                //double content work around
                if (strlen($seo) > 0 && $seite == 1) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . self::getURL() . '/' . $seo);
                    exit();
                }
                $cSEOMerkmal_arr = explode(SEP_MERKMAL, $seo);
                $seo             = $cSEOMerkmal_arr[0];
                $oHersteller_arr = explode(SEP_HST, $seo);
                if (is_array($oHersteller_arr) && count($oHersteller_arr) > 1) {
                    $seo    = $oHersteller_arr[0];
                    $hstseo = $oHersteller_arr[1];
                } else {
                    $seo = $oHersteller_arr[0];
                }
                $oKategorie_arr = explode(SEP_KAT, $seo);
                if (is_array($oKategorie_arr) && count($oKategorie_arr) > 1) {
                    $seo    = $oKategorie_arr[0];
                    $katseo = $oKategorie_arr[1];
                } else {
                    $seo = $oKategorie_arr[0];
                }
                if (intval($seite) > 0) {
                    $_GET['seite'] = intval($seite);
                }
                //split attribute/attribute value
                $oMerkmal_arr = explode(SEP_MM_MMW, $seo);
                if (is_array($oMerkmal_arr) && count($oMerkmal_arr) > 1) {
                    $seo = $oMerkmal_arr[1];
                    //$mmseo = $oMerkmal_arr[0];
                }
                //category filter
                if (strlen($katseo) > 0) {
                    $oSeo = self::DB()->query(
                        "SELECT kKey, cSeo
                            FROM tseo
                            WHERE cSeo = '" . $katseo . "'", 1
                    );

                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $katseo) === 0) {
                        self::$kKategorieFilter = $oSeo->kKey;
                    } else {
                        self::$bKatFilterNotFound = true;
                    }
                }
                //manufacturer filter
                if (strlen($hstseo) > 0) {
                    $oSeo = self::DB()->query(
                        "SELECT kKey, cSeo
                            FROM tseo
                            WHERE cSeo = '" . $hstseo . "'", 1
                    );

                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $hstseo) === 0) {
                        self::$kHerstellerFilter = $oSeo->kKey;
                    }
                }
                //attribute filter
                if (count($cSEOMerkmal_arr) > 1) {
                    $nMerkmalZaehler = 1;
                    foreach ($cSEOMerkmal_arr as $i => $cSEOMerkmal) {
                        if (strlen($cSEOMerkmal) > 0 && $i > 0) {
                            $oSeo = self::DB()->query(
                                "SELECT kKey, kSprache, cKey, cSeo
                                    FROM tseo
                                    WHERE cSeo='" . $cSEOMerkmal . "'", 1
                            );

                            if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $cSEOMerkmal) === 0) {
                                //hänge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann im NAvifilter.
                                $_GET['mf' . $nMerkmalZaehler] = $oSeo->kKey;
                                $nMerkmalZaehler++;
                                self::$bSEOMerkmalNotFound = false;
                            } else {
                                self::$bSEOMerkmalNotFound = true;
                            }
                        }
                    }
                }
                $oSeo = self::DB()->query(
                    "SELECT kKey, kSprache, cKey, cSeo
                        FROM tseo
                        WHERE cSeo = '" . $seo . "'", 1
                );

                //EXPERIMENTAL_MULTILANG_SHOP
                if (isset($oSeo->kSprache) && self::$kSprache !== $oSeo->kSprache && defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    $oSeo->kSprache = self::$kSprache;
                }
                //EXPERIMENTAL_MULTILANG_SHOP END

                //mainwords
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $seo) === 0) {
                    //canonical
                    self::$cCanonicalURL = self::getURL() . '/' . $oSeo->cSeo;

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
                    $kSprache = $oSeo->kSprache;
                    $spr      = (class_exists('Sprache')) ?
                        self::Lang()->getIsoFromLangID($kSprache) :
                        self::DB()->query("SELECT cISO FROM tsprache WHERE kSprache = " . intval($kSprache), 1);
                    $cLang = (isset($spr->cISO)) ? $spr->cISO : null;
                    if ($cLang !== $_SESSION['cISOSprache']) {
                        checkeSpracheWaehrung($cLang);
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
                    $cRP = ' &cRP=' . base64_encode($cRP);
                }
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . self::getURL() . '/navi.php?a=' . $kArtikel . $cRP);
                exit();
            }

            self::setPageType(PAGE_ARTIKEL);
            self::$fileName = 'artikel.php';
        } elseif ((!isset($bSEOMerkmalNotFound) || $bSEOMerkmalNotFound === false) &&
            (!isset($bKatFilterNotFound) || $bKatFilterNotFound === false) &&
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
        } elseif (!self::$kLink) {
            self::$is404         = true;
            self::$AktuelleSeite = '404';
            self::setPageType(PAGE_404);
            //check path
            $cPath = self::getRequestUri();
            $cRequestFile = '/' . ltrim($cPath, '/');
            if (in_array($cRequestFile, [ '/', '/index.php', '/navi.php' ])) {
                $oLink       = self::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_STARTSEITE, 1);
                $kLink       = $oLink->kLink;
                $linkHelper  = LinkHelper::getInstance();
                $Link        = $linkHelper->getPageLink($kLink);
                self::$kLink = $kLink;
                if (isset($Link->nLinkart)) {
                    switch ($Link->nLinkart) {
                        case LINKTYP_STARTSEITE :
                            self::setPageType(PAGE_STARTSEITE);
                            self::$AktuelleSeite = 'STARTSEITE';
                            self::$kLink         = $Link->kLink;
                            break;

                        case LINKTYP_TAGGING :
                            self::setPageType(PAGE_TAGGING);
                            break;

                        case LINKTYP_UMFRAGE :
                            self::setPageType(PAGE_UMFRAGE);
                            break;

                        case LINKTYP_VERSAND :
                            self::setPageType(PAGE_VERSAND);
                            break;

                        case LINKTYP_DATENSCHUTZ :
                            self::setPageType(PAGE_DATENSCHUTZ);
                            break;

                        case LINKTYP_WRB :
                            self::setPageType(PAGE_WRB);
                            break;

                        case LINKTYP_LIVESUCHE :
                            self::setPageType(PAGE_LIVESUCHE);
                            break;

                        case LINKTYP_HERSTELLER :
                            self::setPageType(PAGE_HERSTELLER);
                            break;

                        case LINKTYP_NEWSLETTERARCHIV :
                            self::setPageType(PAGE_NEWSLETTERARCHIV);
                            break;

                        case LINKTYP_GRATISGESCHENK :
                            self::setPageType(PAGE_GRATISGESCHENK);
                            break;

                        case LINKTYP_AUSWAHLASSISTENT :
                            self::setPageType(PAGE_AUSWAHLASSISTENT);
                            break;

                        default:
                            break;

                    }
                }
            } else {
                if (self::Media()->isValidRequest($cPath)) {
                    self::Media()->handleRequest($cPath);
                }
            }
        } else {
            self::$fileName      = 'seite.php';
            self::$AktuelleSeite = 'SEITE';
            self::setPageType(PAGE_EIGENE);
        }
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array $cParameter_arr
     * @param object|null $NaviFilter
     *
     * @return mixed
     */
    public static function buildNaviFilter($cParameter_arr, $NaviFilter = null)
    {
        if ($NaviFilter === null) {
            $NaviFilter = new stdClass();
        }
        $NaviFilter->oSprache_arr = self::Lang()->getLangArray();
        $oSeo                     = null;
        //get active languages
        $oSprache_arr = $NaviFilter->oSprache_arr;
        $bSprache     = (is_array($oSprache_arr) && count($oSprache_arr) > 0);
        //mainwords
        if (isset($cParameter_arr['kKategorie']) && $cParameter_arr['kKategorie'] > 0) {
            if (!isset($NaviFilter->Kategorie)) {
                $NaviFilter->Kategorie = new stdClass();
            }
            $NaviFilter->Kategorie->kKategorie = $cParameter_arr['kKategorie'];
            $oSeo_arr                          = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kKategorie' AND kKey = " . (int)$NaviFilter->Kategorie->kKategorie . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Kategorie->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Kategorie->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oKategorieSprache = self::DB()->query("
                SELECT tkategorie.cName AS cKatName, tkategoriesprache.cName
                    FROM tkategorie
                    LEFT JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                        AND tkategoriesprache.kSprache = " . (int)self::$kSprache . "
                    WHERE tkategorie.kKategorie = " . (int)$NaviFilter->Kategorie->kKategorie, 1
            );
            if (isset($oKategorieSprache->cName) && strlen($oKategorieSprache->cName) > 0) {
                $NaviFilter->Kategorie->cName = $oKategorieSprache->cName;
            } elseif (isset($oKategorieSprache->cKatName) && strlen($oKategorieSprache->cKatName) > 0) {
                $NaviFilter->Kategorie->cName = $oKategorieSprache->cKatName;
            }
        }

        if (isset($cParameter_arr['kHersteller']) && $cParameter_arr['kHersteller'] > 0) {
            if (!isset($NaviFilter->Hersteller)) {
                $NaviFilter->Hersteller = new stdClass();
            }
            $NaviFilter->Hersteller->kHersteller = (int)$cParameter_arr['kHersteller'];
            $oSeo_arr                            = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kHersteller' AND kKey = " . intval($NaviFilter->Hersteller->kHersteller) . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Hersteller->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Hersteller->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oHersteller = self::DB()->select('thersteller', 'kHersteller', (int)$NaviFilter->Hersteller->kHersteller, null, null, null, null, false, 'cName');
            if (!empty($oHersteller->cName)) {
                $NaviFilter->Hersteller->cName = $oHersteller->cName;
            }
        }

        if (isset($cParameter_arr['kSuchanfrage']) && $cParameter_arr['kSuchanfrage'] > 0) {
            if (!isset($NaviFilter->Suchanfrage)) {
                $NaviFilter->Suchanfrage = new stdClass();
            }
            $NaviFilter->Suchanfrage->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
            $oSeo_arr                              = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kSuchanfrage' AND kKey = " . (int)$NaviFilter->Suchanfrage->kSuchanfrage . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oSuchanfrage = self::DB()->select('tsuchanfrage', 'kSuchanfrage', $NaviFilter->Suchanfrage->kSuchanfrage, 'kSprache', self::$kSprache, null, null, false, 'cSuche');
            if (!empty($oSuchanfrage->cSuche)) {
                $NaviFilter->Suchanfrage->cName = $oSuchanfrage->cSuche;
            }
        }

        if (isset($cParameter_arr['kMerkmalWert']) && $cParameter_arr['kMerkmalWert'] > 0) {
            if (!isset($NaviFilter->MerkmalWert)) {
                $NaviFilter->MerkmalWert = new stdClass();
            }
            $NaviFilter->MerkmalWert->kMerkmalWert = (int)$cParameter_arr['kMerkmalWert'];
            $oSeo_arr                              = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey='kMerkmalWert' AND kKey = " . $NaviFilter->MerkmalWert->kMerkmalWert . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->MerkmalWert->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->MerkmalWert->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oSQL            = new stdClass();
            $oSQL->cMMSelect = "tmerkmal.cName";
            $oSQL->cMMJOIN   = '';
            $oSQL->cMMWhere  = '';
            if (self::$kSprache > 0 && !standardspracheAktiv()) {
                $oSQL->cMMSelect = "tmerkmalsprache.cName, tmerkmal.cName AS cMMName";
                $oSQL->cMMJOIN   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                        AND tmerkmalsprache.kSprache = " . (int)self::$kSprache;
            }
            $oSQL->cMMWhere = "tmerkmalwert.kMerkmalWert = '" . $NaviFilter->MerkmalWert->kMerkmalWert . "'";
            if (isset($cParameter_arr['MerkmalFilter_arr']) && is_array($cParameter_arr['MerkmalFilter_arr']) && count($cParameter_arr['MerkmalFilter_arr']) > 0) {
                foreach ($cParameter_arr['MerkmalFilter_arr'] as $kMerkmalWert) {
                    $oSQL->cMMWhere .= " OR tmerkmalwert.kMerkmalWert = " . (int)$kMerkmalWert . " ";
                }
            }
            $oMerkmalWert_arr = self::DB()->query(
                "SELECT tmerkmalwertsprache.cWert, " . $oSQL->cMMSelect . "
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND kSprache = " . self::$kSprache . "
                JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                " . $oSQL->cMMJOIN . "
                WHERE " . $oSQL->cMMWhere, 2
            );
            if (is_array($oMerkmalWert_arr) && (count($oMerkmalWert_arr)) > 0) {
                $oMerkmalWert = $oMerkmalWert_arr[0];
                unset($oMerkmalWert_arr[0]);
                if (isset($oMerkmalWert->cWert) && strlen($oMerkmalWert->cWert) > 0) {
                    if (!isset($NaviFilter->MerkmalWert)) {
                        $NaviFilter->MerkmalWert = new stdClass();
                    }
                    if (isset($oMerkmalWert->cName) && strlen($oMerkmalWert->cName) > 0) {
                        $NaviFilter->MerkmalWert->cName = $oMerkmalWert->cName . ": " . $oMerkmalWert->cWert;
                    } elseif (isset($oMerkmalWert->cMMName) && strlen($oMerkmalWert->cMMName) > 0) {
                        $NaviFilter->MerkmalWert->cName = $oMerkmalWert->cMMName . ": " . $oMerkmalWert->cWert;
                    }
                    if (count($oMerkmalWert_arr) > 0) {
                        foreach ($oMerkmalWert_arr as $oTmpMerkmal) {
                            if (isset($oTmpMerkmal->cName) && strlen($oTmpMerkmal->cName) > 0) {
                                $NaviFilter->MerkmalWert->cName .= ", " . $oTmpMerkmal->cName . ": " . $oTmpMerkmal->cWert;
                            } elseif (isset($oTmpMerkmal->cMMName) && strlen($oTmpMerkmal->cMMName) > 0) {
                                $NaviFilter->MerkmalWert->cName .= ", " . $oTmpMerkmal->cMMName . ": " . $oTmpMerkmal->cWert;
                            }
                        }
                    }
                }
            }
        }

        if (isset($cParameter_arr['kTag']) && $cParameter_arr['kTag'] > 0) {
            if (!isset($NaviFilter->Tag)) {
                $NaviFilter->Tag = new stdClass();
            }
            $NaviFilter->Tag->kTag = (int)$cParameter_arr['kTag'];
            $oSeo_arr              = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kTag' AND kKey = " . $NaviFilter->Tag->kTag . "
                    ORDER BY kSprache", 2
            );
            if (isset($oSeo->cSeo)) {
                $NaviFilter->Tag->cSeo = $oSeo->cSeo;
            }
            if (isset($bSprache)) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Tag->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Tag->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oTag = self::DB()->select('ttag', 'kTag', $NaviFilter->Tag->kTag, 'kSprache', self::$kSprache, null, null, false, 'cName');
            if (!empty($oTag->cName)) {
                $NaviFilter->Tag->cName = $oTag->cName;
            }
        }

        if (isset($cParameter_arr['kNews']) && $cParameter_arr['kNews'] > 0) {
            if (!isset($NaviFilter->News)) {
                $NaviFilter->News = new stdClass();
            }
            $NaviFilter->News->kNews = (int) $cParameter_arr['kNews'];
            $oSeo_arr                = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kNews'
                        AND kKey = " . $NaviFilter->News->kNews . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->News->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->News->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $obj = self::DB()->select('tnews', 'kNews', $NaviFilter->News->kNews, 'kSprache', self::$kSprache, null, null, false, 'cBetreff');
            if (!empty($obj->cBetreff)) {
                $NaviFilter->News->cName = $obj->cBetreff;
            }
        }

        if (isset($cParameter_arr['kNewsMonatsUebersicht']) && $cParameter_arr['kNewsMonatsUebersicht'] > 0) {
            if (!isset($NaviFilter->NewsMonat)) {
                $NaviFilter->NewsMonat = new stdClass();
            }
            $NaviFilter->NewsMonat->kNewsMonatsUebersicht = (int) $cParameter_arr['kNewsMonatsUebersicht'];
            $oSeo_arr                                     = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kNewsMonatsUebersicht'
                        AND kKey = " . $NaviFilter->NewsMonat->kNewsMonatsUebersicht . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->NewsMonat->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->NewsMonat->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $obj = self::DB()->select('tnewsmonatsuebersicht', 'kNewsMonatsUebersicht', $NaviFilter->NewsMonat->kNewsMonatsUebersicht, 'kSprache', self::$kSprache, null, null, false, 'cName');
            if (!empty($obj->cName)) {
                $NaviFilter->NewsMonat->cName = $obj->cName;
            }
        }

        if (isset($cParameter_arr['kNewsKategorie']) && $cParameter_arr['kNewsKategorie'] > 0) {
            if (!isset($NaviFilter->NewsKategorie)) {
                $NaviFilter->NewsKategorie = new stdClass();
            }

            $NaviFilter->NewsKategorie->kNewsKategorie = (int) $cParameter_arr['kNewsKategorie'];
            $oSeo_arr                                  = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kNewsKategorie'
                        AND kKey = " . $NaviFilter->NewsKategorie->kNewsKategorie . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->NewsKategorie->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->NewsKategorie->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $obj = self::DB()->select('tnewskategorie', 'kNewsKategorie', $NaviFilter->NewsKategorie->kNewsKategorie, 'kSprache', self::$kSprache, null, null, false, 'cName');
            if (!empty($obj->cName)) {
                $NaviFilter->NewsKategorie->cName = $obj->cName;
            }
        }
        //search specials
        if (isset($cParameter_arr['kSuchspecial']) && $cParameter_arr['kSuchspecial'] > 0) {
            if (!isset($NaviFilter->Suchspecial)) {
                $NaviFilter->Suchspecial = new stdClass();
            }
            $NaviFilter->Suchspecial->kKey = (int)$cParameter_arr['kSuchspecial'];
            $oSeo_arr                      = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial'
                        AND kKey = " . $NaviFilter->Suchspecial->kKey . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Suchspecial->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Suchspecial->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            switch ($NaviFilter->Suchspecial->kKey) {
                case SEARCHSPECIALS_BESTSELLER:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('bestsellers', 'global');
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('specialOffers', 'global');
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('newProducts', 'global');
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('topOffers', 'global');
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('upcomingProducts', 'global');
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('topReviews', 'global');
                    break;
            }
        }
        //filter
        if (isset($cParameter_arr['kKategorieFilter']) && $cParameter_arr['kKategorieFilter'] > 0 &&
            (!isset($NaviFilter->Kategorie->kKategorie) || $cParameter_arr['kKategorieFilter'] != $NaviFilter->Kategorie->kKategorie)
        ) {
            if (!isset($NaviFilter->KategorieFilter)) {
                $NaviFilter->KategorieFilter = new stdClass();
            }
            $NaviFilter->KategorieFilter->kKategorie = $cParameter_arr['kKategorieFilter'];
            $oSeo_arr                                = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kKategorie' AND kKey = " . (int)$NaviFilter->KategorieFilter->kKategorie . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->KategorieFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->KategorieFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $seo_obj = (isset(self::$kSprache) && self::$kSprache > 0 && !standardspracheAktiv()) ?
                self::DB()->select('tkategoriesprache', 'kKategorie', $NaviFilter->KategorieFilter->kKategorie, 'kSprache', self::$kSprache, null, null, false, 'cName') :
                self::DB()->select('tkategorie', 'kKategorie', $NaviFilter->KategorieFilter->kKategorie, null, null, null, null, false, 'cName');
            if (isset($seo_obj->cName) && strlen($seo_obj->cName) > 0) {
                $NaviFilter->KategorieFilter->cName = $seo_obj->cName;
            }
        }

        if ((isset($cParameter_arr['kHerstellerFilter']) && $cParameter_arr['kHerstellerFilter'] > 0) &&
            (!isset($NaviFilter->Hersteller->kHersteller) ||
                $cParameter_arr['kHerstellerFilter'] != $NaviFilter->Hersteller->kHersteller)
        ) {
            if (!isset($NaviFilter->HerstellerFilter)) {
                $NaviFilter->HerstellerFilter = new stdClass();
            }
            $NaviFilter->HerstellerFilter->kHersteller = $cParameter_arr['kHerstellerFilter'];
            $oSeo_arr                                  = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kHersteller' AND kKey = " . (int)$NaviFilter->HerstellerFilter->kHersteller . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->HerstellerFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->HerstellerFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $seo_obj = self::DB()->select('thersteller', 'kHersteller', $NaviFilter->HerstellerFilter->kHersteller, null, null, null, null, false, 'cName');
            if (!empty($seo_obj->cName)) {
                $NaviFilter->HerstellerFilter->cName = $seo_obj->cName;
            }
        }
        $NaviFilter->MerkmalFilter = array();
        if (isset($cParameter_arr['MerkmalFilter_arr']) && is_array($cParameter_arr['MerkmalFilter_arr']) && (($paramCount = count($cParameter_arr['MerkmalFilter_arr'])) > 0)) {
            for ($i = 0; $i < $paramCount; $i++) {
                $oMerkmalWert               = new stdClass();
                $oMerkmalWert->kMerkmalWert = (int)$cParameter_arr['MerkmalFilter_arr'][$i];
                $oMerkmalWert->cSeo         = array();
                if ($oMerkmalWert->kMerkmalWert > 0) {
                    $oSeo_arr = self::DB()->query("
                        SELECT cSeo, kSprache
                            FROM tseo
                            WHERE cKey = 'kMerkmalWert' AND kKey = " . $oMerkmalWert->kMerkmalWert . "
                            ORDER BY kSprache", 2
                    );

                    if ($bSprache) {
                        foreach ($oSprache_arr as $oSprache) {
                            $oMerkmalWert->cSeo[$oSprache->kSprache] = '';
                            if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                                foreach ($oSeo_arr as $oSeo) {
                                    if ($oSprache->kSprache == $oSeo->kSprache) {
                                        $oMerkmalWert->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                                    }
                                }
                            }
                        }
                    }
                    $seo_obj = self::DB()->query("
                        SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
                            FROM tmerkmalwertsprache
                            JOIN tmerkmalwert ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                            WHERE tmerkmalwertsprache.kSprache = " . self::$kSprache . "
                               AND tmerkmalwertsprache.kMerkmalWert = " . $oMerkmalWert->kMerkmalWert, 1
                    );
                    if (!empty($seo_obj->kMerkmal)) {
                        $oMerkmalWert->kMerkmal = $seo_obj->kMerkmal;
                        $oMerkmalWert->cWert    = $seo_obj->cWert;
                        $oMerkmalWert->cName    = $seo_obj->cWert;
                    }
                    $NaviFilter->MerkmalFilter[] = $oMerkmalWert;
                }
            }
        }
        //tag filter
        $tagCount = (isset($cParameter_arr['TagFilter_arr'])) ? count($cParameter_arr['TagFilter_arr']) : 0;
        if (isset($cParameter_arr['TagFilter_arr']) && is_array($cParameter_arr['TagFilter_arr']) && $tagCount > 0) {
            $NaviFilter->TagFilter = array();
            for ($i = 0; $i < $tagCount; $i++) {
                $oTag       = new stdClass();
                $oTag->kTag = $cParameter_arr['TagFilter_arr'][$i];
                $seo_obj    = new stdClass();
                if (isset(self::$kSprache) && self::$kSprache > 0 && $oTag->kTag > 0) {
                    $seo_obj = self::DB()->select('ttag', 'nAktiv', 1, 'kSprache', self::$kSprache, 'kTag', $oTag->kTag, false, 'cName');
                }
                if (!empty($seo_obj->cName)) {
                    $oTag->cName = $seo_obj->cName;
                }
                $NaviFilter->TagFilter[] = $oTag;
            }
        }
        //search filter
        $NaviFilter->SuchFilter = array();
        $sfCount                = (isset($cParameter_arr['SuchFilter_arr'])) ? count($cParameter_arr['SuchFilter_arr']) : 0;
        if (isset($cParameter_arr['SuchFilter_arr']) && is_array($cParameter_arr['SuchFilter_arr']) && $sfCount > 0) {
            for ($i = 0; $i < $sfCount; $i++) {
                if (!isset($NaviFilter->SuchFilter[$i])) {
                    if (!isset($NaviFilter->SuchFilter)) {
                        $NaviFilter->SuchFilter = array();
                    }
                    $NaviFilter->SuchFilter[$i] = new stdClass();
                }
                $NaviFilter->SuchFilter[$i]->kSuchanfrage = (int)$cParameter_arr['SuchFilter_arr'][$i];
                // Namen holen
                $oSuchanfrage = self::DB()->select('tsuchanfrage', 'kSuchanfrage', $NaviFilter->SuchFilter[$i]->kSuchanfrage, 'kSprache', self::$kSprache, null, null, false, 'cSuche');
                if (!empty($oSuchanfrage->cSuche)) {
                    $NaviFilter->SuchFilter[$i]->cName = $oSuchanfrage->cSuche;
                }
            }
        }
        //rating stars filter
        if (isset($cParameter_arr['nBewertungSterneFilter']) && $cParameter_arr['nBewertungSterneFilter'] > 0) {
            if (!isset($NaviFilter->BewertungFilter)) {
                $NaviFilter->BewertungFilter = new stdClass();
            }
            $NaviFilter->BewertungFilter->nSterne = (int)$cParameter_arr['nBewertungSterneFilter'];
        }
        //price span filter
        if (isset($cParameter_arr['cPreisspannenFilter']) && strlen($cParameter_arr['cPreisspannenFilter']) > 0) {
            if (!isset($NaviFilter->PreisspannenFilter)) {
                $NaviFilter->PreisspannenFilter = new stdClass();
            }
            list($fVon, $fBis) = explode('_', $cParameter_arr['cPreisspannenFilter']);
            if (!isset($NaviFilter->PreisspannenFilter)) {
                $NaviFilter->PreisspannenFilter = new stdClass();
            }
            $NaviFilter->PreisspannenFilter->fVon  = doubleval($fVon);
            $NaviFilter->PreisspannenFilter->fBis  = doubleval($fBis);
            $NaviFilter->PreisspannenFilter->cWert = $NaviFilter->PreisspannenFilter->fVon . '_' . $NaviFilter->PreisspannenFilter->fBis;
            //localize prices
            $NaviFilter->PreisspannenFilter->cVonLocalized = gibPreisLocalizedOhneFaktor($NaviFilter->PreisspannenFilter->fVon);
            $NaviFilter->PreisspannenFilter->cBisLocalized = gibPreisLocalizedOhneFaktor($NaviFilter->PreisspannenFilter->fBis);
        }
        //search special filter
        if (isset($cParameter_arr['kSuchspecialFilter']) && strlen($cParameter_arr['kSuchspecialFilter']) > 0) {
            if (!isset($NaviFilter->SuchspecialFilter)) {
                $NaviFilter->SuchspecialFilter = new stdClass();
            }
            $oSeo_arr = self::DB()->query(
                "SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial'
                        AND kKey = " . (int)$cParameter_arr['kSuchspecialFilter'] . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->SuchspecialFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->SuchspecialFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $NaviFilter->SuchspecialFilter->kKey = $cParameter_arr['kSuchspecialFilter'];
            //get names
            switch ($NaviFilter->SuchspecialFilter->kKey) {
                case SEARCHSPECIALS_BESTSELLER:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('bestsellers', 'global');
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('specialOffers', 'global');
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('newProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('topOffers', 'global');
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('upcomingProducts', 'global');
                    break;

            }
        }
        //sorting
        if (isset($cParameter_arr['nSortierung']) && $cParameter_arr['nSortierung'] > 0) {
            $NaviFilter->nSortierung = (int)$cParameter_arr['nSortierung'];
        }
        //number of products per page
        if (isset($cParameter_arr['nArtikelProSeite']) && $cParameter_arr['nArtikelProSeite'] > 0) {
            $NaviFilter->nAnzahlProSeite = (int)$cParameter_arr['nArtikelProSeite'];
        }
        //search
        if (isset($cParameter_arr['cSuche']) && strlen($cParameter_arr['cSuche']) > 0) {
            $cParameter_arr['cSuche'] = StringHandler::filterXSS($cParameter_arr['cSuche']);
            if (!isset($NaviFilter->Suche)) {
                $NaviFilter->Suche = new stdClass();
            }
            $NaviFilter->Suche->cSuche = $cParameter_arr['cSuche'];
            if (!isset($NaviFilter->EchteSuche)) {
                $NaviFilter->EchteSuche = new stdClass();
            }
            $NaviFilter->EchteSuche->cSuche = $cParameter_arr['cSuche'];
        }
        //page
        $NaviFilter->nSeite = verifyGPCDataInteger('seite');
        if (!isset($NaviFilter->nSeite) || !$NaviFilter->nSeite) {
            $NaviFilter->nSeite = 1;
        }
        if (!function_exists('gibAnzahlFilter')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
        }
        $NaviFilter->nAnzahlFilter = gibAnzahlFilter($NaviFilter);
        self::$NaviFilter          = $NaviFilter;

        return $NaviFilter;
    }

    /**
     * @return int
     */
    public static function getShopVersion()
    {
        $oVersion = self::DB()->query("SELECT nVersion FROM tversion", 1);

        return (isset($oVersion->nVersion) && intval($oVersion->nVersion) > 0) ? intval($oVersion->nVersion) : 0;
    }

    /**
     * Return version of files
     *
     * @return int
     */
    public static function getVersion()
    {
        return intval(JTL_VERSION);
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
        $conf = self::getSettings(array(CONF_LOGO));
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

        return rtrim($cShopURL, '/');
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
        executeHook(HOOK_SHOP_SET_PAGE_TYPE, array('pageType' => $pageType));
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
}
