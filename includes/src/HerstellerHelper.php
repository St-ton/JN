<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class HerstellerHelper
 */
class HerstellerHelper
{
    /**
     * @var HerstellerHelper
     */
    private static $_instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array|mixed
     */
    public $manufacturers;

    /**
     * @var int
     */
    private static $langID;

    /**
     *
     */
    public function __construct()
    {
        $lagerfilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $this->cacheID = 'manuf_' . Shop::Cache()->getBaseID() . ($lagerfilter !== '' ? md5($lagerfilter) : '');
        self::$langID  = Shop::getLanguage();
        if (self::$langID <= 0) {
            if (Shop::getLanguage() > 0) {
                self::$langID = Shop::getLanguage();
            } else {
                $_lang        = gibStandardsprache();
                self::$langID = (int)$_lang->kSprache;
            }
        }
        $this->manufacturers = $this->getManufacturers();
        self::$_instance     = $this;
    }

    /**
     * @return HerstellerHelper
     */
    public static function getInstance()
    {
        return (self::$_instance === null || Shop::getLanguage() !== self::$langID)
            ? new self()
            : self::$_instance;
    }

    /**
     * @return array|mixed
     */
    public function getManufacturers()
    {
        if ($this->manufacturers !== null) {
            return $this->manufacturers;
        }
        if (($manufacturers = Shop::Cache()->get($this->cacheID)) === false) {
            $lagerfilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            // fixes for admin backend
            $manufacturers = Shop::Container()->getDB()->query(
                "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                        thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                        therstellersprache.cMetaDescription, therstellersprache.cBeschreibung, tseo.cSeo
                    FROM thersteller
                    LEFT JOIN therstellersprache 
                        ON therstellersprache.kHersteller = thersteller.kHersteller
                        AND therstellersprache.kSprache = " . self::$langID . "
                    LEFT JOIN tseo 
                        ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . self::$langID . "
                    WHERE EXISTS (
                        SELECT 1
                        FROM tartikel
                        WHERE tartikel.kHersteller = thersteller.kHersteller
                            {$lagerfilter}
                            AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = " . Kundengruppe::getDefaultGroupID() . "
                                )
                        )
                    ORDER BY thersteller.nSortNr, thersteller.cName",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $shopURL       = Shop::getURL() . '/';
            $imageBaseURL  = Shop::getImageBaseURL();
            foreach ($manufacturers as $manufacturer) {
                if (!empty($manufacturer->cBildpfad)) {
                    $manufacturer->cBildpfadKlein  = PFAD_HERSTELLERBILDER_KLEIN . $manufacturer->cBildpfad;
                    $manufacturer->cBildpfadNormal = PFAD_HERSTELLERBILDER_NORMAL . $manufacturer->cBildpfad;
                } else {
                    $manufacturer->cBildpfadKlein  = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                    $manufacturer->cBildpfadNormal = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                }
                $manufacturer->cBildURLKlein  = $imageBaseURL . $manufacturer->cBildpfadKlein;
                $manufacturer->cBildURLNormal = $imageBaseURL . $manufacturer->cBildpfadKlein;
                $manufacturer->kHersteller    = (int)$manufacturer->kHersteller;
                $manufacturer->nSortNr        = (int)$manufacturer->nSortNr;
                $manufacturer->cURLFull       = $shopURL . $manufacturer->cSeo;
            }
            $cacheTags = [CACHING_GROUP_MANUFACTURER, CACHING_GROUP_CORE];
            executeHook(HOOK_GET_MANUFACTURERS, [
                'cached'        => false,
                'cacheTags'     => &$cacheTags,
                'manufacturers' => &$manufacturers
            ]);
            Shop::Cache()->set($this->cacheID, $manufacturers, $cacheTags);
        } else {
            executeHook(HOOK_GET_MANUFACTURERS, [
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
     * @since 5.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('thersteller', $attribute, $value);

        return is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0
     */
    public static function getManufacturerByAttribute(string $attribute, $value, callable $callback = null)
    {
        $mf = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? new Hersteller($res->kHersteller)
            : null;

        return is_callable($callback)
            ? $callback($mf)
            : $mf;
    }
}
