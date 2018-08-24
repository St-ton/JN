<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return \Filter\SearchResultsInterface
 * @deprecated since 5.0.0
 */
function buildSearchResults($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling buildSearchResults() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->generateSearchResults();
}

/**
 * @param object $oSearchResult
 * @param int    $nProductCount
 * @param int    $nLimitN
 * @param int    $nPage
 * @param int    $nProductsPerPage
 * @param int    $nSettingMaxPageCount
 * @deprecated since 5.0.0
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
 * @return \Filter\SearchResultsInterface
 * @deprecated since 5.0.0
 */
function gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, $bExtern, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibArtikelKeys() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->generateSearchResults(null, true, (int)$nArtikelProSeite);
}

/**
 * @param stdClass|\Filter\ProductFilter $NaviFilter
 * @return \Filter\ProductFilter
 */
function updateNaviFilter($NaviFilter)
{
    if (get_class($NaviFilter) === 'stdClass') {
        $NaviFilter = Shop::buildProductFilter(extractParameters($NaviFilter), $NaviFilter);
    }

    return $NaviFilter;
}

/**
 * @param stdClass $NaviFilter
 * @return array
 */
function extractParameters($NaviFilter)
{
    $params = [];
    if (!empty($NaviFilter->Kategorie->kKategorie)) {
        $params['kKategorie'] = (int)$NaviFilter->Kategorie->kKategorie;
    }
    if (!empty($NaviFilter->KategorieFilter->kKategorie)) {
        $params['kKategorieFilter'] = (int)$NaviFilter->Kategorie->kKategorie;
    }
    if (!empty($NaviFilter->Hersteller->kHersteller)) {
        $params['kHersteller'] = (int)$NaviFilter->Hersteller->kHersteller;
    }
    if (!empty($NaviFilter->HerstellerFilter->kHersteller)) {
        $params['kHerstellerFilter'] = (int)$NaviFilter->HerstellerFilter->kHersteller;
    }
    if (!empty($NaviFilter->kSeite)) {
        $params['kSeite'] = (int)$NaviFilter->kSeite;
    }
    if (!empty($NaviFilter->kSuchanfrage)) {
        $params['kSuchanfrage'] = (int)$NaviFilter->kSuchanfrage;
    }
    if (!empty($NaviFilter->MerkmalWert->kMerkmalWert)) {
        $params['kMerkmalWert'] = (int)$NaviFilter->MerkmalWert->kMerkmalWert;
    }
    if (!empty($NaviFilter->Tag->kTag)) {
        $params['kTag'] = (int)$NaviFilter->Tag->kTag;
    }
    if (!empty($NaviFilter->PreisspannenFilter->fVon) && !empty($NaviFilter->PreisspannenFilter->fBis)) {
        $params['cPreisspannenFilter'] = $NaviFilter->PreisspannenFilter->fVon . '_' . $NaviFilter->PreisspannenFilter->fBis;
    }
    if (!empty($NaviFilter->SuchspecialFilter->kKey)) {
        $params['kSuchspecialFilter'] = (int)$NaviFilter->SuchspecialFilter->kKey;
    }
    if (!empty($NaviFilter->Suchspecial->kKey)) {
        $params['kSuchspecial'] = (int)$NaviFilter->Suchspecial->kKey;
    }
    if (!empty($NaviFilter->nSortierung)) {
        $params['nSortierung'] = (int)$NaviFilter->nSortierung;
    }
    if (!empty($NaviFilter->MerkmalFilter) && is_array($NaviFilter->MerkmalFilter)) {
        foreach ($NaviFilter->MerkmalFilter as $mf) {
            $params['MerkmalFilter_arr'] = (int)$mf->kMerkmalWert;
        }
    }
    if (!empty($NaviFilter->TagFilter) && is_array($NaviFilter->TagFilter)) {
        foreach ($NaviFilter->TagFilter as $tf) {
            $params['TagFilter_arr'] = (int)$tf->kTag;
        }
    }
    if (!empty($NaviFilter->SuchFilter) && is_array($NaviFilter->SuchFilter)) {
        foreach ($NaviFilter->SuchFilter as $sf) {
            $params['SuchFilter_arr'] = (int)$sf->kSuchanfrage;
        }
    }
    if (!empty($NaviFilter->nAnzahlProSeite)) {
        $params['nArtikelProSeite'] = (int)$NaviFilter->nAnzahlProSeite;
    }
    if (!empty($NaviFilter->Suche->cSuche)) {
        $params['cSuche'] = $NaviFilter->Suche->cSuche;
    }

    return $params;
}

