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
$smarty                 = Shop::Smarty();
$AktuelleSeite          = 'SEITE';
$Einstellungen          = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_SONSTIGES,
    CONF_NEWS,
    CONF_SITEMAP,
    CONF_ARTIKELUEBERSICHT,
    CONF_AUSWAHLASSISTENT,
    CONF_CACHING,
    CONF_METAANGABEN
]);
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$linkHelper             = LinkHelper::getInstance();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
// hole Link
if (Shop::$isInitialized === true) {
    $kLink = Shop::$kLink;
}
$link = $linkHelper->getPageLink(Shop::$kLink);
if (!isset($link->bHideContent) || !$link->bHideContent) {
    $link->Sprache = $linkHelper->getPageLinkLanguage($link->kLink);
}
$requestURL = baueURL($link, URLART_SEITE);
if ($link->nLinkart === LINKTYP_STARTSEITE) {
    // Work Around für die Startseite
    $cCanonicalURL = Shop::getURL() . '/';
} elseif (strpos($requestURL, '.php') === false) {
    $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
}
$sprachURL = isset($link->languageURLs) ? $link->languageURLs : baueSprachURLS($link, URLART_SEITE);
//hole aktuelle Kategorie, falls eine gesetzt
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
// Gehört der kLink zu einer Spezialseite? Wenn ja, leite um
pruefeSpezialseite($link->nLinkart);
if ($link->nLinkart === LINKTYP_STARTSEITE) {
    if ($link->nHTTPRedirectCode > 0) {
        header('Location: ' . $cCanonicalURL, true, $link->nHTTPRedirectCode);
        exit();
    }
    $AktuelleSeite = 'STARTSEITE';
    $Navigation    = createNavigation($AktuelleSeite);
    $smarty->assign('StartseiteBoxen', gibStartBoxen())
           ->assign('Navigation', $Navigation)
           ->assign('oNews_arr', ($Einstellungen['news']['news_benutzen'] === 'Y') ? gibNews($Einstellungen) : []);
    AuswahlAssistent::startIfRequired(AUSWAHLASSISTENT_ORT_STARTSEITE, 1, Shop::getLanguage(), $smarty);
    if ($Einstellungen['news']['news_benutzen'] === 'Y') {
        $smarty->assign('oNews_arr', gibNews($Einstellungen));
    }
} elseif ($link->nLinkart === LINKTYP_AGB) {
    $smarty->assign('AGB', gibAGBWRB(Shop::getLanguage(), Session::CustomerGroup()->getID()));
} elseif ($link->nLinkart === LINKTYP_WRB) {
    $smarty->assign('WRB', gibAGBWRB(Shop::getLanguage(), Session::CustomerGroup()->getID()));
} elseif ($link->nLinkart === LINKTYP_VERSAND) {
    if (isset($_POST['land'], $_POST['plz']) && !VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'])) {
        $smarty->assign('fehler', Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'));
    }
    if (!isset($kKundengruppe)) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $smarty->assign('laender', gibBelieferbareLaender($kKundengruppe));
} elseif ($link->nLinkart === LINKTYP_LIVESUCHE) {
    $smarty->assign('LivesucheTop', gibLivesucheTop($Einstellungen))
           ->assign('LivesucheLast', gibLivesucheLast($Einstellungen));
} elseif ($link->nLinkart === LINKTYP_TAGGING) {
    $smarty->assign('Tagging', gibTagging($Einstellungen));
} elseif ($link->nLinkart === LINKTYP_HERSTELLER) {
    $smarty->assign('oHersteller_arr', Hersteller::getAll());
} elseif ($link->nLinkart === LINKTYP_NEWSLETTERARCHIV) {
    $smarty->assign('oNewsletterHistory_arr', gibNewsletterHistory());
} elseif ($link->nLinkart === LINKTYP_SITEMAP) {
    gibSeiteSitemap($Einstellungen, $smarty);
} elseif ($link->nLinkart === LINKTYP_404) {
    gibSeiteSitemap($Einstellungen, $smarty);
    Shop::setPageType(PAGE_404);
} elseif ($link->nLinkart === LINKTYP_GRATISGESCHENK) {
    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $oArtikelGeschenk_arr = gibGratisGeschenkArtikel($Einstellungen);
        if (is_array($oArtikelGeschenk_arr) && count($oArtikelGeschenk_arr) > 0) {
            $smarty->assign('oArtikelGeschenk_arr', $oArtikelGeschenk_arr);
        } else {
            $cFehler .= Shop::Lang()->get('freegiftsNogifts', 'errorMessages');
        }
    }
} elseif ($link->nLinkart === LINKTYP_AUSWAHLASSISTENT) {
    AuswahlAssistent::startIfRequired(AUSWAHLASSISTENT_ORT_LINK, $link->kLink, Shop::getLanguage(), $smarty);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_SEITE_PAGE_IF_LINKART);
// MetaTitle bei bFileNotFound redirect
if (Shop::getPageType() === PAGE_404) {
    $Navigation = createNavigation(
        $AktuelleSeite,
        0,
        0,
        Shop::Lang()->get('pagenotfound', 'breadcrumb'),
        $requestURL
    );
} else {
    $Navigation = createNavigation(
        $AktuelleSeite,
        0,
        0,
        (isset($link->Sprache->cName) ? $link->Sprache->cName : ''),
        $requestURL,
        Shop::$kLink
    );
}
$smarty->assign('Navigation', $Navigation)
       ->assign('Link', $link)
       ->assign('requestURL', $requestURL)
       ->assign('sprachURL', $sprachURL)
       ->assign('bSeiteNichtGefunden', Shop::getPageType() === PAGE_404)
       ->assign('cFehler', !empty($cFehler) ? $cFehler : null)
       ->assign('meta_language', StringHandler::convertISO2ISO639(Shop::getLanguageCode()));

$cMetaTitle       = isset($link->Sprache->cMetaTitle) ? $link->Sprache->cMetaTitle : null;
$cMetaDescription = isset($link->Sprache->cMetaDescription) ? $link->Sprache->cMetaDescription : null;
$cMetaKeywords    = isset($link->Sprache->cMetaKeywords) ? $link->Sprache->cMetaKeywords : null;
if (empty($cMetaTitle) || empty($cMetaDescription) || empty($cMetaKeywords)) {
    $kSprache            = Shop::getLanguage();
    $oGlobaleMetaAngaben = isset($oGlobaleMetaAngabenAssoc_arr[$kSprache])
        ? $oGlobaleMetaAngabenAssoc_arr[$kSprache]
        : null;

    if (is_object($oGlobaleMetaAngaben)) {
        if (empty($cMetaTitle)) {
            $cMetaTitle = $oGlobaleMetaAngaben->Title;
        }
        if (empty($cMetaDescription)) {
            $cMetaDescription = $oGlobaleMetaAngaben->Meta_Description;
        }
        if (empty($cMetaKeywords)) {
            $cMetaKeywords = $oGlobaleMetaAngaben->Meta_Keywords;
        }
    }
}

$cMetaTitle = prepareMeta($cMetaTitle, null, (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_title']);

$smarty->assign('meta_title', $cMetaTitle)
       ->assign('meta_description', $cMetaDescription)
       ->assign('meta_keywords', $cMetaKeywords);

executeHook(HOOK_SEITE_PAGE);

$smarty->display('layout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
