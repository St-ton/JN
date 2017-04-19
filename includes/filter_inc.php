<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return stdClass
 * @deprecated since 4.06
 */
function buildSearchResults($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: buildSearchResults() called.', E_WARNING);
    return Shop::getNaviFilter()->getProducts();
}

/**
 * @param object $oSearchResult
 * @param int    $nProductCount
 * @param int    $nLimitN
 * @param int    $nPage
 * @param int    $nProductsPerPage
 * @param int    $nSettingMaxPageCount
 * @deprecated since 4.06
 */
function buildSearchResultPage(&$oSearchResult, $nProductCount, $nLimitN, $nPage, $nProductsPerPage = 25, $nSettingMaxPageCount = 25)
{
    trigger_error('filter_inc.php: buildSearchResultPage() called.', E_WARNING);
}

/**
 * @param object   $FilterSQL
 * @param int      $nArtikelProSeite
 * @param object   $NaviFilter
 * @param bool     $bExtern
 * @param stdClass $oSuchergebnisse
 * @return array
 * @deprecated since 4.06
 */
function gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, $bExtern, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: gibArtikelKeys() called.', E_WARNING);
    return Shop::getNaviFilter()->getProductKeys();
}

/**
 * @param object $NaviFilter
 * @return int
 * @deprecated since 4.06
 */
