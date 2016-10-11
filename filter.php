<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
$cachingOptions = Shop::getSettings(array(CONF_CACHING));
Shop::setPageType(PAGE_ARTIKELLISTE);
/** @global JTLSmarty $smarty */
/** @global Navigationsfilter $NaviFilter*/
$Einstellungen = Shop::getSettings(
    array(
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
    )
);
$suchanfrage = '';
// setze Kat in Session
if (isset($cParameter_arr['kKategorie']) && $cParameter_arr['kKategorie'] > 0) {
    $_SESSION['LetzteKategorie'] = $cParameter_arr['kKategorie'];
    $AktuelleSeite               = 'PRODUKTE';
}
// Standardoptionen
$nArtikelProSeite_arr = array(
    5,
    10,
    25,
    50,
    100
);
if ($cParameter_arr['kSuchanfrage'] > 0) {
    $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$cParameter_arr['kSuchanfrage'], null, null, null, null, false, 'cSuche');
    if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
        $NaviFilter->Suche->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
        $NaviFilter->Suche->cSuche       = $oSuchanfrage->cSuche;
    }
}
// Suchcache beachten / erstellen
if (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
    $NaviFilter->Suche->kSuchCache = bearbeiteSuchCache($NaviFilter);
}

$AktuelleKategorie      = new stdClass();
$AufgeklappteKategorien = new stdClass();
if ($NaviFilter2->hasCategory()) {
    $kKategorie = $NaviFilter2->getActiveState()->getID();
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
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
// Usersortierung
setzeUsersortierung($NaviFilter);


// Hole alle aktiven Sprachen
$NaviFilter->oSprache_arr = Shop::Lang()->getLangArray();

// Filter SQL
$FilterSQL = bauFilterSQL($NaviFilter);

//Shop::dbg($FilterSQL, false, 'filtersql:');
// Erweiterte Darstellung Artikelübersicht
gibErweiterteDarstellung($Einstellungen, $NaviFilter, $cParameter_arr['nDarstellung']);
if (!isset($NaviFilter->Suche)) {
    $NaviFilter->Suche = new stdClass();
}
if (!isset($NaviFilter->Suche->cSuche)) {
    $NaviFilter->Suche->cSuche = '';
}


$oSuchergebnisse = buildSearchResults($FilterSQL, $NaviFilter);
$oSuchergebnisse2 = $NaviFilter2->getProducts();;
Shop::dbg(count($oSuchergebnisse->Artikel->elemente), false, 'elem count original:');
Shop::dbg(count($oSuchergebnisse2->Artikel->elemente), false, 'elem count new:');
$ori = [];
foreach($oSuchergebnisse->Artikel->elemente as $art) {
    $ori[] = (int)$art->kArtikel;
}
$new = [];
foreach($oSuchergebnisse2->Artikel->elemente as $art) {
    $new[] = (int)$art->kArtikel;
}
if ($ori !== $new) {
    echo 'MISMATCH!<br>';
    Shop::dbg($ori, false, 'ori:');
    Shop::dbg($new, true, 'new:');
}

Shop::dbg($oSuchergebnisse->Seitenzahlen, false,'ori:');
Shop::dbg($oSuchergebnisse2->Seitenzahlen, false,'new:');

suchanfragenSpeichern($NaviFilter->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel);
$NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::$kSprache);




// Umleiten falls SEO keine Artikel ergibt
doMainwordRedirect($NaviFilter, count($oSuchergebnisse->Artikel->elemente), true);
// Bestsellers
if (isset($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren']) && $Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
    $products = array();
    foreach ($oSuchergebnisse->Artikel->elemente as $product) {
        $products[] = (int) $product->kArtikel;
    }
    $limit       = (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])) ?
        (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'] :
        3;
    $minsells    = (isset($Einstellungen['global']['global_bestseller_minanzahl'])) ?
        (int)$Einstellungen['global']['global_bestseller_minanzahl'] :
        10;
    $bestsellers = Bestseller::buildBestsellers($products, $_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['Kundengruppe']->darfArtikelKategorienSehen, false, $limit, $minsells);
    Bestseller::ignoreProducts($oSuchergebnisse->Artikel->elemente, $bestsellers);
    $smarty->assign('oBestseller_arr', $bestsellers);
}
// Schauen ob die maximale Anzahl der Artikel >= der max. Anzahl die im Backend eingestellt wurde
if (intval($Einstellungen['artikeluebersicht']['suche_max_treffer']) > 0) {
    if ($oSuchergebnisse->GesamtanzahlArtikel >= intval($Einstellungen['artikeluebersicht']['suche_max_treffer'])) {
        $smarty->assign('nMaxAnzahlArtikel', 1);
    }
}
// Filteroptionen holen
$oSuchergebnisse->Herstellerauswahl = gibHerstellerFilterOptionen($FilterSQL, $NaviFilter);
$oSuchergebnisse2->Herstellerauswahl = $NaviFilter2->getManufacturerFilterOptions();

