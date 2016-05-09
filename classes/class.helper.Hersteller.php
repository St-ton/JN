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
    private static $_instance = null;

    /**
     * @var string
     */
    public $cacheID = null;

    /**
     * @var array|mixed
     */
    public $manufacturers = null;

    /**
     * @var int
     */
    private static $langID = null;

    /**
     *
     */
    public function __construct()
    {
        $lagerfilter         = gibLagerfilter();
        $this->cacheID       = 'manuf_' . Shop::Cache()->getBaseID() . (($lagerfilter !== '') ? md5($lagerfilter) : '');
        $this->manufacturers = $this->getManufacturers();
        self::$langID        = (int)Shop::$kSprache;
        self::$_instance     = $this;
    }

    /**
     * @return HerstellerHelper
     */
    public static function getInstance()
    {
        return (self::$_instance === null || (int)Shop::$kSprache !== self::$langID) ? new self() : self::$_instance;
    }

    /**
     * @return array|mixed
     */
    public function getManufacturers()
    {
        if ($this->manufacturers === null) {
            if (($manufacturers = Shop::Cache()->get($this->cacheID)) === false) {
                $lagerfilter = gibLagerfilter();
                //fixes for admin backend
                $customerGroupID = (isset($_SESSION['Kundengruppe']->kKundengruppe)) ?
                    $_SESSION['Kundengruppe']->kKundengruppe :
                    Kundengruppe::getDefaultGroupID();
                if (Shop::$kSprache !== null) {
                    $kSprache = Shop::$kSprache;
                } elseif (isset($_SESSION['kSprache'])) {
                    $kSprache = $_SESSION['kSprache'];
                } else {
                    $_lang    = gibStandardsprache();
                    $kSprache = $_lang->kSprache;
                }
                $kSprache      = (int)$kSprache;
                $manufacturers = Shop::DB()->query(
                    "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, thersteller.cBildpfad,
                            therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, therstellersprache.cMetaDescription,
                            therstellersprache.cBeschreibung, tseo.cSeo
                        FROM thersteller
                        JOIN tartikel
                            ON thersteller.kHersteller = tartikel.kHersteller
                        LEFT JOIN tartikelsichtbarkeit
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$customerGroupID . "
                        LEFT JOIN therstellersprache
                            ON therstellersprache.kHersteller = thersteller.kHersteller
                            AND therstellersprache.kSprache = " . $kSprache . "
                        LEFT JOIN tseo
                            ON tseo.kKey = thersteller.kHersteller
                            AND tseo.cKey = 'kHersteller'
                            AND tseo.kSprache = " . $kSprache . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            " . $lagerfilter . "
                        GROUP BY  thersteller.kHersteller
                        ORDER BY thersteller.cName", 2
                );
                if (is_array($manufacturers) && count($manufacturers) > 0) {
                    foreach ($manufacturers as $i => $oHersteller) {
                        if (isset($oHersteller->cBildpfad) && strlen($oHersteller->cBildpfad) > 0) {
                            $manufacturers[$i]->cBildpfadKlein  = PFAD_HERSTELLERBILDER_KLEIN . $oHersteller->cBildpfad;
                            $manufacturers[$i]->cBildpfadNormal = PFAD_HERSTELLERBILDER_NORMAL . $oHersteller->cBildpfad;
                        } else {
                            $manufacturers[$i]->cBildpfadKlein  = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                            $manufacturers[$i]->cBildpfadNormal = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                        }
                    }
                }
                Shop::Cache()->set($this->cacheID, $manufacturers, array(CACHING_GROUP_CORE));
            }
            $this->manufacturers = $manufacturers;
        }

        return $this->manufacturers;
    }
}
