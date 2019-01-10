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
    public function __construct(int $nSeitenTyp, array $cParam_arr, int $kSprache, int $kKundengruppe)
    {
        $this->nSeitenTyp    = $nSeitenTyp;
        $this->cParam_arr    = $cParam_arr;
        $this->kSprache      = $kSprache;
        $this->kKundengruppe = $kKundengruppe;
    }

    /**
     * @return $this
     */
    public function load(): self
    {
        $key        = $this->getPageKey();
        $extensions = Shop::Container()->getDB()->queryPrepared(
            "SELECT cClass, kInitial FROM textensionpoint
                WHERE (kSprache = :lid OR kSprache = 0)
                    AND (kKundengruppe = :cgid OR kKundengruppe = 0)
                    AND (nSeite = :ptype OR nSeite = 0)
                    AND ( (cKey = :cky AND (cValue = :cval OR cValue = '')) OR cValue = '')",
            [
                'lid'   => $this->kSprache,
                'cgid'  => $this->kKundengruppe,
                'ptype' => $this->nSeitenTyp,
                'cky'   => $key->cKey,
                'cval'  => $key->cValue
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($extensions as $oExtension) {
            $instance = null;
            $class    = ucfirst($oExtension->cClass);
            if (class_exists($class)) {
                /** @var IExtensionPoint $instance */
                $instance = new $class();
                $instance->init((int)$oExtension->kInitial);
            } else {
                Shop::Container()->getLogService()->error('Extension "' . $class . '" not found');
            }
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getPageKey(): stdClass
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
                $productFilter = Shop::getProductFilter();
                // MerkmalWert
                if ($productFilter->hasAttributeValue()) {
                    $oKey->cKey   = 'kMerkmalWert';
                    $oKey->cValue = $productFilter->getAttributeValue()->getValue();
                } elseif ($productFilter->hasCategory()) {
                    // Kategorie
                    $oKey->cKey   = 'kKategorie';
                    $oKey->cValue = $productFilter->getCategory()->getValue();
                } elseif ($productFilter->hasManufacturer()) {
                    // Hersteller
                    $oKey->cKey   = 'kHersteller';
                    $oKey->cValue = $productFilter->getManufacturer()->getValue();
                } elseif ($productFilter->hasTag()) {
                    // Tag
                    $oKey->cKey   = 'kTag';
                    $oKey->cValue = $productFilter->getTag()->getValue();
                } elseif ($productFilter->hasSearch()) {
                    // Suchbegriff
                    $oKey->cKey   = 'cSuche';
                    $oKey->cValue = $productFilter->getSearch()->getValue();
                } elseif ($productFilter->hasSearchSpecial()) {
                    // Suchspecial
                    $oKey->cKey   = 'kSuchspecial';
                    $oKey->cValue = $productFilter->getSearchSpecial()->getValue();
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
            case PAGE_WARENKORB:
            case PAGE_MEINKONTO:
            case PAGE_KONTAKT:
            case PAGE_NEWSLETTER:
            case PAGE_LOGIN:
            case PAGE_REGISTRIERUNG:
            case PAGE_BESTELLVORGANG:
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