//Shop::dbg($oSuchergebnisse->Herstellerauswahl, false, '$oSuchergebnisse->Herstellerauswahl');
//Shop::dbg($oSuchergebnisse2->Herstellerauswahl, true, '$oSuchergebnisse2->Herstellerauswahl');


$oSuchergebnisse->Bewertung         = gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter);
$oSuchergebnisse2->Bewertung         = $NaviFilter2->getRatingFilterOptions();

//Shop::dbg($oSuchergebnisse->Bewertung, false, '$oSuchergebnisse->Bewertung');
//Shop::dbg($oSuchergebnisse2->Bewertung, true, '$oSuchergebnisse2->Bewertung');


$oSuchergebnisse->Tags              = gibTagFilterOptionen($FilterSQL, $NaviFilter);
$oSuchergebnisse2->Tags              = $NaviFilter2->getTagFilterOptions();

//Shop::dbg($oSuchergebnisse->Tags, false, '$oSuchergebnisse->Tags');
//Shop::dbg($oSuchergebnisse2->Tags, true, '$oSuchergebnisse2->Tags');


if (isset($Einstellungen['navigationsfilter']['allgemein_tagfilter_benutzen']) && $Einstellungen['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
    $oSuchergebnisse->TagsJSON = gibTagFilterJSONOptionen($FilterSQL, $NaviFilter);

    $oTags_arr = [];
    foreach ($oSuchergebnisse2->Tags as $key => $oTags) {
        $oTags_arr[$key] = $oTags;
        $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
    }
    $oSuchergebnisse2->TagsJSON = Boxen::gibJSONString($oTags_arr);

}
$oSuchergebnisse->MerkmalFilter    = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $AktuelleKategorie, function_exists('starteAuswahlAssistent'));
$oSuchergebnisse2->MerkmalFilter    = $NaviFilter2->getAttributeFilterOptions($AktuelleKategorie, function_exists('starteAuswahlAssistent'));

//Shop::dbg($oSuchergebnisse->MerkmalFilter, false, '$oSuchergebnisse->MerkmalFilter');
//Shop::dbg($oSuchergebnisse2->MerkmalFilter, true, '$oSuchergebnisse2->MerkmalFilter');


$oSuchergebnisse->Preisspanne      = gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse);
$oSuchergebnisse2->Preisspanne      = $NaviFilter2->getPriceRangeFilterOptions($FilterSQL, $oSuchergebnisse);

//Shop::dbg($oSuchergebnisse->Preisspanne, false, '$oSuchergebnisse->Preisspanne');
//Shop::dbg($oSuchergebnisse2->Preisspanne, true, '$oSuchergebnisse2->Preisspanne');


$oSuchergebnisse->Kategorieauswahl = gibKategorieFilterOptionen($FilterSQL, $NaviFilter);
$oSuchergebnisse2->Kategorieauswahl = $NaviFilter2->getCategoryFilterOptions();

//Shop::dbg($oSuchergebnisse->Kategorieauswahl, false, '$oSuchergebnisse->Kategorieauswahl');
//Shop::dbg($oSuchergebnisse2->Kategorieauswahl, true, '$oSuchergebnisse2->Kategorieauswahl');


$oSuchergebnisse->SuchFilter       = gibSuchFilterOptionen($FilterSQL, $NaviFilter);
$oSuchergebnisse2->SuchFilter       = $NaviFilter2->getSearchFilterOptions();

//Shop::dbg($oSuchergebnisse->SuchFilter, false, '$oSuchergebnisse->SuchFilter');
//Shop::dbg($oSuchergebnisse2->SuchFilter, true, '$oSuchergebnisse2->SuchFilter');


$oSuchergebnisse->SuchFilterJSON   = gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter);

