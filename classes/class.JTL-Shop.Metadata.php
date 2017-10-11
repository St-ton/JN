<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Metadata
 */
class Metadata
{
    /**
     * @var Navigationsfilter
     */
    private $navigationsfilter;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var string
     */
    private $breadCrumb;

    /**
     * Metadata constructor.
     * @param Navigationsfilter $navigationsfilter
     */
    public function __construct(Navigationsfilter $navigationsfilter)
    {
        $this->navigationsfilter = $navigationsfilter;
        $this->conf              = $navigationsfilter->getConfig();
    }

    /**
     * Holt die Globalen Metaangaben und Return diese als Assoc Array wobei die Keys => kSprache sind
     *
     * @return array|mixed
     * @former holeGlobaleMetaAngaben()
     */
    public static function getGlobalMetaData()
    {
        $cacheID = 'jtl_glob_meta';
        if (($globalMeta = Shop::Cache()->get($cacheID)) !== false) {
            return $globalMeta;
        }
        $globalMeta = [];
        $globalTmp  = Shop::DB()->query("SELECT cName, kSprache, cWertName FROM tglobalemetaangaben ORDER BY kSprache", 2);
        foreach ($globalTmp as $data) {
            if (!isset($globalMeta[$data->kSprache])) {
                $globalMeta[$data->kSprache] = new stdClass();
            }
            $cName                               = $data->cName;
            $globalMeta[$data->kSprache]->$cName = $data->cWertName;
        }
        Shop::Cache()->set($cacheID, $globalMeta, [CACHING_GROUP_CORE]);

        return $globalMeta;
    }

    /**
     * @return array
     * @former holeExcludedKeywords()
     */
    public static function getExcludes()
    {
        $exclude  = [];
        $keyWords = Shop::DB()->query("SELECT * FROM texcludekeywords ORDER BY cISOSprache", 2);
        foreach ($keyWords as $keyWord) {
            $exclude[$keyWord->cISOSprache] = $keyWord;
        }

        return $exclude;
    }

    /**
     * Erhält einen String aus dem alle nicht erlaubten Wörter rausgefiltert werden
     *
     * @param string $cString
     * @param array  $oExcludesKeywords_arr
     * @return string
     * @former gibExcludesKeywordsReplace()
     */
    public static function getFilteredString($cString, $oExcludesKeywords_arr)
    {
        if (is_array($oExcludesKeywords_arr) && count($oExcludesKeywords_arr) > 0) {
            return str_replace(array_map(
                function ($k) {
                    return ' ' . $k . ' ';
                },
                $oExcludesKeywords_arr
            ), ' ', $cString);
        }

        return $cString;
    }

