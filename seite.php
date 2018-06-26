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
$linkHelper             = Shop::Container()->getLinkService();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
if (Shop::$isInitialized === true) {
    $kLink = Shop::$kLink;
}
$link = $linkHelper->getLinkByID(Shop::$kLink);
if ($link === null || !$link->isVisible()) {
    $link = $linkHelper->getSpecialPage(LINKTYP_STARTSEITE);
    $link->setRedirectCode(301);
}
$requestURL = UrlHelper::buildURL($link, URLART_SEITE);
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    $cCanonicalURL = Shop::getURL() . '/';
} elseif (strpos($requestURL, '.php') === false) {
    $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
}
$AufgeklappteKategorien = new KategorieListe();
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    if ($link->getRedirectCode() > 0) {
        header('Location: ' . $cCanonicalURL, true, $link->getRedirectCode());
        exit();
    }
    $AktuelleSeite = 'STARTSEITE';
    $smarty->assign('StartseiteBoxen', CMSHelper::getHomeBoxes())
           ->assign('oNews_arr', ($Einstellungen['news']['news_benutzen'] === 'Y') ? CMSHelper::getHomeNews($Einstellungen) : []);
    AuswahlAssistent::startIfRequired(AUSWAHLASSISTENT_ORT_STARTSEITE, 1, Shop::getLanguage(), $smarty);
} elseif ($link->getLinkType() === LINKTYP_AGB) {
    $smarty->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        Session::CustomerGroup()->getID()
    ));
} elseif ($link->getLinkType() === LINKTYP_WRB) {
    $smarty->assign('WRB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        Session::CustomerGroup()->getID()
    ));
} elseif ($link->getLinkType() === LINKTYP_VERSAND) {
    if (isset($_POST['land'], $_POST['plz']) && !VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'])) {
        $smarty->assign('fehler', Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'));
    }
    $smarty->assign('laender', VersandartHelper::getPossibleShippingCountries(Session\Session::CustomerGroup()->getID()));
} elseif ($link->getLinkType() === LINKTYP_LIVESUCHE) {
    $smarty->assign('LivesucheTop', CMSHelper::getLiveSearchTop($Einstellungen))
           ->assign('LivesucheLast', CMSHelper::getLiveSearchLast($Einstellungen));
} elseif ($link->getLinkType() === LINKTYP_TAGGING) {
    $smarty->assign('Tagging', CMSHelper::getTagging($Einstellungen));
} elseif ($link->getLinkType() === LINKTYP_HERSTELLER) {
    $smarty->assign('oHersteller_arr', Hersteller::getAll());
} elseif ($link->getLinkType() === LINKTYP_NEWSLETTERARCHIV) {
    $smarty->assign('oNewsletterHistory_arr', CMSHelper::getNewsletterHistory());
} elseif ($link->getLinkType() === LINKTYP_SITEMAP) {
    Shop::setPageType(PAGE_SITEMAP);
    $sitemap = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $Einstellungen);
    $sitemap->assignData($smarty);
} elseif ($link->getLinkType() === LINKTYP_404) {
    $sitemap = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $Einstellungen);
    $sitemap->assignData($smarty);
    Shop::setPageType(PAGE_404);
} elseif ($link->getLinkType() === LINKTYP_GRATISGESCHENK) {
    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $oArtikelGeschenk_arr = CMSHelper::getFreeGifts($Einstellungen);
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
$smarty->assign('Link', $link)
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
