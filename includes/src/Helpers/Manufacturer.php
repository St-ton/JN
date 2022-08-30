<?php declare(strict_types=1);

namespace JTL\Helpers;

use JTL\Catalog\Hersteller;
use JTL\Contracts\RoutableInterface;
use JTL\Customer\CustomerGroup;
use JTL\Language\LanguageHelper;
use JTL\Router\RoutableTrait;
use JTL\Shop;

/**
 * Class Manufacturer
 * @package JTL\Helpers
 */
class Manufacturer implements RoutableInterface
{
    use RoutableTrait;

    /**
     * @var Manufacturer|null
     */
    private static ?self $instance = null;

    /**
     * @var string
     */
    public string $cacheID;

    /**
     * @var array|null
     */
    public ?array $manufacturers = null;

    /**
     * @var int
     */
    private static int $langID;

    /**
     * Manufacturer constructor.
     */
    public function __construct()
    {
        $lagerfilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $this->cacheID = 'manuf_' . Shop::Container()->getCache()->getBaseID() .
            ($lagerfilter !== '' ? \md5($lagerfilter) : '');
        self::$langID  = Shop::getLanguageID();
        if (self::$langID <= 0) {
            self::$langID = LanguageHelper::getDefaultLanguage()->getId();
        }
        $this->manufacturers = $this->getManufacturers();
        self::$instance      = $this;
    }

    /**
     * @return Manufacturer
     */
    public static function getInstance(): self
    {
        return (self::$instance === null || Shop::getLanguageID() !== self::$langID)
            ? new self()
            : self::$instance;
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        if ($this->manufacturers !== null) {
            return $this->manufacturers;
        }
        if (($manufacturers = Shop::Container()->getCache()->get($this->cacheID)) === false) {
            $stockFilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $manufacturers = Shop::Container()->getDB()->getCollection(
                'SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                        thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                        therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                        tseo.cSeo, thersteller.cSeo AS originalSeo, therstellersprache.kSprache 
                    FROM thersteller
                    LEFT JOIN therstellersprache 
                        ON therstellersprache.kHersteller = thersteller.kHersteller
                    LEFT JOIN tseo 
                        ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = :skey
                        AND tseo.kSprache = therstellersprache.kSprache
                    WHERE EXISTS (
                        SELECT 1
                        FROM tartikel
                        WHERE tartikel.kHersteller = thersteller.kHersteller ' . $stockFilter . '
                            AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = :cgid
                                )
                        )
                    GROUP BY thersteller.kHersteller, therstellersprache.kSprache
                    ORDER BY thersteller.nSortNr, thersteller.cName',
                [
                    'skey' => 'kHersteller',
                    'cgid' => CustomerGroup::getDefaultGroupID()
                ]
            )->groupBy(['kHersteller'])->toArray();
            foreach ($manufacturers as &$manufacturer) {
                $instance = new Hersteller();
                $instance->map($manufacturer);
                $manufacturer = $instance;
            }
            unset($manufacturer);
            $cacheTags = [\CACHING_GROUP_MANUFACTURER, \CACHING_GROUP_CORE];
            \executeHook(\HOOK_GET_MANUFACTURERS, [
                'cached'        => false,
                'cacheTags'     => &$cacheTags,
                'manufacturers' => &$manufacturers
            ]);
            Shop::Container()->getCache()->set($this->cacheID, $manufacturers, $cacheTags);
        } else {
            \executeHook(\HOOK_GET_MANUFACTURERS, [
                'cached'        => true,
                'cacheTags'     => [],
                'manufacturers' => &$manufacturers
            ]);
        }
        $this->manufacturers = $manufacturers;

        return $this->manufacturers;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('thersteller', $attribute, $value);

        return \is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getManufacturerByAttribute(string $attribute, $value, callable $callback = null)
    {
        $mf = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? new Hersteller($res->kHersteller)
            : null;

        return \is_callable($callback)
            ? $callback($mf)
            : $mf;
    }
}
