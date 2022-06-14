<?php declare(strict_types=1);

namespace JTL;

use JTL\Events\Dispatcher;
use JTL\Filter\FilterInterface;

/**
 * Class Shop
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
}
