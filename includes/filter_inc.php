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
    trigger_error('filter_inc.php: buildSearchResults() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: buildSearchResultPage() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibArtikelKeys() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getProducts(false, null, true, (int)$nArtikelProSeite);
}

/**
 * @param object $NaviFilter
 * @return int
 * @deprecated since 4.06
 */
function gibAnzahlFilter($NaviFilter)
{
    trigger_error('filter_inc.php: gibAnzahlFilter() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibHerstellerFilterOptionen() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getManufacturerFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibKategorieFilterOptionen() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getCategoryFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchFilterOptionen() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibBewertungSterneFilterOptionen() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getRatingFilter()->getOptions();
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
    trigger_error('filter_inc.php: gibPreisspannenFilterOptionen() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getPriceRangeFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.06
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: gibTagFilterOptionen() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibSuchFilterJSONOptionen() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibTagFilterJSONOptionen() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: gibMerkmalFilterOptionen() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getAttributeFilterCollection()->getOptions();
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
    trigger_error('filter_inc.php: gibSuchspecialFilterOptionen() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: bearbeiteSuchCache() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getSearchQuery()->editSearchCache($kSpracheExt);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibSuchFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchFilterSQL() no longer supported.', E_USER_DEPRECATED);
}


/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibHerstellerFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibHerstellerFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibKategorieFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibKategorieFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibBewertungSterneFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibBewertungSterneFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibPreisspannenFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibPreisspannenFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibTagFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibTagFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibMerkmalFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibMerkmalFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibSuchspecialFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibSuchspecialFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 */
function gibArtikelAttributFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: gibArtikelAttributFilterSQL() no longer supported.', E_USER_DEPRECATED);
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmal
 * @return int
 * @deprecated since 4.06
 */
function gibMerkmalPosition($oMerkmalauswahl_arr, $kMerkmal)
{
    trigger_error('filter_inc.php: gibMerkmalPosition() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: checkMerkmalWertVorhanden() called.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.06
 */
function gibArtikelsortierung($NaviFilter)
{
    trigger_error('filter_inc.php: gibArtikelsortierung() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getOrder()->orderBy;
}

/**
 * @param string|int $nUsersortierung
 * @return int
 * @deprecated since 4.06
 */
function mappeUsersortierung($nUsersortierung)
{
    trigger_error('filter_inc.php: mappeUsersortierung() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getMetaData()->mapUserSorting($nUsersortierung);
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
    trigger_error('filter_inc.php: gibNaviURL() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: berechnePreisspannenSQL() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getPriceRangeFilter()->getPriceRangeSQL($oPreis, Session::Currency(), $oPreisspannenfilter_arr);
}

/**
 * @param float $fMax
 * @param float $fMin
 * @return stdClass
 */
function berechneMaxMinStep($fMax, $fMin)
{
    trigger_error('filter_inc.php: berechneMaxMinStep() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getPriceRangeFilter()->calculateSteps($fMax, $fMin);
}

/**
 * @return null|string
 * @deprecated since 4.06
 */
function gibBrotNaviName()
{
    trigger_error('filter_inc.php: gibBrotNaviName() called.', E_USER_DEPRECATED);
    $md = Shop::getNaviFilter()->getMetaData();
    $md->getHeader();
    return $md->getBreadCrumbName();
}

/**
 * @return string
 * @deprecated since 4.06
 */
function gibHeaderAnzeige()
{
    trigger_error('filter_inc.php: gibHeaderAnzeige() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getMetaData()->getHeader();
}

/**
 * @deprecated since 4.06
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: erstelleFilterLoesenURLs() called.', E_USER_DEPRECATED);
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
    trigger_error('filter_inc.php: truncateMetaTitle() called.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getNaviFilter()))->truncateMetaTitle($cTitle);
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
    trigger_error('filter_inc.php: gibNaviMetaTitle() called.', E_USER_DEPRECATED);
    global $oMeta;
    return (new Metadata(Shop::getNaviFilter()))->getMetaTitle($oMeta, $oSuchergebnisse, $GlobaleMetaAngaben_arr);
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
    trigger_error('filter_inc.php: gibNaviMetaDescription() called.', E_USER_DEPRECATED);
    global $oMeta;
    return (new Metadata(Shop::getNaviFilter()))->getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $GlobaleMetaAngaben_arr);
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
    trigger_error('filter_inc.php: gibNaviMetaKeywords() called.', E_USER_DEPRECATED);
    global $oMeta;
    return (new Metadata(Shop::getNaviFilter()))->getMetaKeywords($oMeta, $oArtikel_arr);
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
    trigger_error('filter_inc.php: gibMetaStart() called.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getNaviFilter()))->getMetaStart($oSuchergebnisse);
}

/**
 * @todo
 * @param string $cSuche
 * @param int    $kSprache
 * @return int
 */
function gibSuchanfrageKey($cSuche, $kSprache)
{
    if ($kSprache > 0 && strlen($cSuche) > 0) {
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
    trigger_error('filter_inc.php: setzeUsersortierung() called.', E_USER_DEPRECATED);
    global $AktuelleKategorie;
    Shop::getNaviFilter()->getMetaData()->setUserSort($AktuelleKategorie);
}

/**
 * @deprecated since 4.06
 * @param array  $Einstellungen
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nDarstellung = 0)
{
    trigger_error('filter_inc.php: gibErweiterteDarstellung() called.', E_USER_DEPRECATED);
    Shop::getNaviFilter()->getMetaData()->getExtendedView($nDarstellung);
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        global $smarty;
        $smarty->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }
}

/**
 * @deprecated since 4.06
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oSeitenzahlen
 * @param int    $nMaxAnzeige
 * @param string $cFilterShopURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
{
    trigger_error('filter_inc.php: baueSeitenNaviURL() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getMetaData()->buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige, $cFilterShopURL);
}

/**
 * @throws Exception
 * @deprecated since 4.06
 */
function bearbeiteSuchCacheFulltext($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0)
{
    throw new Exception('filter_inc.php: bearbeiteSuchCacheFulltext() no longer supported.');
}

/**
 * @throws Exception
 * @deprecated since 4.06
 */
function isFulltextIndexActive()
{
    throw new Exception('filter_inc.php: isFulltextIndexActive() no longer supported.');
}

/**
 * @deprecated since 4.06
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierKategoriepfade($a, $b)
{
    trigger_error('filter_inc.php: sortierKategoriepfade() called.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param null|array $Einstellungen
 * @param bool $bExtendedJTLSearch
 * @return array
 * @deprecated since 4.06
 */
function gibSortierliste($Einstellungen = null, $bExtendedJTLSearch = false)
{
    trigger_error('filter_inc.php: gibSortierliste() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getMetaData()->getSortingOptions($bExtendedJTLSearch);
}

/**
 * @deprecated since 4.06
 * @param array $search
 * @param null|array $Einstellungen
 * @return null|stdClass
 */
function gibNextSortPrio($search, $Einstellungen = null)
{
    trigger_error('filter_inc.php: gibNextSortPrio() called.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->getMetaData()->getNextSearchPriority($search);
}

/**
 * @todo
 * @param object $NaviFilter
 * @return mixed|stdClass
 * @deprecated since 4.06
 */
function bauFilterSQL($NaviFilter)
{
    $FilterSQL = new stdClass();
    //Filter SQLs Objekte
    $FilterSQL->oHerstellerFilterSQL      = new stdClass();
    $FilterSQL->oKategorieFilterSQL       = new stdClass();
    $FilterSQL->oMerkmalFilterSQL         = new stdClass();
    $FilterSQL->oTagFilterSQL             = new stdClass();
    $FilterSQL->oBewertungSterneFilterSQL = new stdClass();
    $FilterSQL->oPreisspannenFilterSQL    = new stdClass();
    $FilterSQL->oSuchFilterSQL            = new stdClass();
    $FilterSQL->oSuchspecialFilterSQL     = new stdClass();
    $FilterSQL->oArtikelAttributFilterSQL = new stdClass();

    return $FilterSQL;
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
    trigger_error('filter_inc.php: baueArtikelAnzahl() called.', E_USER_DEPRECATED);
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
                AND tartikelsichtbarkeit.kKundengruppe = " . Session::CustomerGroup()->getID() . "
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