/**
 * @param object $NaviFilter
 * @return int
 * @deprecated since 5.0.0
 */
function gibAnzahlFilter($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibAnzahlFilter() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getFilterCount();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibHerstellerFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibHerstellerFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getManufacturerFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibKategorieFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getCategoryFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->searchFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibBewertungSterneFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getRatingFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibPreisspannenFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getPriceRangeFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->tagFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterJSONOptionen() is deprecated.', E_USER_DEPRECATED);
    $oSuchfilter_arr = gibSuchFilterOptionen($FilterSQL, $NaviFilter); // cURL
    foreach ($oSuchfilter_arr as $key => $oSuchfilter) {
        $oSuchfilter_arr[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
    }

    return \Boxes\AbstractBox::getJSONString($oSuchfilter_arr);
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibTagFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterJSONOptionen() is deprecated.', E_USER_DEPRECATED);
    $oTags_arr = gibTagFilterOptionen($FilterSQL, $NaviFilter);
    foreach ($oTags_arr as $key => $oTags) {
        $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
    }

    return \Boxes\AbstractBox::getJSONString($oTags_arr);
}

/**
 * @param object         $FilterSQL
 * @param object         $NaviFilter
 * @param Kategorie|null $oAktuelleKategorie
 * @param bool           $bForce
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $oAktuelleKategorie = null, $bForce = false)
{
    trigger_error('filter_inc.php: calling gibMerkmalFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getAttributeFilterCollection()->getOptions();
}

/**
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchspecialFilterOptionen() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->searchFilterCompat->getOptions();
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 * @param int    $kSpracheExt
 * @return int
 */
function bearbeiteSuchCache($NaviFilter, $kSpracheExt = 0)
{
    trigger_error('filter_inc.php: calling bearbeiteSuchCache() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getSearchQuery()->editSearchCache($kSpracheExt);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibSuchFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}


/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibHerstellerFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibHerstellerFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibKategorieFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibKategorieFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibBewertungSterneFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibBewertungSterneFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibPreisspannenFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibPreisspannenFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibTagFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibTagFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibMerkmalFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibMerkmalFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function gibSuchspecialFilterSQL($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibSuchspecialFilterSQL() is deprecated and will have no effect', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 */
function checkMerkmalWertVorhanden($oMerkmalauswahl_arr, $kMerkmalWert)
{
    trigger_error('filter_inc.php: calling checkMerkmalWertVorhanden() is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibArtikelsortierung($NaviFilter)
{
    trigger_error('filter_inc.php: calling gibArtikelsortierung() is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getFilterSQL()->getOrder()->orderBy;
}

/**
 * @param string|int $nUsersortierung
 * @return int
 * @deprecated since 5.0.0
 */
function mappeUsersortierung($nUsersortierung)
{
    trigger_error('filter_inc.php: calling mappeUsersortierung() is deprecated.', E_USER_DEPRECATED);
    $mapper = new \Mapper\SortingType();
    return $mapper->mapUserSorting($nUsersortierung);
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
    return updateNaviFilter($NaviFilter)->getFilterURL()->getURL($oZusatzFilter, $bCanonical);
}

/**
 * @param object       $oPreis
 * @param object|array $oPreisspannenfilter_arr
 * @return string
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 */
function gibHeaderAnzeige()
{
    trigger_error('filter_inc.php: calling gibHeaderAnzeige() is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->getHeader();
}

/**
 * @deprecated since 5.0.0
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling erstelleFilterLoesenURLs() is deprecated.', E_USER_DEPRECATED);
    $sr = new \Filter\SearchResults();
    $sr->convert($oSuchergebnisse);
    Shop::getProductFilter()->getFilterURL()->createUnsetFilterURLs(
        new stdClass(),
        $sr
    );
}

/**
 * @deprecated since 5.0.0
 * @param string $cTitle
 * @return string
 * @deprecated since 5.0.0
 */
function truncateMetaTitle($cTitle)
{
    trigger_error('filter_inc.php: calling truncateMetaTitle() is deprecated.', E_USER_DEPRECATED);
    return (new \Filter\Metadata(Shop::getProductFilter()))->truncateMetaTitle($cTitle);
}

/**
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 5.0.0
 */
function gibNaviMetaTitle($NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error('filter_inc.php: calling gibNaviMetaTitle() is deprecated.', E_USER_DEPRECATED);
    $sr = new \Filter\SearchResults();
    $sr->convert($oSuchergebnisse);

    return (new \Filter\Metadata(updateNaviFilter($NaviFilter)))->generateMetaTitle(
        $sr,
        $globalMeta
    );
}

/**
 * @param array  $articles
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 5.0.0
 */
function gibNaviMetaDescription($articles, $NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error('filter_inc.php: calling gibNaviMetaDescription() is deprecated.', E_USER_DEPRECATED);
    $sr = new \Filter\SearchResults();
    $sr->convert($oSuchergebnisse);

    return (new \Filter\Metadata(updateNaviFilter($NaviFilter)))->generateMetaDescription(
        $articles,
        $sr,
        $globalMeta
    );
}

/**
 * @param array  $articles
 * @param object $NaviFilter
 * @param array  $excludeKeywords
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function gibNaviMetaKeywords($articles, $NaviFilter, $excludeKeywords = [])
{
    trigger_error('filter_inc.php: calling gibNaviMetaKeywords() is deprecated.', E_USER_DEPRECATED);
    return (new \Filter\Metadata(updateNaviFilter($NaviFilter)))->generateMetaKeywords($articles);
}

/**
 * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
 *
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return string
 * @deprecated since 5.0.0
 */
function gibMetaStart($NaviFilter, $oSuchergebnisse)
{
    trigger_error('filter_inc.php: calling gibMetaStart() is deprecated.', E_USER_DEPRECATED);
    $pf = updateNaviFilter($NaviFilter);
    $sr = new \Filter\SearchResults();
    $sr->convert($oSuchergebnisse);
    return (new \Filter\Metadata($pf))->getMetaStart($sr);
}

/**
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function setzeUsersortierung($NaviFilter)
{
    trigger_error('filter_inc.php: calling setzeUsersortierung() is deprecated.', E_USER_DEPRECATED);
    global $AktuelleKategorie;
    updateNaviFilter($NaviFilter)->setUserSort($AktuelleKategorie);
}

/**
 * @deprecated since 5.0.0
 * @param array  $Einstellungen
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nDarstellung = 0)
{
    trigger_error('filter_inc.php: calling gibErweiterteDarstellung() is deprecated.', E_USER_DEPRECATED);
    updateNaviFilter($NaviFilter)->getMetaData()->getExtendedView($nDarstellung);
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        Shop::Smarty()->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }
}

/**
 * @deprecated since 5.0.0
 * @param object productFilter
 * @param bool   $seo
 * @param object $pages
 * @param int    $maxPages
 * @param string $filterURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $seo, $pages, $maxPages = 7, $filterURL = '')
{
    trigger_error('filter_inc.php: calling baueSeitenNaviURL() is deprecated.', E_USER_DEPRECATED);
    $productFilter = updateNaviFilter($NaviFilter);
    if (is_a($pages, 'stdClass')) {
        $p = new \Filter\Pagination\Info();
        $p->setMaxPage($pages->maxSeite);
        $p->setMinPage($pages->minSeite);
        $p->setTotalPages($pages->maxSeiten);
        $p->setCurrentPage($pages->AktuelleSeite);
        $pages = $p;
    }
    if (strlen($filterURL) > 0) {
        $seo = false;
    }
    $oSeite_arr = [];
    $naviURL    = $productFilter->getFilterURL()->getURL();
    $seo        = $seo && strpos($naviURL, '?') === false;
    if ($pages->getTotalPages() > 0 && $pages->getCurrentPage()> 0) {
        $nMax = (int)floor($maxPages / 2);
        if ($pages->getTotalPages() > $maxPages) {
            if ($pages->getCurrentPage() - $nMax >= 1) {
                $nDiff = 0;
                $nVon  = $pages->getCurrentPage() - $nMax;
            } else {
                $nVon  = 1;
                $nDiff = $nMax - $pages->getCurrentPage() + 1;
            }
            if ($pages->getCurrentPage() + $nMax + $nDiff <= $pages->getTotalPages()) {
                $nBis = $pages->getCurrentPage() + $nMax + $nDiff;
            } else {
                $nDiff = $pages->getCurrentPage() + $nMax - $pages->getTotalPages();
                if ($nDiff === 0) {
                    $nVon -= ($maxPages - ($nMax + 1));
                } elseif ($nDiff > 0) {
                    $nVon = $pages->getCurrentPage() - $nMax - $nDiff;
                }
                $nBis = $pages->getTotalPages();
            }
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = $nVon; $i <= $nBis; ++$i) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i;
                if ($i === $pages->getCurrentPage()) {
                    $oSeite->cURL = '';
                } elseif ($oSeite->nSeite === 1) {
                    $oSeite->cURL = $naviURL . $filterURL;
                } elseif ($seo) {
                    $cURL         = $naviURL;
                    $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                        ? $cURL . '&amp;seite=' . $oSeite->nSeite . $filterURL
                        : $cURL . SEP_SEITE . $oSeite->nSeite;
                } else {
                    $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $filterURL;
                }
                $oSeite_arr[] = $oSeite;
            }
        } else {
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = 0; $i < $pages->getTotalPages(); ++$i) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i + 1;

                if ($i + 1 === $pages->getCurrentPage()) {
                    $oSeite->cURL = '';
                } elseif ($oSeite->nSeite === 1) {
                    $oSeite->cURL = $naviURL . $filterURL;
                } elseif ($seo) {
                    $cURL         = $naviURL;
                    $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                        ? $cURL . '&amp;seite=' . $oSeite->nSeite . $filterURL
                        : $cURL . SEP_SEITE . $oSeite->nSeite;
                } else {
                    $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $filterURL;
                }
                $oSeite_arr[] = $oSeite;
            }
        }
        // Baue Zurück-URL
        $oSeite_arr['zurueck']       = new stdClass();
        $oSeite_arr['zurueck']->nBTN = 1;
        if ($pages->getCurrentPage() > 1) {
            $oSeite_arr['zurueck']->nSeite = $pages->getCurrentPage() - 1;
            if ($oSeite_arr['zurueck']->nSeite === 1) {
                $oSeite_arr['zurueck']->cURL = $naviURL . $filterURL;
            } elseif ($seo) {
                $cURL = $naviURL;
                if (strpos(basename($cURL), 'index.php') !== false) {
                    $oSeite_arr['zurueck']->cURL = $cURL . '&amp;seite=' .
                        $oSeite_arr['zurueck']->nSeite . $filterURL;
                } else {
                    $oSeite_arr['zurueck']->cURL = $cURL . SEP_SEITE .
                        $oSeite_arr['zurueck']->nSeite;
                }
            } else {
                $oSeite_arr['zurueck']->cURL = $naviURL . '&amp;seite=' .
                    $oSeite_arr['zurueck']->nSeite . $filterURL;
            }
        }
        // Baue Vor-URL
        $oSeite_arr['vor']       = new stdClass();
        $oSeite_arr['vor']->nBTN = 1;
        if ($pages->getCurrentPage() < $pages->getMaxPage()) {
            $oSeite_arr['vor']->nSeite = $pages->getCurrentPage() + 1;
            if ($seo) {
                $cURL = $naviURL;
                if (strpos(basename($cURL), 'index.php') !== false) {
                    $oSeite_arr['vor']->cURL = $cURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $filterURL;
                } else {
                    $oSeite_arr['vor']->cURL = $cURL . SEP_SEITE . $oSeite_arr['vor']->nSeite;
                }
            } else {
                $oSeite_arr['vor']->cURL = $naviURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $filterURL;
            }
        }
    }

    return $oSeite_arr;
}

/**
 * @param stdClass $oSuchCache
 * @param array    $cSuchspalten_arr
 * @param array    $cSuch_arr
 * @param int      $nLimit
 * @throws Exception
 * @deprecated since 5.0.0
 */
function bearbeiteSuchCacheFulltext($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0)
{
    throw new Exception('filter_inc.php: calling bearbeiteSuchCacheFulltext() is deprecated and will have no effect');
}

/**
 * @throws Exception
 * @deprecated since 5.0.0
 */
function isFulltextIndexActive()
{
    throw new Exception('filter_inc.php: calling isFulltextIndexActive() is deprecated and will have no effect');
}

/**
 * @deprecated since 5.0.0
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
 * @param null|array $conf
 * @param bool $bExtendedJTLSearch
 * @return array
 * @deprecated since 5.0.0
 */
function gibSortierliste($conf = null, $bExtendedJTLSearch = false)
{
    trigger_error('filter_inc.php: calling gibSortierliste() is deprecated.', E_USER_DEPRECATED);
    $conf           = $conf ?? Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
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
    while (($obj = gibNextSortPrio($search, $conf)) !== null) {
        $search[] = $obj->name;
        unset($obj->name);
        $sortingOptions[] = $obj;
    }

    return $sortingOptions;
}

/**
 * @deprecated since 5.0.0
 * @param array $search
 * @param null|array $conf
 * @return null|stdClass
 */
function gibNextSortPrio($search, $conf = null)
{
    trigger_error('filter_inc.php: calling gibNextSortPrio() is deprecated.', E_USER_DEPRECATED);
    $conf = $conf ?? Shop::getConfig([CONF_ARTIKELUEBERSICHT]);
    $max  = 0;
    $obj  = null;
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_name']
        && !in_array('suche_sortierprio_name', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name';
        $obj->value           = SEARCH_SORT_NAME_ASC;
        $obj->angezeigterName = \Shop::Lang()->get('sortNameAsc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_name'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_name_ab']
        && !in_array('suche_sortierprio_name_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name_ab';
        $obj->value           = SEARCH_SORT_NAME_DESC;
        $obj->angezeigterName = \Shop::Lang()->get('sortNameDesc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_name_ab'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_preis']
        && !in_array('suche_sortierprio_preis', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis';
        $obj->value           = SEARCH_SORT_PRICE_ASC;
        $obj->angezeigterName = \Shop::Lang()->get('sortPriceAsc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_preis'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_preis_ab']
        && !in_array('suche_sortierprio_preis_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis_ab';
        $obj->value           = SEARCH_SORT_PRICE_DESC;
        $obj->angezeigterName = \Shop::Lang()->get('sortPriceDesc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_ean']
        && !in_array('suche_sortierprio_ean', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_ean';
        $obj->value           = SEARCH_SORT_EAN;
        $obj->angezeigterName = \Shop::Lang()->get('sortEan');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_ean'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_erstelldatum']
        && !in_array('suche_sortierprio_erstelldatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erstelldatum';
        $obj->value           = SEARCH_SORT_NEWEST_FIRST;
        $obj->angezeigterName = \Shop::Lang()->get('sortNewestFirst');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_artikelnummer']
        && !in_array('suche_sortierprio_artikelnummer', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_artikelnummer';
        $obj->value           = SEARCH_SORT_PRODUCTNO;
        $obj->angezeigterName = \Shop::Lang()->get('sortProductno');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_lagerbestand']
        && !in_array('suche_sortierprio_lagerbestand', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_lagerbestand';
        $obj->value           = SEARCH_SORT_AVAILABILITY;
        $obj->angezeigterName = \Shop::Lang()->get('sortAvailability');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_lagerbestand'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_gewicht']
        && !in_array('suche_sortierprio_gewicht', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_gewicht';
        $obj->value           = SEARCH_SORT_WEIGHT;
        $obj->angezeigterName = \Shop::Lang()->get('sortWeight');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_gewicht'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum']
        && !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erscheinungsdatum';
        $obj->value           = SEARCH_SORT_DATEOFISSUE;
        $obj->angezeigterName = \Shop::Lang()->get('sortDateofissue');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_bestseller']
        && !in_array('suche_sortierprio_bestseller', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bestseller';
        $obj->value           = SEARCH_SORT_BESTSELLER;
        $obj->angezeigterName = \Shop::Lang()->get('bestseller');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_bestseller'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_bewertung']
        && !in_array('suche_sortierprio_bewertung', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bewertung';
        $obj->value           = SEARCH_SORT_RATING;
        $obj->angezeigterName = \Shop::Lang()->get('rating');
    }

    return $obj;
}

/**
 * @param object $NaviFilter
 * @return mixed|stdClass
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 */
function baueArtikelAnzahl($FilterSQL, &$oSuchergebnisse, $nArtikelProSeite = 20, $nLimitN = 20)
{
    trigger_error('filter_inc.php: calling baueArtikelAnzahl() is deprecated and will have no effect', E_USER_DEPRECATED);
    $oAnzahl = Shop::Container()->getDB()->query(
        'SELECT count(*) AS nGesamtAnzahl
            FROM(
                SELECT tartikel.kArtikel
                FROM tartikel ' . 
                ($FilterSQL->oSuchspecialFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oKategorieFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oSuchFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oMerkmalFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oTagFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oBewertungSterneFilterSQL->cJoin ?? '') . ' ' .
                ($FilterSQL->oPreisspannenFilterSQL->cJoin ?? '') .
            ' LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . Session::CustomerGroup()->getID() . '
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0 ' .
                gibLagerfilter() . ' ' .
                ($FilterSQL->oSuchspecialFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oSuchFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oHerstellerFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oKategorieFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oMerkmalFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oTagFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oBewertungSterneFilterSQL->cWhere ?? '') . ' ' .
                ($FilterSQL->oPreisspannenFilterSQL->cWhere ?? '') .
                ' GROUP BY tartikel.kArtikel ' . 
                ($FilterSQL->oMerkmalFilterSQL->cHaving ?? '') .
                ') AS tAnzahl',
        \DB\ReturnType::SINGLE_OBJECT
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
    $nPage                = $GLOBALS['NaviFilter']->nSeite ?? 1;
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
    $sr = new \Filter\SearchResults();
    $oSuchergebnisse = $sr->convert($oSuchergebnisse);
}