$oSuchergebnisse2->SuchFilterJSON   = [];
foreach ($oSuchergebnisse2->SuchFilter as $key => $oSuchfilter) {
    $oSuchergebnisse2->SuchFilterJSON[$key] = $oSuchfilter;
    $oSuchergebnisse2->SuchFilterJSON[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
}
$oSuchergebnisse2->SuchFilterJSON = Boxen::gibJSONString($oSuchergebnisse2->SuchFilterJSON);

if (!$cParameter_arr['kSuchspecial']) {
    $oSuchergebnisse->Suchspecialauswahl = gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter);
    $oSuchergebnisse2->Suchspecialauswahl = $NaviFilter2->getSearchSpecialFilterOptions();

//    Shop::dbg($oSuchergebnisse->Suchspecialauswahl, false, '$oSuchergebnisse->Suchspecialauswahl');
//    Shop::dbg($oSuchergebnisse2->Suchspecialauswahl, true, '$oSuchergebnisse2->Suchspecialauswahl');
}
$smarty->assign('oNaviSeite_arr', baueSeitenNaviURL($NaviFilter, true, $oSuchergebnisse->Seitenzahlen, $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']));
if (verifyGPCDataInteger('zahl') > 0) {
    $_SESSION['ArtikelProSeite'] = verifyGPCDataInteger('zahl');
    setFsession(0, 0, $_SESSION['ArtikelProSeite']);
}
if (!isset($_SESSION['ArtikelProSeite']) && $Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N') {
    $_SESSION['ArtikelProSeite'] = min((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'], ARTICLES_PER_PAGE_HARD_LIMIT);
}
// Verfügbarkeitsbenachrichtigung allgemeiner CaptchaCode
$smarty->assign('code_benachrichtigung_verfuegbarkeit', generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']));
// Verfügbarkeitsbenachrichtigung pro Artikel
if (is_array($oSuchergebnisse->Artikel->elemente)) {
    foreach ($oSuchergebnisse->Artikel->elemente as $Artikel) {
        $Artikel->verfuegbarkeitsBenachrichtigung = gibVerfuegbarkeitsformularAnzeigen($Artikel, $Einstellungen['artikeldetails']['benachrichtigung_nutzen']);
    }
}
if (count($oSuchergebnisse->Artikel->elemente) === 0) {
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        // hole alle enthaltenen Kategorien
        $KategorieInhalt                  = new stdClass();
        $KategorieInhalt->Unterkategorien = new KategorieListe();
        $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->Kategorie->kKategorie);

        // wenn keine eigenen Artikel in dieser Kat, Top Angebote / Bestseller
        // aus unterkats + unterunterkats rausholen und anzeigen?
        if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Top' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
            $KategorieInhalt->TopArtikel = new ArtikelListe();
            $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
        }
        if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Bestseller' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
            $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
            $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel($KategorieInhalt->Unterkategorien, (isset($KategorieInhalt->TopArtikel)) ? $KategorieInhalt->TopArtikel : 0);
        }
        $smarty->assign('KategorieInhalt', $KategorieInhalt);
    } else {
        // Suchfeld anzeigen
        $oSuchergebnisse->SucheErfolglos = 1;
    }
}
erstelleFilterLoesenURLs(true, $oSuchergebnisse);


// Header bauen
$NaviFilter->cBrotNaviName          = gibBrotNaviName();
$oSuchergebnisse->SuchausdruckWrite = gibHeaderAnzeige();

$oSuchergebnisse2->SuchausdruckWrite = $NaviFilter2->getHeader();

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
if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
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
} elseif (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
    $oNavigationsinfo->oHersteller = new Hersteller($NaviFilter->Hersteller->kHersteller);

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
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
} elseif (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
    $oNavigationsinfo->oMerkmalWert = new MerkmalWert($NaviFilter->MerkmalWert->kMerkmalWert);

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
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
} elseif (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
} elseif (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
    $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
} elseif (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
    $cBrotNavi = createNavigation('', '', 0, Shop::Lang()->get('search', 'breadcrumb') . ': ' . $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
}
// Canonical
if (strpos(basename(gibNaviURL($NaviFilter, true, null)), '.php') === false || !SHOP_SEO) {
    $cSeite = '';
    if (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
        $cSeite = SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
    }
    $cCanonicalURL = gibNaviURL($NaviFilter, true, null, 0, true) . $cSeite;
}
// Auswahlassistent
if (function_exists('starteAuswahlAssistent')) {
    starteAuswahlAssistent(AUSWAHLASSISTENT_ORT_KATEGORIE, $cParameter_arr['kKategorie'], Shop::$kSprache, $smarty, $Einstellungen['auswahlassistent']);
}
$smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
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
       ->assign('SEP_SEITE', SEP_SEITE)
       ->assign('SEP_KAT', SEP_KAT)
       ->assign('SEP_HST', SEP_HST)
       ->assign('SEP_MERKMAL', SEP_MERKMAL)
       ->assign('SEO', true)
       ->assign('SESSION_NOTWENDIG', false);

executeHook(HOOK_FILTER_PAGE);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben();
$oExcludedKeywordsAssoc_arr   = holeExcludedKeywords();
$smarty->assign(
    'meta_title', gibNaviMetaTitle(
        $NaviFilter,
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr
    )
);
$smarty->assign(
    'meta_description', gibNaviMetaDescription(
        $oSuchergebnisse->Artikel->elemente,
        $NaviFilter,
        $oSuchergebnisse,
        $oGlobaleMetaAngabenAssoc_arr
    )
);
$smarty->assign(
    'meta_keywords', gibNaviMetaKeywords(
        $oSuchergebnisse->Artikel->elemente,
        $NaviFilter,
        (isset($oExcludedKeywordsAssoc_arr[$_SESSION['cISOSprache']]->cKeywords) ? explode(' ', $oExcludedKeywordsAssoc_arr[$_SESSION['cISOSprache']]->cKeywords) : array())
    )
);
executeHook(HOOK_FILTER_ENDE);

$smarty->display('productlist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
