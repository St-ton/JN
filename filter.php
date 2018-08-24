<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
Shop::setPageType(PAGE_ARTIKELLISTE);
/** @global JTLSmarty $smarty */
/** @global \Filter\ProductFilter $NaviFilter*/
$Einstellungen      = Shopsetting::getInstance()->getAll();
$conf               = $Einstellungen;
$bestsellers        = [];
$suchanfrage        = '';
$doSearch           = true;
$KategorieInhalt    = null;
$AktuelleKategorie  = new Kategorie();
$expandedCategories = new KategorieListe();
$hasError           = false;
$cParameter_arr     = Shop::getParameters();
if ($NaviFilter->hasCategory()) {
    $AktuelleSeite               = 'PRODUKTE';
    $kKategorie                  = $NaviFilter->getCategory()->getValue();
    $_SESSION['LetzteKategorie'] = $kKategorie;
    if ($AktuelleKategorie->kKategorie === null) {
        // temp. workaround: do not return 404 when non-localized existing category is loaded
        if (KategorieHelper::categoryExists($kKategorie)) {
            $AktuelleKategorie->loadFromDB($kKategorie);
        } else {
            Shop::$is404             = true;
            $cParameter_arr['is404'] = true;

            return;
        }
    }
    $expandedCategories->getOpenCategories($AktuelleKategorie);
}
$NaviFilter->setUserSort($AktuelleKategorie);
$oSuchergebnisse = $NaviFilter->generateSearchResults($AktuelleKategorie);
$pages           = $oSuchergebnisse->getPages();
if ($conf['navigationsfilter']['allgemein_weiterleitung'] === 'Y' && $oSuchergebnisse->getVisibleProductCount() === 1) {
    $hasSubCategories = ($categoryID = $NaviFilter->getCategory()->getValue()) > 0
        ? (new \Kategorie(
            $categoryID,
            $NaviFilter->getFilterConfig()->getLanguageID(),
            $NaviFilter->getFilterConfig()->getCustomerGroupID())
        )
            ->existierenUnterkategorien()
        : false;
    if ($NaviFilter->getFilterCount() > 0
        || $NaviFilter->getRealSearch() !== null
        || ($NaviFilter->getCategory()->getValue() > 0 && !$hasSubCategories)
    ) {
        http_response_code(301);
        $product = $oSuchergebnisse->getProducts()->pop();
        $url     = empty($product->cURL)
            ? (\Shop::getURL() . '/?a=' . $product->kArtikel)
            : (\Shop::getURL() . '/' . $product->cURL);
        header('Location: ' . $url);
        exit;
    }
}
if ($pages->getCurrentPage() > 0
    && $pages->getTotalPages() > 0
    && ($oSuchergebnisse->getVisibleProductCount() === 0 || ($pages->getCurrentPage() > $pages->getTotalPages()))
) {
    http_response_code(301);
    header('Location: ' . $NaviFilter->getFilterURL()->getURL());
    exit;
}
Redirect::doMainwordRedirect($NaviFilter, $oSuchergebnisse->getVisibleProductCount(), true);
if ($conf['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
    $productsIDs = $oSuchergebnisse->getProducts()->map(function ($article) {
        return (int)$article->kArtikel;
    });
    $bestsellers = Bestseller::buildBestsellers(
        $productsIDs,
        Session::CustomerGroup()->getID(),
        Session::CustomerGroup()->mayViewCategories(),
        false,
        (int)$conf['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'],
        (int)$conf['global']['global_bestseller_minanzahl']
    );
    $products = $oSuchergebnisse->getProducts()->all();
    Bestseller::ignoreProducts($products, $bestsellers);
}
if (RequestHelper::verifyGPCDataInt('zahl') > 0) {
    $_SESSION['ArtikelProSeite'] = RequestHelper::verifyGPCDataInt('zahl');
}
if (!isset($_SESSION['ArtikelProSeite'])
    && $conf['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N'
) {
    $_SESSION['ArtikelProSeite'] = min(
        (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'],
        ARTICLES_PER_PAGE_HARD_LIMIT
    );
}
$oSuchergebnisse->getProducts()->transform(function ($product) use ($conf) {
    $product->verfuegbarkeitsBenachrichtigung = ArtikelHelper::showAvailabilityForm(
        $product,
        $conf['artikeldetails']['benachrichtigung_nutzen']
    );

    return $product;
});
if ($oSuchergebnisse->getProducts()->count() === 0) {
    if ($NaviFilter->hasCategory()) {
        $KategorieInhalt                  = new stdClass();
        $KategorieInhalt->Unterkategorien = new KategorieListe();
        $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->getCategory()->getValue());

        $tb = $conf['artikeluebersicht']['topbest_anzeigen'];
        if ($tb === 'Top' || $tb === 'TopBest') {
            $KategorieInhalt->TopArtikel = new ArtikelListe();
            $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
        }
        if ($tb === 'Bestseller' || $tb === 'TopBest') {
            $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
            $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel(
                $KategorieInhalt->Unterkategorien,
                $KategorieInhalt->TopArtikel ?? null
            );
        }
    } else {
        $oSuchergebnisse->setSearchUnsuccessful(true);
    }
}
$oNavigationsinfo = $NaviFilter->getMetaData()->getNavigationInfo($AktuelleKategorie, $expandedCategories);
if (strpos(basename($NaviFilter->getFilterURL()->getURL()), '.php') === false) {
    $cCanonicalURL = $NaviFilter->getFilterURL()->getURL(null, true) . ($pages->getCurrentPage() > 1
        ? SEP_SEITE . $pages->getCurrentPage()
        : '');
}
AuswahlAssistent::startIfRequired(
    AUSWAHLASSISTENT_ORT_KATEGORIE,
    $cParameter_arr['kKategorie'],
    Shop::getLanguageID(),
    $smarty,
    [],
    $NaviFilter
);
$pagination = new \Filter\Pagination\Pagination($NaviFilter, new \Filter\Pagination\ItemFactory());
$pagination->create($pages);
$smarty->assign('NaviFilter', $NaviFilter)
       ->assign('KategorieInhalt', $KategorieInhalt)
       ->assign('oErweiterteDarstellung', $NaviFilter->getMetaData()->getExtendedView($cParameter_arr['nDarstellung']))
       ->assign('oBestseller_arr', $bestsellers)
       ->assign('oNaviSeite_arr', $pagination->getItemsCompat())
       ->assign('filterPagination', $pagination)
       ->assign('Suchergebnisse', $oSuchergebnisse)
       ->assign('oNavigationsinfo', $oNavigationsinfo)
       ->assign('nMaxAnzahlArtikel', (int)($oSuchergebnisse->getProductCount() >=
           (int)$conf['artikeluebersicht']['suche_max_treffer']));

executeHook(HOOK_FILTER_PAGE);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$oGlobaleMetaAngabenAssoc_arr = \Filter\Metadata::getGlobalMetaData();
$smarty->assign(
    'meta_title',
    $oNavigationsinfo->generateMetaTitle(
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
)->assign(
    'meta_description',
    $oNavigationsinfo->generateMetaDescription(
        $oSuchergebnisse->getProducts()->all(),
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
)->assign(
    'meta_keywords',
    $oNavigationsinfo->generateMetaKeywords(
        $oSuchergebnisse->getProducts()->all(),
        $AktuelleKategorie
    )
);
executeHook(HOOK_FILTER_ENDE);
$smarty->display('productlist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
