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
     * @param int   $pageType
     * @param array $params
     * @param int   $kSprache
     * @param int   $kKundengruppe
     */
    public function __construct(int $pageType, array $params, int $kSprache, int $kKundengruppe)
    {
        $this->nSeitenTyp    = $pageType;
        $this->cParam_arr    = $params;
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
        foreach ($extensions as $extension) {
            $instance = null;
            $class    = ucfirst($extension->cClass);
            if (class_exists($class)) {
                /** @var IExtensionPoint $instance */
                $instance = new $class();
                $instance->init((int)$extension->kInitial);
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
        $key         = new stdClass();
        $key->cValue = '';
        $key->cKey   = null;
        $key->nPage  = $this->nSeitenTyp;

        switch ($key->nPage) {
            case PAGE_ARTIKEL:
                $key->cKey   = 'kArtikel';
                $key->cValue = isset($this->cParam_arr['kArtikel']) ? (int)$this->cParam_arr['kArtikel'] : null;
                break;

            case PAGE_NEWS:
                if (isset($this->cParam_arr['kNewsKategorie']) && (int)$this->cParam_arr['kNewsKategorie'] > 0) {
                    $key->cKey   = 'kNewsKategorie';
                    $key->cValue = (int)$this->cParam_arr['kNewsKategorie'];
                } else {
                    $key->cKey   = 'kNews';
                    $key->cValue = isset($this->cParam_arr['kNews']) ? (int)$this->cParam_arr['kNews'] : null;
                }
                break;

            case PAGE_BEWERTUNG:
                $key->cKey   = 'kArtikel';
                $key->cValue = (int)$this->cParam_arr['kArtikel'];
                break;

            case PAGE_EIGENE:
                $key->cKey   = 'kLink';
                $key->cValue = (int)$this->cParam_arr['kLink'];
                break;

            case PAGE_UMFRAGE:
                $key->cKey   = 'kUmfrage';
                $key->cValue = (int)$this->cParam_arr['kUmfrage'];
                break;

            case PAGE_ARTIKELLISTE:
                $productFilter = Shop::getProductFilter();
                // MerkmalWert
                if ($productFilter->hasAttributeValue()) {
                    $key->cKey   = 'kMerkmalWert';
                    $key->cValue = $productFilter->getAttributeValue()->getValue();
                } elseif ($productFilter->hasCategory()) {
                    // Kategorie
                    $key->cKey   = 'kKategorie';
                    $key->cValue = $productFilter->getCategory()->getValue();
                } elseif ($productFilter->hasManufacturer()) {
                    // Hersteller
                    $key->cKey   = 'kHersteller';
                    $key->cValue = $productFilter->getManufacturer()->getValue();
                } elseif ($productFilter->hasTag()) {
                    // Tag
                    $key->cKey   = 'kTag';
                    $key->cValue = $productFilter->getTag()->getValue();
                } elseif ($productFilter->hasSearch()) {
                    // Suchbegriff
                    $key->cKey   = 'cSuche';
                    $key->cValue = $productFilter->getSearch()->getValue();
                } elseif ($productFilter->hasSearchSpecial()) {
                    // Suchspecial
                    $key->cKey   = 'kSuchspecial';
                    $key->cValue = $productFilter->getSearchSpecial()->getValue();
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

        return $key;
    }
}
