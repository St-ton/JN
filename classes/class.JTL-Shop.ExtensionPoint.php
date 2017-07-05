<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ExtensionPoint
 */
class ExtensionPoint
{
    /**
     * @var int
     */
    protected $nSeitenTyp;

    /**
     * @var array
     */
    protected $cParam_arr;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @param int   $nSeitenTyp
     * @param array $cParam_arr
     * @param int   $kSprache
     * @param int   $kKundengruppe
     */
    public function __construct($nSeitenTyp, $cParam_arr, $kSprache, $kKundengruppe)
    {
        $this->nSeitenTyp    = (int)$nSeitenTyp;
        $this->cParam_arr    = $cParam_arr;
        $this->kSprache      = (int)$kSprache;
        $this->kKundengruppe = (int)$kKundengruppe;
    }

    /**
     * @return $this
     */
    public function load()
    {
        $oKey           = $this->getPageKey();
        $oExtension_arr = Shop::DB()->query(
            "SELECT * FROM textensionpoint
                WHERE
                 (kSprache = '{$this->kSprache}' OR kSprache = 0)
                    AND
                 (kKundengruppe = '{$this->kKundengruppe}' OR kKundengruppe = 0)
                    AND
                 (nSeite = '{$this->nSeitenTyp}' OR nSeite = 0)
                    AND
                 (
                    (cKey = '{$oKey->cKey}' AND (cValue = '{$oKey->cValue}' OR cValue = '')) OR cValue = ''
                 )", 2
        );
        foreach ($oExtension_arr as $oExtension) {
            $oHandle = null;
            $cClass  = ucfirst($oExtension->cClass);
            if (class_exists($cClass)) {
                /** @var IExtensionPoint $oHandle */
                $oHandle = new $cClass();
                $oHandle->init($oExtension->kInitial);
            } else {
                Jtllog::writeLog("Extension '{$cClass}' not found", JTLLOG_LEVEL_ERROR);
            }
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getPageKey()
    {
        $oKey         = new stdClass();
        $oKey->cValue = '';
        $oKey->cKey   = null;
        $oKey->nPage  = $this->nSeitenTyp;

        switch ($oKey->nPage) {
            case PAGE_ARTIKEL:
                $oKey->cKey   = 'kArtikel';
                $oKey->cValue = isset($this->cParam_arr['kArtikel']) ? (int)$this->cParam_arr['kArtikel'] : null;
                break;

            case PAGE_NEWS:
                if (isset($this->cParam_arr['kNewsKategorie']) && (int)$this->cParam_arr['kNewsKategorie'] > 0) {
                    $oKey->cKey   = 'kNewsKategorie';
                    $oKey->cValue = (int)$this->cParam_arr['kNewsKategorie'];
                } else {
                    $oKey->cKey   = 'kNews';
                    $oKey->cValue = isset($this->cParam_arr['kNews']) ? (int)$this->cParam_arr['kNews'] : null;
                }
                break;

            case PAGE_BEWERTUNG:
                $oKey->cKey   = 'kArtikel';
                $oKey->cValue = (int)$this->cParam_arr['kArtikel'];
                break;

            case PAGE_EIGENE:
                $oKey->cKey   = 'kLink';
                $oKey->cValue = (int)$this->cParam_arr['kLink'];
                break;

            case PAGE_UMFRAGE:
                $oKey->cKey   = 'kUmfrage';
                $oKey->cValue = (int)$this->cParam_arr['kUmfrage'];
                break;

            case PAGE_ARTIKELLISTE:
                $oNaviFilter = Shop::getNaviFilter();
                // MerkmalWert
                if ($oNaviFilter->hasAttributeValue()) {
                    $oKey->cKey   = 'kMerkmalWert';
                    $oKey->cValue = $oNaviFilter->getAttributeValue()->getValue();
                } elseif ($oNaviFilter->hasCategory()) {
                    // Kategorie
                    $oKey->cKey   = 'kKategorie';
                    $oKey->cValue = $oNaviFilter->getCategory()->getValue();
                } elseif ($oNaviFilter->hasManufacturer()) {
                    // Hersteller
                    $oKey->cKey   = 'kHersteller';
                    $oKey->cValue = $oNaviFilter->getManufacturer()->getValue();
                } elseif ($oNaviFilter->hasTag()) {
                    // Tag
                    $oKey->cKey   = 'kTag';
                    $oKey->cValue = $oNaviFilter->getTag()->getValue();
                } elseif ($oNaviFilter->hasSearch()) {
                    // Suchbegriff
                    $oKey->cKey   = 'cSuche';
                    $oKey->cValue = $oNaviFilter->getSearch()->getValue();
                } elseif ($oNaviFilter->hasSearchSpecial()) {
                    // Suchspecial
                    $oKey->cKey   = 'kSuchspecial';
                    $oKey->cValue = $oNaviFilter->getSearchSpecial()->getValue();
                }

                break;

            case PAGE_NEWSLETTERARCHIV:
            case PAGE_PLUGIN:
            case PAGE_STARTSEITE:
            case PAGE_VERSAND:
            case PAGE_AGB:
            case PAGE_DATENSCHUTZ:
            case PAGE_TAGGING:
            case PAGE_LIVESUCHE:
            case PAGE_HERSTELLER:
            case PAGE_SITEMAP:
            case PAGE_GRATISGESCHENK:
            case PAGE_WRB:
            case PAGE_AUSWAHLASSISTENT:
            case PAGE_BESTELLABSCHLUSS:
            case PAGE_RMA:
            case PAGE_WARENKORB:
            case PAGE_MEINKONTO:
            case PAGE_KONTAKT:
            case PAGE_NEWSLETTER:
            case PAGE_LOGIN:
            case PAGE_REGISTRIERUNG:
            case PAGE_BESTELLVORGANG:
            case PAGE_DRUCKANSICHT:
            case PAGE_PASSWORTVERGESSEN:
            case PAGE_WARTUNG:
            case PAGE_WUNSCHLISTE:
            case PAGE_VERGLEICHSLISTE:
            default:
                break;

        }

        return $oKey;
    }
}
