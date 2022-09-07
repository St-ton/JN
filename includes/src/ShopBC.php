<?php declare(strict_types=1);

namespace JTL;

use JTL\Cache\JTLCacheInterface;
use JTL\Events\Dispatcher;
use JTL\Filter\FilterInterface;
use JTL\Language\LanguageHelper;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Smarty\JTLSmarty;

/**
 * Class ShopBC
 * @package JTL
 */
class ShopBC
{
    /**
     * @var int
     */
    public static int $kKonfigPos = 0;

    /**
     * @var int
     */
    public static int $kKategorie = 0;

    /**
     * @var int
     */
    public static int $kArtikel = 0;

    /**
     * @var int
     */
    public static int $kVariKindArtikel = 0;

    /**
     * @var int
     */
    public static int $kSeite = 0;

    /**
     * @var int
     */
    public static int $kLink = 0;

    /**
     * @var int
     */
    public static int $nLinkart = 0;

    /**
     * @var int
     */
    public static int $kHersteller = 0;

    /**
     * @var int
     */
    public static int $kSuchanfrage = 0;

    /**
     * @var int
     */
    public static int $kMerkmalWert = 0;

    /**
     * @var int
     */
    public static int $kSuchspecial = 0;

    /**
     * @var int
     */
    public static int $kNews = 0;

    /**
     * @var int
     */
    public static int $kNewsMonatsUebersicht = 0;

    /**
     * @var int
     */
    public static int $kNewsKategorie = 0;

    /**
     * @var int
     */
    public static int $nBewertungSterneFilter = 0;

    /**
     * @var string
     */
    public static string $cPreisspannenFilter = '';

    /**
     * @var int
     */
    public static int $kHerstellerFilter = 0;

    /**
     * @var int[]
     */
    public static array $manufacturerFilterIDs = [];

    /**
     * @var int[]
     */
    public static array $categoryFilterIDs = [];

    /**
     * @var int
     */
    public static int $kKategorieFilter = 0;

    /**
     * @var int
     */
    public static int $kSuchspecialFilter = 0;

    /**
     * @var int[]
     */
    public static array $searchSpecialFilterIDs = [];

    /**
     * @var int
     */
    public static int $kSuchFilter = 0;

    /**
     * @var int
     */
    public static int $nDarstellung = 0;

    /**
     * @var int
     */
    public static int $nSortierung = 0;

    /**
     * @var int
     */
    public static int $nSort = 0;

    /**
     * @var int
     */
    public static int $show = 0;

    /**
     * @var int
     */
    public static int $vergleichsliste = 0;

    /**
     * @var bool
     */
    public static bool $bFileNotFound = false;

    /**
     * @var string
     */
    public static string $cCanonicalURL = '';

    /**
     * @var bool
     */
    public static bool $is404 = false;

    /**
     * @var array
     */
    public static array $MerkmalFilter = [];

    /**
     * @var array
     */
    public static array $SuchFilter = [];

    /**
     * @var int
     */
    public static int $kWunschliste = 0;

    /**
     * @var bool
     */
    public static bool $bSEOMerkmalNotFound = false;

    /**
     * @var bool
     */
    public static bool $bKatFilterNotFound = false;

    /**
     * @var bool
     */
    public static bool $bHerstellerFilterNotFound = false;

    /**
     * @var string|null
     */
    public static ?string $fileName = null;

    /**
     * @var string
     */
    public static string $AktuelleSeite;

    /**
     * @var int
     */
    public static int $pageType = \PAGE_UNBEKANNT;

    /**
     * @var bool
     */
    public static bool $directEntry = true;

    /**
     * @var bool
     */
    public static bool $bSeo = false;

    /**
     * @var bool
     */
    public static bool $isInitialized = false;

    /**
     * @var int
     */
    public static int $nArtikelProSeite = 0;

    /**
     * @var string
     */
    public static string $cSuche = '';

    /**
     * @var int
     */
    public static int $seite = 0;

    /**
     * @var int
     */
    public static int $nSterne = 0;

    /**
     * @var int
     */
    public static int $nNewsKat = 0;

    /**
     * @var string
     */
    public static string $cDatum = '';

