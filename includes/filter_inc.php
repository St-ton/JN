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
 * @deprecated since 4.07
 */
function buildSearchResults($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling buildSearchResults() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getProducts();
}

/**
 * @param object $oSearchResult
 * @param int    $nProductCount
 * @param int    $nLimitN
 * @param int    $nPage
 * @param int    $nProductsPerPage
 * @param int    $nSettingMaxPageCount
 * @deprecated since 4.07
 */
function buildSearchResultPage(&$oSearchResult, $nProductCount, $nLimitN, $nPage, $nProductsPerPage = 25, $nSettingMaxPageCount = 25)
{
    trigger_error('filter_inc.php: calling buildSearchResultPage() is deprecated.', E_USER_DEPRECATED);
}

/**
 * @param object   $FilterSQL
 * @param int      $nArtikelProSeite
 * @param object   $NaviFilter
 * @param bool     $bExtern
 * @param stdClass $oSuchergebnisse
 * @return stdClass
 * @deprecated since 4.07
 */
function gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, $bExtern, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibArtikelKeys() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getProducts(false, null, true, (int)$nArtikelProSeite);
}

/**
 * @param object $NaviFilter
 * @return int
 * @deprecated since 4.07
 */
function gibAnzahlFilter($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibAnzahlFilter() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getFilterCount();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibHerstellerFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibHerstellerFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getManufacturerFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibKategorieFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getCategoryFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->searchFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibBewertungSterneFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getRatingFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibPreisspannenFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getPriceRangeFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 4.07
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->tagFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.07
 */
function gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterJSONOptionen() is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 4.07
 */
function gibTagFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterJSONOptionen() is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 4.07
 */
function gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $oAktuelleKategorie = null, $bForce = false)
{
    trigger_error('filter_inc.php: calling gibMerkmalFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getAttributeFilterCollection()->getOptions();
}

/**
 * @deprecated since 4.07
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
 * @deprecated since 4.07
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchspecialFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->searchFilterCompat->getOptions();
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 * @param int    $kSpracheExt
 * @return int
 */
function bearbeiteSuchCache($NaviFilter, $kSpracheExt = 0)
{
    trigger_error('filter_inc.php: calling bearbeiteSuchCache() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getSearchQuery()->editSearchCache($kSpracheExt);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibSuchFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}


/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibHerstellerFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibHerstellerFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibKategorieFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibKategorieFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibBewertungSterneFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibBewertungSterneFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibPreisspannenFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibPreisspannenFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibTagFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibMerkmalFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibMerkmalFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibSuchspecialFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchspecialFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function gibArtikelAttributFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibArtikelAttributFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmal
 * @return int
 * @deprecated since 4.07
 */
function gibMerkmalPosition($oMerkmalauswahl_arr, $kMerkmal)
{
    trigger_error('filter_inc.php: calling gibMerkmalPosition() is deprecated.', E_USER_DEPRECATED);
    return -1;
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmalWert
 * @return bool
 * @deprecated since 4.07
 */
function checkMerkmalWertVorhanden($oMerkmalauswahl_arr, $kMerkmalWert)
{
    trigger_error('filter_inc.php: calling checkMerkmalWertVorhanden() is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 * @deprecated since 4.07
 */
function gibArtikelsortierung($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibArtikelsortierung() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getFilterSQL()->getOrder()->orderBy;
}

/**
 * @param string|int $nUsersortierung
 * @return int
 * @deprecated since 4.07
 */
function mappeUsersortierung($nUsersortierung)
{
    trigger_error('filter_inc.php: calling mappeUsersortierung() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->mapUserSorting($nUsersortierung);
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
    trigger_error('filter_inc.php: calling gibNaviURL() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getFilterURL()->getURL($oZusatzFilter, $bCanonical);
}

/**
 * @param object       $oPreis
 * @param object|array $oPreisspannenfilter_arr
 * @return string
 * @deprecated since 4.07
 */
function berechnePreisspannenSQL($oPreis, $oPreisspannenfilter_arr = null)
{
    trigger_error('filter_inc.php: calling berechnePreisspannenSQL() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()
               ->getPriceRangeFilter()
               ->getPriceRangeSQL($oPreis, Session::Currency(), $oPreisspannenfilter_arr);
}

/**
 * @param float $fMax
 * @param float $fMin
 * @return stdClass
 */
function berechneMaxMinStep($fMax, $fMin)
{
    trigger_error('filter_inc.php: calling berechneMaxMinStep() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getPriceRangeFilter()->calculateSteps($fMax, $fMin);
}

/**
 * @return null|string
 * @deprecated since 4.07
 */
function gibBrotNaviName()
{
    trigger_error('filter_inc.php: calling gibBrotNaviName() is deprecated.', E_USER_DEPRECATED);
    $md = Shop::getProductFilter()->getMetaData();
    $md->getHeader();

    return $md->getBreadCrumbName();
}

/**
 * @return string
 * @deprecated since 4.07
 */
function gibHeaderAnzeige()
{
    trigger_error('filter_inc.php: calling gibHeaderAnzeige() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->getHeader();
}

/**
 * @deprecated since 4.07
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling erstelleFilterLoesenURLs() is deprecated.', E_USER_DEPRECATED);
    Shop::getProductFilter()->getFilterURL()->createUnsetFilterURLs(
        new stdClass(),
        new ProductFilterSearchResults($oSuchergebnisse)
    );
}

/**
 * @deprecated since 4.07
 * @param string $cTitle
 * @return string
 * @deprecated since 4.07
 */
function truncateMetaTitle($cTitle)
{
    trigger_error('filter_inc.php: calling truncateMetaTitle() is deprecated.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getProductFilter()))->truncateMetaTitle($cTitle);
}

/**
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 4.07
 */
function gibNaviMetaTitle($NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error('filter_inc.php: calling gibNaviMetaTitle() is deprecated.', E_USER_DEPRECATED);

    return (new Metadata(Shop::getProductFilter()))->generateMetaTitle(
        new ProductFilterSearchResults($oSuchergebnisse),
        $globalMeta
    );
}

/**
 * @param array  $articles
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 4.07
 */
function gibNaviMetaDescription($articles, $NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error('filter_inc.php: calling gibNaviMetaDescription() is deprecated.', E_USER_DEPRECATED);

    return (new Metadata(Shop::getProductFilter()))->generateMetaDescription(
        $articles,
        new ProductFilterSearchResults($oSuchergebnisse),
        $globalMeta
    );
}

/**
 * @param array  $articles
 * @param object $NaviFilter
 * @param array  $excludeKeywords
 * @return mixed|string
 * @deprecated since 4.07
 */
function gibNaviMetaKeywords($articles, $NaviFilter, $excludeKeywords = [])
{
    trigger_error('filter_inc.php: calling gibNaviMetaKeywords() is deprecated.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getProductFilter()))->generateMetaKeywords($articles);
}

/**
 * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
 *
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return string
 * @deprecated since 4.07
 */
function gibMetaStart($NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibMetaStart() is deprecated.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getProductFilter()))->getMetaStart(new ProductFilterSearchResults($oSuchergebnisse));
}

/**
 * @deprecated since 4.07
 * @param string $cSuche
 * @param int    $kSprache
 * @return int
 */
function gibSuchanfrageKey($cSuche, $kSprache)
{
    trigger_error('filter_inc.php: calling bauFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
    return 0;
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 */
function setzeUsersortierung($NaviFilter)
{
    trigger_error('filter_inc.php: calling setzeUsersortierung() is deprecated.', E_USER_DEPRECATED);
    global $AktuelleKategorie;
    Shop::getProductFilter()->getMetaData()->setUserSort($AktuelleKategorie);
}

/**
 * @deprecated since 4.07
 * @param array  $Einstellungen
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nDarstellung = 0)
{
    trigger_error('filter_inc.php: calling gibErweiterteDarstellung() is deprecated.', E_USER_DEPRECATED);
    Shop::getProductFilter()->getMetaData()->getExtendedView($nDarstellung);
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        global $smarty;
        $smarty->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }
}

/**
 * @deprecated since 4.07
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oSeitenzahlen
 * @param int    $nMaxAnzeige
 * @param string $cFilterShopURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
{
    trigger_error('filter_inc.php: calling baueSeitenNaviURL() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige, $cFilterShopURL);
}

/**
 * @param stdClass $oSuchCache
 * @param array    $cSuchspalten_arr
 * @param array    $cSuch_arr
 * @param int      $nLimit
 * @throws Exception
 * @deprecated since 4.07
 */
function bearbeiteSuchCacheFulltext($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0)
{
    throw new Exception('filter_inc.php: calling bearbeiteSuchCacheFulltext() is deprecated and will have no effect');
}

/**
 * @throws Exception
 * @deprecated since 4.07
 */
function isFulltextIndexActive()
{
    throw new Exception('filter_inc.php: calling isFulltextIndexActive() is deprecated and will have no effect');
}

/**
 * @deprecated since 4.07
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierKategoriepfade($a, $b)
{
    trigger_error('filter_inc.php: calling sortierKategoriepfade() is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param null|array $Einstellungen
 * @param bool $bExtendedJTLSearch
 * @return array
 * @deprecated since 4.07
 */
function gibSortierliste($Einstellungen = null, $bExtendedJTLSearch = false)
{
    trigger_error('filter_inc.php: calling gibSortierliste() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->getSortingOptions($bExtendedJTLSearch);
}

/**
 * @deprecated since 4.07
 * @param array $search
 * @param null|array $Einstellungen
 * @return null|stdClass
 */
function gibNextSortPrio($search, $Einstellungen = null)
{
    trigger_error('filter_inc.php: calling gibNextSortPrio() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->getNextSearchPriority($search);
}

/**
 * @param object $NaviFilter
 * @return mixed|stdClass
 * @deprecated since 4.07
 */
function bauFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling bauFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
    $FilterSQL = new stdClass();
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
}

/**
 * @deprecated since 4.07
 * @param object $oExtendedJTLSearchResponse
 * @return array
 * @throws Exception
 */
function gibArtikelKeysExtendedJTLSearch($oExtendedJTLSearchResponse)
{
    trigger_error('filter_inc.php: calling gibArtikelKeysExtendedJTLSearch() is deprecated and will have no effect', E_USER_DEPRECATED);
    return [];
}

/**
 * @param object $FilterSQL
 * @param object $oSuchergebnisse
 * @param int    $nArtikelProSeite
 * @param int    $nLimitN
 * @deprecated since 4.07
 */
function baueArtikelAnzahl($FilterSQL, &$oSuchergebnisse, $nArtikelProSeite = 20, $nLimitN = 20)
{
    trigger_error('filter_inc.php: calling baueArtikelAnzahl() is deprecated and will have no effect', E_USER_DEPRECATED);
    $oAnzahl = Shop::DB()->query(
        'SELECT count(*) AS nGesamtAnzahl
            FROM(
                SELECT tartikel.kArtikel
                FROM tartikel ' . 
                (isset($FilterSQL->oSuchspecialFilterSQL->cJoin) ? $FilterSQL->oSuchspecialFilterSQL->cJoin : '') . ' ' . 
                (isset($FilterSQL->oKategorieFilterSQL->cJoin) ? $FilterSQL->oKategorieFilterSQL->cJoin : '') . ' ' . 
                (isset($FilterSQL->oSuchFilterSQL->cJoin) ? $FilterSQL->oSuchFilterSQL->cJoin : '') . ' ' . 
                (isset($FilterSQL->oMerkmalFilterSQL->cJoin) ? $FilterSQL->oMerkmalFilterSQL->cJoin : '') . ' ' .
                (isset($FilterSQL->oTagFilterSQL->cJoin) ? $FilterSQL->oTagFilterSQL->cJoin : '') . ' ' . 
                (isset($FilterSQL->oBewertungSterneFilterSQL->cJoin) ? $FilterSQL->oBewertungSterneFilterSQL->cJoin : '') . ' ' . 
                (isset($FilterSQL->oPreisspannenFilterSQL->cJoin) ? $FilterSQL->oPreisspannenFilterSQL->cJoin : '') .
            ' LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . Session::CustomerGroup()->getID() . '
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0 ' . 
                gibLagerfilter() . ' ' . 
                (isset($FilterSQL->oSuchspecialFilterSQL->cWhere) ? $FilterSQL->oSuchspecialFilterSQL->cWhere : '') . ' ' .
                (isset($FilterSQL->oSuchFilterSQL->cWhere) ? $FilterSQL->oSuchFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oHerstellerFilterSQL->cWhere) ? $FilterSQL->oHerstellerFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oKategorieFilterSQL->cWhere) ? $FilterSQL->oKategorieFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oMerkmalFilterSQL->cWhere) ? $FilterSQL->oMerkmalFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oTagFilterSQL->cWhere) ? $FilterSQL->oTagFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oBewertungSterneFilterSQL->cWhere) ? $FilterSQL->oBewertungSterneFilterSQL->cWhere : '') . ' ' . 
                (isset($FilterSQL->oPreisspannenFilterSQL->cWhere) ? $FilterSQL->oPreisspannenFilterSQL->cWhere : '') . 
                ' GROUP BY tartikel.kArtikel ' . 
                (isset($FilterSQL->oMerkmalFilterSQL->cHaving) ? $FilterSQL->oMerkmalFilterSQL->cHaving : '') . 
                ') AS tAnzahl',
        1
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
    $conf                 = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    $nPage                = isset($GLOBALS['NaviFilter']->nSeite) ? $GLOBALS['NaviFilter']->nSeite : 1;
    $nSettingMaxPageCount = (int)$conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];

    $oSuchergebnisse->GesamtanzahlArtikel = $oAnzahl->nGesamtAnzahl;
    $oSuchergebnisse->ArtikelVon          = $nLimitN + 1;
    $oSuchergebnisse->ArtikelBis          = min($nLimitN + $nArtikelProSeite, $oSuchergebnisse->GesamtanzahlArtikel);

    if (!isset($oSuchergebnisse->Seitenzahlen)) {
        $oSuchergebnisse->Seitenzahlen = new stdClass();
    }
    $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $nPage;
    $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $nArtikelProSeite);
    $oSuchergebnisse->Seitenzahlen->minSeite      = min(
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite - $nSettingMaxPageCount / 2,
        0
    );
    $oSuchergebnisse->Seitenzahlen->maxSeite      = max(
        $oSuchergebnisse->Seitenzahlen->MaxSeiten,
        $oSuchergebnisse->Seitenzahlen->minSeite + $nSettingMaxPageCount - 1
    );
    if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
        $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
    }
    $oSuchergebnisse = new ProductFilterSearchResults($oSuchergebnisse);
}
