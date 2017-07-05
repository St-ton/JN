<?php

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
     * Metadata constructor.
     * @param Navigationsfilter $navigationsfilter
     */
    public function __construct(Navigationsfilter $navigationsfilter)
    {
        $this->navigationsfilter = $navigationsfilter;
        $this->conf              = $navigationsfilter->getConfig();
        
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
            $excludes              = holeExcludedKeywords();
            $oExcludesKeywords_arr = isset($excludes[$_SESSION['cISOSprache']]->cKeywords)
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = gibExcludesKeywordsReplace(
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
        $append = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
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
            $cMetaTitle .= ', ' . Shop::Lang()->get('page', 'global') . ' ' .
                $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($globalMeta[$languageID]->Title)) {
            $cMetaTitle .= ' - ' . $globalMeta[$languageID]->Title;
        }

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
            && $this->navigationsfilter->getTagFilters()[0]->cName !== null
        ) {
            $cMetaTitle .= ' ' . $this->navigationsfilter->getTagFilters()[0]->cName;
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
            foreach ($this->navigationsfilter->getAttributeFilters() as $oMerkmalFilter) {
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
}
