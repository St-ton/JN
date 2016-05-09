<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';

$cart = (isset($_SESSION['Warenkorb'])) ?
    $_SESSION['Warenkorb'] :
    new Warenkorb();

if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
    //falls Artikel/Kategorien nicht gesehen werden dürfen -> login
    header('Location: ' . Shop::getURL() . '/jtl.php?li=1', true, 303);
    exit;
}
// Wurde ein Kindartikel zum Vaterumgeleitet? Falls ja => Redirect POST Daten entpacken und zuweisen
if (isset($_GET['cRP']) && strlen($_GET['cRP']) > 0) {
    $cRP_arr = explode('&', base64_decode($_GET['cRP']));
    if (is_array($cRP_arr) && count($cRP_arr) > 0) {
        foreach ($cRP_arr as $cRP) {
            list($cName, $cWert) = explode('=', $cRP);
            $_POST[$cName]       = $cWert;
        }
    }
}
Shop::run();
$cParameter_arr  = Shop::getParameters();
$NaviFilter      = Shop::buildNaviFilter($cParameter_arr);
$oSuchergebnisse = new stdClass();
$cFehler         = '';
//erstelle $smarty
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);

//support for artikel_after_cart_add
if (isset($_POST['a']) && isset($_POST['wke'])) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    if (function_exists('gibArtikelXSelling')) {
        $smarty->assign('Xselling', gibArtikelXSelling($_POST['a']));
    }
}

