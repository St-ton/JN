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
$Einstellungen          = Shopsetting::getInstance()->getAll();
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$linkHelper             = Shop::Container()->getLinkService();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
// hole Link
if (Shop::$isInitialized === true) {
    $kLink = Shop::$kLink;
}
$link = $linkHelper->getLinkByID(Shop::$kLink);
if ($link === null || !$link->isVisible()) {
    $link = $linkHelper->getSpecialPage(LINKTYP_STARTSEITE);
    $link->setRedirectCode(301);
}
$requestURL = baueURL($link, URLART_SEITE);
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    // Work Around für die Startseite
    $cCanonicalURL = Shop::getURL() . '/';
} elseif (strpos($requestURL, '.php') === false) {
    $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
}
// hole aktuelle Kategorie, falls eine gesetzt
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    if ($link->getRedirectCode() > 0) {
        header('Location: ' . $cCanonicalURL, true, $link->getRedirectCode());
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
} elseif ($link->getLinkType() === LINKTYP_AGB) {
    $smarty->assign('AGB', gibAGBWRB(Shop::getLanguage(), Session::CustomerGroup()->getID()));
} elseif ($link->getLinkType() === LINKTYP_WRB) {
    $smarty->assign('WRB', gibAGBWRB(Shop::getLanguage(), Session::CustomerGroup()->getID()));
} elseif ($link->getLinkType() === LINKTYP_VERSAND) {
    if (isset($_POST['land'], $_POST['plz']) && !VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'])) {
        $smarty->assign('fehler', Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'));
    }
    if (!isset($kKundengruppe)) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $smarty->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe));
} elseif ($link->getLinkType() === LINKTYP_LIVESUCHE) {
    $smarty->assign('LivesucheTop', gibLivesucheTop($Einstellungen))
           ->assign('LivesucheLast', gibLivesucheLast($Einstellungen));
} elseif ($link->getLinkType() === LINKTYP_TAGGING) {
    $smarty->assign('Tagging', gibTagging($Einstellungen));
} elseif ($link->getLinkType() === LINKTYP_HERSTELLER) {
    $smarty->assign('oHersteller_arr', Hersteller::getAll());
} elseif ($link->getLinkType() === LINKTYP_NEWSLETTERARCHIV) {
    $smarty->assign('oNewsletterHistory_arr', gibNewsletterHistory());
} elseif ($link->getLinkType() === LINKTYP_SITEMAP) {
    gibSeiteSitemap($Einstellungen, $smarty);
} elseif ($link->getLinkType() === LINKTYP_404) {
    gibSeiteSitemap($Einstellungen, $smarty);
    Shop::setPageType(PAGE_404);
} elseif ($link->getLinkType() === LINKTYP_GRATISGESCHENK) {
    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $oArtikelGeschenk_arr = gibGratisGeschenkArtikel($Einstellungen);
        if (is_array($oArtikelGeschenk_arr) && count($oArtikelGeschenk_arr) > 0) {
            $smarty->assign('oArtikelGeschenk_arr', $oArtikelGeschenk_arr);
        } else {
            $cFehler .= Shop::Lang()->get('freegiftsNogifts', 'errorMessages');
        }
    }
} elseif ($link->getLinkType() === LINKTYP_AUSWAHLASSISTENT) {
    AuswahlAssistent::startIfRequired(AUSWAHLASSISTENT_ORT_LINK, $link->getID(), Shop::getLanguage(), $smarty);
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
        $link->getName() ?? '',
        $requestURL,
        Shop::$kLink
    );
}
$smarty->assign('Navigation', $Navigation)
       ->assign('Link', $link)
       ->assign('bSeiteNichtGefunden', Shop::getPageType() === PAGE_404)
       ->assign('cFehler', !empty($cFehler) ? $cFehler : null)
       ->assign('meta_language', StringHandler::convertISO2ISO639(Shop::getLanguageCode()));

$cMetaTitle       = $link->getMetaTitle();
$cMetaDescription = $link->getMetaDescription() ?? null;
$cMetaKeywords    = $link->getMetaKeyword() ?? null;
if (empty($cMetaTitle) || empty($cMetaDescription) || empty($cMetaKeywords)) {
    $kSprache            = Shop::getLanguage();
    $oGlobaleMetaAngaben = $oGlobaleMetaAngabenAssoc_arr[$kSprache] ?? null;

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
$cMetaTitle       = \Filter\Metadata::prepareMeta($cMetaTitle, null, (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_title']);
$cMetaDescription = \Filter\Metadata::prepareMeta($cMetaDescription, null, (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_description']);

$smarty->assign('meta_title', $cMetaTitle)
       ->assign('meta_description', $cMetaDescription)
       ->assign('meta_keywords', $cMetaKeywords);

executeHook(HOOK_SEITE_PAGE);

$smarty->display('layout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