    /**
     * Mapped die Suchspecial Einstellungen und liefert die Einstellungswerte als Assoc Array zurück.
     * Das Array kann via kKey Assoc angesprochen werden.
     *
     * @param array $config
     * @return array
     * @former gibSuchspecialEinstellungMapping()
     */
    public static function getSearchSpecialConfigMapping($config)
    {
        $mapping = [];
        if (is_array($config) && count($config) > 0) {
            foreach ($config as $key => $oSuchspecialEinstellung) {
                switch ($key) {
                    case 'suchspecials_sortierung_bestseller':
                        $mapping[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                        break;
                    case 'suchspecials_sortierung_sonderangebote':
                        $mapping[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                        break;
                    case 'suchspecials_sortierung_neuimsortiment':
                        $mapping[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                        break;
                    case 'suchspecials_sortierung_topangebote':
                        $mapping[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                        break;
                    case 'suchspecials_sortierung_inkuerzeverfuegbar':
                        $mapping[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                        break;
                    case 'suchspecials_sortierung_topbewertet':
                        $mapping[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                        break;
                    default:
                        break;
                }
            }
        }

        return $mapping;
    }

    /**
     * @param stdClass       $oMeta
     * @param array          $oArtikel_arr
     * @param stdClass       $oSuchergebnisse
     * @param array          $globalMeta
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $globalMeta, $oKategorie = null ) {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        $maxLength = !empty($this->conf['metaangaben']['global_meta_maxlaenge_description'])
            ? (int)$this->conf['metaangaben']['global_meta_maxlaenge_description']
            : 0;
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaDescription) > 0) {
            $oMeta->cMetaDescription = strip_tags($oMeta->cMetaDescription);

            return prepareMeta(
                $oMeta->cMetaDescription,
                null,
                $maxLength
            );
        }
        // Kategorieattribut?
        $cKatDescription = '';
        $languageID      = $this->navigationsfilter->getLanguageID();
        if ($this->navigationsfilter->hasCategory()) {
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->navigationsfilter->getCategory()->getValue());
            if (!empty($oKategorie->cMetaDescription)) {
                // meta description via new method
                return prepareMeta(
                    strip_tags($oKategorie->cMetaDescription),
                    null,
                    $maxLength
                );
            }
            if (!empty($oKategorie->categoryAttributes['meta_description']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
                return prepareMeta(
                    strip_tags($oKategorie->categoryAttributes['meta_description']->cWert),
                    null,
                    $maxLength
                );
            }
            if (!empty($oKategorie->KategorieAttribute['meta_description'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                return prepareMeta(
                    strip_tags($oKategorie->KategorieAttribute['meta_description']),
                    null,
                    $maxLength
                );
            }
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (!empty($oKategorie->cBeschreibung)) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $oKategorie->cBeschreibung));
            } elseif ($oKategorie->bUnterKategorien) {
                // Hat die aktuelle Kategorie Unterkategorien?
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);

                if (!empty($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (!empty($oUnterkat->cName)) {
                            $cKatDescription .= $i > 0
                                ? ', ' . strip_tags($oUnterkat->cName)
                                : strip_tags($oUnterkat->cName);
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription  = str_replace('"', '', $cKatDescription);
                $cKatDescription  = StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                $cMetaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                    ? trim(
                        strip_tags($globalMeta[$languageID]->Meta_Description_Praefix) .
                        ' ' .
                        $cKatDescription
                    )
                    : trim($cKatDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1
                    && $oSuchergebnisse->ArtikelVon > 0
                    && $oSuchergebnisse->ArtikelBis > 0
                ) {
                    $cMetaDescription .= ', ' . Shop::Lang()->get('products', 'global') .
                        " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
                }

                return prepareMeta($cMetaDescription, null, $maxLength);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $cMetaDescription = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr);
            $nCount       = min(12, count($oArtikel_arr));
            $cArtikelName = '';
            for ($i = 0; $i < $nCount; ++$i) {
                $cArtikelName .= $i > 0
                    ? ' - ' . $oArtikel_arr[$i]->cName
                    : $oArtikel_arr[$i]->cName;
            }
            $cArtikelName = str_replace('"', '', $cArtikelName);
            $cArtikelName = StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

            $cMetaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                ? $this->getMetaStart($oSuchergebnisse) .
                ': ' .
                $globalMeta[$languageID]->Meta_Description_Praefix .
                ' ' . $cArtikelName
                : $this->getMetaStart($oSuchergebnisse) . ': ' . $cArtikelName;
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if (
                $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 &&
                $oSuchergebnisse->ArtikelVon > 0 &&
                $oSuchergebnisse->ArtikelBis > 0
            ) {
                $cMetaDescription .= ', ' . Shop::Lang()->get('products', 'global') . ' ' .
                    $oSuchergebnisse->ArtikelVon . ' - ' . $oSuchergebnisse->ArtikelBis;
            }
        }

        return prepareMeta(strip_tags($cMetaDescription), null, $maxLength);
    }

    /**
     * @param stdClass       $oMeta
     * @param array          $oArtikel_arr
     * @param Kategorie|null $oKategorie
     * @return mixed|string
     */
    public function getMetaKeywords($oMeta, $oArtikel_arr, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaKeywords) > 0) {
            $oMeta->cMetaKeywords = strip_tags($oMeta->cMetaKeywords);

            return $oMeta->cMetaKeywords;
        }
        // Kategorieattribut?
        $cKatKeywords = '';
        if ($this->navigationsfilter->hasCategory()) {
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->navigationsfilter->getCategory()->getValue());
            if (!empty($oKategorie->cMetaKeywords)) {
                // meta keywords via new method
                return strip_tags($oKategorie->cMetaKeywords);
            }
            if (!empty($oKategorie->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                return strip_tags($oKategorie->categoryAttributes['meta_keywords']->cWert);
            }
            if (!empty($oKategorie->KategorieAttribute['meta_keywords'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */

                return strip_tags($oKategorie->KategorieAttribute['meta_keywords']);
            }
        }
        // Keine eingestellten Metas vorhanden => baue Standard Metas
        $cMetaKeywords = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr); // Shuffle alle Artikel
            $nCount                = min(6, count($oArtikel_arr));
            $cArtikelName          = '';
            $excludes              = self::getExcludes();
            $oExcludesKeywords_arr = isset($excludes[$_SESSION['cISOSprache']]->cKeywords)
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = self::getFilteredString(
                    $oArtikel_arr[$i]->cName,
                    $oExcludesKeywords_arr
                ); // Filter nicht erlaubte Keywords
                if (strpos($cExcArtikelName, ' ') !== false) {
                    // Wenn der Dateiname aus mehreren Wörtern besteht
                    $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                    $cSubName        = '';
                    if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                        foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                            if (strlen($cSubNameTMP) > 2) {
                                $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                                $cSubName .= $j > 0
                                    ? ', ' . $cSubNameTMP
                                    : $cSubNameTMP;
                            }
                        }
                    }
                    $cArtikelName .= $cSubName;
                } elseif ($i > 0) {
                    $cArtikelName .= ', ' . $oArtikel_arr[$i]->cName;
                } else {
                    $cArtikelName .= $oArtikel_arr[$i]->cName;
                }
            }
            $cMetaKeywords = $cArtikelName;
            // Prüfe doppelte Einträge und lösche diese
            $cMetaKeywordsUnique_arr = [];
            $cMeta_arr               = explode(', ', $cMetaKeywords);
            if (is_array($cMeta_arr) && count($cMeta_arr) > 1) {
                foreach ($cMeta_arr as $cMeta) {
                    if (!in_array($cMeta, $cMetaKeywordsUnique_arr, true)) {
                        $cMetaKeywordsUnique_arr[] = $cMeta;
                    }
                }
                $cMetaKeywords = implode(', ', $cMetaKeywordsUnique_arr);
            }
        } elseif (!empty($oKategorie->kKategorie)) {
            // Hat die aktuelle Kategorie Unterkategorien?
            if ($oKategorie->bUnterKategorien) {
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);
                if (!empty($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                            $cKatKeywords .= $i > 0
                                ? ', ' . $oUnterkat->cName
                                : $oUnterkat->cName;
                        }
                    }
                }
            } elseif (!empty($oKategorie->cBeschreibung)) { // Hat die aktuelle Kategorie eine Beschreibung?
                $cKatKeywords = $oKategorie->cBeschreibung;
            }
            $cKatKeywords  = str_replace('"', '', $cKatKeywords);
            $cMetaKeywords = $cKatKeywords;

            return strip_tags($cMetaKeywords);
        }

