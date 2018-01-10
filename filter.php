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
/** @global ProductFilter $NaviFilter*/
$Einstellungen      = Shopsetting::getInstance()->getAll();
$productsPerPage    = [5, 10, 25, 50, 100];
$bestsellers        = [];
$suchanfrage        = '';
$doSearch           = true;
$AktuelleKategorie  = new stdClass();
$oSuchergebnisse    = new stdClass();
$expandedCategories = new stdClass();
$hasError           = false;
$cParameter_arr     = Shop::getParameters();
if ($NaviFilter->hasCategory()) {
    $AktuelleSeite               = 'PRODUKTE';
    $kKategorie                  = $NaviFilter->getCategory()->getValue();
    $AktuelleKategorie           = new Kategorie($kKategorie);
    $_SESSION['LetzteKategorie'] = $kKategorie;
    if ($AktuelleKategorie->kKategorie === null) {
        //temp. workaround: do not return 404 when non-localized existing category is loaded
        if (KategorieHelper::categoryExists($kKategorie)) {
            $AktuelleKategorie->kKategorie = $kKategorie;
        } else {
            Shop::$is404             = true;
            $cParameter_arr['is404'] = true;

            return;
        }
    }
    $expandedCategories = new KategorieListe();
    $expandedCategories->getOpenCategories($AktuelleKategorie);
}

// Usersortierung
$NaviFilter->getMetaData()->setUserSort($AktuelleKategorie);
// Erweiterte Darstellung Artikelübersicht
$oSuchergebnisse = $NaviFilter->getProducts(true, $AktuelleKategorie);
// Umleiten falls SEO keine Artikel ergibt
doMainwordRedirect($NaviFilter, $oSuchergebnisse->Artikel->elemente->count(), true);
// Bestsellers
if ($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
    $productsIDs = $oSuchergebnisse->Artikel->elemente->map(function ($article) {
        return (int)$article->kArtikel;
    });
    $limit       = isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])
        ? (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl']
        : 3;
    $minsells    = isset($Einstellungen['global']['global_bestseller_minanzahl'])
        ? (int)$Einstellungen['global']['global_bestseller_minanzahl']
        : 10;
    $bestsellers = Bestseller::buildBestsellers(
        $productsIDs,
        Session::CustomerGroup()->getID(),
        Session::CustomerGroup()->mayViewCategories(),
        false,
        $limit,
        $minsells
    );
    $products = $oSuchergebnisse->Artikel->elemente->getItems();
    Bestseller::ignoreProducts($products, $bestsellers);
}
$smarty->assign('oErweiterteDarstellung', $NaviFilter->getMetaData()->getExtendedView($cParameter_arr['nDarstellung']))
       ->assign('oBestseller_arr', $bestsellers);
if (verifyGPCDataInteger('zahl') > 0) {
    $_SESSION['ArtikelProSeite'] = verifyGPCDataInteger('zahl');
    setFsession(0, 0, $_SESSION['ArtikelProSeite']);
}
if (!isset($_SESSION['ArtikelProSeite'])
    && $Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N'
) {
    $_SESSION['ArtikelProSeite'] = min(
        (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'],
        ARTICLES_PER_PAGE_HARD_LIMIT
    );
}
// Verfügbarkeitsbenachrichtigung pro Artikel
$oSuchergebnisse->Artikel->elemente->transform(function ($article) use ($Einstellungen) {
    $article->verfuegbarkeitsBenachrichtigung = gibVerfuegbarkeitsformularAnzeigen(
        $article,
        $Einstellungen['artikeldetails']['benachrichtigung_nutzen']
    );

    return $article;
});

if ($oSuchergebnisse->Artikel->elemente->count() === 0) {
    if ($NaviFilter->hasCategory()) {
        // hole alle enthaltenen Kategorien
        $KategorieInhalt                  = new stdClass();
        $KategorieInhalt->Unterkategorien = new KategorieListe();
        $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->getCategory()->getValue());

        $tb = $Einstellungen['artikeluebersicht']['topbest_anzeigen'];
        // wenn keine eigenen Artikel in dieser Kat, Top Angebote / Bestseller
        // aus unterkats + unterunterkats rausholen und anzeigen?
        if ($tb === 'Top' || $tb === 'TopBest') {
            $KategorieInhalt->TopArtikel = new ArtikelListe();
            $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
        }
        if ($tb === 'Bestseller' || $tb === 'TopBest') {
            $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
            $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel(
                $KategorieInhalt->Unterkategorien,
                isset($KategorieInhalt->TopArtikel) ? $KategorieInhalt->TopArtikel : 0
            );
        }
        $smarty->assign('KategorieInhalt', $KategorieInhalt);
    } else {
        // Suchfeld anzeigen
        $oSuchergebnisse->SucheErfolglos = 1;
    }
}
// Navigation
$oNavigationsinfo = $NaviFilter->getMetaData()->getNavigationInfo($AktuelleKategorie, $expandedCategories);
// Canonical
if (strpos(basename($NaviFilter->getFilterURL()->getURL()), '.php') === false) {
    $cSeite        = isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite)
    && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1
        ? SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite
        : '';
    $cCanonicalURL = $NaviFilter->getFilterURL()->getURL(null, true) . $cSeite;
}
AuswahlAssistent::startIfRequired(
    AUSWAHLASSISTENT_ORT_KATEGORIE,
    $cParameter_arr['kKategorie'],
    Shop::getLanguageID(),
    $smarty,
    [],
    $NaviFilter
);
$smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
       ->assign('code_benachrichtigung_verfuegbarkeit',
           generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']))
       ->assign('oNaviSeite_arr', $oNavigationsinfo->buildPageNavigation(
           true,
           $oSuchergebnisse->Seitenzahlen,
           $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']))
       ->assign('ArtikelProSeite', $productsPerPage)
       ->assign('Navigation', $oNavigationsinfo->getBreadCrumb())
       ->assign('Sortierliste', $NaviFilter->getMetaData()->getSortingOptions())
       ->assign('Suchergebnisse', $oSuchergebnisse)
       ->assign('requestURL', isset($requestURL) ? $requestURL : null)
       ->assign('sprachURL', isset($sprachURL) ? $sprachURL : null)
       ->assign('oNavigationsinfo', $oNavigationsinfo)
       ->assign('SEO', true)
       ->assign('nMaxAnzahlArtikel', (int)($oSuchergebnisse->GesamtanzahlArtikel >=
           (int)$Einstellungen['artikeluebersicht']['suche_max_treffer']))
       ->assign('SESSION_NOTWENDIG', false);

executeHook(HOOK_FILTER_PAGE);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$oGlobaleMetaAngabenAssoc_arr = Metadata::getGlobalMetaData();
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
        $oSuchergebnisse->Artikel->elemente->getItems(),
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
)->assign(
    'meta_keywords',
    $oNavigationsinfo->generateMetaKeywords(
        $oSuchergebnisse->Artikel->elemente->getItems(),
        $AktuelleKategorie
    )
);
executeHook(HOOK_FILTER_ENDE);
$smarty->display('productlist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
