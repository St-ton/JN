<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_EIGENE);
$AktuelleSeite = 'SEITE';
$Einstellungen = Shop::getSettings(array(
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_SONSTIGES,
    CONF_NEWS,
    CONF_SITEMAP,
    CONF_ARTIKELUEBERSICHT,
    CONF_AUSWAHLASSISTENT,
    CONF_CACHING
));

//hole alle OberKategorien
$AufgeklappteKategorien = new KategorieListe();
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$startKat               = new Kategorie();
$linkHelper             = LinkHelper::getInstance();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat->kKategorie = 0;
//hole Link
if (Shop::$isInitialized === true) {
    $kLink = Shop::$kLink;
}
if (!isset($link)) {
    $link = $linkHelper->getPageLink($kLink);
}
//support for page cache
$cacheID = $smarty->getCacheID('layout/index.tpl', array('link' => $link), null);
if ($smarty->isCached('layout/index.tpl', $cacheID) === true) {
    executeHook(HOOK_SEITE_PAGE_IF_LINKART);
    require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('layout/index.tpl', $cacheID);
} else {
    if (isset($link->nLinkart) && $link->nLinkart == LINKTYP_EXTERNE_URL) {
        header('Location: ' . $link->cURL, true, 303);
        exit;
    }

    if (!isset($link->bHideContent) || !$link->bHideContent) {
        $link->Sprache = $linkHelper->getPageLinkLanguage($link->kLink);
    }
    //url
    $requestURL = baueURL($link, URLART_SEITE);
    $smarty->assign('cmsurl', $requestURL);
    // Canonical
    if (strpos($requestURL, '.php') === false || !SHOP_SEO) {
        $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
    }
    if ($link->nLinkart == LINKTYP_STARTSEITE) {
        // Work Around für die Startseite
        $cCanonicalURL = Shop::getURL() . '/';
    }
    $sprachURL = baueSprachURLS($link, URLART_SEITE);
    //hole aktuelle Kategorie, falls eine gesetzt
    $AufgeklappteKategorien = new KategorieListe();
    $startKat               = new Kategorie();
    $startKat->kKategorie   = 0;
    // Gehört der kLink zu einer Spezialseite? Wenn ja, leite um
    pruefeSpezialseite($link->nLinkart);
    if ($link->nLinkart == LINKTYP_STARTSEITE) {
        Shop::setPageType(PAGE_STARTSEITE);
        if ($link->nHTTPRedirectCode > 0) {
            header('Location: ' . $cCanonicalURL, true, $link->nHTTPRedirectCode);
            exit();
        }
        $AktuelleSeite = 'STARTSEITE';
        $Navigation    = createNavigation($AktuelleSeite);
        $smarty->assign('StartseiteBoxen', gibStartBoxen())
               ->assign('Navigation', $Navigation);
        // Auswahlassistent
        if (function_exists('starteAuswahlAssistent')) {
            starteAuswahlAssistent(AUSWAHLASSISTENT_ORT_STARTSEITE, 1, Shop::$kSprache, $smarty, $Einstellungen['auswahlassistent']);
        }
        if ($Einstellungen['news']['news_benutzen'] === 'Y') {
            $smarty->assign('oNews_arr', gibNews($Einstellungen));
        }
    } elseif ($link->nLinkart == LINKTYP_DATENSCHUTZ) {
        Shop::setPageType(PAGE_DATENSCHUTZ);
    } elseif ($link->nLinkart == LINKTYP_AGB) {
        Shop::setPageType(PAGE_AGB);
        $smarty->assign('AGB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe));
    } elseif ($link->nLinkart == LINKTYP_WRB) {
        Shop::setPageType(PAGE_WRB);
        $smarty->assign('WRB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe));
    } elseif ($link->nLinkart == LINKTYP_VERSAND) {
        Shop::setPageType(PAGE_VERSAND);
        if (isset($_POST['land']) && isset($_POST['plz'])) {
            if (!VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'])) {
                $smarty->assign('fehler', Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'));
            }
        }
        if (!isset($kKundengruppe)) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $smarty->assign('laender', gibBelieferbareLaender($kKundengruppe));
    } elseif ($link->nLinkart == LINKTYP_LIVESUCHE) {
        Shop::setPageType(PAGE_LIVESUCHE);
        $smarty->assign('LivesucheTop', gibLivesucheTop($Einstellungen))
               ->assign('LivesucheLast', gibLivesucheLast($Einstellungen));
    } elseif ($link->nLinkart == LINKTYP_TAGGING) {
        Shop::setPageType(PAGE_TAGGING);
        $smarty->assign('Tagging', gibTagging($Einstellungen));
    } elseif ($link->nLinkart == LINKTYP_HERSTELLER) {
        Shop::setPageType(PAGE_HERSTELLER);
        $smarty->assign('oHersteller_arr', Hersteller::getAll());
    } elseif ($link->nLinkart == LINKTYP_NEWSLETTERARCHIV) {
        Shop::setPageType(PAGE_NEWSLETTERARCHIV);
        $smarty->assign('oNewsletterHistory_arr', gibNewsletterHistory());
    } elseif ($link->nLinkart == LINKTYP_SITEMAP) {
        Shop::setPageType(PAGE_SITEMAP);
        gibSeiteSitemap($Einstellungen, $smarty);
    } elseif ($link->nLinkart == LINKTYP_GRATISGESCHENK) {
        Shop::setPageType(PAGE_GRATISGESCHENK);
        if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
            $oArtikelGeschenk_arr = gibGratisGeschenkArtikel($Einstellungen);
            if (is_array($oArtikelGeschenk_arr) && count($oArtikelGeschenk_arr) > 0) {
                $smarty->assign('oArtikelGeschenk_arr', $oArtikelGeschenk_arr);
            } else {
                $cFehler .= Shop::Lang()->get('freegiftsNogifts', 'errorMessages');
            }
        }
    } elseif ($link->nLinkart == LINKTYP_AUSWAHLASSISTENT) {
        Shop::setPageType(PAGE_AUSWAHLASSISTENT);
        // Auswahlassistent
        if (function_exists('starteAuswahlAssistent')) {
            starteAuswahlAssistent(AUSWAHLASSISTENT_ORT_LINK, $link->kLink, Shop::$kSprache, $smarty, $Einstellungen['auswahlassistent']);
        }
    } elseif ($link->nLinkart == LINKTYP_404) {
        Shop::setPageType(PAGE_404);
        gibSeiteSitemap($Einstellungen, $smarty);
    }

    executeHook(HOOK_SEITE_PAGE_IF_LINKART);
    require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

    if (isset($cFehler) && strlen($cFehler) > 0) {
        $smarty->assign('cFehler', $cFehler);
    }
    // MetaTitle bei bFileNotFound redirect
    if (!isset($bFileNotFound)) {
        $bFileNotFound = false;
    }
    if ($bFileNotFound) {
        $Navigation = createNavigation($AktuelleSeite, 0, 0, Shop::Lang()->get('pagenotfound', 'breadcrumb'), $requestURL);
    } else {
        $Navigation = createNavigation($AktuelleSeite, 0, 0, ((isset($link->Sprache->cName)) ? $link->Sprache->cName : ''), $requestURL, $kLink);
    }
    $smarty->assign('Navigation', $Navigation)
           ->assign('Einstellungen', $Einstellungen)
           ->assign('Link', $link)
           ->assign('requestURL', $requestURL)
           ->assign('sprachURL', $sprachURL)
           ->assign('bSeiteNichtGefunden', $bFileNotFound)
           ->assign('meta_language', StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));

    $cMetaTitle       = (isset($link->Sprache->cMetaTitle)) ? $link->Sprache->cMetaTitle : null;
    $cMetaDescription = (isset($link->Sprache->cMetaDescription)) ? $link->Sprache->cMetaDescription : null;
    $cMetaKeywords    = (isset($link->Sprache->cMetaKeywords)) ? $link->Sprache->cMetaKeywords : null;
    if (strlen($cMetaTitle) === 0 || strlen($cMetaDescription) === 0 || strlen($cMetaKeywords) === 0) {
        $kSprache            = Shop::$kSprache;
        $oGlobaleMetaAngaben = (isset($oGlobaleMetaAngabenAssoc_arr[$kSprache])) ? $oGlobaleMetaAngabenAssoc_arr[$kSprache] : null;

        if (is_object($oGlobaleMetaAngaben)) {
            if (strlen($cMetaTitle) === 0) {
                $cMetaTitle = $oGlobaleMetaAngaben->Title;
            }
            if (strlen($cMetaDescription) === 0) {
                $cMetaDescription = $oGlobaleMetaAngaben->Meta_Description;
            }
            if (strlen($cMetaKeywords) === 0) {
                $cMetaKeywords = $oGlobaleMetaAngaben->Meta_Keywords;
            }
        }
    }

    $smarty->assign('meta_title', $cMetaTitle)
           ->assign('meta_description', $cMetaDescription)
           ->assign('meta_keywords', $cMetaKeywords);
    executeHook(HOOK_SEITE_PAGE);
    $smarty->display('layout/index.tpl', $cacheID);
}
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