//earlier setzeUsersortierung() for page cache ID
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
// Usersortierung
setzeUsersortierung($NaviFilter);
$cachingOptions = Shop::getSettings(array(CONF_CACHING));
$cacheID        = $smarty->getCacheID('productlist/index.tpl', array('naviFilter' => $NaviFilter, 'oSuchergebnisse' => $oSuchergebnisse), null);
if ($smarty->isCached('productlist/index.tpl', $cacheID) === true) {
    //workaround for dynamic header cart
    Shop::setPageType(PAGE_ARTIKELLISTE);
    $numArticles = (isset($cart->kWarenkorb)) ? $cart->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) : 0;
    if ($numArticles > 0) {
        $warensumme      = array();
        $gesamtsumme     = array();
        $warensumme[0]   = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true));
        $warensumme[1]   = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), false));
        $gesamtsumme[0]  = gibPreisStringLocalized($cart->gibGesamtsummeWaren(true, true));
        $gesamtsumme[1]  = gibPreisStringLocalized($cart->gibGesamtsummeWaren(false, true));
        $kKundengruppe   = $_SESSION['Kundengruppe']->kKundengruppe;
        $cKundenherkunft = '';
        if (isset($_SESSION['Kunde']->cLand) && strlen($_SESSION['Kunde']->cLand) > 0) {
            $cKundenherkunft = $_SESSION['Kunde']->cLand;
        }
        $oVersandartKostenfrei = gibVersandkostenfreiAb($kKundengruppe, $cKundenherkunft);
        $smarty->assign('WarenkorbArtikelanzahl', $numArticles)
               ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt(array(C_WARENKORBPOS_TYP_ARTIKEL)))
               ->assign('WarenkorbWarensumme', $warensumme)
               ->assign('WarenkorbGesamtsumme', $gesamtsumme)
               ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
               ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
               ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
               ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString(
                       $oVersandartKostenfrei,
                       $cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true)
                   )
               )->assign('WarenkorbVersandkostenfreiLaenderHinweis', baueVersandkostenfreiLaenderString($oVersandartKostenfrei));
    }
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('productlist/index.tpl', $cacheID);
} else {
    $doSearch      = true;
    $Einstellungen = Shop::getSettings(
        array(
            CONF_GLOBAL,
            CONF_RSS,
            CONF_VERGLEICHSLISTE,
            CONF_ARTIKELUEBERSICHT,
            CONF_ARTIKELDETAILS,
            CONF_BEWERTUNG,
            CONF_NAVIGATIONSFILTER,
            CONF_BOXEN,
            CONF_METAANGABEN,
            CONF_SUCHSPECIAL,
            CONF_BILDER,
            CONF_SONSTIGES,
            CONF_AUSWAHLASSISTENT)
    );
    // Suche prüfen
    if (strlen($cParameter_arr['cSuche']) > 0) {
        $nMindestzeichen = 3;
        if ((int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'] > 0) {
            $nMindestzeichen = (int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'];
        }
        preg_match("/[\w" . utf8_decode('äÄüÜöÖß') . "\.\-]{" . $nMindestzeichen . ",}/", str_replace(' ', '', $cParameter_arr['cSuche']), $cTreffer_arr);
        if (count($cTreffer_arr) === 0) {
            $cFehler                 = Shop::Lang()->get('expressionHasTo', 'global') . ' ' . $nMindestzeichen . ' ' . Shop::Lang()->get('lettersDigits', 'global');
            $oSuchergebnisse->Fehler = $cFehler;
            $doSearch                = false;
        }
    }
    //wurde was in den Warenkorb gelegt?
    checkeWarenkorbEingang();
    // Prüfe Variationskombi
    if (ArtikelHelper::isVariChild($cParameter_arr['kArtikel'])) {
        Shop::$kVariKindArtikel             = $cParameter_arr['kArtikel'];
        Shop::$kArtikel                     = ArtikelHelper::getParent($cParameter_arr['kArtikel']);
        $cParameter_arr['kVariKindArtikel'] = Shop::$kVariKindArtikel;
        $cParameter_arr['kArtikel']         = Shop::$kArtikel;
    }
    if (!$cParameter_arr['kWunschliste'] && strlen(verifyGPDataString('wlid')) > 0) {
        header('Location: wunschliste.php?wlid=' . verifyGPDataString('wlid') . '&error=1', true, 303);
        exit();
    }
    $smarty->assign('NaviFilter', $NaviFilter);

    loeseHttps();
    Shop::setPageType(PAGE_ARTIKELLISTE);

    if ($cParameter_arr['kHersteller'] > 0 ||
        $cParameter_arr['kSuchanfrage'] > 0 ||
        $cParameter_arr['kMerkmalWert'] > 0 ||
        $cParameter_arr['kTag'] > 0 ||
        $cParameter_arr['kKategorie'] > 0 ||
        strlen($cParameter_arr['cPreisspannenFilter']) > 0 ||
        $cParameter_arr['nBewertungSterneFilter'] > 0 ||
        $cParameter_arr['kHerstellerFilter'] > 0 ||
        $cParameter_arr['kKategorieFilter'] > 0 ||
        strlen($cParameter_arr['cSuche']) > 0 ||
        $cParameter_arr['kSuchspecial'] > 0 ||
        $cParameter_arr['kSuchspecialFilter'] > 0 ||
        $cParameter_arr['kSuchFilter'] > 0
    ) {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
        $suchanfrage = '';
        // setze Kat in Session
        if ($cParameter_arr['kKategorie'] > 0) {
            $_SESSION['LetzteKategorie'] = $cParameter_arr['kKategorie'];
        }
        //hole aktuelle Kategorie + bild, falls eine gesetzt
        $AktuelleKategorie = new Kategorie($cParameter_arr['kKategorie']);
        //Artikelanzahl pro Seite
        if ($cParameter_arr['nArtikelProSeite'] == 0) {
            $cParameter_arr['nArtikelProSeite'] = 20;
            if ($Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0) {
                $cParameter_arr['nArtikelProSeite'] = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'];
            }
            if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
                $cParameter_arr['nArtikelProSeite'] = (int)$_SESSION['ArtikelProSeite'];
            }
        }
        $oSuchergebnisse->Artikel           = new ArtikelListe();
        $oArtikel_arr                       = array();
        $oSuchergebnisse->MerkmalFilter     = array();
        $oSuchergebnisse->Herstellerauswahl = array();
        $oSuchergebnisse->Tags              = array();
        $oSuchergebnisse->Bewertung         = array();
        $oSuchergebnisse->Preisspanne       = array();
        $oSuchergebnisse->Suchspecial       = array();
        $oSuchergebnisse->SuchFilter        = array();
        // JTL Search
        $oExtendedJTLSearchResponse = null;
        $bExtendedJTLSearch         = false;

        executeHook(HOOK_NAVI_PRESUCHE, array('cValue' => &$NaviFilter->EchteSuche->cSuche, 'bExtendedJTLSearch' => &$bExtendedJTLSearch));
        // Keine Suche sondern vielleicht nur ein Filter?
        if (strlen($cParameter_arr['cSuche']) === 0) {
            $bExtendedJTLSearch = false;
        }
        // SuchFilter
        if (is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) {
            $sfCount = count($NaviFilter->SuchFilter);
            for ($i = 0; $i < $sfCount; $i++) {
                $oSuchanfrage = Shop::DB()->query("SELECT cSuche FROM tsuchanfrage WHERE kSuchanfrage = " . (int)$NaviFilter->SuchFilter[$i]->kSuchanfrage, 1);
                if (strlen($oSuchanfrage->cSuche) > 0) {
                    // Nicht vorhandene Suchcaches werden hierdurch neu generiert
                    if (!isset($NaviFilter->Suche)) {
                        $NaviFilter->Suche = new stdClass();
                    }
                    $NaviFilter->Suche->cSuche              = $oSuchanfrage->cSuche;
                    $NaviFilter->SuchFilter[$i]->kSuchCache = bearbeiteSuchCache($NaviFilter);
                    //$oSuchanfrage->cSuche = $NaviFilter->Suche->cSuche;
                    unset($NaviFilter->Suche->cSuche);

                    $NaviFilter->SuchFilter[$i]->cSuche = $oSuchanfrage->cSuche;
                }
            }
        }
        if ($cParameter_arr['kSuchanfrage'] > 0) {
            $oSuchanfrage = Shop::DB()->query("SELECT cSuche FROM tsuchanfrage WHERE kSuchanfrage = " . (int)$cParameter_arr['kSuchanfrage'], 1);

            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                if (!isset($NaviFilter->Suche)) {
                    $NaviFilter->Suche = new stdClass();
                }
                $NaviFilter->Suche->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
                $NaviFilter->Suche->cSuche       = $oSuchanfrage->cSuche;
            }
        }
        //Suche da? Dann bearbeiten
        if (!$bExtendedJTLSearch && isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
            //XSS abfangen
            $NaviFilter->Suche->cSuche     = StringHandler::filterXSS($NaviFilter->Suche->cSuche, 1);
            $NaviFilter->Suche->kSuchCache = bearbeiteSuchCache($NaviFilter);
        }
        // Usersortierung
        setzeUsersortierung($NaviFilter);
        // Filter SQL
        $FilterSQL = bauFilterSQL($NaviFilter);
        // Hook
        executeHook(HOOK_NAVI_CREATE, array(
            'naviFilter' => &$NaviFilter,
            'filterSQL'  => &$FilterSQL
        ));
        // Erweiterte Darstellung Artikelübersicht
        gibErweiterteDarstellung($Einstellungen, $NaviFilter, $cParameter_arr['nDarstellung']);

        if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
            $cParameter_arr['nArtikelProSeite'] = $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        }
        if (verifyGPCDataInteger('af') > 0) {
            $_SESSION['ArtikelProSeite'] = verifyGPCDataInteger('af');
            setFsession(0, 0, $_SESSION['ArtikelProSeite']);
            $cParameter_arr['nArtikelProSeite'] = $_SESSION['ArtikelProSeite'];
            if (isset($_SESSION['oErweiterteDarstellung'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        if (!isset($_SESSION['ArtikelProSeite']) && $Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N') {
            $_SESSION['ArtikelProSeite'] = min((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'], ARTICLES_PER_PAGE_HARD_LIMIT);
        }
        // $nArtikelProSeite auf max. ARTICLES_PER_PAGE_HARD_LIMIT beschränken
        $cParameter_arr['nArtikelProSeite'] = min((int)$cParameter_arr['nArtikelProSeite'], ARTICLES_PER_PAGE_HARD_LIMIT);

        executeHook(
            HOOK_NAVI_SUCHE,
            array(
                'bExtendedJTLSearch'         => $bExtendedJTLSearch,
                'oExtendedJTLSearchResponse' => &$oExtendedJTLSearchResponse,
                'cValue'                     => &$NaviFilter->EchteSuche->cSuche,
                'nArtikelProSeite'           => &$cParameter_arr['nArtikelProSeite'],
                'nSeite'                     => &$NaviFilter->nSeite,
                'nSortierung'                => (isset($_SESSION['Usersortierung'])) ? $_SESSION['Usersortierung'] : null,
                'bLagerbeachten'             => $Einstellungen['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL ? true : false
            )
        );
        //Ab diesen Artikel rausholen
        $nLimitN = ($NaviFilter->nSeite - 1) * $cParameter_arr['nArtikelProSeite'];
        if ($doSearch === false) {
            $oSuchergebnisse->Artikel->elemente           = array();
            $oSuchergebnisse->GesamtanzahlArtikel         = 0;
            $oSuchergebnisse->SucheErfolglos              = 1;
            $oSuchergebnisse->Seitenzahlen                = new stdClass();
            $oSuchergebnisse->Seitenzahlen->AktuelleSeite = 0;
            $oSuchergebnisse->Seitenzahlen->MaxSeiten     = 0;
            $oSuchergebnisse->Seitenzahlen->minSeite      = 0;
            $oSuchergebnisse->Seitenzahlen->maxSeite      = 0;
        } elseif (!$bExtendedJTLSearch) {
            baueArtikelAnzahl($FilterSQL, $oSuchergebnisse, $cParameter_arr['nArtikelProSeite'], $nLimitN);
        }

        $bEchteSuche = false;
        if (!$bExtendedJTLSearch && strlen($cParameter_arr['cSuche']) > 0) {
            $bEchteSuche = true;
        }
        if (!$bExtendedJTLSearch) {
            if (isset($NaviFilter->Suche->cSuche)) {
                suchanfragenSpeichern($NaviFilter->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel, $bEchteSuche);
                $NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::$kSprache);
            } else {
                if (!isset($NaviFilter->Suche)) {
                    $NaviFilter->Suche = new stdClass();
                }
                $NaviFilter->Suche->kSuchanfrage = 0;
            }
        }
        if ($doSearch === true) {
            // JTL Search
            if ($bExtendedJTLSearch) {
                $oSuchergebnisse->Artikel->elemente = gibArtikelKeysExtendedJTLSearch($oExtendedJTLSearchResponse);
                buildSearchResultPage(
                    $oSuchergebnisse,
                    $oExtendedJTLSearchResponse->oSearch->nItemFound,
                    $nLimitN,
                    $NaviFilter->nSeite,
                    $cParameter_arr['nArtikelProSeite'],
                    $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
                );
            } else {
                $oSuchergebnisse->Artikel->elemente = gibArtikelKeys($FilterSQL, $cParameter_arr['nArtikelProSeite'], $NaviFilter, false, $oSuchergebnisse);
            }
        }
        // Umleiten falls SEO keine Artikel ergibt
        doMainwordRedirect($NaviFilter, count($oSuchergebnisse->Artikel->elemente), true);
        // Bestsellers
        if (isset($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren']) &&
            $Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y'
        ) {
            $products = array();
            foreach ($oSuchergebnisse->Artikel->elemente as $product) {
                $products[] = $product->kArtikel;
            }
            $limit = (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])) ?
                (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'] :
                3;
            $minsells = (isset($Einstellungen['boxen']['boxen_bestseller_minanzahl'])) ?
                (int)$Einstellungen['boxen']['boxen_bestseller_minanzahl'] :
                10;
            $bestsellers = Bestseller::buildBestsellers($products, $_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['Kundengruppe']->darfArtikelKategorienSehen, false, $limit, $minsells);
            Bestseller::ignoreProducts($oSuchergebnisse->Artikel->elemente, $bestsellers);
            $smarty->assign('oBestseller_arr', $bestsellers);
        }

        $cFilterShopURL = '';
        if ($bExtendedJTLSearch) {
            $cFilter_arr    = JtlSearch::getFilter($_GET);
            $cFilterShopURL = JtlSearch::buildFilterShopURL($cFilter_arr);
        }

        $smarty->assign(
            'oNaviSeite_arr',
            baueSeitenNaviURL(
                $NaviFilter,
                true,
                $oSuchergebnisse->Seitenzahlen,
                $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl'],
                $cFilterShopURL
            )
        );
        // Schauen ob die maximale Anzahl der Artikel >= der max. Anzahl die im Backend eingestellt wurde
        if ((int)$Einstellungen['artikeluebersicht']['suche_max_treffer'] > 0) {
            if ($oSuchergebnisse->GesamtanzahlArtikel >= (int)$Einstellungen['artikeluebersicht']['suche_max_treffer']) {
                $smarty->assign('nMaxAnzahlArtikel', 1);
            }
        }
        //Filteroptionen rausholen
        if (!$bExtendedJTLSearch) {
            $oSuchergebnisse->Herstellerauswahl = gibHerstellerFilterOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->Bewertung         = gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->Tags              = gibTagFilterOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->TagsJSON          = gibTagFilterJSONOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->MerkmalFilter     = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $AktuelleKategorie, function_exists('starteAuswahlAssistent'));
            $oSuchergebnisse->Preisspanne       = gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse);
            $oSuchergebnisse->Kategorieauswahl  = gibKategorieFilterOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->SuchFilter        = gibSuchFilterOptionen($FilterSQL, $NaviFilter);
            $oSuchergebnisse->SuchFilterJSON    = gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter);
        }
        if (!$cParameter_arr['kSuchspecial'] && !$cParameter_arr['kSuchspecialFilter']) {
            $oSuchergebnisse->Suchspecialauswahl = gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter);
        }
        //hole aktuelle Kategorie, falls eine gesetzt
        if ($cParameter_arr['kKategorie'] > 0) {
            $AktuelleKategorie = new Kategorie($cParameter_arr['kKategorie']);
        } elseif (verifyGPCDataInteger('kategorie') > 0) {
            $AktuelleKategorie = new Kategorie(verifyGPCDataInteger('kategorie'));
        }
        $AufgeklappteKategorien = new stdClass();
        if ($AktuelleKategorie->kKategorie > 0) {
            $AufgeklappteKategorien = new KategorieListe();
            $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
            $startKat             = new Kategorie();
            $startKat->kKategorie = 0;
        }
        // Verfügbarkeitsbenachrichtigung allgemeiner CaptchaCode
        $smarty->assign(
            'code_benachrichtigung_verfuegbarkeit',
            (isset($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha'])) ?
                generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']) :
                null
        );
        // Verfügbarkeitsbenachrichtigung pro Artikel
        if (is_array($oSuchergebnisse->Artikel->elemente)) {
            foreach ($oSuchergebnisse->Artikel->elemente as $Artikel) {
                if (!isset($Einstellungen['artikeldetails']['benachrichtigung_nutzen'])) {
                    $Einstellungen['artikeldetails']['benachrichtigung_nutzen'] = null;
                }
                $n                                        = gibVerfuegbarkeitsformularAnzeigen($Artikel, $Einstellungen['artikeldetails']['benachrichtigung_nutzen']);
                $Artikel->verfuegbarkeitsBenachrichtigung = $n;
            }
        }
        if (count($oSuchergebnisse->Artikel->elemente) === 0) {
            if (!isset($NaviFilter->Kategorie->kKategorie)) {
                $oSuchergebnisse->SucheErfolglos = 1;
            }
        }
        if (count($oSuchergebnisse->Artikel->elemente) === 0) {
            if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
                //hole alle enthaltenen Kategorien
                if (!isset($KategorieInhalt)) {
                    $KategorieInhalt = new stdClass();
                }
                $KategorieInhalt->Unterkategorien = new KategorieListe();
                $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->Kategorie->kKategorie);

                //wenn keine eigenen Artikel in dieser Kat, Top Angebote / Bestseller aus unterkats + unterunterkats rausholen und anzeigen?
                if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Top' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
                    $KategorieInhalt->TopArtikel = new ArtikelListe();
                    $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
                }
                if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Bestseller' || $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest') {
                    if (isset($KategorieInhalt->TopArtikel)) {
                        $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
                        $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel($KategorieInhalt->Unterkategorien, $KategorieInhalt->TopArtikel);
                    }
                }
                $smarty->assign('KategorieInhalt', $KategorieInhalt);
            } else {
                // Suchfeld anzeigen
                $oSuchergebnisse->SucheErfolglos = 1;
            }
        }
        //URLs bauen, die Filter lösen
        erstelleFilterLoesenURLs(true, $oSuchergebnisse);
        // Header bauen
        $NaviFilter->cBrotNaviName          = gibBrotNaviName();
        $oSuchergebnisse->SuchausdruckWrite = gibHeaderAnzeige();
        $oSuchergebnisse->cSuche            = strip_tags(trim($cParameter_arr['cSuche']));
        // Mainword NaviBilder
        $oNavigationsinfo           = new stdClass();
        $oNavigationsinfo->cName    = '';
        $oNavigationsinfo->cBildURL = '';

        $AufgeklappteKategorien = new stdClass();
        if ($cParameter_arr['kKategorie'] > 0) {
            $AktuelleKategorie      = new Kategorie($cParameter_arr['kKategorie']);
            $AufgeklappteKategorien = new KategorieListe();
            $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
        }
        $startKat             = new Kategorie();
        $startKat->kKategorie = 0;
        // Navigation
        $cBrotNavi = '';
        if (!isset($oMeta)) {
            $oMeta = new stdClass();
        }
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

            $oMeta->cMetaTitle       = $oNavigationsinfo->oHersteller->cMetaTitle;
            $oMeta->cMetaDescription = $oNavigationsinfo->oHersteller->cMetaDescription;
            $oMeta->cMetaKeywords    = $oNavigationsinfo->oHersteller->cMetaKeywords;

            $cBrotNavi = createNavigation('', '', 0, $NaviFilter->cBrotNaviName, gibNaviURL($NaviFilter, true, null));
        } elseif (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
            $oNavigationsinfo->oMerkmalWert = new MerkmalWert($NaviFilter->MerkmalWert->kMerkmalWert);

            if ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'Y') {
                $oNavigationsinfo->cName = $oNavigationsinfo->oMerkmalWert->cName;
            } elseif ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'BT') {
                $oNavigationsinfo->cName    = (isset($oNavigationsinfo->oMerkmalWert->cName)) ? $oNavigationsinfo->oMerkmalWert->cName : null;
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
        // Work around fürs Template
        $smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
               ->assign('PFAD_ART_ABNAHMEINTERVALL', PFAD_ART_ABNAHMEINTERVALL)
               ->assign('Navigation', $cBrotNavi)
               ->assign('cFehler', $cFehler)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('Sortierliste', gibSortierliste($Einstellungen, $bExtendedJTLSearch))
               ->assign('Einstellungen', $Einstellungen)
               ->assign('Suchergebnisse', $oSuchergebnisse)
               ->assign('requestURL', (isset($requestURL)) ? $requestURL : null)
               ->assign('sprachURL', (isset($sprachURL)) ? $sprachURL : null)
               ->assign('oNavigationsinfo', $oNavigationsinfo)
               ->assign('SEO', false)
               ->assign('SESSION_NOTWENDIG', false);

        require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
        executeHook(HOOK_NAVI_PAGE);

        $oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben(); // Globale Metaangaben
        $oExcludedKeywordsAssoc_arr   = holeExcludedKeywords(); // Excluded Meta Keywords
        $smarty->assign(
            'meta_title', gibNaviMetaTitle(
                $NaviFilter,
                $oSuchergebnisse,
                $oGlobaleMetaAngabenAssoc_arr
            )
        )->assign(
            'meta_description', gibNaviMetaDescription(
                $oSuchergebnisse->Artikel->elemente,
                $NaviFilter,
                $oSuchergebnisse,
                $oGlobaleMetaAngabenAssoc_arr
            )
        )->assign(
            'meta_keywords', gibNaviMetaKeywords(
                $oSuchergebnisse->Artikel->elemente,
                $NaviFilter,
                explode(' ', $oExcludedKeywordsAssoc_arr[$_SESSION['cISOSprache']]->cKeywords)
            )
        );

        executeHook(HOOK_NAVI_ENDE);

        $smarty->display('productlist/index.tpl', $cacheID);
    } else {
        //Artikel
        if ($cParameter_arr['kArtikel'] > 0) {
            require_once PFAD_ROOT . 'artikel.php';
        } elseif ($cParameter_arr['kWunschliste'] > 0) {
            require_once PFAD_ROOT . 'wunschliste.php';
        } elseif ($cParameter_arr['vergleichsliste'] > 0) {
            require_once PFAD_ROOT . 'vergleichsliste.php';
        } elseif ($cParameter_arr['kNews'] > 0 || $cParameter_arr['kNewsMonatsUebersicht'] > 0 || $cParameter_arr['kNewsKategorie'] > 0) {
            require_once PFAD_ROOT . 'news.php';
        } elseif ($cParameter_arr['kUmfrage'] > 0) {
            require_once PFAD_ROOT . 'umfrage.php';
        } else {
            if (!$cParameter_arr['kSeite']) {
                $Link   = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_STARTSEITE, 1);
                $kSeite = $Link->kLink;
            }

            require_once PFAD_ROOT . 'seite.php';
        }
        if (Shop::$is404 === true) {
            $cParameter_arr['is404'] = true;
        }
        if ($cParameter_arr['is404'] === true) {
            if (isset($seo) && strlen($seo) > 0) {
                executeHook(HOOK_INDEX_SEO_404, array('seo' => $seo));
            }
            if (!Shop::$kLink) {
                $hookInfos     = urlNotFoundRedirect(array('key' => 'kLink', 'value' => $cParameter_arr['kLink']));
                $kLink         = $hookInfos['value'];
                $bFileNotFound = $hookInfos['isFileNotFound'];
                if (!$kLink) {
                    $oLink       = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_404, 1);
                    $kLink       = $oLink->kLink;
                    Shop::$kLink = $kLink;
                }
            }
            require_once PFAD_ROOT . 'seite.php';
        }
    }
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
