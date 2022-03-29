<?php declare(strict_types=1);

namespace JTL;

use Exception;
use JTL\Catalog\Category\Kategorie;
use JTL\Consent\Manager;
use JTL\Consent\ManagerInterface;
use JTL\Events\Dispatcher;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Mapper\PageTypeToPageName;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Router\ControllerFactory;
use JTL\Router\Router;
use JTL\Router\State as RoutingState;
use JTL\Services\DefaultServicesInterface;
use JTL\Services\Factory;
use JTL\Session\Frontend;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;
use stdClass;
use function Functional\first;
use function Functional\map;
use function Functional\tail;

/**
 * Class Shop
 * @package JTL
 * @method static LanguageHelper Lang()
 * @method static Smarty\JTLSmarty Smarty(bool $fast_init = false, string $context = ContextType::FRONTEND)
 * @method static bool has(string $key)
 * @method static Shop set(string $key, mixed $value)
 * @method static null|mixed get($key)
 */
final class Shop extends ShopBC
{
    /**
     * @var string
     */
    public static string $cISO = '';

    /**
     * @var int
     */
    public static int $kSprache = 0;

    /**
     * @var DefaultServicesInterface|null
     */
    private static ?DefaultServicesInterface $container = null;

    /**
     * @var null|Shop
     */
    private static ?Shop $instance = null;

    /**
     * @var string|null
     */
    private static ?string $imageBaseURL = null;

    /**
     * @var array
     */
    private static array $url = [];

    /**
     * @var bool
     */
    private static bool $isFrontend = true;

    /**
     * @var string
     */
    public static string $uri = '';

    /**
     * @var null|bool
     */
    protected static ?bool $logged = null;

    /**
     * @var null|string
     */
    protected static ?string $adminToken = null;

    /**
     * @var null|string
     */
    protected static ?string $adminLangTag = null;

    /**
     * @var array
     */
    private array $registry = [];

    /**
     * @var ProductFilter|null
     */
    public static ?ProductFilter $productFilter = null;

    /**
     * @var RoutingState
     */
    private static RoutingState $state;

    /**
     * @var array
     */
    private static array $mapping = [
        'Lang'   => 'getLanguageHelper',
        'Smarty' => 'getSmarty',
        'has'    => 'registryHas',
        'set'    => 'registrySet',
        'get'    => 'registryGet'
    ];

    /**
     *
     */
    private function __construct()
    {
        self::$state    = new RoutingState();
        self::$instance = $this;
    }

