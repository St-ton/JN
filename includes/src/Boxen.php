<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Boxen
 * @deprecated since 5.0.0
 */
class Boxen
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'boxes'     => 'BoxList',
        'boxConfig' => 'Config'
    ];

    /**
     * @var array
     */
    public $boxConfig = [];

    /**
     * @var string
     */
    public $lagerFilter = '';

    /**
     * @var string
     */
    public $cVaterSQL = ' AND tartikel.kVaterArtikel = 0';

    /**
     * unrendered box template file name + data
     *
     * @var array
     */
    public $rawData = [];

    /**
     * @var array
     */
    public $visibility;

    /**
     * @var Boxen
     */
    private static $_instance;

    /**
     * @var \Services\JTL\BoxService
     */
    private $boxService;

    /**
     * @return Boxen
     * @deprecated since 5.0.0
     */
    public static function getInstance(): self
    {
        trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);

        return self::$_instance ?? new self();
    }

    /**
     * @deprecated since 5.0.0
     */
    public function __construct()
    {
        $this->boxService = Shop::Container()->getBoxService();
        self::$_instance  = $this;
    }

    /**
     * @return array
     */
    public function getBoxList(): array
    {
        return $this->boxService->getBoxes();
    }

    /**
     * @param array $boxList
     */
    public function setBoxList(array $boxList)
    {
        trigger_error(__CLASS__ . ': setting boxes here is not possible anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int $nSeite
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeVorlagen(int $nSeite = -1): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $cSQL          = '';
        $oVorlagen_arr = [];

        if ($nSeite >= 0) {
            $cSQL = 'WHERE (cVerfuegbar = "' . $nSeite . '" OR cVerfuegbar = "0")';
        }
        $oVorlage_arr = Shop::Container()->getDB()->query(
            "SELECT * 
                FROM tboxvorlage " . $cSQL . " 
                ORDER BY cVerfuegbar ASC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oVorlage_arr as $oVorlage) {
            $nID   = 0;
            $cName = 'Vorlage';
            if ($oVorlage->eTyp === 'text') {
                $nID   = 1;
                $cName = 'Inhalt';
            } elseif ($oVorlage->eTyp === 'link') {
                $nID   = 2;
                $cName = 'Linkliste';
            } elseif ($oVorlage->eTyp === 'plugin') {
                $nID   = 3;
                $cName = 'Plugin';
            } elseif ($oVorlage->eTyp === 'catbox') {
                $nID   = 4;
                $cName = 'Kategorie';
            }

            if (!isset($oVorlagen_arr[$nID])) {
                $oVorlagen_arr[$nID]               = new stdClass();
                $oVorlagen_arr[$nID]->oVorlage_arr = [];
            }

            $oVorlagen_arr[$nID]->cName          = $cName;
            $oVorlagen_arr[$nID]->oVorlage_arr[] = $oVorlage;
        }

        return $oVorlagen_arr;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function gibBoxInhalt(int $kBox, string $cISO = '')
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return strlen($cISO) > 0
            ? Shop::Container()->getDB()->select('tboxsprache', 'kBox', $kBox, 'cISO', $cISO)
            : Shop::Container()->getDB()->selectAll('tboxsprache', 'kBox', $kBox);
    }

    /**
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @param bool $force
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeBoxen(int $nSeite = 0, bool $bAktiv = true, bool $bVisible = false, bool $force = false): array
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Consider using ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );
        if (count($this->boxService->getBoxes()) === 0) {
            return $this->boxService->buildList($nSeite, $bAktiv, $bVisible);
        }

        return $this->boxService->getBoxes();
    }

    /**
     * generate array of currently active boxes
     *
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @return $this
     * @deprecated since 5.0.0
     */
    public function build(int $nSeite = 0, bool $bAktiv = true, bool $bVisible = false): self
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Consider using ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );
        if (count($this->boxService->getBoxes()) === 0) {
            $this->boxService->buildList($nSeite, $bAktiv, $bVisible);
        }

        return $this;
    }

    /**
     * read linkgroup array and search for specific ID
     *
     * @param int $id
     * @return \Link\LinkGroupInterface|null
     * @deprecated since 5.0.0
     */
    private function getLinkGroupByID(int $id)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getLinkService()->getLinkGroupByID($id);
    }

    /**
     * supply data for specific box types
     *
     * @param int    $kBoxVorlage
     * @param object $oBox
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function prepareBox(int $kBoxVorlage, $oBox)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $kKundengruppe     = Session::CustomerGroup()->getID();
        $currencyCachePart = '_cur_' . Session::Currency()->getID();
        $kSprache          = Shop::getLanguage();
        switch ($kBoxVorlage) {
            case BOX_BESTSELLER :
                $oBox->compatName = 'Bestseller';
                if (!Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = (int)$this->boxConfig['boxen']['box_bestseller_anzahl_basis'];
                $anzahl       = (int)$this->boxConfig['boxen']['box_bestseller_anzahl_anzeige'];
                $nAnzahl      = ((int)$this->boxConfig['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$this->boxConfig['global']['global_bestseller_minanzahl']
                    : 100;
                if ($limit < 1) {
                    $limit = 10;
                }
                $cacheID = 'box_bestseller_' . $kKundengruppe . $currencyCachePart . '_' .
                    $kSprache . '_' . md5($this->cVaterSQL . $this->lagerFilter);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::Container()->getDB()->query(
                        "SELECT tartikel.kArtikel
                            FROM tbestseller, tartikel
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tbestseller.kArtikel = tartikel.kArtikel
                                AND round(tbestseller.fAnzahl) >= " . $nAnzahl . "
                                $this->cVaterSQL
                                $this->lagerFilter
                            ORDER BY fAnzahl DESC LIMIT " . $limit,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    if (is_array($menge) && count($menge) > 0) {
                        $rndkeys = array_rand($menge, min($anzahl, count($menge)));
                        if (is_array($rndkeys)) {
                            foreach ($rndkeys as $key) {
                                if (isset($menge[$key]->kArtikel) && $menge[$key]->kArtikel > 0) {
                                    $kArtikel_arr[] = (int)$menge[$key]->kArtikel;
                                }
                            }
                        } elseif (is_int($rndkeys)) {
                            if (isset($menge[$rndkeys]->kArtikel) && $menge[$rndkeys]->kArtikel > 0) {
                                $kArtikel_arr[] = (int)$menge[$rndkeys]->kArtikel;
                            }
                        }
                    }

                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = SearchSpecialHelper::buildURL(SEARCHSPECIALS_BESTSELLER);
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    executeHook(HOOK_BOXEN_INC_BESTSELLER, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_TRUSTEDSHOPS_GUETESIEGEL :
                $oBox->compatName = 'TrustedShopsSiegelbox';
                if ($this->boxConfig['trustedshops']['trustedshops_nutzen'] === 'Y') {
                    $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                    $shopURL       = Shop::getURL(true) . '/';
                    if ((int)$oTrustedShops->nAktiv === 1 && strlen($oTrustedShops->tsId) > 0) {
                        $oBox->anzeigen          = 'Y';
                        $oBox->cLogoURL          = $oTrustedShops->cLogoURL;
                        $oBox->cLogoSiegelBoxURL = $oTrustedShops->cLogoSiegelBoxURL[StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])];
                        $oBox->cBild             = $shopURL . PFAD_GFX_TRUSTEDSHOPS . 'trustedshops_m.png';
                        $oBox->cBGBild           = $shopURL . PFAD_GFX_TRUSTEDSHOPS . 'bg_yellow.jpg';
                    }
                }
                break;

            case BOX_TRUSTEDSHOPS_KUNDENBEWERTUNGEN :
                $oBox->compatName    = 'TrustedShopsKundenbewertung';
                $cValidSprachISO_arr = ['de', 'en', 'fr', 'pl', 'es'];
                $lang                = Shop::getLanguageCode();
                if ($this->boxConfig['trustedshops']['trustedshops_nutzen'] !== 'Y'
                    || !in_array(StringHandler::convertISO2ISO639($lang), $cValidSprachISO_arr, true)
                ) {
                    break;
                }
                $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($lang));
                $tsRating      = $oTrustedShops->holeKundenbewertungsstatus(StringHandler::convertISO2ISO639($lang));
                if (isset($tsRating->cTSID)
                    && (int)$tsRating->nStatus === 1
                    && strlen($tsRating->cTSID) > 0
                ) {
                    $cURLSprachISO_arr = [
                        'de' => 'https://www.trustedshops.com/bewertung/info_' . $tsRating->cTSID . '.html',
                        'en' => 'https://www.trustedshops.com/buyerrating/info_' . $tsRating->cTSID . '.html',
                        'fr' => 'https://www.trustedshops.com/evaluation/info_' . $tsRating->cTSID . '.html',
                        'es' => 'https://www.trustedshops.com/evaluacion/info_' . $tsRating->cTSID . '.html',
                        'pl' => ''
                    ];
                    $oBox->anzeigen    = 'Y';
                    if (!$this->cachecheck($filename = $tsRating->cTSID . '.gif', 10800)) {
                        if (!$oTrustedShops::ladeKundenbewertungsWidgetNeu($filename)) {
                            $oBox->anzeigen = 'N';
                        }
                        // Prüft alle X Stunden ob ein Zertifikat noch gültig ist
                        $oTrustedShops->pruefeZertifikat(StringHandler::convertISO2ISO639($lang));
                    }
                    $oBox->cBildPfad    = Shop::getImageBaseURL() . PFAD_GFX_TRUSTEDSHOPS . $filename;
                    $oBox->cBildPfadURL = $cURLSprachISO_arr[StringHandler::convertISO2ISO639($lang)];
                    $oBox->oStatistik   = $oTrustedShops->gibKundenbewertungsStatistik();
                }
                break;

            case BOX_UMFRAGE :
                $oBox->compatName = 'Umfrage';
                $oBox->anzeigen   = 'N';
                $cSQL             = '';
                if (isset($this->boxConfig['umfrage']['news_anzahl_box'])
                    && (int)$this->boxConfig['umfrage']['news_anzahl_box'] > 0
                ) {
                    $cSQL = ' LIMIT ' . (int)$this->boxConfig['umfrage']['umfrage_box_anzahl'];
                }
                $cacheID = 'bu_' . $kSprache . '_' . Session::CustomerGroup()->getID() . md5($cSQL);
                if (($oUmfrage_arr = Shop::Cache()->get($cacheID)) === false) {
                    // Umfrage Übersicht
                    $oUmfrage_arr = Shop::Container()->getDB()->query(
                        "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe, 
                            tumfrage.cName, tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, 
                            tumfrage.nAktiv, tumfrage.dGueltigVon, tumfrage.dGueltigBis, tumfrage.dErstellt, tseo.cSeo,
                            DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de,
                            DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de, 
                            count(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                            FROM tumfrage
                            JOIN tumfragefrage 
                                ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kUmfrage'
                                AND tseo.kKey = tumfrage.kUmfrage
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE tumfrage.nAktiv = 1
                                AND tumfrage.kSprache = " . $kSprache . "
                                AND (cKundengruppe LIKE '%;-1;%' 
                                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID() . "', REPLACE(cKundengruppe, ';', ',')) > 0)
                                AND ((dGueltigVon <= now() 
                                    AND dGueltigBis >= now()) || (dGueltigVon <= now() 
                                    AND dGueltigBis = '0000-00-00 00:00:00'))
                            GROUP BY tumfrage.kUmfrage
                            ORDER BY tumfrage.dGueltigVon DESC" . $cSQL,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($oUmfrage_arr as $i => $oUmfrage) {
                        $oUmfrage_arr[$i]->cURL     = UrlHelper::buildURL($oUmfrage, URLART_UMFRAGE);
                        $oUmfrage_arr[$i]->cURLFull = UrlHelper::buildURL($oUmfrage, URLART_UMFRAGE, true);
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_CORE];
                    executeHook(HOOK_BOXEN_INC_UMFRAGE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oUmfrage_arr, $cacheTags); //@todo: invalidate
                }
                $oBox->oUmfrage_arr = $oUmfrage_arr;

                break;

            case BOX_PREISRADAR :
                $oBox->compatName = 'Preisradar';
                $oBox->anzeigen   = 'N';
                $nLimit           = (isset($this->boxConfig['boxen']['boxen_preisradar_anzahl'])
                    && (int)$this->boxConfig['boxen']['boxen_preisradar_anzahl'] > 0)
                    ? (int)$this->boxConfig['boxen']['boxen_preisradar_anzahl']
                    : 3;
                $nTage            = (isset($this->boxConfig['boxen']['boxen_preisradar_anzahltage'])
                    && (int)$this->boxConfig['boxen']['boxen_preisradar_anzahltage'] > 0)
                    ? (int)$this->boxConfig['boxen']['boxen_preisradar_anzahltage']
                    : 30;
                $cacheID          = 'box_price_radar_' . $currencyCachePart . $nTage . '_' . $nLimit .
                    '_' . $kSprache . '_' . Session::CustomerGroup()->getID();
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oPreisradar_arr         = Preisradar::getProducts(Session::CustomerGroup()->getID(), $nLimit,
                        $nTage);
                    $oBox->Artikel           = new stdClass();
                    $oBox->Artikel->elemente = [];
                    if (count($oPreisradar_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $defaultOptions = Artikel::getDefaultOptions();
                        foreach ($oPreisradar_arr as $oPreisradar) {
                            $oArtikel = new Artikel();
                            $oArtikel->fuelleArtikel($oPreisradar->kArtikel, $defaultOptions);
                            $oArtikel->oPreisradar                     = new stdClass();
                            $oArtikel->oPreisradar->fDiff              = $oPreisradar->fDiff * -1;
                            $oArtikel->oPreisradar->fDiffLocalized[0]  = Preise::getLocalizedPriceString(
                                TaxHelper::getGross($oArtikel->oPreisradar->fDiff, $oArtikel->Preise->fUst)
                            );
                            $oArtikel->oPreisradar->fDiffLocalized[1]  = Preise::getLocalizedPriceString(
                                $oArtikel->oPreisradar->fDiff
                            );
                            $oArtikel->oPreisradar->fOldVKLocalized[0] = Preise::getLocalizedPriceString(
                                TaxHelper::getGross(
                                    $oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff,
                                    $oArtikel->Preise->fUst
                                )
                            );
                            $oArtikel->oPreisradar->fOldVKLocalized[1] = Preise::getLocalizedPriceString(
                                $oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff
                            );
                            $oArtikel->oPreisradar->fProzentDiff       = $oPreisradar->fProzentDiff;

                            if ((int)$oArtikel->kArtikel > 0) {
                                $oBox->Artikel->elemente[] = $oArtikel;
                            }
                        }
                    }
                    Shop::Cache()->set($cacheID, $oBox, [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE]);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_NEWS_KATEGORIEN :
                $oBox->compatName = 'NewsKategorie';
                $cSQL             = '';
                if ((int)$this->boxConfig['news']['news_anzahl_box'] > 0) {
                    $cSQL = " LIMIT " . (int)$this->boxConfig['news']['news_anzahl_box'];
                }
                $cacheID = 'bnk_' . $kSprache . '_' . Session::CustomerGroup()->getID() . '_' . md5($cSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oNewsKategorie_arr = Shop::Container()->getDB()->query(
                        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                            tnewskategorie.cPreviewImage, tseo.cSeo,
                            count(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                            FROM tnewskategorie
                            LEFT JOIN tnewskategorienews 
                                ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                            LEFT JOIN tnews 
                                ON tnews.kNews = tnewskategorienews.kNews
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kNewsKategorie'
                                AND tseo.kKey = tnewskategorie.kNewsKategorie
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE tnewskategorie.kSprache = " . $kSprache . "
                                AND tnewskategorie.nAktiv = 1
                                AND tnews.nAktiv = 1
                                AND tnews.dGueltigVon <= now()
                                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID() . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                                AND tnews.kSprache = " . $kSprache . "
                            GROUP BY tnewskategorienews.kNewsKategorie
                            ORDER BY tnewskategorie.nSort DESC" . $cSQL,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($oNewsKategorie_arr as $i => $oNewsKategorie) {
                        $oNewsKategorie_arr[$i]->cURL     = UrlHelper::buildURL($oNewsKategorie, URLART_NEWSKATEGORIE);
                        $oNewsKategorie_arr[$i]->cURLFull = UrlHelper::buildURL(
                            $oNewsKategorie,
                            URLART_NEWSKATEGORIE,
                            true
                        );
                    }
                    $oBox->anzeigen           = 'Y';
                    $oBox->oNewsKategorie_arr = $oNewsKategorie_arr;
                    $cacheTags                = [CACHING_GROUP_BOX, CACHING_GROUP_NEWS];
                    executeHook(HOOK_BOXEN_INC_NEWSKATEGORIE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_HERSTELLER :
                $oBox->compatName    = 'Hersteller';
                $oBox->anzeigen      = 'Y';
                $oBox->manufacturers = HerstellerHelper::getInstance()->getManufacturers();
                break;

            case BOX_NEWS_AKTUELLER_MONAT :
                $oBox->compatName = 'News';
                $cSQL             = '';
                if ((int)$this->boxConfig['news']['news_anzahl_box'] > 0) {
                    $cSQL = ' LIMIT ' . (int)$this->boxConfig['news']['news_anzahl_box'];
                }
                $oNewsMonatsUebersicht_arr = Shop::Container()->getDB()->query(
                    "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                        month(tnews.dGueltigVon) AS nMonat, year( tnews.dGueltigVon ) AS nJahr, count(*) AS nAnzahl
                        FROM tnews
                        JOIN tnewsmonatsuebersicht 
                            ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                            AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                            AND tnewsmonatsuebersicht.kSprache = " . $kSprache . "
                        LEFT JOIN tseo 
                            ON cKey = 'kNewsMonatsUebersicht'
                            AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                            AND tseo.kSprache = " . $kSprache . "
                        WHERE tnews.dGueltigVon < now()
                            AND tnews.nAktiv = 1
                            AND tnews.kSprache = " . $kSprache . "
                        GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                        ORDER BY tnews.dGueltigVon DESC" . $cSQL,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($oNewsMonatsUebersicht_arr as $i => $oNewsMonatsUebersicht) {
                    $oNewsMonatsUebersicht_arr[$i]->cURL     = UrlHelper::buildURL($oNewsMonatsUebersicht, URLART_NEWSMONAT);
                    $oNewsMonatsUebersicht_arr[$i]->cURLFull = UrlHelper::buildURL(
                        $oNewsMonatsUebersicht,
                        URLART_NEWSMONAT,
                        true
                    );
                }
                $oBox->anzeigen                  = 'Y';
                $oBox->oNewsMonatsUebersicht_arr = $oNewsMonatsUebersicht_arr;

                executeHook(HOOK_BOXEN_INC_NEWS);
                break;

            case BOX_TOP_BEWERTET :
                $cacheID = 'box_top_rated_' . $currencyCachePart . $this->boxConfig['boxen']['boxen_topbewertet_minsterne'] . '_' .
                    $kSprache . '_' . $this->boxConfig['boxen']['boxen_topbewertet_basisanzahl'] . md5($this->cVaterSQL);
                if (($boxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $boxCached;
                    break;
                }
                $oBox->compatName = 'TopBewertet';
                $oTopBewertet_arr = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                        FROM tartikel
                        JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                        WHERE round(fDurchschnittsBewertung) >= " . (int)$this->boxConfig['boxen']['boxen_topbewertet_minsterne'] . "
                        $this->cVaterSQL
                        ORDER BY tartikelext.fDurchschnittsBewertung DESC
                        LIMIT " . (int)$this->boxConfig['boxen']['boxen_topbewertet_basisanzahl'],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (count($oTopBewertet_arr) === 0) {
                    break;
                }
                $kArtikel_arr = [];
                $oArtikel_arr = [];
                // Alle kArtikels aus der DB Menge in ein Array speichern
                foreach ($oTopBewertet_arr as $oTopBewertet) {
                    $oTopBewertet->kArtikel = (int)$oTopBewertet->kArtikel;
                    $kArtikel_arr[]         = (int)$oTopBewertet->kArtikel;
                }
                // Wenn das Array Elemente besitzt
                if (count($kArtikel_arr) > 0) {
                    // Gib mir X viele Random Keys
                    $nAnzahlKeys = (int)$this->boxConfig['boxen']['boxen_topbewertet_anzahl'];
                    if (count($oTopBewertet_arr) < (int)$this->boxConfig['boxen']['boxen_topbewertet_anzahl']) {
                        $nAnzahlKeys = count($oTopBewertet_arr);
                    }
                    $kKey_arr = array_rand($kArtikel_arr, $nAnzahlKeys);

                    if (is_array($kKey_arr) && count($kKey_arr) > 0) {
                        // Lauf die Keys durch und hole baue Artikelobjekte
                        $defaultOptions = Artikel::getDefaultOptions();
                        foreach ($kKey_arr as $i => $kKey) {
                            $oArtikel_arr[] = (new Artikel())->fuelleArtikel($kArtikel_arr[$kKey], $defaultOptions);
                        }
                    }
                    // Laufe die DB Menge durch und assigne zu jedem Artikelobjekt noch die Durchschnittsbewertung
                    foreach ($oTopBewertet_arr as $oTopBewertet) {
                        foreach ($oArtikel_arr as $j => $oArtikel) {
                            if ($oTopBewertet->kArtikel === $oArtikel->kArtikel) {
                                $oArtikel_arr[$j]->fDurchschnittsBewertung = round($oTopBewertet->fDurchschnittsBewertung * 2) / 2;
                            }
                        }
                    }
                }
                $oBox->anzeigen     = 'Y';
                $oBox->oArtikel_arr = $oArtikel_arr;
                $oBox->cURL         = SearchSpecialHelper::buildURL(SEARCHSPECIALS_TOPREVIEWS);
                $cacheTags          = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                executeHook(HOOK_BOXEN_INC_TOPBEWERTET, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_VERGLEICHSLISTE :
                $oBox->compatName = 'Vergleichsliste';
                $oArtikel_arr     = [];
                if (isset($_SESSION['Vergleichsliste']->oArtikel_arr)) {
                    $oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
                }
                if (count($oArtikel_arr) === 0) {
                    break;
                }
                $cGueltigePostVars_arr = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
                $cZusatzParams         = '';
                $cPostMembers_arr      = array_keys($_REQUEST);
                foreach ($cPostMembers_arr as $cPostMember) {
                    if ((int)$_REQUEST[$cPostMember] > 0 && in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                        $cZusatzParams .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                    }
                }
                $cZusatzParams  = StringHandler::filterXSS($cZusatzParams);
                $oTMP_arr       = [];
                $cRequestURI    = Shop::getRequestUri();
                $defaultOptions = Artikel::getDefaultOptions();
                if ($cRequestURI === 'io.php') {
                    // Box wird von einem Ajax-Call gerendert
                    $cRequestURI = LinkHelper::getInstance()->getStaticRoute('vergleichsliste.php');
                }
                foreach ($oArtikel_arr as $oArtikel) {
                    $nPosAnd   = strrpos($cRequestURI, '&');
                    $nPosQuest = strrpos($cRequestURI, '?');
                    $nPosWD    = strpos($cRequestURI, 'vlplo=');

                    if ($nPosWD) {
                        $cRequestURI = substr($cRequestURI, 0, $nPosWD);
                    }
                    // z.b. index.php
                    $cDeleteParam = '?vlplo=';
                    if ($nPosAnd === strlen($cRequestURI) - 1) {
                        // z.b. index.php?a=4&
                        $cDeleteParam = 'vlplo=';
                    } elseif ($nPosAnd) {
                        // z.b. index.php?a=4&b=2
                        $cDeleteParam = '&vlplo=';
                    } elseif ($nPosQuest) {
                        // z.b. index.php?a=4
                        $cDeleteParam = '&vlplo=';
                    } elseif ($nPosQuest === strlen($cRequestURI) - 1) {
                        // z.b. index.php?
                        $cDeleteParam = 'vlplo=';
                    }
                    $artikel = new Artikel();
                    $artikel->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                    $artikel->cURLDEL = $cRequestURI . $cDeleteParam . $oArtikel->kArtikel . $cZusatzParams;
                    if (isset($oArtikel->oVariationen_arr) && count($oArtikel->oVariationen_arr) > 0) {
                        $artikel->Variationen = $oArtikel->oVariationen_arr;
                    }
                    if ($artikel->kArtikel > 0) {
                        $oTMP_arr[] = $artikel;
                    }
                }
                $oBox->anzeigen  = 'Y';
                $oBox->cAnzeigen = $this->boxConfig['boxen']['boxen_vergleichsliste_anzeigen'];
                $oBox->nAnzahl   = (int)$this->boxConfig['vergleichsliste']['vergleichsliste_anzahl'];
                $oBox->Artikel   = $oTMP_arr;

                executeHook(HOOK_BOXEN_INC_VERGLEICHSLISTE, ['box' => $oBox]);
                break;

            case BOX_WUNSCHLISTE :
                $oBox->compatName = 'Wunschliste';
                if (empty($_SESSION['Wunschliste']->kWunschliste)) {
                    break;
                }
                $CWunschlistePos_arr   = $_SESSION['Wunschliste']->CWunschlistePos_arr;
                $cGueltigePostVars_arr = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
                $cZusatzParams         = '';
                $cPostMembers_arr      = array_keys($_REQUEST);
                foreach ($cPostMembers_arr as $cPostMember) {
                    if ((int)$_REQUEST[$cPostMember] > 0 && in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                        $cZusatzParams .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                    }
                }
                $cZusatzParams = StringHandler::filterXSS($cZusatzParams);
                foreach ($CWunschlistePos_arr as $CWunschlistePos) {
                    $cRequestURI  = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'];
                    $nPosAnd      = strrpos($cRequestURI, '&');
                    $nPosQuest    = strrpos($cRequestURI, '?');
                    $nPosWD       = strpos($cRequestURI, 'wlplo=');
                    $cDeleteParam = '?wlplo='; // z.b. index.php
                    if ($nPosWD) {
                        $cRequestURI = substr($cRequestURI, 0, $nPosWD);
                    }
                    if ($nPosAnd === strlen($cRequestURI) - 1) {
                        // z.b. index.php?a=4&
                        $cDeleteParam = 'wlplo=';
                    } elseif ($nPosAnd) {
                        // z.b. index.php?a=4&b=2
                        $cDeleteParam = '&wlplo=';
                    } elseif ($nPosQuest) {
                        // z.b. index.php?a=4
                        $cDeleteParam = '&wlplo=';
                    } elseif ($nPosQuest === strlen($cRequestURI) - 1) {
                        // z.b. index.php?
                        $cDeleteParam = 'wlplo=';
                    }
                    $CWunschlistePos->cURL = $cRequestURI .
                        $cDeleteParam .
                        $CWunschlistePos->kWunschlistePos .
                        $cZusatzParams;
                    if (Session::CustomerGroup()->isMerchant()) {
                        $fPreis = isset($CWunschlistePos->Artikel->Preise->fVKNetto)
                            ? (int)$CWunschlistePos->fAnzahl * $CWunschlistePos->Artikel->Preise->fVKNetto
                            : 0;
                    } else {
                        $fPreis = isset($CWunschlistePos->Artikel->Preise->fVKNetto)
                            ? (int)$CWunschlistePos->fAnzahl * ($CWunschlistePos->Artikel->Preise->fVKNetto *
                                (100 + $_SESSION['Steuersatz'][$CWunschlistePos->Artikel->kSteuerklasse]) / 100)
                            : 0;
                    }
                    $CWunschlistePos->cPreis = Preise::getLocalizedPriceString($fPreis, Session::Currency());
                }
                $oBox->anzeigen            = 'Y';
                $oBox->nAnzeigen           = (int)$this->boxConfig['boxen']['boxen_wunschzettel_anzahl'];
                $oBox->nBilderAnzeigen     = $this->boxConfig['boxen']['boxen_wunschzettel_bilder'];
                $oBox->CWunschlistePos_arr = array_reverse($CWunschlistePos_arr);

                executeHook(HOOK_BOXEN_INC_WUNSCHZETTEL, ['box' => &$oBox]);
                break;

            case BOX_TAGWOLKE :
                $oBox->compatName = 'Tagwolke';
                $limit            = (int)$this->boxConfig['boxen']['boxen_tagging_count'];
                $limitSQL         = ($limit > 0) ? ' LIMIT ' . $limit : '';
                $cacheID          = 'box_tag_cloud_' . $currencyCachePart . $kSprache . '_' . $limit;
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $Tagwolke_arr  = [];
                $tagwolke_objs = Shop::Container()->getDB()->query(
                    "SELECT ttag.kTag,ttag.cName, tseo.cSeo,sum(ttagartikel.nAnzahlTagging) AS Anzahl 
                        FROM ttag
                        JOIN ttagartikel 
                            ON ttagartikel.kTag = ttag.kTag
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kTag'
                            AND tseo.kKey = ttag.kTag
                            AND tseo.kSprache = " . $kSprache . "
                        WHERE ttag.nAktiv = 1 
                            AND ttag.kSprache = " . $kSprache . " 
                        GROUP BY ttag.kTag 
                        ORDER BY Anzahl DESC" . $limitSQL,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );

                if (($count = count($tagwolke_objs)) > 0) {
                    // Priorität berechnen
                    $prio_step = ($tagwolke_objs[0]->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / 9;
                    foreach ($tagwolke_objs as $tagwolke) {
                        $tagwolke->Klasse   = ($prio_step < 1) ?
                            rand(1, 10) :
                            (round(($tagwolke->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / $prio_step) + 1);
                        $tagwolke->cURL     = UrlHelper::buildURL($tagwolke, URLART_TAG);
                        $tagwolke->cURLFull = UrlHelper::buildURL($tagwolke, URLART_TAG, true);
                        $Tagwolke_arr[]     = $tagwolke;
                    }
                }
                $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                if (count($Tagwolke_arr) > 0) {
                    $oBox->anzeigen    = 'Y';
                    $oBox->Tagbegriffe = $Tagwolke_arr;
                    shuffle($oBox->Tagbegriffe);
                    $oBox->TagbegriffeJSON = self::gibJSONString($oBox->Tagbegriffe);
                    executeHook(HOOK_BOXEN_INC_TAGWOLKE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                }
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_SUCHWOLKE :
                $oBox->compatName = 'Suchwolke';
                $nWolkenLimit     = (int)$this->boxConfig['boxen']['boxen_livesuche_count'];
                $cacheID          = 'box_search_tags_' . $currencyCachePart . $kSprache . '_' . $nWolkenLimit;
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $oSuchwolke_arr = Shop::Container()->getDB()->query(
                    "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, 
                        tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
                        tsuchanfrage.dZuletztGesucht, tseo.cSeo
                        FROM tsuchanfrage
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kSuchanfrage'
                            AND tseo.kKey = tsuchanfrage.kSuchanfrage
                            AND tseo.kSprache = " . $kSprache . "
                        WHERE tsuchanfrage.kSprache = " . $kSprache . "
                            AND tsuchanfrage.nAktiv = 1
                            AND tsuchanfrage.kSuchanfrage > 0
                        GROUP BY tsuchanfrage.kSuchanfrage
                        ORDER BY tsuchanfrage.nAnzahlGesuche DESC
                        LIMIT " . $nWolkenLimit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (($count = count($oSuchwolke_arr)) > 0) {
                    // Priorität berechnen
                    $prio_step = ($oSuchwolke_arr[0]->nAnzahlGesuche - $oSuchwolke_arr[$count - 1]->nAnzahlGesuche) / 9;
                    foreach ($oSuchwolke_arr as $i => $oSuchwolke) {
                        $oSuchwolke->Klasse   = ($prio_step < 1) ?
                            rand(1, 10) :
                            (round(($oSuchwolke->nAnzahlGesuche - $oSuchwolke_arr[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
                        $oSuchwolke->cURL     = UrlHelper::buildURL($oSuchwolke, URLART_LIVESUCHE);
                        $oSuchwolke->cURLFull = UrlHelper::buildURL($oSuchwolke, URLART_LIVESUCHE, true);
                        $oSuchwolke_arr[$i]   = $oSuchwolke;
                    }
                    $oBox->anzeigen = 'Y';
                    //hole anzuzeigende Suchwolke
                    $oBox->Suchbegriffe = $oSuchwolke_arr;
                    shuffle($oBox->Suchbegriffe);
                    $oBox->SuchbegriffeJSON = self::gibJSONString($oBox->Suchbegriffe);
                    $cacheTags              = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    executeHook(HOOK_BOXEN_INC_SUCHWOLKE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                }
                break;

            case BOX_IN_KUERZE_VERFUEGBAR :
                $oBox->compatName = 'ErscheinendeProdukte';
                if (!$kKundengruppe || !Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $limit   = (int)$this->boxConfig['boxen']['box_erscheinende_anzahl_anzeige'];
                $cacheID = 'box_ikv_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $menge        = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            $this->lagerFilter
                            $this->cVaterSQL
                            AND now() < tartikel.dErscheinungsdatum
                        ORDER BY rand() LIMIT " . $limit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                $kArtikel_arr = array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $menge);
                $cacheTags    = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                if (count($kArtikel_arr) > 0) {
                    $oBox->anzeigen = 'Y';
                    $oBox->Artikel  = new ArtikelListe();
                    $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                    $oBox->cURL = SearchSpecialHelper::buildURL(SEARCHSPECIALS_UPCOMINGPRODUCTS);
                    executeHook(HOOK_BOXEN_INC_ERSCHEINENDEPRODUKTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                }
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_ZULETZT_ANGESEHEN :
                $oBox->compatName = 'ZuletztAngesehen';
                if (!Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (isset($_SESSION['ZuletztBesuchteArtikel'])
                    && is_array($_SESSION['ZuletztBesuchteArtikel'])
                    && count($_SESSION['ZuletztBesuchteArtikel']) > 0
                ) {
                    $oTMP_arr       = [];
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($_SESSION['ZuletztBesuchteArtikel'] as $i => $oArtikel) {
                        $artikel = new Artikel();
                        $artikel->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                        if ($artikel->kArtikel > 0) {
                            $oTMP_arr[$i] = $artikel;
                        }
                    }
                    $oBox->Artikel    = array_reverse($oTMP_arr);
                    $oBox->anzeigen   = 'Y';
                    $oBox->compatName = 'ZuletztAngesehen';

                    executeHook(HOOK_BOXEN_INC_ZULETZTANGESEHEN, ['box' => $oBox]);
                }
                break;

            case BOX_TOP_ANGEBOT :
                $oBox->compatName = 'TopAngebot';
                if (!Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $limit   = $this->boxConfig['boxen']['box_topangebot_anzahl_anzeige'];
                $cacheID = 'box_top_offer_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $menge        = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cTopArtikel = 'Y'
                            $this->lagerFilter
                            $this->cVaterSQL
                        ORDER BY rand() LIMIT " . $limit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                $kArtikel_arr = array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $menge);
                $cacheTags    = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                if (count($kArtikel_arr) > 0) {
                    $oBox->anzeigen = 'Y';
                    $oBox->Artikel  = new ArtikelListe();
                    $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                    $oBox->cURL = SearchSpecialHelper::buildURL(SEARCHSPECIALS_TOPOFFERS);
                    executeHook(HOOK_BOXEN_INC_TOPANGEBOTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                }
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_NEUE_IM_SORTIMENT :
                $oBox->compatName = 'NeuImSortiment';
                if (!Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $limit      = $this->boxConfig['boxen']['box_neuimsortiment_anzahl_anzeige'];
                $alter_tage = 30;
                if ($this->boxConfig['boxen']['box_neuimsortiment_alter_tage'] > 0) {
                    $alter_tage = $this->boxConfig['boxen']['box_neuimsortiment_alter_tage'];
                }
                $cacheID = 'box_new_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $alter_tage . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $menge        = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cNeu = 'Y'
                            $this->lagerFilter
                            $this->cVaterSQL
                            AND cNeu = 'Y' 
                            AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < dErstellt
                        ORDER BY rand() LIMIT " . $limit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                $kArtikel_arr = array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $menge);
                $cacheTags    = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                if (count($kArtikel_arr) > 0) {
                    $oBox->anzeigen = 'Y';
                    $oBox->Artikel  = new ArtikelListe();
                    $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                    $oBox->cURL = SearchSpecialHelper::buildURL(SEARCHSPECIALS_NEWPRODUCTS);
                    executeHook(HOOK_BOXEN_INC_NEUIMSORTIMENT, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                }
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_SONDERANGEBOT :
                $oBox->compatName = 'Sonderangebote';
                if (!Session::CustomerGroup()->mayViewCategories()) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $limit   = $this->boxConfig['boxen']['box_sonderangebote_anzahl_anzeige'];
                $cacheID = 'box_special_offer_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) !== false) {
                    $oBox = $oBoxCached;
                    break;
                }
                $menge        = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        JOIN tartikelsonderpreis 
                            ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        JOIN tsonderpreise 
                            ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                            AND tsonderpreise.kKundengruppe = $kKundengruppe
                            AND tartikelsonderpreis.cAktiv = 'Y'
                            AND tartikelsonderpreis.dStart <= now()
                            AND (tartikelsonderpreis.dEnde >= CURDATE() 
                                OR tartikelsonderpreis.dEnde = '0000-00-00')
                            $this->lagerFilter
                            $this->cVaterSQL
                        ORDER BY rand() LIMIT " . $limit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                $kArtikel_arr = array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $menge);
                $cacheTags    = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                if (count($kArtikel_arr) > 0) {
                    $oBox->anzeigen = 'Y';
                    $oBox->Artikel  = new ArtikelListe();
                    $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                    $oBox->cURL = SearchSpecialHelper::buildURL(SEARCHSPECIALS_SPECIALOFFERS);
                    executeHook(HOOK_BOXEN_INC_SONDERANGEBOTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                }
                Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                break;

            case BOX_WARENKORB :
                $oBox->compatName = 'Warenkorb';
                if (isset($_SESSION['Warenkorb'], $_SESSION['Warenkorb']->PositionenArr)) {
                    $oArtikel_arr = [];
                    foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
                        $oArtikel_arr[] = $oPosition;
                    }
                    $oBox->elemente = array_reverse($oArtikel_arr);
                    $oBox->anzeigen = (count($oArtikel_arr) > 0) ? 'Y' : 'N';
                }
                break;

            case BOX_SCHNELLKAUF :
                $oBox->compatName = 'Schnellkauf';
                $oBox->anzeigen   = 'Y';
                executeHook(HOOK_BOXEN_INC_SCHNELLKAUF);
                break;

            case BOX_GLOBALE_MERKMALE :
                $oBox->compatName = 'oGlobalMerkmal_arr';
                $oBox->anzeigen   = 'Y';
                require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';
                $oBox->globaleMerkmale = Session::CustomerGroup()->mayViewCategories()
                    ? gibSitemapGlobaleMerkmale()
                    : [];
                break;

            case BOX_LOGIN :
                $oBox->compatName = 'Login';
                $oBox->anzeigen   = 'Y';
                break;

            case BOX_KATEGORIEN :
                $oBox->compatName = 'Kategorien';
                $oBox->anzeigen   = 'Y';
                break;

            default :
                if ($oBox->eTyp === 'plugin' && !empty($oBox->kCustomID)) {
                    $_plgn           = new Plugin($oBox->kCustomID);
                    $oBox->cTemplate = $_plgn->cFrontendPfad . PFAD_PLUGIN_BOXEN . $oBox->cTemplate;
                    $oBox->oPlugin   = $_plgn;
                }
                $oBox->anzeigen = 'Y';
                break;
        }

        return $oBox;
    }

    /**
     * @param string $filename_cache
     * @param int    $timeout
     * @return bool
     * @deprecated since 5.0.0
     */
    private function cachecheck(string $filename_cache, int $timeout = 10800): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $filename_cache = PFAD_ROOT . PFAD_GFX_TRUSTEDSHOPS . $filename_cache;

        return file_exists($filename_cache)
            ? ((time() - filemtime($filename_cache)) < $timeout)
            : false;
    }

    /**
     * @return array
     * @throws Exception
     * @throws SmartyException
     * @deprecated since 5.0.0
     */
    public function render(): array
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Consider using ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );

        return $this->boxService->render($this->boxService->getBoxes());
    }

    /**
     * @param int $kArtikel
     * @param int $nMaxAnzahl
     * @deprecated since 5.0.0
     */
    public function addRecentlyViewed(int $kArtikel, $nMaxAnzahl = null)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        if ($kArtikel <= 0) {
            return;
        }
        if ($nMaxAnzahl === null) {
            $nMaxAnzahl = (int)$this->boxConfig['boxen']['box_zuletztangesehen_anzahl'];
        }
        if (!isset($_SESSION['ZuletztBesuchteArtikel']) || !is_array($_SESSION['ZuletztBesuchteArtikel'])) {
            $_SESSION['ZuletztBesuchteArtikel'] = [];
        }
        $oArtikel           = new stdClass();
        $oArtikel->kArtikel = $kArtikel;
        if (isset($_SESSION['ZuletztBesuchteArtikel']) && count($_SESSION['ZuletztBesuchteArtikel']) > 0) {
            $alreadyPresent = false;
            foreach ($_SESSION['ZuletztBesuchteArtikel'] as $_article) {
                if (isset($_article->kArtikel) && $_article->kArtikel === $oArtikel->kArtikel) {
                    $alreadyPresent = true;
                    break;
                }
            }
            if ($alreadyPresent === false) {
                if (count($_SESSION['ZuletztBesuchteArtikel']) < $nMaxAnzahl) {
                    $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
                } else {
                    $oTMP_arr = array_reverse($_SESSION['ZuletztBesuchteArtikel']);
                    array_pop($oTMP_arr);
                    $oTMP_arr                           = array_reverse($oTMP_arr);
                    $oTMP_arr[]                         = $oArtikel;
                    $_SESSION['ZuletztBesuchteArtikel'] = $oTMP_arr;
                }
            }
        } else {
            $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
        }
        executeHook(HOOK_ARTIKEL_INC_ZULETZTANGESEHEN);
    }

    /**
     * @deprecated since 5.0.0
     * @param int $kSeite
     * @return string
     */
    public function mappekSeite(int $kSeite): string
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        switch ($kSeite) {
            default:
            case PAGE_UNBEKANNT:
                return 'Unbekannt';
            case PAGE_ARTIKEL:
                return 'Artikeldetails';
            case PAGE_ARTIKELLISTE:
                return 'Artikelliste';
            case PAGE_WARENKORB:
                return 'Warenkorb';
            case PAGE_MEINKONTO:
                return 'Mein Konto';
            case PAGE_KONTAKT:
                return 'Kontakt';
            case PAGE_UMFRAGE:
                return 'Umfrage';
            case PAGE_NEWS:
                return 'News';
            case PAGE_NEWSLETTER:
                return 'Newsletter';
            case PAGE_LOGIN:
                return 'Login';
            case PAGE_REGISTRIERUNG:
                return 'Registrierung';
            case PAGE_BESTELLVORGANG:
                return 'Bestellvorgang';
            case PAGE_BEWERTUNG:
                return 'Bewertung';
            case PAGE_DRUCKANSICHT:
                return 'Druckansicht';
            case PAGE_PASSWORTVERGESSEN:
                return 'Passwort vergessen';
            case PAGE_WARTUNG:
                return 'Wartung';
            case PAGE_WUNSCHLISTE:
                return 'Wunschliste';
            case PAGE_VERGLEICHSLISTE:
                return 'Vergleichsliste';
            case PAGE_STARTSEITE:
                return 'Startseite';
            case PAGE_VERSAND:
                return 'Versand';
            case PAGE_AGB:
                return 'AGB';
            case PAGE_DATENSCHUTZ:
                return 'Datenschutz';
            case PAGE_TAGGING:
                return 'Tagging';
            case PAGE_LIVESUCHE:
                return 'Livesuche';
            case PAGE_HERSTELLER:
                return 'Hersteller';
            case PAGE_SITEMAP:
                return 'Sitemap';
            case PAGE_GRATISGESCHENK:
                return 'Gratis Geschenk';
            case PAGE_WRB:
                return 'WRB';
            case PAGE_PLUGIN:
                return 'Plugin';
            case PAGE_NEWSLETTERARCHIV:
                return 'Newsletterarchiv';
            case PAGE_EIGENE:
                return 'Eigene Seite';
            case PAGE_AUSWAHLASSISTENT:
                return 'Auswahlassistent';
            case PAGE_BESTELLABSCHLUSS:
                return 'Bestellabschluss';
            case PAGE_RMA:
                return 'Warenr&uuml;cksendung';
        }
    }

    /**
     * @param int  $nSeite
     * @param bool $bGlobal
     * @return array|bool
     * @deprecated since 5.0.0
     */
    public function holeBoxAnzeige(int $nSeite, bool $bGlobal = true)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $oBoxAnzeige = [];
        $oBox_arr    = Shop::Container()->getDB()->selectAll('tboxenanzeige', 'nSeite', $nSeite);
        if (is_array($oBox_arr) && count($oBox_arr)) {
            foreach ($oBox_arr as $oBox) {
                $oBoxAnzeige[$oBox->ePosition] = (boolean)$oBox->bAnzeigen;
            }
            $this->visibility = $oBoxAnzeige;

            return $oBoxAnzeige;
        }

        return $nSeite !== 0 && $bGlobal
            ? $this->holeBoxAnzeige(0)
            : false;
    }

    /**
     * @param int      $nSeite
     * @param string   $ePosition
     * @param bool|int $bAnzeigen
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBoxAnzeige(int $nSeite, string $ePosition, $bAnzeigen): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $bAnzeigen      = (int)$bAnzeigen;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $bOk = Shop::Container()->getDB()->executeQueryPrepared(
                        "REPLACE INTO tboxenanzeige 
                        SET bAnzeigen = :show,
                            nSeite = :page, 
                            ePosition = :position",
                        ['show' => $bAnzeigen, 'page' => $i, 'position' => $ePosition],
                        \DB\ReturnType::DEFAULT
                    ) && $bOk;
            }

            return $bOk;
        }

        return Shop::Container()->getDB()->executeQueryPrepared(
            "REPLACE INTO tboxenanzeige 
                SET bAnzeigen = :show, 
                    nSeite = :page, 
                    ePosition = :position",
            ['show' => $bAnzeigen, 'page' => $nSeite, 'position' => $ePosition],
            \DB\ReturnType::DEFAULT
        );
    }

    /**
     * @param int $kBoxvorlage
     * @return stdClass|null
     * @deprecated since 5.0.0
     */
    public function holeVorlage(int $kBoxvorlage)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getDB()->select('tboxvorlage', 'kBoxvorlage', $kBoxvorlage);
    }

    /**
     * @param string $ePosition
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeContainer(string $ePosition): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getDB()->selectAll('tboxen', ['kBoxvorlage', 'ePosition'], [0, $ePosition], 'kBox',
            'kBox ASC');
    }

    /**
     * @param int    $kBoxvorlage
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $kContainer
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBox(int $kBoxvorlage, int $nSeite, string $ePosition = 'left', int $kContainer = 0): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $validPageTypes = $this->getValidPageTypes();
        $oBox           = new stdClass();
        $oBoxVorlage    = $this->holeVorlage($kBoxvorlage);
        $oBox->cTitel   = '';
        if ($oBoxVorlage) {
            $oBox->cTitel = $oBoxVorlage->cName;
        }

        $oBox->kBoxvorlage = $kBoxvorlage;
        $oBox->ePosition   = $ePosition;
        $oBox->kContainer  = $kContainer;
        $oBox->kCustomID   = (isset($oBoxVorlage->kCustomID) && is_numeric($oBoxVorlage->kCustomID))
            ? (int)$oBoxVorlage->kCustomID
            : 0;

        $kBox = Shop::Container()->getDB()->insert('tboxen', $oBox);
        if ($kBox) {
            $cnt                = count($validPageTypes);
            $oBoxSichtbar       = new stdClass();
            $oBoxSichtbar->kBox = $kBox;
            for ($i = 0; $i < $cnt; $i++) {
                $oBoxSichtbar->nSort  = $this->letzteSortierID($nSeite, $ePosition, $kContainer);
                $oBoxSichtbar->kSeite = $i;
                $oBoxSichtbar->bAktiv = ($nSeite === $i || $nSeite === 0) ? 1 : 0;
                Shop::Container()->getDB()->insert('tboxensichtbar', $oBoxSichtbar);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $kBox
     * @return stdClass
     * @deprecated since 5.0.0
     */
    public function holeBox(int $kBox): stdClass
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $oBox = Shop::Container()->getDB()->query(
            "SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = " . $kBox,
            \DB\ReturnType::SINGLE_OBJECT
        );

        $oBox->oSprache_arr      = ($oBox && ($oBox->eTyp === 'text' || $oBox->eTyp === 'catbox'))
            ? $this->gibBoxInhalt($kBox)
            : [];
        $oBox->kBox              = (int)$oBox->kBox;
        $oBox->kBoxvorlage       = (int)$oBox->kBoxvorlage;
        $oBox->supportsRevisions = $oBox->kBoxvorlage === 30 || $oBox->kBoxvorlage === 31; // only "Eigene Box"

        return $oBox;
    }

    /**
     * @param int    $kBox
     * @param string $cTitel
     * @param int    $kCustomID
     * @return bool
     * @deprecated since 5.0.0
     */
    public function bearbeiteBox(int $kBox, $cTitel, int $kCustomID = 0): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $oBox            = new stdClass();
        $oBox->cTitel    = $cTitel;
        $oBox->kCustomID = $kCustomID;

        return Shop::Container()->getDB()->update('tboxen', 'kBox', $kBox, $oBox) >= 0;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @param string $cTitel
     * @param string $cInhalt
     * @return bool
     * @deprecated since 5.0.0
     */
    public function bearbeiteBoxSprache(int $kBox, string $cISO, string $cTitel, string $cInhalt): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $oBox = Shop::Container()->getDB()->select('tboxsprache', 'kBox', $kBox, 'cISO', $cISO);
        if (isset($oBox->kBox)) {
            $_upd          = new stdClass();
            $_upd->cTitel  = $cTitel;
            $_upd->cInhalt = $cInhalt;

            return Shop::Container()->getDB()->update('tboxsprache', ['kBox', 'cISO'], [$kBox, $cISO], $_upd) >= 0;
        }
        $_ins          = new stdClass();
        $_ins->kBox    = $kBox;
        $_ins->cISO    = $cISO;
        $_ins->cTitel  = $cTitel;
        $_ins->cInhalt = $cInhalt;

        return Shop::Container()->getDB()->insert('tboxsprache', $_ins) > 0;
    }

    /**
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $kContainer
     * @return int
     * @deprecated since 5.0.0
     */
    public function letzteSortierID(int $nSeite, string $ePosition = 'left', int $kContainer = 0): int
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $oBox = Shop::Container()->getDB()->queryPrepared(
            'SELECT tboxensichtbar.nSort, tboxen.ePosition
                FROM tboxensichtbar
                LEFT JOIN tboxen
                    ON tboxensichtbar.kBox = tboxen.kBox
                    WHERE tboxensichtbar.kSeite = :pageid
                        AND tboxen.ePosition = :position
                        AND tboxen.kContainer = :containerid
                ORDER BY tboxensichtbar.nSort DESC LIMIT 1',
            [
                'pageid'      => $nSeite,
                'position'    => $ePosition,
                'containerid' => $kContainer
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return $oBox ? ++$oBox->nSort : 0;
    }

    /**
     * @param int          $kBox
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     * @deprecated since 5.0.0
     */
    public function filterBoxVisibility(int $kBox, int $kSeite, $cFilter = ''): int
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        if (is_array($cFilter)) {
            $cFilter = array_unique($cFilter);
            $cFilter = implode(',', $cFilter);
        }
        $_upd          = new stdClass();
        $_upd->cFilter = $cFilter;

        return Shop::Container()->getDB()->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, $kSeite], $_upd);
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param int      $nSort
     * @param bool|int $bAktiv
     * @return bool
     * @deprecated since 5.0.0
     */
    public function sortBox(int $kBox, int $nSeite, int $nSort, $bAktiv = true): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $bAktiv         = (int)$bAktiv;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $oBox = Shop::Container()->getDB()->select('tboxensichtbar', 'kBox', $kBox);
                $bOk  = !empty($oBox)
                    ? (Shop::Container()->getDB()->query(
                            "UPDATE tboxensichtbar 
                                SET nSort = " . $nSort . ",
                                    bAktiv = " . $bAktiv . " 
                                WHERE kBox = " . $kBox . " 
                                    AND kSeite = " . $i, 4
                        ) !== false)
                    : (Shop::Container()->getDB()->query(
                            "INSERT INTO tboxensichtbar 
                                SET kBox = " . $kBox . ",
                                    kSeite = " . $i . ", 
                                    nSort = " . $nSort . ", 
                                    bAktiv = " . $bAktiv, 4
                        ) === true);
            }

            return $bOk;
        }

        return Shop::Container()->getDB()->query(
                "REPLACE INTO tboxensichtbar 
                  SET kBox = " . $kBox . ", 
                      kSeite = " . $nSeite . ", 
                      nSort = " . $nSort . ", 
                      bAktiv = " . $bAktiv, 3
            ) !== false;
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param bool|int $bAktiv
     * @return bool
     * @deprecated since 5.0.0
     */
    public function aktiviereBox(int $kBox, int $nSeite, $bAktiv = true): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $bAktiv         = (int)$bAktiv;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $_upd         = new stdClass();
                $_upd->bAktiv = $bAktiv;
                $bOk          = Shop::Container()->getDB()->update(
                        'tboxensichtbar',
                        ['kBox', 'kSeite'],
                        [$kBox, $i],
                        $_upd
                    ) >= 0;
            }

            return $bOk;
        }
        $_upd         = new stdClass();
        $_upd->bAktiv = $bAktiv;

        return Shop::Container()->getDB()->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, 0], $_upd) >= 0;
    }

    /**
     * @param int $kBox
     * @return bool
     * @deprecated since 5.0.0
     */
    public function loescheBox(int $kBox): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $bOk = Shop::Container()->getDB()->delete('tboxen', 'kBox', $kBox) > 0;

        return $bOk
            ? (Shop::Container()->getDB()->delete('tboxensichtbar', 'kBox', $kBox) > 0)
            : false;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function gibLinkGruppen(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getDB()->query("SELECT * FROM tlinkgruppe", \DB\ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @param int $kBoxvorlage
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isVisible(int $kBoxvorlage): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        foreach ($this->boxes as $_position => $_boxes) {
            foreach ($_boxes as $_box) {
                if ((int)$_box->kBoxvorlage === $kBoxvorlage) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Filter\ProductFilter              $pf
     * @param \Filter\ProductFilterSearchResults $sr
     * @return bool
     * @deprecated since 5.0.0
     */
    public function gibBoxenFilterNach(\Filter\ProductFilter $pf, \Filter\ProductFilterSearchResults $sr): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $cf  = $pf->getCategoryFilter();
        $mf  = $pf->getManufacturerFilter();
        $prf = $pf->getPriceRangeFilter();
        $rf  = $pf->getRatingFilter();
        $tf  = $pf->tagFilterCompat;
        $afc = $pf->getAttributeFilterCollection();
        $ssf = $pf->getSearchSpecialFilter();
        $sf  = $pf->searchFilterCompat;

        $invis      = \Filter\Visibility::SHOW_NEVER();
        $visContent = \Filter\Visibility::SHOW_CONTENT();

        return ((!$cf->getVisibility()->equals($invis) && !$cf->getVisibility()->equals($visContent))
            || (!$mf->getVisibility()->equals($invis) && !$mf->getVisibility()->equals($visContent))
            || (!$prf->getVisibility()->equals($invis) && !$prf->getVisibility()->equals($visContent))
            || (!$rf->getVisibility()->equals($invis) && !$rf->getVisibility()->equals($visContent))
            || (!$tf->getVisibility()->equals($invis) && !$tf->getVisibility()->equals($visContent))
            || (!$afc->getVisibility()->equals($invis) && !$afc->getVisibility()->equals($visContent))
            || (!$ssf->getVisibility()->equals($invis) && !$ssf->getVisibility()->equals($visContent))
            || (!$sf->getVisibility()->equals($invis) && !$sf->getVisibility()->equals($visContent))
        );
    }

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function getRawData(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return $this->boxService->getRawData();
    }

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function compatGet(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $boxes = [];
        foreach ($this->rawData as $_type => $_boxes) {
            $boxes[$_type] = [];
            foreach ($_boxes as $_box) {
                $boxes[$_type][] = $_box['obj'];
            }
        }

        return $boxes;
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $oCloud_arr
     * @param string $nSpeed
     * @param string $nOpacity
     * @param bool   $cColor
     * @param bool   $cColorHover
     * @return string
     * @deprecated since 5.0.0
     */
    public static function gibJSONString(
        $oCloud_arr,
        $nSpeed = '1',
        $nOpacity = '0.2',
        $cColor = false,
        $cColorHover = false
    ): string {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $iCur = 0;
        $iMax = 15;
        if (!count($oCloud_arr)) {
            return '';
        }
        $oTags_arr                       = [];
        $oTags_arr['options']['speed']   = $nSpeed;
        $oTags_arr['options']['opacity'] = $nOpacity;
        $gibTagFarbe                     = function () {
            $cColor = '';
            $cCodes = ['00', '33', '66', '99', 'CC', 'FF'];
            for ($i = 0; $i < 3; $i++) {
                $cColor .= $cCodes[rand(0, count($cCodes) - 1)];
            }

            return '0x' . $cColor;
        };

        foreach ($oCloud_arr as $oCloud) {
            if ($iCur++ >= $iMax) {
                break;
            }
            $cName               = $oCloud->cName ?? $oCloud->cSuche;
            $cRandomColor        = (!$cColor || !$cColorHover) ? $gibTagFarbe() : '';
            $cName               = urlencode($cName);
            $cName               = str_replace('+', ' ', $cName); /* fix :) */
            $oTags_arr['tags'][] = [
                'name'  => $cName,
                'url'   => $oCloud->cURL,
                'size'  => (count($oCloud_arr) <= 5) ? '100' : (string)($oCloud->Klasse * 10), /* 10 bis 100 */
                'color' => $cColor ?: $cRandomColor,
                'hover' => $cColorHover ?: $cRandomColor
            ];
        }

        return urlencode(json_encode($oTags_arr));
    }

    /**
     * get classname for sidebar panels
     *
     * @return string
     * @deprecated since 5.0.0
     */
    public function getClass(): string
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $class = '';
        $i     = 0;
        foreach ($this->boxes as $position => $_boxes) {
            if ($_boxes !== null) {
                $class .= ($i !== 0 ? ' ' : '') . 'panel_' . $position;
            }
            ++$i;
        }

        return $class;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getInvisibleBoxes(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $unavailabe = \Functional\filter(Template::getInstance()->getBoxLayoutXML(), function ($e) {
            return $e === false;
        });
        $mapped     = \Functional\map($unavailabe, function ($e, $key) {
            return "'" . $key . "'";
        });

        return Shop::Container()->getDB()->query(
            'SELECT tboxen.*, tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate 
                FROM tboxen 
                    LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE ePosition IN (' . implode(',', $mapped) . ')',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getValidPageTypes(): array
    {
        return [
            PAGE_UNBEKANNT,
            PAGE_ARTIKEL,
            PAGE_ARTIKELLISTE,
            PAGE_WARENKORB,
            PAGE_MEINKONTO,
            PAGE_KONTAKT,
            PAGE_UMFRAGE,
            PAGE_NEWS,
            PAGE_NEWSLETTER,
            PAGE_LOGIN,
            PAGE_REGISTRIERUNG,
            PAGE_BESTELLVORGANG,
            PAGE_BEWERTUNG,
            PAGE_DRUCKANSICHT,
            PAGE_PASSWORTVERGESSEN,
            PAGE_WARTUNG,
            PAGE_WUNSCHLISTE,
            PAGE_VERGLEICHSLISTE,
            PAGE_STARTSEITE,
            PAGE_VERSAND,
            PAGE_AGB,
            PAGE_DATENSCHUTZ,
            PAGE_TAGGING,
            PAGE_LIVESUCHE,
            PAGE_HERSTELLER,
            PAGE_SITEMAP,
            PAGE_GRATISGESCHENK,
            PAGE_WRB,
            PAGE_PLUGIN,
            PAGE_NEWSLETTERARCHIV,
            PAGE_NEWSARCHIV,
            PAGE_EIGENE,
            PAGE_AUSWAHLASSISTENT,
            PAGE_BESTELLABSCHLUSS,
            PAGE_RMA
        ];
    }
}