function gibAnzahlFilter($NaviFilter)
{
    trigger_error('filter_inc.php: gibAnzahlFilter() called.', E_WARNING);
    return Shop::getNaviFilter()->getFilterCount();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibHerstellerFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibHerstellerFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->HerstellerFilter->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibKategorieFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->KategorieFilter->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->searchFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibBewertungSterneFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->BewertungFilter->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: gibPreisspannenFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->PreisspannenFilter->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibTagFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->tagFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.06
 */
function gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchFilterJSONOptionen() called.', E_WARNING);
    $oSuchfilter_arr = gibSuchFilterOptionen($FilterSQL, $NaviFilter); // cURL
    foreach ($oSuchfilter_arr as $key => $oSuchfilter) {
        $oSuchfilter_arr[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
    }

    return Boxen::gibJSONString($oSuchfilter_arr);
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.06
 */
function gibTagFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibTagFilterJSONOptionen() called.', E_WARNING);
    $oTags_arr = gibTagFilterOptionen($FilterSQL, $NaviFilter);
    foreach ($oTags_arr as $key => $oTags) {
        $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
    }

    return Boxen::gibJSONString($oTags_arr);
}

/**
 * @param object         $FilterSQL
 * @param object         $NaviFilter
 * @param Kategorie|null $oAktuelleKategorie
 * @param bool           $bForce
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $oAktuelleKategorie = null, $bForce = false)
{
    trigger_error('filter_inc.php: gibMerkmalFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->attributeFilterCompat->getOptions();
}

/**
 * @deprecated since 4.06
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierMerkmalWerteNumerisch($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a->cWert < $b->cWert) ? -1 : 1;
}

/**
 * @deprecated since 4.06
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchspecialFilterOptionen() called.', E_WARNING);
    return Shop::getNaviFilter()->searchFilterCompat->getOptions();
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @param int    $kSpracheExt
 * @return int
 */
function bearbeiteSuchCache($NaviFilter, $kSpracheExt = 0)
{
    trigger_error('filter_inc.php: bearbeiteSuchCache() called.', E_WARNING);
    return Shop::getNaviFilter()->Suchanfrage->editSearchCache($kSpracheExt);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibSuchFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibSuchFilterSQL() no longer supported.');
}


/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibHerstellerFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibHerstellerFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibKategorieFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibKategorieFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibBewertungSterneFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibBewertungSterneFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibPreisspannenFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibPreisspannenFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibTagFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibTagFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibMerkmalFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibMerkmalFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibSuchspecialFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibSuchspecialFilterSQL() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @throws Exception
 */
function gibArtikelAttributFilterSQL($NaviFilter)
{
    throw new Exception('filter_inc.php: gibArtikelAttributFilterSQL() no longer supported.');
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmal
 * @return int
 * @deprecated since 4.06
 */
function gibMerkmalPosition($oMerkmalauswahl_arr, $kMerkmal)
{
    trigger_error('filter_inc.php: gibMerkmalPosition() called.', E_WARNING);
    return -1;
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmalWert
 * @return bool
 * @deprecated since 4.06
 */
function checkMerkmalWertVorhanden($oMerkmalauswahl_arr, $kMerkmalWert)
{
    trigger_error('filter_inc.php: checkMerkmalWertVorhanden() called.', E_WARNING);
    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.06
 */
function gibArtikelsortierung($NaviFilter)
{
    trigger_error('filter_inc.php: gibArtikelsortierung() called.', E_WARNING);
    return Shop::getNaviFilter()->getOrder()->orderBy;
}

/**
 * @param string|int $nUsersortierung
 * @return int
 * @deprecated since 4.06
 */
function mappeUsersortierung($nUsersortierung)
{
    trigger_error('filter_inc.php: mappeUsersortierung() called.', E_WARNING);
    return Shop::getNaviFilter()->mapUserSorting($nUsersortierung);
}

/**
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oZusatzFilter
 * @param int    $kSprache
 * @param bool   $bCanonical
 * @return string
 */
function gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter, $kSprache = 0, $bCanonical = false)
{
    trigger_error('filter_inc.php: gibNaviURL() called.', E_WARNING);
    return Shop::getNaviFilter()->getURL($bSeo, $oZusatzFilter, $bCanonical);
}

/**
 * @param object       $oPreis
 * @param object|array $oPreisspannenfilter_arr
 * @return string
 * @deprecated since 4.06
 */
function berechnePreisspannenSQL($oPreis, $oPreisspannenfilter_arr = null)
{
    trigger_error('filter_inc.php: berechnePreisspannenSQL() called.', E_WARNING);
    return Shop::getNaviFilter()->getPriceRangeSQL();
}

/**
 * @param float $fMax
 * @param float $fMin
 * @return stdClass
 */
function berechneMaxMinStep($fMax, $fMin)
{
    trigger_error('filter_inc.php: berechneMaxMinStep() called.', E_WARNING);
    return Shop::getNaviFilter()->PreisspannenFilter->calculateSteps($fMax, $fMin);
}

/**
 * @return null|string
 * @deprecated since 4.06
 */
function gibBrotNaviName()
{
    trigger_error('filter_inc.php: gibBrotNaviName() called.', E_WARNING);
    return Shop::getNaviFilter()->cBrotNaviName;
}

/**
 * @return string
 * @deprecated since 4.06
 */
function gibHeaderAnzeige()
{
    trigger_error('filter_inc.php: gibHeaderAnzeige() called.', E_WARNING);
    return Shop::getNaviFilter()->getHeader();
}

/**
 * @deprecated since 4.06
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: erstelleFilterLoesenURLs() called.', E_WARNING);
    Shop::getNaviFilter()->createUnsetFilterURLs($bSeo, $oSuchergebnisse);
}

/**
 * @deprecated since 4.06
 * @param string $cTitle
 * @return string
 * @deprecated since 4.06
 */
function truncateMetaTitle($cTitle)
{
    trigger_error('filter_inc.php: truncateMetaTitle() called.', E_WARNING);
    return Shop::getNaviFilter()->truncateMetaTitle($cTitle);
}

/**
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array $GlobaleMetaAngaben_arr
 * @return string
 * @deprecated since 4.06
 */
function gibNaviMetaTitle($NaviFilter, $oSuchergebnisse, $GlobaleMetaAngaben_arr)
{
    trigger_error('filter_inc.php: gibNaviMetaTitle() called.', E_WARNING);
    global $oMeta;
    return Shop::getNaviFilter()->getMetaTitle($oMeta, $oSuchergebnisse, $GlobaleMetaAngaben_arr);
}

/**
 * @param array  $oArtikel_arr
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $GlobaleMetaAngaben_arr
 * @return string
 * @deprecated since 4.06
 */
function gibNaviMetaDescription($oArtikel_arr, $NaviFilter, $oSuchergebnisse, $GlobaleMetaAngaben_arr)
{
    trigger_error('filter_inc.php: gibNaviMetaDescription() called.', E_WARNING);
    global $oMeta;
    return Shop::getNaviFilter()->getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $GlobaleMetaAngaben_arr);
}

/**
 * @param array  $oArtikel_arr
 * @param object $NaviFilter
 * @param array  $oExcludesKeywords_arr
 * @return mixed|string
 * @deprecated since 4.06
 */
function gibNaviMetaKeywords($oArtikel_arr, $NaviFilter, $oExcludesKeywords_arr = [])
{
    trigger_error('filter_inc.php: gibNaviMetaKeywords() called.', E_WARNING);
    global $oMeta;
    return Shop::getNaviFilter()->getMetaKeywords($oMeta, $oArtikel_arr);
}

/**
 * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
 *
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return string
 * @deprecated since 4.06
 */
function gibMetaStart($NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: gibMetaStart() called.', E_WARNING);
    return Shop::getNaviFilter()->getMetaStart($oSuchergebnisse);
}

/**
 * @todo
 * @param string $cSuche
 * @param int    $kSprache
 * @return int
 */
function gibSuchanfrageKey($cSuche, $kSprache)
{
    if (strlen($cSuche) > 0 && $kSprache > 0) {
        $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'cSuche', Shop::DB()->escape($cSuche), 'kSprache', (int)$kSprache);

        if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
            return (int)$oSuchanfrage->kSuchanfrage;
        }
    }

    return 0;
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function setzeUsersortierung($NaviFilter)
{
    trigger_error('filter_inc.php: setzeUsersortierung() called.', E_WARNING);
    global $AktuelleKategorie;
    Shop::getNaviFilter()->setUserSort($AktuelleKategorie);
}

/**
 * @todo
 * @param array  $Einstellungen
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nDarstellung = 0)
{
    global $smarty;

    if (!isset($_SESSION['oErweiterteDarstellung'])) {
        $nStdDarstellung                                    = 0;
        $_SESSION['oErweiterteDarstellung']                 = new stdClass();
        $_SESSION['oErweiterteDarstellung']->cURL_arr       = [];
        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

        if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
            $oKategorie = new Kategorie($NaviFilter->Kategorie->kKategorie);
            if (!empty($oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG])) {
                $nStdDarstellung = (int)$oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG];
            }
        }
        if ($nDarstellung === 0 &&
            isset($Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
            (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
        ) {
            $nStdDarstellung = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
        }
        if ($nStdDarstellung > 0) {
            switch ($nStdDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
                default: // when given invalid option from wawi attribute
                    $nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                    if (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                        (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                    ) { // fallback to configured default
                        $nDarstellung = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
                    }
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
            }
        } else {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE; // Std ist Listendarstellung
            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
            }
        }
    }
    if ($nDarstellung > 0) {
        $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
        switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
            case ERWDARSTELLUNG_ANSICHT_LISTE:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
                break;
            case ERWDARSTELLUNG_ANSICHT_GALERIE:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                }
                break;
            case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                }
                break;
        }

        if (isset($_SESSION['ArtikelProSeite'])) {
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
        }
    }
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        $naviURL                                                                      = gibNaviURL($NaviFilter, false, null);
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_LISTE;
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_GALERIE;
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_MOSAIK;
        $smarty->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }

    $smarty->assign('ERWDARSTELLUNG_ANSICHT_LISTE', ERWDARSTELLUNG_ANSICHT_LISTE)
           ->assign('ERWDARSTELLUNG_ANSICHT_GALERIE', ERWDARSTELLUNG_ANSICHT_GALERIE)
           ->assign('ERWDARSTELLUNG_ANSICHT_MOSAIK', ERWDARSTELLUNG_ANSICHT_MOSAIK);
}

/**
 * @todo
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oSeitenzahlen
 * @param int    $nMaxAnzeige
 * @param string $cFilterShopURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
{
    if (strlen($cFilterShopURL) > 0) {
        $bSeo = false;
    }
    $cURL       = '';
    $oSeite_arr = [];
    $nAnfang    = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
    $nEnde      = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeiten ist, wird nEnde gesetzt
    $nVon       = 0; // Die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
    $nBis       = 0; // Begrenzt durch $nMaxAnzeige.
    $naviURL    = gibNaviURL($NaviFilter, $bSeo, null);
    if (isset($oSeitenzahlen->MaxSeiten, $oSeitenzahlen->AktuelleSeite) &&
        $oSeitenzahlen->MaxSeiten > 0 &&
        $oSeitenzahlen->AktuelleSeite > 0
    ) {
        $oSeitenzahlen->AktuelleSeite = (int)$oSeitenzahlen->AktuelleSeite;
        $nMax                         = floor($nMaxAnzeige / 2);
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
                if ($nDiff == 0) {
                    $nVon -= ($nMaxAnzeige - ($nMax + 1));
                } elseif ($nDiff > 0) {
                    $nVon = $oSeitenzahlen->AktuelleSeite - $nMax - $nDiff;
                }
                $nBis = (int)$oSeitenzahlen->MaxSeiten;
            }
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = $nVon; $i <= $nBis; $i++) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i;

                if ($i === $oSeitenzahlen->AktuelleSeite) {
                    $oSeite->cURL = '';
                } else {
                    if ($oSeite->nSeite === 1) {
                        $oSeite->cURL = $naviURL . $cFilterShopURL;
                    } else {
                        if ($bSeo) {
                            $cURL = $naviURL;
                            if (strpos(basename($cURL), 'index.php') !== false) {
                                $oSeite->cURL = $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            } else {
                                $oSeite->cURL = $cURL . SEP_SEITE . $oSeite->nSeite;
                            }
                        } else {
                            $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                        }
                    }
                }

                $oSeite_arr[] = $oSeite;
            }
        } else {
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = 0; $i < $oSeitenzahlen->MaxSeiten; $i++) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i + 1;

                if ($i + 1 === $oSeitenzahlen->AktuelleSeite) {
                    $oSeite->cURL = '';
                } else {
                    if ($oSeite->nSeite === 1) {
                        $oSeite->cURL = $naviURL . $cFilterShopURL;
                    } else {
                        if ($bSeo) {
                            $cURL = $naviURL;
                            if (strpos(basename($cURL), 'index.php') !== false) {
                                $oSeite->cURL = $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            } else {
                                $oSeite->cURL = $cURL . SEP_SEITE . $oSeite->nSeite;
                            }
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
 * @todo
 * @param object $NaviFilter
 * @return mixed|stdClass
 * @deprecated since 4.06
 */
function bauFilterSQL($NaviFilter)
{
    die('bauFilterSQL()');
    $cacheID = 'fsql_' . md5(serialize($NaviFilter));
    if (($FilterSQL = Shop::Cache()->get($cacheID)) === false) {
        $FilterSQL = new stdClass();
        //Filter SQLs Objekte
        $FilterSQL->oHerstellerFilterSQL      = gibHerstellerFilterSQL($NaviFilter);
        $FilterSQL->oKategorieFilterSQL       = gibKategorieFilterSQL($NaviFilter);
        $FilterSQL->oMerkmalFilterSQL         = gibMerkmalFilterSQL($NaviFilter);
        $FilterSQL->oTagFilterSQL             = gibTagFilterSQL($NaviFilter);
        $FilterSQL->oBewertungSterneFilterSQL = gibBewertungSterneFilterSQL($NaviFilter);
        $FilterSQL->oPreisspannenFilterSQL    = gibPreisspannenFilterSQL($NaviFilter);
        $FilterSQL->oSuchFilterSQL            = gibSuchFilterSQL($NaviFilter);
        $FilterSQL->oSuchspecialFilterSQL     = gibSuchspecialFilterSQL($NaviFilter);
        $FilterSQL->oArtikelAttributFilterSQL = gibArtikelAttributFilterSQL($NaviFilter);

        executeHook(HOOK_FILTER_INC_BAUFILTERSQL, [
            'NaviFilter' => &$NaviFilter,
            'FilterSQL'  => &$FilterSQL
            ]
        );

        Shop::Cache()->set($cacheID, $FilterSQL, [CACHING_GROUP_CATEGORY]);
    }

    return $FilterSQL;
}

/**
 * @todo
 * @param null|array $Einstellungen
 * @param bool $bExtendedJTLSearch
 * @return array
 */
function gibSortierliste($Einstellungen = null, $bExtendedJTLSearch = false)
{
    $Sortierliste = [];
    $search       = [];
    if ($bExtendedJTLSearch) {
        $names     = ['suche_sortierprio_name', 'suche_sortierprio_name_ab', 'suche_sortierprio_preis', 'suche_sortierprio_preis_ab'];
        $values    = [SEARCH_SORT_NAME_ASC, SEARCH_SORT_NAME_DESC, SEARCH_SORT_PRICE_ASC, SEARCH_SORT_PRICE_DESC];
        $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
        foreach ($names as $i => $name) {
            $obj                  = new stdClass();
            $obj->name            = $name;
            $obj->value           = $values[$i];
            $obj->angezeigterName = Shop::Lang()->get($languages[$i], 'global');

            $Sortierliste[] = $obj;
        }

        return $Sortierliste;
    }
    if ($Einstellungen === null) {
        $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    }
    while (($obj = gibNextSortPrio($search, $Einstellungen)) !== null) {
        $search[] = $obj->name;
        unset($obj->name);
        $Sortierliste[] = $obj;
    }

    return $Sortierliste;
}

/**
 * @todo
 * @param array $search
 * @param null|array $Einstellungen
 * @return null|stdClass
 */
function gibNextSortPrio($search, $Einstellungen = null)
{
    if ($Einstellungen === null) {
        $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    }
    $max = 0;
    $obj = null;
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_name'] &&
        !in_array('suche_sortierprio_name', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name';
        $obj->value           = SEARCH_SORT_NAME_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameAsc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_name'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_name_ab'] &&
        !in_array('suche_sortierprio_name_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name_ab';
        $obj->value           = SEARCH_SORT_NAME_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameDesc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_name_ab'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_preis'] &&
        !in_array('suche_sortierprio_preis', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis';
        $obj->value           = SEARCH_SORT_PRICE_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_preis'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_preis_ab'] &&
        !in_array('suche_sortierprio_preis_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis_ab';
        $obj->value           = SEARCH_SORT_PRICE_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_preis_ab'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_ean'] &&
        !in_array('suche_sortierprio_ean', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_ean';
        $obj->value           = SEARCH_SORT_EAN;
        $obj->angezeigterName = Shop::Lang()->get('sortEan', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_ean'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_erstelldatum'] &&
        !in_array('suche_sortierprio_erstelldatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erstelldatum';
        $obj->value           = SEARCH_SORT_NEWEST_FIRST;
        $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_erstelldatum'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_artikelnummer'] &&
        !in_array('suche_sortierprio_artikelnummer', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_artikelnummer';
        $obj->value           = SEARCH_SORT_PRODUCTNO;
        $obj->angezeigterName = Shop::Lang()->get('sortProductno', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_artikelnummer'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_lagerbestand'] &&
        !in_array('suche_sortierprio_lagerbestand', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_lagerbestand';
        $obj->value           = SEARCH_SORT_AVAILABILITY;
        $obj->angezeigterName = Shop::Lang()->get('sortAvailability', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_lagerbestand'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_gewicht'] &&
        !in_array('suche_sortierprio_gewicht', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_gewicht';
        $obj->value           = SEARCH_SORT_WEIGHT;
        $obj->angezeigterName = Shop::Lang()->get('sortWeight', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_gewicht'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'] &&
        !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erscheinungsdatum';
        $obj->value           = SEARCH_SORT_DATEOFISSUE;
        $obj->angezeigterName = Shop::Lang()->get('sortDateofissue', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_bestseller'] &&
        !in_array('suche_sortierprio_bestseller', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bestseller';
        $obj->value           = SEARCH_SORT_BESTSELLER;
        $obj->angezeigterName = Shop::Lang()->get('bestseller', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_bestseller'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_bewertung'] &&
        !in_array('suche_sortierprio_bewertung', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bewertung';
        $obj->value           = SEARCH_SORT_RATING;
        $obj->angezeigterName = Shop::Lang()->get('rating', 'global');
    }

    return $obj;
}


/**
 * @todo
 * @param stdClass $oSuchCache
 * @param array $cSuchspalten_arr
 * @param array $cSuch_arr
 * @param int $nLimit
 *
 * @return int
 */
function bearbeiteSuchCacheFulltext($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0)
{
    $nLimit = (int)$nLimit;

    if ($oSuchCache->kSuchCache > 0) {
        $cArtikelSpalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return 'tartikel.' . $item_arr[1];
        }, $cSuchspalten_arr);

        $cSprachSpalten_arr = array_filter($cSuchspalten_arr, function ($item) {
            return preg_match('/tartikelsprache\.(.*)/', $item) ? true : false;
        });

        $match = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
        $cSQL  = "SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $match AS score
                    FROM tartikel
                    WHERE $match " . gibLagerfilter() . " ";

        if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
            $match  = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
            $cSQL  .= "UNION DISTINCT
                SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $match AS score
                    FROM tartikel
                    INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                    WHERE $match " . gibLagerfilter() . " ";
        }

        $cISQL = "INSERT INTO tsuchcachetreffer
                    SELECT kSuchCache, kArtikelTMP, ROUND(MAX(15 - score) * 10)
                    FROM ($cSQL) AS i
                    LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                        AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                    GROUP BY kSuchCache, kArtikelTMP" . ($nLimit > 0 ? " LIMIT $nLimit" : '');

        Shop::DB()->query($cISQL, 3);
    }

    return $oSuchCache->kSuchCache;
}

/**
 * @todo
 * @return bool
 */
function isFulltextIndexActive()
{
    static $active = null;

    if (!isset($active)) {
        $active = Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
        && Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1) ? true : false;
    }

    return $active;
}


/**
 * @todo
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierKategoriepfade($a, $b)
{
    return strcmp($a->cName, $b->cName);
}

/**
 * @todo?
 * @deprecated since 4.06
 * @param object $oExtendedJTLSearchResponse
 * @return array
 * @throws Exception
 */
function gibArtikelKeysExtendedJTLSearch($oExtendedJTLSearchResponse)
{
    throw new Exception('filter_inc.php: gibArtikelKeysExtendedJTLSearch() no longer supported.');
    $oArtikel_arr = [];
    if (isset($oExtendedJTLSearchResponse->oSearch->oItem_arr) &&
        is_array($oExtendedJTLSearchResponse->oSearch->oItem_arr) && count($oExtendedJTLSearchResponse->oSearch->oItem_arr) > 0) {
        // Artikelkeys in der Session halten, da andere Seite wie z.b. Artikel.php auf die voherige Artikelübersicht Daten aufbaut.
        $_SESSION['oArtikelUebersichtKey_arr']   = isset($oArtikelKey_arr) ? $oArtikelKey_arr : [];
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel die auch wirklich auf der Seite angezeigt werden
        foreach ($oExtendedJTLSearchResponse->oSearch->oItem_arr as $oItem) {
            $oArtikel                                = new Artikel();
            $oArtikelOptionen                        = new stdClass();
            $oArtikelOptionen->nMerkmale             = 1;
            $oArtikelOptionen->nAttribute            = 1;
            $oArtikelOptionen->nArtikelAttribute     = 1;
            $oArtikelOptionen->nVariationKombiKinder = 1;
            //$oArtikelOptionen->nVariationDetailPreis = 1;
            $oArtikel->fuelleArtikel($oItem->nId, $oArtikelOptionen);
            if ($oArtikel->kArtikel !== null) {
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($oArtikel->nIstVater === 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                }
                $oArtikel_arr[] = $oArtikel;
            }
        }
    }

    return $oArtikel_arr;
}

/**
 * @todo
 * @param object $FilterSQL
 * @param object $oSuchergebnisse
 * @param int    $nArtikelProSeite
 * @param int    $nLimitN
 * @deprecated since 4.06
 */
function baueArtikelAnzahl($FilterSQL, &$oSuchergebnisse, $nArtikelProSeite = 20, $nLimitN = 20)
{
    trigger_error('filter_inc.php: baueArtikelAnzahl() called.', E_WARNING);
    $kKundengruppe = isset($_SESSION['Kundengruppe']->kKundengruppe) ? (int)$_SESSION['Kundengruppe']->kKundengruppe : null;
    if (!$kKundengruppe) {
        $oKundengruppe = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe WHERE cStandard = 'Y'", 1);
        $kKundengruppe = (int)$oKundengruppe->kKundengruppe;
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = new stdClass();
        }
        $_SESSION['Kundengruppe']->kKundengruppe = $oKundengruppe->kKundengruppe;
    }
    //Anzahl holen
    $oAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nGesamtAnzahl
            FROM(
                SELECT tartikel.kArtikel
                FROM tartikel
                " . (isset($FilterSQL->oSuchspecialFilterSQL->cJoin) ? $FilterSQL->oSuchspecialFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oKategorieFilterSQL->cJoin) ? $FilterSQL->oKategorieFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oSuchFilterSQL->cJoin) ? $FilterSQL->oSuchFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oMerkmalFilterSQL->cJoin) ? $FilterSQL->oMerkmalFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oTagFilterSQL->cJoin) ? $FilterSQL->oTagFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oBewertungSterneFilterSQL->cJoin) ? $FilterSQL->oBewertungSterneFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oPreisspannenFilterSQL->cJoin) ? $FilterSQL->oPreisspannenFilterSQL->cJoin : '') . "
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                " . gibLagerfilter() . "
                " . (isset($FilterSQL->oSuchspecialFilterSQL->cWhere) ? $FilterSQL->oSuchspecialFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oSuchFilterSQL->cWhere) ? $FilterSQL->oSuchFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oHerstellerFilterSQL->cWhere) ? $FilterSQL->oHerstellerFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oKategorieFilterSQL->cWhere) ? $FilterSQL->oKategorieFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oMerkmalFilterSQL->cWhere) ? $FilterSQL->oMerkmalFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oTagFilterSQL->cWhere) ? $FilterSQL->oTagFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oBewertungSterneFilterSQL->cWhere) ? $FilterSQL->oBewertungSterneFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oPreisspannenFilterSQL->cWhere) ? $FilterSQL->oPreisspannenFilterSQL->cWhere : '') . "
            GROUP BY tartikel.kArtikel
            " . (isset($FilterSQL->oMerkmalFilterSQL->cHaving) ? $FilterSQL->oMerkmalFilterSQL->cHaving : '') . "
                ) AS tAnzahl", 1
    );
    executeHook(
        HOOK_FILTER_INC_BAUEARTIKELANZAHL, [
            'oAnzahl'          => &$oAnzahl,
            'FilterSQL'        => &$FilterSQL,
            'oSuchergebnisse'  => &$oSuchergebnisse,
            'nArtikelProSeite' => &$nArtikelProSeite,
            'nLimitN'          => &$nLimitN
        ]
    );
    $conf = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    if (isset($GLOBALS['NaviFilter'])) {
        buildSearchResultPage(
            $oSuchergebnisse,
            $oAnzahl->nGesamtAnzahl,
            $nLimitN,
            $GLOBALS['NaviFilter']->nSeite,
            $nArtikelProSeite,
            $conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
        );
    } else { //workaround for sitemap export
        buildSearchResultPage(
            $oSuchergebnisse,
            $oAnzahl->nGesamtAnzahl,
            $nLimitN,
            1,
            $nArtikelProSeite,
            $conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
        );
    }
}