    /**
     * @return Shop
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * object wrapper - this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? \call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper - this allows to call Shop::Container()->getDB()->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? \call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param string $key
     * @return null|mixed
     */
    public function registryGet(string $key)
    {
        return $this->registry[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function registrySet(string $key, $value): self
    {
        $this->registry[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function registryHas(string $key): bool
    {
        return isset($this->registry[$key]);
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
     * @param string $url
     */
    public static function setImageBaseURL(string $url): void
    {
        self::$imageBaseURL = \rtrim($url, '/') . '/';
    }

    /**
     * @return string
     */
    public static function getImageBaseURL(): string
    {
        if (self::$imageBaseURL === null) {
            self::setImageBaseURL(\defined('IMAGE_BASE_URL') ? \IMAGE_BASE_URL : self::getURL());
        }

        return self::$imageBaseURL;
    }

    /**
     * get language instance
     *
     * @return LanguageHelper
     */
    public function getLanguageHelper(): LanguageHelper
    {
        return LanguageHelper::getInstance();
    }

    /**
     * @param bool        $fast
     * @param string|null $context
     * @return JTLSmarty
     */
    public function getSmarty(bool $fast = false, string $context = null): JTLSmarty
    {
        $context = $context ?? (self::isFrontend() ? ContextType::FRONTEND : ContextType::BACKEND);

        return JTLSmarty::getInstance($fast, $context);
    }

    /**
     * quick&dirty debugging
     *
     * @param mixed           $var - the variable to debug
     * @param bool            $die - set true to die() afterwards
     * @param null|int|string $beforeString - a prefix string
     * @param int             $backtrace - backtrace depth
     */
    public static function dbg($var, bool $die = false, $beforeString = null, int $backtrace = 0): void
    {
        $nl     = \PHP_SAPI === 'cli' ? \PHP_EOL : '<br>';
        $trace  = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace);
        $callee = first($trace);
        $info   = \pathinfo($callee['file']);
        echo $info['basename'] . ':' . $callee['line'] . ' ';
        if ($beforeString !== null) {
            echo $beforeString . $nl;
        }
        if (\PHP_SAPI !== 'cli') {
            echo '<pre>';
        }
        \var_dump($var);
        if ($backtrace > 0) {
            echo $nl . 'Backtrace:' . $nl;
            \var_dump(tail($trace));
        }
        if (\PHP_SAPI !== 'cli') {
            echo '</pre>';
        }
        if ($die === true) {
            die();
        }
    }

    /**
     * get current language/language ISO
     *
     * @return int|string
     * @var bool $iso
     */
    public static function getLanguage(bool $iso = false)
    {
        return $iso === false ? self::$kSprache : self::$cISO;
    }

    /**
     * get current language/language ISO
     *
     * @return int
     * @var bool $iso
     */
    public static function getLanguageID(): int
    {
        return self::$kSprache;
    }

    /**
     * get current language/language ISO
     *
     * @return string|null
     * @var bool $iso
     */
    public static function getLanguageCode(): ?string
    {
        return self::$cISO;
    }

    /**
     * set language/language ISO
     *
     * @param int         $languageID
     * @param string|null $iso
     */
    public static function setLanguage(int $languageID, string $iso = null): void
    {
        self::$kSprache = $languageID;
        if ($iso !== null) {
            self::$cISO = $iso;
        }
    }

    /**
     * @param array|int $config
     * @return array
     */
    public static function getSettings($config): array
    {
        return Shopsetting::getInstance()->getSettings($config);
    }

    /**
     * @param int $sectionID
     * @return array|null
     */
    public static function getSettingSection(int $sectionID): ?array
    {
        return Shopsetting::getInstance()->getSection($sectionID);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public static function getSettingValue(int $section, string $option)
    {
        return Shopsetting::getInstance()->getValue($section, $option);
    }

    /**
     * Load plugin event driven system
     * @param bool $isFrontend
     */
    public static function bootstrap(bool $isFrontend = true): void
    {
        self::$isFrontend = $isFrontend;
        if (\SAFE_MODE === true) {
            return;
        }
        $db      = self::Container()->getDB();
        $cache   = self::Container()->getCache();
        $cacheID = 'plgnbtsrp';
        if (($plugins = $cache->get($cacheID)) === false) {
            $plugins = map($db->getObjects(
                'SELECT kPlugin, bBootstrap, bExtension
                    FROM tplugin
                    WHERE nStatus = :state
                      AND bBootstrap = 1
                    ORDER BY nPrio ASC',
                ['state' => State::ACTIVATED]
            ) ?: [], static function ($e) {
                $e->kPlugin    = (int)$e->kPlugin;
                $e->bBootstrap = (int)$e->bBootstrap;
                $e->bExtension = (int)$e->bExtension;

                return $e;
            });
            $cache->set($cacheID, $plugins, [\CACHING_GROUP_PLUGIN]);
        }
        $dispatcher      = Dispatcher::getInstance();
        $extensionLoader = new PluginLoader($db, $cache);
        $pluginLoader    = new LegacyPluginLoader($db, $cache);
        foreach ($plugins as $plugin) {
            $loader = $plugin->bExtension === 1 ? $extensionLoader : $pluginLoader;
            if (($p = PluginHelper::bootstrap($plugin->kPlugin, $loader)) !== null) {
                $p->boot($dispatcher);
                $p->loaded();
            }
        }
    }

    /**
     * @return bool
     */
    public static function isFrontend(): bool
    {
        return self::$isFrontend === true;
    }

    /**
     * @param bool $isFrontend
     */
    public static function setIsFrontend(bool $isFrontend): void
    {
        self::$isFrontend = $isFrontend;
    }

    /**
     * @return void
     */
    public static function run(): void
    {
        if (Request::postVar('action') === 'updateconsent' && Form::validateToken()) {
            $manager = new Manager(self::Container()->getDB());
            $res     = (object)['status' => 'OK', 'data' => $manager->save(Request::postVar('data'))];
            die(\json_encode($res, \JSON_THROW_ON_ERROR));
        }
        $router      = new Router(self::Container()->getDB(), new RoutingState());
        self::$state = $router->init();
        $params = self::$state->getAsParams();
        self::setParams($params);
        if (self::$state->productsPerPage !== 0) {
            $_SESSION['ArtikelProSeite'] = self::$state->productsPerPage;
        }
        self::$isInitialized = true;
        self::Container()->get(ManagerInterface::class)->initActiveItems(self::getLanguageID());
        $conf = new Config();
        $conf->setLanguageID(self::getLanguageID());
        $conf->setLanguages(LanguageHelper::getInstance()->getLangArray());
        $conf->setCustomerGroupID(Frontend::getCustomerGroup()->getID());
        $conf->setConfig(Shopsetting::getInstance()->getAll());
        $conf->setBaseURL(self::getURL() . '/');
        self::setImageBaseURL(\defined('IMAGE_BASE_URL') ? \IMAGE_BASE_URL : self::getURL());
        self::$productFilter = new ProductFilter($conf, self::Container()->getDB(), self::Container()->getCache());
        self::getLanguageFromServerName();
        $router->dispatch();
    }

    /**
     * @return RoutingState
     */
    public static function getState(): RoutingState
    {
        return self::$state;
    }

    public static function validateState(): void
    {
        if (self::$state->categoryID > 0
            && !Kategorie::isVisible(self::$state->categoryID, Frontend::getCustomerGroup()->getID())
        ) {
            self::$state->categoryID = 0;
            self::$state->is404 = true;
            self::$kKategorie = 0;
            self::$is404 = true;
        }
        if (Product::isVariChild(self::$state->productID)) {
            self::$state->childProductID = self::$state->productID;
            self::$state->productID      = Product::getParent(self::$state->productID);
            self::$kVariKindArtikel = self::$state->childProductID;
            self::$kArtikel = self::$state->productID;
        }
        if (self::$state->productID > 0) {
            $redirect = Request::verifyGPCDataInt('r');
            if ($redirect > 0
                && (self::$state->newsItemID > 0 // get param "n" was used as product amount
                    || (isset($_GET['n']) && (float)$_GET['n'] > 0)) // product amount was a float >0 and <1
            ) {
                // GET param "n" is often misused as "amount of product"
                self::$state->newsItemID = 0;
                if ($redirect === \R_LOGIN_WUNSCHLISTE) {
                    // login redirect on wishlist add when not logged in uses get param "n" as amount
                    // and "a" for the product ID - but we want to go to the login page, not to the product page
                    self::$state->productID = 0;
                    self::$kArtikel = 0;
                }
            } elseif (($redirect === \R_LOGIN_BEWERTUNG || $redirect === \R_LOGIN_TAG)
                && Frontend::getCustomer()->getID() > 0
            ) {
                // avoid redirect to product page for ratings that require logged in customers
                self::$state->productID = 0;
                self::$kArtikel = 0;
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    public static function setParams(array $params): void
    {
        foreach ($params as $key => $val) {
            if (\property_exists(__CLASS__, $key)) {
                self::${$key} = $val;
            }
        }
    }

    /**
     * @return array
     */
    public static function getParameters(): array
    {
        return self::$state->getAsParams();
    }

    private static function getLanguageFromServerName(): void
    {
        if (\EXPERIMENTAL_MULTILANG_SHOP !== true) {
            return;
        }
        foreach (Frontend::getLanguages() as $language) {
            $code    = \mb_convert_case($language->getCode(), \MB_CASE_UPPER);
            $shopURL = \defined('URL_SHOP_' . $code) ? \constant('URL_SHOP_' . $code) : \URL_SHOP;
            if ($_SERVER['HTTP_HOST'] === \parse_url($shopURL)['host']) {
                self::setLanguage($language->getId(), $language->getCode());
                break;
            }
        }
    }

    /**
     * check for seo url
     * @deprecated since 5.2.0
     */
    public static function seoCheck(): void
    {
//        self::getLanguageFromServerName();
//        $uri = self::$uri;
//        \executeHook(\HOOK_SEOCHECK_ANFANG, ['uri' => &$uri]);
//        if (\mb_strpos($uri, '/') === 0) {
//            $uri = \mb_substr($uri, 1);
//        }
//        self::seoCheckFinish();
    }

    public static function seoCheckFinish(): void
    {
//        self::dbg($_GET, false, __METHOD__);
        self::$MerkmalFilter                  = ProductFilter::initCharacteristicFilter();
        self::$SuchFilter                     = ProductFilter::initSearchFilter();
        self::$categoryFilterIDs              = ProductFilter::initCategoryFilter();
        self::$state->characteristicFilterIDs = self::$MerkmalFilter;
        self::$state->searchFilterIDs         = self::$SuchFilter;
        self::$state->categoryFilterIDs       = self::$categoryFilterIDs;

        \executeHook(\HOOK_SEOCHECK_ENDE);
    }

    /**
     * @param int $languageID
     */
    public static function updateLanguage(int $languageID): void
    {
        $iso = self::Lang()->getIsoFromLangID($languageID)->cISO ?? '';
        if ($iso !== $_SESSION['cISOSprache']) {
            Frontend::checkReset($iso);
            Tax::setTaxRates();
        }
        if (self::$productFilter->getFilterConfig()->getLanguageID() !== $languageID) {
            self::$productFilter->getFilterConfig()->setLanguageID($languageID);
            self::$productFilter->initBaseStates();
        }
        $customer     = Frontend::getCustomer();
        $customerLang = $customer->getLanguageID();
        if ($customerLang > 0 && $customerLang !== $languageID) {
            $customer->setLanguageID($languageID);
            $customer->updateInDB();
        }
    }

    /**
     * decide which page to load
     * @return string|null
     */
    public static function getEntryPoint(): ?string
    {
        $cf             = new ControllerFactory(self::$state, self::Container()->getDB());
        self::$fileName = $cf->getEntryPoint();
        self::setPageType(self::$state->pageType);
        die('pdd');

        return self::$fileName;
    }

    /**
     * @return bool
     */
    public static function check404(): bool
    {
        if (self::$state->is404 !== true) {
            return false;
        }
        \executeHook(\HOOK_INDEX_SEO_404, ['seo' => self::getRequestUri()]);
        if (!self::$state->linkID) {
            $hookInfos = Redirect::urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => self::$state->linkID
            ]);
            $linkID    = $hookInfos['value'];
            if (!$linkID) {
                self::$state->linkID = self::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404) ?: 0;
                self::$kLink = self::$state->linkID;
            }
        }

        return true;
    }

    /**
     * build product filter object from parameters
     *
     * @param array                       $params
     * @param stdClass|null|ProductFilter $productFilter
     * @param bool                        $validate
     * @return ProductFilter
     */
    public static function buildProductFilter(
        array $params,
        $productFilter = null,
        bool $validate = true
    ): ProductFilter {
        $pf = new ProductFilter(
            Config::getDefault(),
            self::Container()->getDB(),
            self::Container()->getCache()
        );
        if ($productFilter !== null) {
            foreach (\get_object_vars($productFilter) as $k => $v) {
                $pf->$k = $v;
            }
        }

        return $pf->initStates($params, $validate);
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
    public static function setProductFilter(ProductFilter $productFilter): void
    {
        self::$productFilter = $productFilter;
    }

    /**
     * @return Version
     */
    public static function getShopDatabaseVersion(): Version
    {
        $version = self::Container()->getDB()->getSingleObject('SELECT nVersion FROM tversion')->nVersion;

        if ($version === '5' || $version === 5) {
            $version = '5.0.0';
        }

        return Version::parse($version);
    }

    /**
     * Return version of files
     *
     * @return string
     */
    public static function getApplicationVersion(): string
    {
        return \APPLICATION_VERSION;
    }

    /**
     * get logo from db, fallback to first file in logo dir
     *
     * @return string|null - image path/null if no logo was found
     * @var bool $fullURL - prepend shop url if set to true
     */
    public static function getLogo(bool $fullUrl = false): ?string
    {
        $ret  = null;
        $logo = self::getSettingValue(\CONF_LOGO, 'shop_logo');
        if ($logo !== null && $logo !== '') {
            $ret = \PFAD_SHOPLOGO . $logo;
        } elseif (\is_dir(\PFAD_ROOT . \PFAD_SHOPLOGO)) {
            $dir = \opendir(\PFAD_ROOT . \PFAD_SHOPLOGO);
            if (!$dir) {
                return '';
            }
            while (($file = \readdir($dir)) !== false) {
                if ($file !== '.' && $file !== '..' && \mb_strpos($file, \SHOPLOGO_NAME) !== false) {
                    $ret = \PFAD_SHOPLOGO . $file;
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
     * @param array $urls
     */
    public static function setURLs(array $urls): void
    {
        self::$url = $urls;
    }

    /**
     * @param bool     $forceSSL
     * @param int|null $langID
     * @return string - the shop URL without trailing slash
     */
    public static function getURL(bool $forceSSL = false, int $langID = null): string
    {
        $langID = $langID ?? self::$kSprache;
        $idx    = (int)$forceSSL;
        if (isset(self::$url[$langID][$idx]) && self::isFrontend()) {
            return self::$url[$langID][$idx];
        }
        $url                      = self::buildBaseURL($forceSSL);
        self::$url[$langID][$idx] = $url;

        return $url;
    }

    /**
     * @param bool $forceSSL
     * @return string - the shop Admin URL without trailing slash
     */
    public static function getAdminURL(bool $forceSSL = false): string
    {
        return \rtrim(self::buildBaseURL($forceSSL) . '/' . \PFAD_ADMIN, '/');
    }

    /**
     * @param bool $forceSSL
     * @return string
     */
    private static function buildBaseURL(bool $forceSSL): string
    {
        $url = \URL_SHOP;
        if (\mb_strpos($url, 'http://') === 0) {
            $sslStatus = Request::checkSSL();
            if ($sslStatus === 2) {
                $url = \str_replace('http://', 'https://', $url);
            } elseif ($sslStatus === 4 || ($sslStatus === 3 && $forceSSL)) {
                $url = \str_replace('http://', 'https://', $url);
            }
        }

        return \rtrim($url, '/');
    }

    /**
     * @param int $pageType
     */
    public static function setPageType(int $pageType): void
    {
        $mapper                = new PageTypeToPageName();
        self::$pageType        = $pageType;
        self::$state->pageType = $pageType;
        self::$AktuelleSeite   = $mapper->map($pageType);
        \executeHook(\HOOK_SHOP_SET_PAGE_TYPE, [
            'pageType' => self::$pageType,
            'pageName' => self::$AktuelleSeite
        ]);
    }

    /**
     * @return int
     */
    public static function getPageType(): int
    {
        return self::$state->pageType ?? \PAGE_UNBEKANNT;
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public static function getRequestUri(bool $decoded = false): string
    {
        $shopURLdata = \parse_url(self::getURL());
        $baseURLdata = \parse_url(self::getRequestURL());

        $uri = isset($baseURLdata['path'])
            ? \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path'] ?? '') + 1)
            : '';
        $uri = '/' . $uri;

        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @param bool $sessionSwitchAllowed
     * @return bool
     */
    public static function isAdmin(bool $sessionSwitchAllowed = false): bool
    {
        if (\is_bool(self::$logged)) {
            return self::$logged;
        }
        if (\session_name() === 'eSIdAdm') {
            // admin session already active
            self::$logged       = self::Container()->getAdminAccount()->logged();
            self::$adminToken   = $_SESSION['jtl_token'];
            self::$adminLangTag = $_SESSION['AdminAccount']->language;
        } elseif (!empty($_SESSION['loggedAsAdmin']) && $_SESSION['loggedAsAdmin'] === true) {
            // frontend session has been notified by admin session
            self::$logged       = true;
            self::$adminToken   = $_SESSION['adminToken'];
            self::$adminLangTag = $_SESSION['adminLangTag'];
            self::Container()->getGetText()->setLanguage(self::$adminLangTag);
        } elseif ($sessionSwitchAllowed === true
            && isset($_COOKIE['eSIdAdm'])
            && Request::verifyGPDataString('fromAdmin') === 'yes'
        ) {
            // frontend session has not been notified yet
            // try to fetch information autonomously
            $frontendId = \session_id();
            \session_write_close();
            \session_name('eSIdAdm');
            \session_id($_COOKIE['eSIdAdm']);
            \session_start();
            self::$logged = $_SESSION['loginIsValid'] ?? null;

            if (isset($_SESSION['jtl_token'], $_SESSION['AdminAccount'])) {
                $adminToken                   = $_SESSION['jtl_token'];
                $adminLangTag                 = $_SESSION['AdminAccount']->language;
                $_SESSION['frontendUpToDate'] = true;

                if (self::$logged) {
                    self::Container()->getGetText();
                }
            } else {
                $adminToken   = null;
                $adminLangTag = null;
            }

            \session_write_close();
            \session_name('JTLSHOP');
            \session_id($frontendId);
            \session_start();
            self::$adminToken          = $_SESSION['adminToken'] = $adminToken;
            self::$adminLangTag        = $_SESSION['adminLangTag'] = $adminLangTag;
            $_SESSION['loggedAsAdmin'] = self::$logged;
        } else {
            // no information about admin session available
            self::$logged       = null;
            self::$adminToken   = null;
            self::$adminLangTag = null;
        }

        return self::$logged ?? false;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public static function getAdminSessionToken(): ?string
    {
        if (self::isAdmin()) {
            return self::$adminToken;
        }

        return null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public static function getCurAdminLangTag(): ?string
    {
        if (self::isAdmin()) {
            return self::$adminLangTag;
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function isBrandfree(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_BRANDFREE);
    }

    /**
     * Get the default container of the jtl shop
     *
     * @return DefaultServicesInterface
     */
    public static function Container(): DefaultServicesInterface
    {
        if (!self::$container) {
            $factory         = new Factory();
            self::$container = $factory->createContainers();
        }

        return self::$container;
    }

    /**
     * @param bool $admin
     * @return string
     */
    public static function getFaviconURL(bool $admin = false): string
    {
        if ($admin) {
            $faviconUrl = self::getAdminURL();
            if (\file_exists(\PFAD_ROOT . \PFAD_ADMIN . 'favicon.ico')) {
                $faviconUrl .= '/favicon.ico';
            } else {
                $faviconUrl .= '/favicon-default.ico';
            }
        } else {
            $smarty           = JTLSmarty::getInstance();
            $templateDir      = $smarty->getTemplateDir($smarty->context);
            $shopTemplatePath = $smarty->getTemplateUrlPath();
            $faviconUrl       = self::getURL() . '/';

            if (\file_exists($templateDir . 'themes/base/images/favicon.ico')) {
                $faviconUrl .= $shopTemplatePath . 'themes/base/images/favicon.ico';
            } elseif (\file_exists($templateDir . 'favicon.ico')) {
                $faviconUrl .= $shopTemplatePath . 'favicon.ico';
            } elseif (\file_exists(\PFAD_ROOT . 'favicon.ico')) {
                $faviconUrl .= 'favicon.ico';
            } else {
                $faviconUrl .= 'favicon-default.ico';
            }
        }

        return $faviconUrl;
    }

    /**
     * @return string
     * @throws Exceptions\CircularReferenceException
     * @throws Exceptions\ServiceNotFoundException
     */
    public static function getHomeURL(): string
    {
        $homeURL = self::getURL() . '/';
        try {
            if (!LanguageHelper::isDefaultLanguageActive()) {
                $homeURL = self::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE)->getURL();
            }
        } catch (SpecialPageNotFoundException $e) {
            self::Container()->getLogService()->error($e->getMessage());
        }

        return $homeURL;
    }

    /**
     * @return string
     */
    public static function getRequestURL(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'] ?? '');
    }
}