    /**
     * @var int
     */
    public static int $nAnzahl = 0;

    /**
     * @var FilterInterface[]
     */
    public static array $customFilters = [];

    /**
     * @var string
     */
    protected static string $optinCode = '';

    /**
     * @param string       $eventName
     * @param array|object $arguments
     * @deprecated since 5.2.0
     */
    public static function fire(string $eventName, $arguments = []): void
    {
        \trigger_error(__METHOD__ . ' is deprecated - use dispatcher directly.', \E_USER_DEPRECATED);
        Dispatcher::getInstance()->fire($eventName, $arguments);
    }

    /**
     * @param array|int $config
     * @return array
     * @deprecated since 5.2.0
     */
    public static function getConfig($config): array
    {
        \trigger_error(__METHOD__ . ' is deprecated - use JTL\Shop::getSettings() instead.', \E_USER_DEPRECATED);
        return Shop::getSettings($config);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     * @deprecated since 5.2.0
     */
    public static function getConfigValue(int $section, string $option)
    {
        \trigger_error(__METHOD__ . ' is deprecated - use JTL\Shop::getSettingValue() instead.', \E_USER_DEPRECATED);
        return Shopsetting::getInstance()->getValue($section, $option);
    }

    /**
     * @return Services\DefaultServicesInterface
     * @deprecated since 5.2.0
     */
    public function _Container()
    {
        //\trigger_error(__METHOD__ . ' is deprecated - use JTL\Shop::Container() instead.', \E_USER_DEPRECATED);
        return Shop::Container();
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getApplicationVersion(): string
    {
        //\trigger_error(__METHOD__ . ' is deprecated - use APPLICATION_VERSION constant instead.', \E_USER_DEPRECATED);
        return \APPLICATION_VERSION;
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getFaviconURL(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $smarty           = JTLSmarty::getInstance();
        $templateDir      = $smarty->getTemplateDir($smarty->context);
        $shopTemplatePath = $smarty->getTemplateUrlPath();
        $faviconUrl       = Shop::getURL() . '/';
        if (\file_exists($templateDir . 'themes/base/images/favicon.ico')) {
            $faviconUrl .= $shopTemplatePath . 'themes/base/images/favicon.ico';
        } elseif (\file_exists($templateDir . 'favicon.ico')) {
            $faviconUrl .= $shopTemplatePath . 'favicon.ico';
        } elseif (\file_exists(\PFAD_ROOT . 'favicon.ico')) {
            $faviconUrl .= 'favicon.ico';
        } else {
            $faviconUrl .= 'favicon-default.ico';
        }

        return $faviconUrl;
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getHomeURL(): string
    {
        $homeURL = Shop::getURL() . '/';
        try {
            if (!LanguageHelper::isDefaultLanguageActive()) {
                $homeURL = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE)?->getURL();
            }
        } catch (SpecialPageNotFoundException $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }

        return $homeURL;
    }

    /**
     * @return bool
     * @deprecated since 5.2.0
     */
    public static function check404(): bool
    {
        $state = Shop::getState();
        if ($state->is404 !== true) {
            return false;
        }
        \executeHook(\HOOK_INDEX_SEO_404, ['seo' => self::getRequestUri()]);
        if (!$state->linkID) {
            $hookInfos = Redirect::urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => $state->linkID
            ]);
            $linkID    = $hookInfos['value'];
            if (!$linkID) {
                $state->linkID = Shop::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404) ?: 0;
                self::$kLink   = $state->linkID;
            }
        }

        return true;
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getRequestUri(bool $decoded = false): string
    {
        $shopPath = \parse_url(Shop::getURL(), \PHP_URL_PATH) ?? '';
        $basePath = \parse_url(self::getRequestURL(), \PHP_URL_PATH);
        $uri      = $basePath !== null
            ? \mb_substr($basePath, \mb_strlen($shopPath) + 1)
            : '';
        $uri      = '/' . $uri;
        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getRequestURL(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * @return JTLCacheInterface
     * @deprecated since 5.0.0
     */
    public static function Cache(): JTLCacheInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated - use JTL\Shop::Container()->getCache().', \E_USER_DEPRECATED);
        return Shop::Container()->getCache();
    }
}