        return strip_tags(StringHandler::htmlentitydecode(str_replace('"', '', $cMetaKeywords), ENT_NOQUOTES));
    }

    /**
     * @param stdClass       $oMeta
     * @param stdClass       $oSuchergebnisse
     * @param array          $globalMeta
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaTitle($oMeta, $oSuchergebnisse, $globalMeta, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $languageID = $this->navigationsfilter->getLanguageID();
        $append     = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        // Pruefen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaTitle) > 0) {
            $oMeta->cMetaTitle = strip_tags($oMeta->cMetaTitle);
            // Globalen Meta Title anhaengen
            if ($append === true && !empty($globalMeta[$languageID]->Title)) {
                return $this->truncateMetaTitle(
                    $oMeta->cMetaTitle . ' ' .
                    $globalMeta[$languageID]->Title
                );
            }

            return $this->truncateMetaTitle($oMeta->cMetaTitle);
        }
        // Set Default Titles
        $cMetaTitle = $this->getMetaStart($oSuchergebnisse);
        $cMetaTitle = str_replace('"', "'", $cMetaTitle);
        $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        // Kategorieattribute koennen Standard-Titles ueberschreiben
        if ($this->navigationsfilter->hasCategory()) {
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->navigationsfilter->getCategory()->getValue());
            if (!empty($oKategorie->cTitleTag)) {
                // meta title via new method
                $cMetaTitle = strip_tags($oKategorie->cTitleTag);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->categoryAttributes['meta_title']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
                $cMetaTitle = strip_tags($oKategorie->categoryAttributes['meta_title']->cWert);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->KategorieAttribute['meta_title'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cMetaTitle = strip_tags($oKategorie->KategorieAttribute['meta_title']);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            }
        }
        // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
        if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
            $cMetaTitle .= ', ' . Shop::Lang()->get('page') . ' ' .
                $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($globalMeta[$languageID]->Title)) {
            $cMetaTitle .= ' - ' . $globalMeta[$languageID]->Title;
        }
        // @todo: temp. fix to avoid destroyed header
        $cMetaTitle = str_replace(['<', '>'], ['&lt;', '&gt;'], $cMetaTitle);

        return $this->truncateMetaTitle($cMetaTitle);
    }

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
     *
     * @param stdClass $oSuchergebnisse
     * @return string
     */
    public function getMetaStart($oSuchergebnisse)
    {
        $cMetaTitle = '';
        // @todo: simplify
        // MerkmalWert
        if ($this->navigationsfilter->hasAttributeValue()) {
            $cMetaTitle .= $this->navigationsfilter->getAttributeValue()->getName();
        } elseif ($this->navigationsfilter->hasCategory()) { // Kategorie
            $cMetaTitle .= $this->navigationsfilter->getCategory()->getName();
        } elseif ($this->navigationsfilter->hasManufacturer()) { // Hersteller
            $cMetaTitle .= $this->navigationsfilter->getManufacturer()->getName();
        } elseif ($this->navigationsfilter->hasTag()) { // Tag
            $cMetaTitle .= $this->navigationsfilter->getTag()->getName();
        } elseif ($this->navigationsfilter->hasSearch()) { // Suchebegriff
            $cMetaTitle .= $this->navigationsfilter->getSearch()->getName();
            //@todo: does this work?
            //$cMetaTitle .= $this->Suche->getName();
        } elseif ($this->navigationsfilter->hasSearchQuery()) { // Suchebegriff
            $cMetaTitle .= $this->navigationsfilter->getSearchQuery()->getName();
        }  elseif ($this->navigationsfilter->hasSearchSpecial()) { // Suchspecial
            $cMetaTitle .= $this->navigationsfilter->getSearchSpecial()->getName();
        }
        // Kategoriefilter
        if ($this->navigationsfilter->hasCategoryFilter()) {
            $cMetaTitle .= ' ' . $this->navigationsfilter->getCategoryFilter()->getName();
        }
        // Herstellerfilter
        if (!empty($oSuchergebnisse->Herstellerauswahl[0]->cName) 
            && $this->navigationsfilter->hasManufacturerFilter()
        ) {
            $cMetaTitle .= ' ' . $this->navigationsfilter->getManufacturerFilter()->getName();
        }
        // Tagfilter
        if ($this->navigationsfilter->hasTagFilter()
            && $this->navigationsfilter->getTagFilter()[0]->cName !== null
        ) {
            $cMetaTitle .= ' ' . $this->navigationsfilter->getTagFilter()[0]->cName;
        }
        // Suchbegrifffilter
        if ($this->navigationsfilter->hasSearchFilter()) {
            foreach ($this->navigationsfilter->getSearchFilters() as $i => $oSuchFilter) {
                if ($oSuchFilter->cName !== null) {
                    $cMetaTitle .= ' ' . $oSuchFilter->getName();
                }
            }
        }
        // Suchspecialfilter
        if ($this->navigationsfilter->hasSearchSpecialFilter()) {
            switch ($this->navigationsfilter->getSearchSpecialFilter()->getValue()) {
                case SEARCHSPECIALS_BESTSELLER:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('bestsellers');
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('specialOffers');
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('newProducts');
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topOffers');
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('upcomingProducts');
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topReviews');
                    break;

                default:
                    break;
            }
        }
        // MerkmalWertfilter
        if ($this->navigationsfilter->hasAttributeFilter()) {
            foreach ($this->navigationsfilter->getAttributeFilter() as $oMerkmalFilter) {
                if ($oMerkmalFilter->cName !== null) {
                    $cMetaTitle .= ' ' . $oMerkmalFilter->cName;
                }
            }
        }

        return ltrim($cMetaTitle);
    }

    /**
     * @param string $cTitle
     * @return string
     */
    public function truncateMetaTitle($cTitle)
    {
        return ($length = (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0
            ? substr($cTitle, 0, $length)
            : $cTitle;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if ($this->navigationsfilter->hasCategory()) {
            $this->breadCrumb = $this->navigationsfilter->getCategory()->getName();

            return $this->breadCrumb;
        }
        if ($this->navigationsfilter->hasManufacturer()) {
            $this->breadCrumb = $this->navigationsfilter->getManufacturer()->getName();

            return Shop::Lang()->get('productsFrom') . ' ' . $this->breadCrumb;
        }
        if ($this->navigationsfilter->hasAttributeValue()) {
            $this->breadCrumb = $this->navigationsfilter->getAttributeValue()->getName();

            return Shop::Lang()->get('productsWith') . ' ' . $this->breadCrumb;
        }
        if ($this->navigationsfilter->hasTag()) {
            $this->breadCrumb = $this->navigationsfilter->getTag()->getName();

            return Shop::Lang()->get('showAllProductsTaggedWith') . ' ' . $this->breadCrumb;
        }
        if ($this->navigationsfilter->hasSearchSpecial()) {
            $this->breadCrumb = $this->navigationsfilter->getSearchSpecial()->getName();

            return $this->breadCrumb;
        }
        if ($this->navigationsfilter->hasSearch()) {
            $this->breadCrumb = $this->navigationsfilter->getSearch()->getName();
        } elseif ($this->navigationsfilter->getSearchQuery()->isInitialized()) {
            $this->breadCrumb = $this->navigationsfilter->getSearchQuery()->getName();
        }
        if (!empty($this->navigationsfilter->getSearch()->cSuche)
            || !empty($this->navigationsfilter->getSearchQuery()->cSuche)
        ) {
            return Shop::Lang()->get('for') . ' ' . $this->breadCrumb;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBreadCrumbName()
    {
        return $this->breadCrumb;
    }

    /**
     * @param bool     $bSeo
     * @param stdClass $oSeitenzahlen
     * @param int      $nMaxAnzeige
     * @param string   $cFilterShopURL
     * @return array
     * @former baueSeitenNaviURL
     */
    public function buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
    {
        if (strlen($cFilterShopURL) > 0) {
            $bSeo = false;
        }
        $cURL       = '';
        $oSeite_arr = [];
        $nVon       = 0; // Die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
        $nBis       = 0; // Begrenzt durch $nMaxAnzeige.
        $naviURL    = $this->navigationsfilter->getURL($bSeo);
        $bSeo       = $bSeo && strpos($naviURL, '?') === false;
        if (isset($oSeitenzahlen->MaxSeiten, $oSeitenzahlen->AktuelleSeite)
            && $oSeitenzahlen->MaxSeiten > 0
            && $oSeitenzahlen->AktuelleSeite > 0
        ) {
            $oSeitenzahlen->AktuelleSeite = (int)$oSeitenzahlen->AktuelleSeite;
            $nMax                         = (int)floor($nMaxAnzeige / 2);
            if ($oSeitenzahlen->MaxSeiten > $nMaxAnzeige) {
                if ($oSeitenzahlen->AktuelleSeite - $nMax >= 1) {
                    $nDiff = 0;
                    $nVon  = $oSeitenzahlen->AktuelleSeite - $nMax;
                } else {
                    $nVon  = 1;
                    $nDiff = $nMax - $oSeitenzahlen->AktuelleSeite + 1;
                }
                if ($oSeitenzahlen->AktuelleSeite + $nMax + $nDiff <= $oSeitenzahlen->MaxSeiten) {
                    $nBis = $oSeitenzahlen->AktuelleSeite + $nMax + $nDiff;
                } else {
                    $nDiff = $oSeitenzahlen->AktuelleSeite + $nMax - $oSeitenzahlen->MaxSeiten;
                    if ($nDiff === 0) {
                        $nVon -= ($nMaxAnzeige - ($nMax + 1));
                    } elseif ($nDiff > 0) {
                        $nVon = $oSeitenzahlen->AktuelleSeite - $nMax - $nDiff;
                    }
                    $nBis = (int)$oSeitenzahlen->MaxSeiten;
                }
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = $nVon; $i <= $nBis; ++$i) {
                    $oSeite         = new stdClass();
                    $oSeite->nSeite = $i;
                    if ($i === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }
                    $oSeite_arr[] = $oSeite;
                }
            } else {
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = 0; $i < $oSeitenzahlen->MaxSeiten; ++$i) {
                    $oSeite         = new stdClass();
                    $oSeite->nSeite = $i + 1;

                    if ($i + 1 === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }
                    $oSeite_arr[] = $oSeite;
                }
            }
            // Baue Zurück-URL
            $oSeite_arr['zurueck']       = new stdClass();
            $oSeite_arr['zurueck']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite > 1) {
                $oSeite_arr['zurueck']->nSeite = (int)$oSeitenzahlen->AktuelleSeite - 1;
                if ($oSeite_arr['zurueck']->nSeite === 1) {
                    $oSeite_arr['zurueck']->cURL = $naviURL . $cFilterShopURL;
                } else {
                    if ($bSeo) {
                        $cURL = $naviURL;
                        if (strpos(basename($cURL), 'index.php') !== false) {
                            $oSeite_arr['zurueck']->cURL = $cURL . '&amp;seite=' .
                                $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                        } else {
                            $oSeite_arr['zurueck']->cURL = $cURL . SEP_SEITE .
                                $oSeite_arr['zurueck']->nSeite;
                        }
                    } else {
                        $oSeite_arr['zurueck']->cURL = $naviURL . '&amp;seite=' .
                            $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                    }
                }
            }
            // Baue Vor-URL
            $oSeite_arr['vor']       = new stdClass();
            $oSeite_arr['vor']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite < $oSeitenzahlen->maxSeite) {
                $oSeite_arr['vor']->nSeite = $oSeitenzahlen->AktuelleSeite + 1;
                if ($bSeo) {
                    $cURL = $naviURL;
                    if (strpos(basename($cURL), 'index.php') !== false) {
                        $oSeite_arr['vor']->cURL = $cURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                    } else {
                        $oSeite_arr['vor']->cURL = $cURL . SEP_SEITE . $oSeite_arr['vor']->nSeite;
                    }
                } else {
                    $oSeite_arr['vor']->cURL = $naviURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                }
            }
        }

        return $oSeite_arr;
    }

    /**
     * @param int $nDarstellung
     * @return stdClass
     * @former gibErweiterteDarstellung
     */
    public function getExtendedView($nDarstellung = 0)
    {
        if (!isset($_SESSION['oErweiterteDarstellung'])) {
            $nStdDarstellung                                    = 0;
            $_SESSION['oErweiterteDarstellung']                 = new stdClass();
            $_SESSION['oErweiterteDarstellung']->cURL_arr       = [];
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

            if ($this->navigationsfilter->hasCategory()) {
                $oKategorie = new Kategorie($this->navigationsfilter->getCategory()->getValue());
                if (!empty($oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG])) {
                    $nStdDarstellung = (int)$oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG];
                }
            }
            if ($nDarstellung === 0
                && isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'])
                && (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
            ) {
                $nStdDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
            }
            if ($nStdDarstellung > 0) {
                switch ($nStdDarstellung) {
                    case ERWDARSTELLUNG_ANSICHT_LISTE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                        }
                        break;
                    default: // when given invalid option from wawi attribute
                        $nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                        ) { // fallback to configured default
                            $nDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
                        }
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                }
            } else {
                // Std ist Listendarstellung
                $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                if (isset($_SESSION['ArtikelProSeite'])) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                        (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
            }
        }
        if ($nDarstellung > 0) {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
            switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
            }

            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        if (isset($_SESSION['oErweiterteDarstellung'])) {
            $naviURL = $this->navigationsfilter->getURL(false) . '&amp;ed=';

            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL .
                ERWDARSTELLUNG_ANSICHT_LISTE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL .
                ERWDARSTELLUNG_ANSICHT_GALERIE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL .
                ERWDARSTELLUNG_ANSICHT_MOSAIK;
        }

        return $_SESSION['oErweiterteDarstellung'];
    }

    /**
     * @param bool $bExtendedJTLSearch
     * @return array
     * @former gibSortierliste
     */
    public function getSortingOptions($bExtendedJTLSearch = false)
    {
        $sortingOptions = [];
        $search         = [];
        if ($bExtendedJTLSearch !== false) {
            static $names = [
                'suche_sortierprio_name',
                'suche_sortierprio_name_ab',
                'suche_sortierprio_preis',
                'suche_sortierprio_preis_ab'
            ];
            static $values = [
                SEARCH_SORT_NAME_ASC,
                SEARCH_SORT_NAME_DESC,
                SEARCH_SORT_PRICE_ASC,
                SEARCH_SORT_PRICE_DESC
            ];
            static $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
            foreach ($names as $i => $name) {
                $obj                  = new stdClass();
                $obj->name            = $name;
                $obj->value           = $values[$i];
                $obj->angezeigterName = Shop::Lang()->get($languages[$i]);

                $sortingOptions[] = $obj;
            }

            return $sortingOptions;
        }
        while (($obj = $this->getNextSearchPriority($search)) !== null) {
            $search[] = $obj->name;
            unset($obj->name);
            $sortingOptions[] = $obj;
        }

        return $sortingOptions;
    }

    /**
     * @param array $search
     * @return null|stdClass
     * @former gibNextSortPrio
     */
    public function getNextSearchPriority($search)
    {
        $max = 0;
        $obj = null;
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name']
            && !in_array('suche_sortierprio_name', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_name';
            $obj->value           = SEARCH_SORT_NAME_ASC;
            $obj->angezeigterName = Shop::Lang()->get('sortNameAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name_ab']
            && !in_array('suche_sortierprio_name_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_name_ab';
            $obj->value           = SEARCH_SORT_NAME_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortNameDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis']
            && !in_array('suche_sortierprio_preis', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis';
            $obj->value           = SEARCH_SORT_PRICE_ASC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab']
            && !in_array('suche_sortierprio_preis_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis_ab';
            $obj->value           = SEARCH_SORT_PRICE_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_ean']
            && !in_array('suche_sortierprio_ean', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_ean';
            $obj->value           = SEARCH_SORT_EAN;
            $obj->angezeigterName = Shop::Lang()->get('sortEan');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_ean'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum']
            && !in_array('suche_sortierprio_erstelldatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erstelldatum';
            $obj->value           = SEARCH_SORT_NEWEST_FIRST;
            $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer']
            && !in_array('suche_sortierprio_artikelnummer', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_artikelnummer';
            $obj->value           = SEARCH_SORT_PRODUCTNO;
            $obj->angezeigterName = Shop::Lang()->get('sortProductno');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand']
            && !in_array('suche_sortierprio_lagerbestand', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_lagerbestand';
            $obj->value           = SEARCH_SORT_AVAILABILITY;
            $obj->angezeigterName = Shop::Lang()->get('sortAvailability');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_gewicht']
            && !in_array('suche_sortierprio_gewicht', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_gewicht';
            $obj->value           = SEARCH_SORT_WEIGHT;
            $obj->angezeigterName = Shop::Lang()->get('sortWeight');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum']
            && !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erscheinungsdatum';
            $obj->value           = SEARCH_SORT_DATEOFISSUE;
            $obj->angezeigterName = Shop::Lang()->get('sortDateofissue');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bestseller']
            && !in_array('suche_sortierprio_bestseller', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bestseller';
            $obj->value           = SEARCH_SORT_BESTSELLER;
            $obj->angezeigterName = Shop::Lang()->get('bestseller');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bewertung']
            && !in_array('suche_sortierprio_bewertung', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bewertung';
            $obj->value           = SEARCH_SORT_RATING;
            $obj->angezeigterName = Shop::Lang()->get('rating');
        }

        return $obj;
    }

    /**
     * @param null|Kategorie $currentCategory
     */
    public function setUserSort($currentCategory = null)
    {
        $gpcSort = verifyGPCDataInteger('Sortierung');
        // Der User möchte die Standardsortierung wiederherstellen
        if ($gpcSort === 100) {
            unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
        }
        // Wenn noch keine Sortierung gewählt wurde => setze Standard-Sortierung aus Option
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        // Eine Suche wurde ausgeführt und die Suche wird auf die Suchtreffersuche eingestellt
        if ($this->navigationsfilter->getSearch()->kSuchCache > 0 && !isset($_SESSION['nUsersortierungWahl'])) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // Kategorie Funktionsattribut
        if (!empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $_SESSION['Usersortierung'] = $this->mapUserSorting(
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]
            );
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->navigationsfilter->hasSearchSpecial()) {
            // Gibt die Suchspecials als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = self::getSearchSpecialConfigMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            $ssConf = isset($oSuchspecialEinstellung_arr[$this->navigationsfilter->getSearchSpecial()->getValue()]) ?: null;
            if ($ssConf !== null && $ssConf !== -1 && count($oSuchspecialEinstellung_arr) > 0) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$this->navigationsfilter->getSearchSpecial()->getValue()];
            }
        }
        // Der User hat expliziet eine Sortierung eingestellt
        if ($gpcSort > 0 && $gpcSort !== 100) {
            $_SESSION['Usersortierung']         = $gpcSort;
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
            setFsession(0, $_SESSION['Usersortierung'], 0);
        }
    }

    /**
     * @param int|string $sort
     * @return int
     */
    public function mapUserSorting($sort)
    {
        // Ist die Usersortierung ein Integer => Return direkt den Integer
        preg_match('/\d+/', $sort, $cTreffer_arr);
        if (isset($cTreffer_arr[0]) && strlen($sort) === strlen($cTreffer_arr[0])) {
            return (int)$sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (strtolower($sort)) {
            case SEARCH_SORT_CRITERION_NAME:
                return SEARCH_SORT_NAME_ASC;

            case SEARCH_SORT_CRITERION_NAME_ASC:
                return SEARCH_SORT_NAME_ASC;

            case SEARCH_SORT_CRITERION_NAME_DESC:
                return SEARCH_SORT_NAME_DESC;

            case SEARCH_SORT_CRITERION_PRODUCTNO:
                return SEARCH_SORT_PRODUCTNO;

            case SEARCH_SORT_CRITERION_AVAILABILITY:
                return SEARCH_SORT_AVAILABILITY;

            case SEARCH_SORT_CRITERION_WEIGHT:
                return SEARCH_SORT_WEIGHT;

            case SEARCH_SORT_CRITERION_PRICE:
                return SEARCH_SORT_PRICE_ASC;

            case SEARCH_SORT_CRITERION_PRICE_ASC:
                return SEARCH_SORT_PRICE_ASC;

            case SEARCH_SORT_CRITERION_PRICE_DESC:
                return SEARCH_SORT_PRICE_DESC;

            case SEARCH_SORT_CRITERION_EAN:
                return SEARCH_SORT_EAN;

            case SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return SEARCH_SORT_NEWEST_FIRST;

            case SEARCH_SORT_CRITERION_DATEOFISSUE:
                return SEARCH_SORT_DATEOFISSUE;

            case SEARCH_SORT_CRITERION_BESTSELLER:
                return SEARCH_SORT_BESTSELLER;

            case SEARCH_SORT_CRITERION_RATING:
                return SEARCH_SORT_RATING;

            default:
                return SEARCH_SORT_STANDARD;
        }
    }
}
