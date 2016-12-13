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
$cachingOptions = Shop::getSettings([CONF_CACHING]);
Shop::setPageType(PAGE_ARTIKELLISTE);
/** @global JTLSmarty $smarty */
/** @global Navigationsfilter $NaviFilter */
/** @global array $cParameter_arr */
$Einstellungen          = Shop::getSettings([
        CONF_GLOBAL,
        CONF_RSS,
        CONF_ARTIKELUEBERSICHT,
        CONF_VERGLEICHSLISTE,
        CONF_BEWERTUNG,
        CONF_NAVIGATIONSFILTER,
        CONF_BOXEN,
        CONF_ARTIKELDETAILS,
        CONF_METAANGABEN,
        CONF_SUCHSPECIAL,
        CONF_BILDER,
        CONF_PREISVERLAUF,
        CONF_SONSTIGES,
        CONF_AUSWAHLASSISTENT
    ]
);
$nArtikelProSeite_arr   = [5, 10, 25, 50, 100];
$doSearch               = true;
$suchanfrage            = '';
$AktuelleKategorie      = new stdClass();
$AufgeklappteKategorien = new stdClass();
$startKat               = new Kategorie();
$oSuchergebnisse        = new stdClass();
$startKat->kKategorie   = 0;
if (strlen($cParameter_arr['cSuche']) > 0 || (isset($_GET['qs']) && strlen($_GET['qs']) === 0)) {
    $nMindestzeichen = ((int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'] > 0)
        ? (int)$Einstellungen['artikeluebersicht']['suche_min_zeichen']
        : 3;
    preg_match("/[\w" . utf8_decode('äÄüÜöÖß') . "\.\-]{" . $nMindestzeichen . ",}/", str_replace(' ', '', $cParameter_arr['cSuche']), $cTreffer_arr);
    if (count($cTreffer_arr) === 0) {
        $cFehler                                      = Shop::Lang()->get('expressionHasTo', 'global') .
            ' ' . $nMindestzeichen . ' ' .
            Shop::Lang()->get('lettersDigits', 'global');
        $oSuchergebnisse->Artikel                     = new stdClass();
        $oSuchergebnisse->Artikel->elemente           = [];
        $oSuchergebnisse->GesamtanzahlArtikel         = 0;
        $oSuchergebnisse->SucheErfolglos              = 1;
        $oSuchergebnisse->Seitenzahlen                = new stdClass();
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite = 0;
        $oSuchergebnisse->Seitenzahlen->MaxSeiten     = 0;
        $oSuchergebnisse->Seitenzahlen->minSeite      = 0;
        $oSuchergebnisse->Seitenzahlen->maxSeite      = 0;
        $oSuchergebnisse->Fehler                      = $cFehler;
        $oSuchergebnisse->cSuche                      = strip_tags(trim($cParameter_arr['cSuche']));
        $doSearch                                     = false;
    }
}
if ($doSearch) {
    // setze Kat in Session
    if (isset($cParameter_arr['kKategorie']) && $cParameter_arr['kKategorie'] > 0) {
        $_SESSION['LetzteKategorie'] = $cParameter_arr['kKategorie'];
        $AktuelleSeite               = 'PRODUKTE';
    }
    if ($cParameter_arr['kSuchanfrage'] > 0) {
        $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$cParameter_arr['kSuchanfrage'], null,
            null, null, null, false, 'cSuche');
        if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
            $NaviFilter->Suche->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
            $NaviFilter->Suche->cSuche       = $oSuchanfrage->cSuche;
        }
    }
    // Suchcache beachten / erstellen
    if (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        $NaviFilter->Suche->kSuchCache = bearbeiteSuchCache($NaviFilter);
    }
    if ($NaviFilter->hasCategory()) {
        $kKategorie        = $NaviFilter->getActiveState()->getValue();
        $AktuelleKategorie = new Kategorie($kKategorie);
        if (!isset($AktuelleKategorie->kKategorie) || $AktuelleKategorie->kKategorie === null) {
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
    $NaviFilter->setUserSort($AktuelleKategorie);
    // Erweiterte Darstellung Artikelübersicht
    gibErweiterteDarstellung($Einstellungen, $NaviFilter, $cParameter_arr['nDarstellung']);
    $oSuchergebnisse = $NaviFilter->getProducts();

    suchanfragenSpeichern($NaviFilter->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel);
    $NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::$kSprache);
    // Umleiten falls SEO keine Artikel ergibt
    doMainwordRedirect($NaviFilter, count($oSuchergebnisse->Artikel->elemente), true);
    // Bestsellers
    if (isset($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren']) && $Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
        $products = [];
        foreach ($oSuchergebnisse->Artikel->elemente as $product) {
            $products[] = (int)$product->kArtikel;
        }
        $limit       = (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])) ?
            (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'] :
            3;
        $minsells    = (isset($Einstellungen['global']['global_bestseller_minanzahl'])) ?
            (int)$Einstellungen['global']['global_bestseller_minanzahl'] :
            10;
        $bestsellers = Bestseller::buildBestsellers($products, $_SESSION['Kundengruppe']->kKundengruppe,
            $_SESSION['Kundengruppe']->darfArtikelKategorienSehen, false, $limit, $minsells);
        Bestseller::ignoreProducts($oSuchergebnisse->Artikel->elemente, $bestsellers);
        $smarty->assign('oBestseller_arr', $bestsellers);
    }

    $oSuchergebnisse = $NaviFilter->setFilterOptions($oSuchergebnisse, $AktuelleKategorie);
    if (verifyGPCDataInteger('zahl') > 0) {
        $_SESSION['ArtikelProSeite'] = verifyGPCDataInteger('zahl');
        setFsession(0, 0, $_SESSION['ArtikelProSeite']);
    }
    if (!isset($_SESSION['ArtikelProSeite']) && $Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N') {
        $_SESSION['ArtikelProSeite'] = min((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'], ARTICLES_PER_PAGE_HARD_LIMIT);
    }
    // Verfügbarkeitsbenachrichtigung pro Artikel
    if (is_array($oSuchergebnisse->Artikel->elemente)) {
        foreach ($oSuchergebnisse->Artikel->elemente as $Artikel) {
            $Artikel->verfuegbarkeitsBenachrichtigung = gibVerfuegbarkeitsformularAnzeigen($Artikel, $Einstellungen['artikeldetails']['benachrichtigung_nutzen']);
        }
    }
    if (count($oSuchergebnisse->Artikel->elemente) === 0) {
        if ($NaviFilter->Kategorie->isInitialized()) {
            // hole alle enthaltenen Kategorien
            $KategorieInhalt                  = new stdClass();
            $KategorieInhalt->Unterkategorien = new KategorieListe();
            $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->Kategorie->getValue());
            // wenn keine eigenen Artikel in dieser Kat, Top Angebote / Bestseller
            // aus unterkats + unterunterkats rausholen und anzeigen?
            if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Top' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
                $KategorieInhalt->TopArtikel = new ArtikelListe();
                $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
            }
            if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Bestseller' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
                $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
                $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel($KategorieInhalt->Unterkategorien,
                    (isset($KategorieInhalt->TopArtikel)) ? $KategorieInhalt->TopArtikel : 0);
            }
            $smarty->assign('KategorieInhalt', $KategorieInhalt);
        } else {
            // Suchfeld anzeigen
            $oSuchergebnisse->SucheErfolglos = 1;
        }
    }
    $NaviFilter->createUnsetFilterURLs(true, $oSuchergebnisse);
}
// Header bauen
$oSuchergebnisse->SuchausdruckWrite = $NaviFilter->getHeader();

