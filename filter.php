<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
$cachingOptions = Shop::getSettings(array(CONF_CACHING));
$cacheID        = $smarty->getCacheID('productlist/index.tpl', array('naviFilter' => $NaviFilter), null);
Shop::setPageType(PAGE_ARTIKELLISTE);
if ($smarty->isCached('productlist/index.tpl', $cacheID) === true) {
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('productlist/index.tpl', $cacheID);
} else {
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
            if (!isset($NaviFilter->Suche)) {
                $NaviFilter->Suche = new stdClass();
            }
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
    if ($cParameter_arr['kKategorie'] > 0) {
        $AktuelleKategorie = new Kategorie($cParameter_arr['kKategorie']);
        if (!isset($AktuelleKategorie->kKategorie) || $AktuelleKategorie->kKategorie === null) {
            //temp. workaround: do not return 404 when non-localized existing category is loaded
            if (KategorieHelper::categoryExists($cParameter_arr['kKategorie'])) {
                $AktuelleKategorie->kKategorie = $cParameter_arr['kKategorie'];
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
    // Erweiterte Darstellung Artikelübersicht
    gibErweiterteDarstellung($Einstellungen, $NaviFilter, $cParameter_arr['nDarstellung']);
    if (!isset($NaviFilter->Suche)) {
        $NaviFilter->Suche = new stdClass();
    }
    if (!isset($NaviFilter->Suche->cSuche)) {
        $NaviFilter->Suche->cSuche = '';
    }
    $oSuchergebnisse = buildSearchResults($FilterSQL, $NaviFilter);
    suchanfragenSpeichern($NaviFilter->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel);
    $NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::$kSprache);
    // Umleiten falls SEO keine Artikel ergibt
    doMainwordRedirect($NaviFilter, count($oSuchergebnisse->Artikel->elemente), $bSeo);
    // Bestsellers
    if (isset($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren']) && $Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
        $products = array();
        foreach ($oSuchergebnisse->Artikel->elemente as $product) {
            $products[] = $product->kArtikel;
        }
        $limit = 3;
        if (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])) {
            $limit = (int) $Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'];
        }
        $minsells = 10;
        if (isset($Einstellungen['boxen']['boxen_bestseller_minanzahl'])) {
            $minsells = $Einstellungen['boxen']['boxen_bestseller_minanzahl'];
        }
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
    $oSuchergebnisse->Bewertung         = gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter);
    $oSuchergebnisse->Tags              = gibTagFilterOptionen($FilterSQL, $NaviFilter);
    if (isset($Einstellungen['navigationsfilter']['allgemein_tagfilter_benutzen']) && $Einstellungen['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
        $oSuchergebnisse->TagsJSON = gibTagFilterJSONOptionen($FilterSQL, $NaviFilter);
    }
    $oSuchergebnisse->MerkmalFilter    = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $AktuelleKategorie, function_exists('starteAuswahlAssistent'));
    $oSuchergebnisse->Preisspanne      = gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse);
    $oSuchergebnisse->Kategorieauswahl = gibKategorieFilterOptionen($FilterSQL, $NaviFilter);
    $oSuchergebnisse->SuchFilter       = gibSuchFilterOptionen($FilterSQL, $NaviFilter);
    $oSuchergebnisse->SuchFilterJSON   = gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter);
    if (!$cParameter_arr['kSuchspecial']) {
        $oSuchergebnisse->Suchspecialauswahl = gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter);
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
            $n                                        = gibVerfuegbarkeitsformularAnzeigen($Artikel, $Einstellungen['artikeldetails']['benachrichtigung_nutzen']);
            $Artikel->verfuegbarkeitsBenachrichtigung = $n;
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

    $smarty->display('productlist/index.tpl', $cacheID);
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
