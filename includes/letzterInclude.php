<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

$smarty        = Shop::Smarty();
$oBrowser      = getBrowser();
$linkHelper    = LinkHelper::getInstance();
$oTemplate     = Template::getInstance();
$tplDir        = PFAD_TEMPLATES . $oTemplate->getDir() . '/';
$shopLogo      = Shop::getLogo();
$shopURL       = Shop::getURL();
$cart          = $_SESSION['Warenkorb'] ?? new Warenkorb();
$Einstellungen = Shopsetting::getInstance()->getAll();
$themeDir      = empty($Einstellungen['template']['theme']['theme_default'])
    ? 'evo'
    : $Einstellungen['template']['theme']['theme_default'];
$cShopName     = empty($Einstellungen['global']['global_shopname'])
    ? 'JTL-Shop'
    : $Einstellungen['global']['global_shopname'];
$cMinify_arr = $oTemplate->getMinifyArray();
$cCSS_arr    = $cMinify_arr["{$themeDir}.css"] ?? [];
$cJS_arr     = $cMinify_arr['jtl3.js'] ?? [];
executeHook(HOOK_LETZTERINCLUDE_CSS_JS, [
    'cCSS_arr'                  => &$cCSS_arr,
    'cJS_arr'                   => &$cJS_arr,
    'cPluginCss_arr'            => &$cMinify_arr['plugin_css'],
    'cPluginCssConditional_arr' => &$cMinify_arr['plugin_css_conditional'],
    'cPluginJsHead_arr'         => &$cMinify_arr['plugin_js_head'],
    'cPluginJsBody_arr'         => &$cMinify_arr['plugin_js_body']
]);
$kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
    ? $_SESSION['Kunde']->kKundengruppe
    : Session::CustomerGroup()->getID();
$cKundenherkunft = (isset($_SESSION['Kunde']->cLand) && strlen($_SESSION['Kunde']->cLand) > 0)
    ? $_SESSION['Kunde']->cLand
    : '';
