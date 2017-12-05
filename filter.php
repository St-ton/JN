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
/** @global array $cParameter_arr */
/** @global ProductFilter $NaviFilter*/
$Einstellungen          = Shopsetting::getInstance()->getAll();
$nArtikelProSeite_arr   = [5, 10, 25, 50, 100];
$suchanfrage            = '';
$doSearch               = true;
$AktuelleKategorie      = new stdClass();
$oSuchergebnisse        = new stdClass();
$AufgeklappteKategorien = new stdClass();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$hasError               = false;
$nMindestzeichen        = ((int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'] > 0)
    ? (int)$Einstellungen['artikeluebersicht']['suche_min_zeichen']
    : 3;
if (strlen($cParameter_arr['cSuche']) > 0 || (isset($_GET['qs']) && strlen($_GET['qs']) === 0)) {
    preg_match("/[\w" . utf8_decode('äÄüÜöÖß') . "\.\-]{" . $nMindestzeichen . ",}/",
        str_replace(' ', '', $cParameter_arr['cSuche']), $cTreffer_arr);
    $hasError = count($cTreffer_arr) === 0;
}
// setze Kat in Session
if (isset($cParameter_arr['kKategorie']) && $cParameter_arr['kKategorie'] > 0) {
    $_SESSION['LetzteKategorie'] = $cParameter_arr['kKategorie'];
    $AktuelleSeite               = 'PRODUKTE';
}
if ($NaviFilter->hasCategory()) {
    $kKategorie        = $NaviFilter->getBaseState()->getValue();
    $AktuelleKategorie = new Kategorie($kKategorie);
    if ($AktuelleKategorie->kKategorie === null) {
        //temp. workaround: do not return 404 when non-localized existing category is loaded
        if (KategorieHelper::categoryExists($kKategorie)) {
            $AktuelleKategorie->kKategorie = $kKategorie;
        } else {
            $is404                   = true;
            $cParameter_arr['is404'] = true;

            return;
        }
    }
    $AufgeklappteKategorien = new KategorieListe();
    $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
}
// Usersortierung
$NaviFilter->getMetaData()->setUserSort($AktuelleKategorie);
// Erweiterte Darstellung Artikelübersicht
$smarty->assign('oErweiterteDarstellung', $NaviFilter->getMetaData()->getExtendedView($cParameter_arr['nDarstellung']));
$oSuchergebnisse = $NaviFilter->getProducts(true, $AktuelleKategorie);
if ($hasError) {
    $cFehler                              = Shop::Lang()->get('expressionHasTo') .
        ' ' . $nMindestzeichen . ' ' .
        Shop::Lang()->get('lettersDigits');
    $oSuchergebnisse->GesamtanzahlArtikel = 0;
    $oSuchergebnisse->SucheErfolglos      = 1;
    $oSuchergebnisse->Fehler              = $cFehler;
    $oSuchergebnisse->cSuche              = strip_tags(trim($cParameter_arr['cSuche']));
}
// @todo: this is already called in ProductFilter::getProduct() - remove line?
//$NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::getLanguage());
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
    $smarty->assign('oBestseller_arr', $bestsellers);
}
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
        // wenn keine eigenen Artikel in dieser Kat, Top Angebote / Bestseller
        // aus unterkats + unterunterkats rausholen und anzeigen?
        if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Top'
            || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest'
        ) {
            $KategorieInhalt->TopArtikel = new ArtikelListe();
            $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
        }
        if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Bestseller'
            || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest'
        ) {
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
$oNavigationsinfo = $NaviFilter->getMetaData()->getNavigationInfo($AktuelleKategorie, $AufgeklappteKategorien);
// Canonical
if (strpos(basename($NaviFilter->getFilterURL()->getURL()), '.php') === false) {
    $cSeite        = isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite)
    && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1
        ? SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite
        : '';
    $cCanonicalURL = $NaviFilter->getFilterURL()->getURL(null, true) . $cSeite;
}
// Auswahlassistent
if (TEMPLATE_COMPATIBILITY === true && function_exists('starteAuswahlAssistent')) {
    starteAuswahlAssistent(
        AUSWAHLASSISTENT_ORT_KATEGORIE,
        $cParameter_arr['kKategorie'],
        Shop::getLanguageID(),
        $smarty,
        $Einstellungen['auswahlassistent']
    );
} elseif (class_exists('AuswahlAssistent')) {
    AuswahlAssistent::startIfRequired(
        AUSWAHLASSISTENT_ORT_KATEGORIE,
        $cParameter_arr['kKategorie'],
        Shop::getLanguageID(),
        $smarty
    );
}
$smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
       ->assign('code_benachrichtigung_verfuegbarkeit',
           generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']))
       ->assign('oNaviSeite_arr', $oNavigationsinfo->buildPageNavigation(
           true,
           $oSuchergebnisse->Seitenzahlen,
           $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']))
       ->assign('ArtikelProSeite', $nArtikelProSeite_arr)
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
);
$smarty->assign(
    'meta_description',
    $oNavigationsinfo->generateMetaDescription(
        $oSuchergebnisse->Artikel->elemente->getItems(),
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
);
$smarty->assign(
    'meta_keywords',
    $oNavigationsinfo->generateMetaKeywords(
        $oSuchergebnisse->Artikel->elemente->getItems(),
        $AktuelleKategorie
    )
);
executeHook(HOOK_FILTER_ENDE);
$smarty->display('productlist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