// Mainword NaviBilder
$oNavigationsinfo           = new stdClass();
$oNavigationsinfo->cName    = '';
$oNavigationsinfo->cBildURL = '';
// Navigation
$cBrotNavi               = '';
$oMeta                   = new stdClass();
$oMeta->cMetaTitle       = '';
$oMeta->cMetaDescription = '';
$oMeta->cMetaKeywords    = '';
if ($NaviFilter->Kategorie->isInitialized()) {
    $oNavigationsinfo->oKategorie = $AktuelleKategorie;

    if ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'Y') {
        $oNavigationsinfo->cName = $AktuelleKategorie->cName;
    } elseif ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'BT') {
        $oNavigationsinfo->cName    = $AktuelleKategorie->cName;
        $oNavigationsinfo->cBildURL = $AktuelleKategorie->getKategorieBild();
    } elseif ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'B') {
        $oNavigationsinfo->cBildURL = $AktuelleKategorie->getKategorieBild();
    }
    $cBrotNavi = createNavigation('PRODUKTE', $AufgeklappteKategorien);
} elseif ($NaviFilter->Hersteller->isInitialized()) {
    $oNavigationsinfo->oHersteller = new Hersteller($NaviFilter->Hersteller->getValue());

    if ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'Y') {
        $oNavigationsinfo->cName = $oNavigationsinfo->oHersteller->cName;
    } elseif ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'BT') {
        $oNavigationsinfo->cName    = $oNavigationsinfo->oHersteller->cName;
        $oNavigationsinfo->cBildURL = $oNavigationsinfo->oHersteller->cBildpfadNormal;
    } elseif ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'B') {
        $oNavigationsinfo->cBildURL = $oNavigationsinfo->oHersteller->cBildpfadNormal;
    }
    if (isset($oNavigationsinfo->oHersteller->cMetaTitle)) {
        $oMeta->cMetaTitle = $oNavigationsinfo->oHersteller->cMetaTitle;
    }
    if (isset($oNavigationsinfo->oHersteller->cMetaDescription)) {
        $oMeta->cMetaDescription = $oNavigationsinfo->oHersteller->cMetaDescription;
    }
    if (isset($oNavigationsinfo->oHersteller->cMetaKeywords)) {
        $oMeta->cMetaKeywords = $oNavigationsinfo->oHersteller->cMetaKeywords;
    }
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, $NaviFilter->getURL(true, null));
} elseif ($NaviFilter->MerkmalWert->isInitialized()) {
    $oNavigationsinfo->oMerkmalWert = new MerkmalWert($NaviFilter->MerkmalWert->getValue());

    if ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'Y') {
        $oNavigationsinfo->cName = $oNavigationsinfo->oMerkmalWert->cWert;
    } elseif ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'BT') {
        $oNavigationsinfo->cName    = $oNavigationsinfo->oMerkmalWert->cWert;
        $oNavigationsinfo->cBildURL = $oNavigationsinfo->oMerkmalWert->cBildpfadNormal;
    } elseif ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'B') {
        $oNavigationsinfo->cBildURL = $oNavigationsinfo->oMerkmalWert->cBildpfadNormal;
    }
    if (isset($oNavigationsinfo->oMerkmalWert->cMetaTitle)) {
        $oMeta->cMetaTitle = $oNavigationsinfo->oMerkmalWert->cMetaTitle;
    }
    if (isset($oNavigationsinfo->oMerkmalWert->cMetaDescription)) {
        $oMeta->cMetaDescription = $oNavigationsinfo->oMerkmalWert->cMetaDescription;
    }
    if (isset($oNavigationsinfo->oMerkmalWert->cMetaKeywords)) {
        $oMeta->cMetaKeywords = $oNavigationsinfo->oMerkmalWert->cMetaKeywords;
    }
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, $NaviFilter->getURL(true, null));
} elseif ($NaviFilter->Tag->isInitialized()) {
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, $NaviFilter->getURL(true, null));
} elseif ($NaviFilter->Suchspecial->isInitialized()) {
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, $NaviFilter->getURL(true, null));
} elseif ($NaviFilter->Suche->isInitialized()) {
    $cBrotNavi = createNavigation('', '', 0, Shop::Lang()->get('search', 'breadcrumb') . ': ' .
        $NaviFilter->cBrotNaviName, $NaviFilter->getURL(true, null));
}
// Canonical
if (strpos(basename($NaviFilter->getURL(true, null)), '.php') === false || !SHOP_SEO) {
    $cSeite = '';
    if (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
        $cSeite = SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
    }
    $cCanonicalURL = $NaviFilter->getURL(true, null, true) . $cSeite;
}
// Auswahlassistent
if (function_exists('starteAuswahlAssistent')) {
    starteAuswahlAssistent(AUSWAHLASSISTENT_ORT_KATEGORIE, $cParameter_arr['kKategorie'], Shop::getLanguage(), $smarty, $Einstellungen['auswahlassistent']);
}
$smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
        ->assign('code_benachrichtigung_verfuegbarkeit', generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']))
        ->assign('oNaviSeite_arr', baueSeitenNaviURL($NaviFilter, true, $oSuchergebnisse->Seitenzahlen, $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']))
        ->assign('PFAD_ART_ABNAHMEINTERVALL', PFAD_ART_ABNAHMEINTERVALL)
        ->assign('ArtikelProSeite', $nArtikelProSeite_arr)
        ->assign('Navigation', $cBrotNavi)
        ->assign('Einstellungen', $Einstellungen)
        ->assign('Sortierliste', gibSortierliste($Einstellungen))
        ->assign('Einstellungen', $Einstellungen)
        ->assign('Suchergebnisse', $oSuchergebnisse)
        ->assign('requestURL', (isset($requestURL)) ? $requestURL : null)
        ->assign('sprachURL', (isset($sprachURL)) ? $sprachURL : null)
        ->assign('oNavigationsinfo', $oNavigationsinfo)
        ->assign('SEO', true)
        ->assign('nMaxAnzahlArtikel', (int)($oSuchergebnisse->GesamtanzahlArtikel >= intval($Einstellungen['artikeluebersicht']['suche_max_treffer'])))
        ->assign('SESSION_NOTWENDIG', false);

executeHook(HOOK_FILTER_PAGE);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben();
$smarty->assign(
    'meta_title',
    $NaviFilter->getMetaTitle(
        $oMeta,
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
);
$smarty->assign(
    'meta_description',
    $NaviFilter->getMetaDescription(
        $oMeta,
        $oSuchergebnisse->Artikel->elemente,
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr,
        $AktuelleKategorie
    )
);
$smarty->assign(
    'meta_keywords',
    $NaviFilter->getMetaKeywords(
        $oMeta,
        $oSuchergebnisse->Artikel->elemente,
        $AktuelleKategorie
    )
);
//Shop::dbg($NaviFilter->getActiveFilters2(), true, 'active:');
//Shop::dbg($NaviFilter->getFilterValue('FilterFelixFilter'), true, '$NaviFilter:');
executeHook(HOOK_FILTER_ENDE);
$smarty->display('productlist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