$warensumme[0]         = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true));
$warensumme[1]         = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false));
$gesamtsumme[0]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(true, true));
$gesamtsumme[1]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(false, true));
$oVersandartKostenfrei = gibVersandkostenfreiAb($kKundengruppe, $cKundenherkunft);
$oGlobaleMetaAngaben   = $oGlobaleMetaAngabenAssoc_arr[Shop::getLanguageID()] ?? null;
$pagetType             = Shop::getPageType();

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
// Kategorielisten aufbauen
if (!isset($AktuelleKategorie)) {
    $AktuelleKategorie = null;
}
if (!isset($NaviFilter)) {
    $NaviFilter = Shop::run();
}
$smarty->assign('linkgroups', $linkHelper->activate($pagetType))
       ->assign('NaviFilter', $NaviFilter)
       ->assign('manufacturers', HerstellerHelper::getInstance()->getManufacturers())
       ->assign('cPluginCss_arr', $cMinify_arr['plugin_css'])
       ->assign('oUnterKategorien_arr', KategorieHelper::getSubcategoryList($AktuelleKategorie, false))
       ->assign('cPluginCssConditional_arr', $cMinify_arr['plugin_css_conditional'])
       ->assign('cPluginJsHead_arr', $cMinify_arr['plugin_js_head'])
       ->assign('cPluginJsBody_arr', $cMinify_arr['plugin_js_body'])
       ->assign('cCSS_arr', $cCSS_arr)
       ->assign('cJS_arr', $cJS_arr)
       ->assign('nTemplateVersion', $oTemplate->getVersion())
       ->assign('currentTemplateDir', $tplDir)
       ->assign('currentTemplateDirFull', $shopURL . '/' . $tplDir)
       ->assign('currentTemplateDirFullPath', PFAD_ROOT . $tplDir)
       ->assign('currentThemeDir', $tplDir . 'themes/' . $themeDir . '/')
       ->assign('currentThemeDirFull', $shopURL . '/' . $tplDir . 'themes/' . $themeDir . '/')
       ->assign('session_name', session_name())
       ->assign('session_id', session_id())
       ->assign('lang', Shop::getLanguageCode())
       ->assign('ShopURL', $shopURL)
       ->assign('imageBaseURL', Shop::getImageBaseURL())
       ->assign('ShopURLSSL', Shop::getURL(true))
       ->assign('NettoPreise', Session::CustomerGroup()->getIsMerchant())
       ->assign('Anrede_m', Shop::Lang()->get('salutationM'))
       ->assign('Anrede_w', Shop::Lang()->get('salutationW'))
       ->assign('oTrennzeichenGewicht', Trennzeichen::getUnit(JTL_SEPARATOR_WEIGHT, Shop::getLanguageID()))
       ->assign('oTrennzeichenMenge', Trennzeichen::getUnit(JTL_SEPARATOR_AMOUNT, Shop::getLanguageID()))
       ->assign('cShopName', $cShopName)
       ->assign('KaufabwicklungsURL', $linkHelper->getStaticRoute('bestellvorgang.php'))
       ->assign('WarenkorbArtikelanzahl', $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ->assign('WarenkorbWarensumme', $warensumme)
       ->assign('WarenkorbGesamtsumme', $gesamtsumme)
       ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
       ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
       ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString($oVersandartKostenfrei,
           $cart->gibGesamtsummeWarenExt(
               [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
               true
           )))
       ->assign('meta_title', $cMetaTitle ?? '')
       ->assign('meta_description', $cMetaDescription ?? '')
       ->assign('meta_keywords', $cMetaKeywords ?? '')
       ->assign('meta_publisher', $Einstellungen['metaangaben']['global_meta_publisher'])
       ->assign('meta_copyright', $Einstellungen['metaangaben']['global_meta_copyright'])
       ->assign('meta_language', StringHandler::convertISO2ISO639($_SESSION['cISOSprache']))
       ->assign('oSpezialseiten_arr', $linkHelper->getSpecialPages())
       ->assign('bNoIndex', $linkHelper->checkNoIndex())
       ->assign('bAjaxRequest', isAjaxRequest())
       ->assign('oBrowser', $oBrowser)
       ->assign('jtl_token', getTokenInput())
       ->assign('oSuchspecialoverlay_arr', holeAlleSuchspecialOverlays(Shop::getLanguageID()))
       ->assign('oSuchspecial_arr', baueAlleSuchspecialURLs())
       ->assign('ShopLogoURL', $shopLogo)
       ->assign('ShopLogoURL_abs', $shopLogo === '' ? '' : ($shopURL . $shopLogo))
       ->assign('nSeitenTyp', $pagetType)
       ->assign('bExclusive', isset($_GET['exclusive_content']))
       ->assign('bAdminWartungsmodus', isset($bAdminWartungsmodus) && $bAdminWartungsmodus)
       ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
       ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
       ->assign('FavourableShipping', $cart->getFavourableShipping())
       ->assign('Einstellungen', $Einstellungen)
       ->assign('deletedPositions', Warenkorb::$deletedPositions)
       ->assign('updatedPositions', Warenkorb::$updatedPositions)
       ->assign('cCanonicalURL', $cCanonicalURL ?? null)
       ->assign('AktuelleKategorie', $AktuelleKategorie)
       ->assign('bLiveEditMode', ((!empty($bLiveEditMode)) ? $bLiveEditMode : false))
       ->assign('showLoginCaptcha', isset($_SESSION['showLoginCaptcha']) && $_SESSION['showLoginCaptcha'])
       ->assign('PFAD_SLIDER', $shopURL . '/' . PFAD_BILDER_SLIDER)
       ->assign('Suchergebnisse', $oSuchergebnisse ?? new \Filter\ProductFilterSearchResults());

require_once PFAD_ROOT . PFAD_INCLUDES . 'besucher.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';

$smarty
    ->assign('opc', Shop::Container()->getOPC())
    ->assign('opcPage', Shop::Container()->getOPC()->getCurPage());

// Kampagnen
pruefeKampagnenParameter();
// Währungs- und Sprachlinks (um die Sprache oder Währung zu wechseln ohne die aktuelle Seite zu verlieren)
setzeSpracheUndWaehrungLink();
// Extension Point
$oExtension = (new ExtensionPoint($pagetType, Shop::getParameters(), Shop::getLanguageID(), $kKundengruppe))->load();
executeHook(HOOK_LETZTERINCLUDE_INC);
$boxes       = Boxen::getInstance();
$boxesToShow = $boxes->build($pagetType)->render();
/* @global Artikel $AktuellerArtikel */
if (isset($AktuellerArtikel->kArtikel) && $AktuellerArtikel->kArtikel > 0) {
    // Letzten angesehenden Artikel hinzufügen
    $boxes->addRecentlyViewed($AktuellerArtikel->kArtikel);
}
$visitorCount = $Einstellungen['global']['global_zaehler_anzeigen'] === 'Y'
    ? (int)Shop::Container()->getDB()->query('SELECT nZaehler FROM tbesucherzaehler', \DB\ReturnType::SINGLE_OBJECT)->nZaehler
    : 0;
$smarty->assign('bCookieErlaubt', isset($_COOKIE['JTLSHOP']))
       ->assign('nIsSSL', pruefeSSL())
       ->assign('boxes', $boxesToShow)
       ->assign('nZeitGebraucht', isset($nStartzeit) ? (microtime(true) - $nStartzeit) : 0)
       ->assign('Besucherzaehler', $visitorCount);
