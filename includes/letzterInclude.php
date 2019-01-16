<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Category;
use Helpers\Form;
use Helpers\Manufacturer;
use Helpers\Request;
use Helpers\ShippingMethod;

$smarty        = Shop::Smarty();
$oTemplate     = Template::getInstance();
$tplDir        = PFAD_TEMPLATES . $oTemplate->getDir() . '/';
$shopLogo      = Shop::getLogo();
$shopURL       = Shop::getURL();
$cart          = $_SESSION['Warenkorb'] ?? new Warenkorb();
$Einstellungen = Shopsetting::getInstance()->getAll();
$linkHelper    = Shop::Container()->getLinkService();
$themeDir      = empty($Einstellungen['template']['theme']['theme_default'])
    ? 'evo'
    : $Einstellungen['template']['theme']['theme_default'];
$cShopName     = empty($Einstellungen['global']['global_shopname'])
    ? 'JTL-Shop'
    : $Einstellungen['global']['global_shopname'];
$minify        = $oTemplate->getMinifyArray();
$css           = $minify["{$themeDir}.css"] ?? [];
$js            = $minify['jtl3.js'] ?? [];
executeHook(HOOK_LETZTERINCLUDE_CSS_JS, [
    'cCSS_arr'          => &$css,
    'cJS_arr'           => &$js,
    'cPluginCss_arr'    => &$minify['plugin_css'],
    'cPluginJsHead_arr' => &$minify['plugin_js_head'],
    'cPluginJsBody_arr' => &$minify['plugin_js_body']
]);

$debugbar         = Shop::Container()->getDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer();
$kKundengruppe    = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
    ? $_SESSION['Kunde']->kKundengruppe
    : \Session\Frontend::getCustomerGroup()->getID();
$cKundenherkunft = (isset($_SESSION['Kunde']->cLand) && strlen($_SESSION['Kunde']->cLand) > 0)
    ? $_SESSION['Kunde']->cLand
    : '';

$warensumme[0]   = Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true));
$warensumme[1]   = Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false));
$gesamtsumme[0]  = Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren(true, true));
$gesamtsumme[1]  = Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren(false, true));

$oVersandartKostenfrei = ShippingMethod::getFreeShippingMinimum($kKundengruppe, $cKundenherkunft);
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
if (!isset($AktuelleKategorie)) {
    $AktuelleKategorie = null;
}
if (!isset($NaviFilter)) {
    $NaviFilter = Shop::run();
}
$linkHelper->activate($pagetType);

$smarty->assign('linkgroups', $linkHelper->getLinkGroups())
       ->assign('NaviFilter', $NaviFilter)
       ->assign('manufacturers', Manufacturer::getInstance()->getManufacturers())
       ->assign('cPluginCss_arr', $minify['plugin_css'])
       ->assign('oUnterKategorien_arr', Category::getSubcategoryList($AktuelleKategorie->kKategorie ?? -1))
       ->assign('cPluginJsHead_arr', $minify['plugin_js_head'])
       ->assign('cPluginJsBody_arr', $minify['plugin_js_body'])
       ->assign('cCSS_arr', $css)
       ->assign('cJS_arr', $js)
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
       ->assign('NettoPreise', \Session\Frontend::getCustomerGroup()->getIsMerchant())
       ->assign('cShopName', $cShopName)
       ->assign('KaufabwicklungsURL', $linkHelper->getStaticRoute('bestellvorgang.php'))
       ->assign('WarenkorbArtikelanzahl', $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ->assign('WarenkorbWarensumme', $warensumme)
       ->assign('WarenkorbGesamtsumme', $gesamtsumme)
       ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
       ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
       ->assign(
           'WarenkorbVersandkostenfreiHinweis',
           ShippingMethod::getShippingFreeString(
               $oVersandartKostenfrei,
               $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
           )
       )
       ->assign('meta_title', $cMetaTitle ?? '')
       ->assign('meta_description', $cMetaDescription ?? '')
       ->assign('meta_keywords', $cMetaKeywords ?? '')
       ->assign('meta_publisher', $Einstellungen['metaangaben']['global_meta_publisher'])
       ->assign('meta_copyright', $Einstellungen['metaangaben']['global_meta_copyright'])
       ->assign('meta_language', StringHandler::convertISO2ISO639($_SESSION['cISOSprache']))
       ->assign('oSpezialseiten_arr', $linkHelper->getSpecialPages())
       ->assign('bNoIndex', $NaviFilter->getMetaData()->checkNoIndex())
       ->assign('bAjaxRequest', Request::isAjaxRequest())
       ->assign('jtl_token', Form::getTokenInput())
       ->assign('ShopLogoURL', $shopLogo)
       ->assign('ShopLogoURL_abs', $shopLogo === '' ? '' : ($shopURL . $shopLogo))
       ->assign('nSeitenTyp', $pagetType)
       ->assign('bExclusive', isset($_GET['exclusive_content']))
       ->assign('bAdminWartungsmodus', isset($bAdminWartungsmodus) && $bAdminWartungsmodus)
       ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
       ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
       ->assign('FavourableShipping', $cart->getFavourableShipping())
       ->assign('Einstellungen', $Einstellungen)
       ->assign('isFluidTemplate', isset($Einstellungen['template']['theme']['pagelayout'])
           && $Einstellungen['template']['theme']['pagelayout'] === 'fluid')
       ->assign('deletedPositions', Warenkorb::$deletedPositions)
       ->assign('updatedPositions', Warenkorb::$updatedPositions)
       ->assign('cCanonicalURL', $cCanonicalURL ?? null)
       ->assign('Firma', new Firma())
       ->assign('AktuelleKategorie', $AktuelleKategorie)
       ->assign('showLoginCaptcha', isset($_SESSION['showLoginCaptcha']) && $_SESSION['showLoginCaptcha'])
       ->assign('PFAD_SLIDER', $shopURL . '/' . PFAD_BILDER_SLIDER)
       ->assign('Suchergebnisse', $oSuchergebnisse ?? new \Filter\SearchResults())
       ->assign('opc', Shop::Container()->getOPC())
       ->assign('opcPageService', Shop::Container()->getOPCPageService())
       ->assign('shopFaviconURL', Shop::getFaviconURL());

$nav = new \JTL\Navigation(Shop::Lang(), Shop::Container()->getLinkService());
$nav->setPageType(Shop::getPageType());
$nav->setProductFilter($NaviFilter);
if (isset($AufgeklappteKategorien) && $AufgeklappteKategorien instanceof KategorieListe) {
    $nav->setCategoryList($AufgeklappteKategorien);
} elseif (isset($expandedCategories) && $expandedCategories instanceof KategorieListe) {
    $nav->setCategoryList($expandedCategories);
}
if (isset($AktuellerArtikel) && $AktuellerArtikel instanceof Artikel) {
    $nav->setProduct($AktuellerArtikel);
}
if (isset($link) && $link instanceof \Link\Link) {
    $nav->setLink($link);
}
if (isset($breadCrumbName, $breadCrumbURL)) {
    $breadCrumbEntry = new \JTL\NavigationEntry();
    $breadCrumbEntry->setURL($breadCrumbURL);
    $breadCrumbEntry->setName($breadCrumbName);
    $breadCrumbEntry->setURLFull($breadCrumbURL);
    $nav->setCustomNavigationEntry($breadCrumbEntry);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'besucher.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
Visitor::generateData();
Kampagne::checkCampaignParameters();
Sprache::generateLanguageAndCurrencyLinks();
$oExtension = (new ExtensionPoint($pagetType, Shop::getParameters(), Shop::getLanguageID(), $kKundengruppe))->load();
executeHook(HOOK_LETZTERINCLUDE_INC);
$boxes       = Shop::Container()->getBoxService();
$boxesToShow = $boxes->render($boxes->buildList($pagetType), $pagetType);
/* @global null|Artikel $AktuellerArtikel */
if (isset($AktuellerArtikel->kArtikel) && $AktuellerArtikel->kArtikel > 0) {
    $boxes->addRecentlyViewed($AktuellerArtikel->kArtikel);
}
$visitorCount = $Einstellungen['global']['global_zaehler_anzeigen'] === 'Y'
    ? (int)Shop::Container()->getDB()->query(
        'SELECT nZaehler FROM tbesucherzaehler',
        \DB\ReturnType::SINGLE_OBJECT
    )->nZaehler
    : 0;
$debugbar->getTimer()->stopMeasure('init');
$smarty->assign('bCookieErlaubt', isset($_COOKIE['JTLSHOP']))
       ->assign('Brotnavi', $nav->createNavigation())
       ->assign('nIsSSL', Request::checkSSL())
       ->assign('boxes', $boxesToShow)
       ->assign('nZeitGebraucht', isset($nStartzeit) ? (microtime(true) - $nStartzeit) : 0)
       ->assign('Besucherzaehler', $visitorCount)
       ->assign('alertList', Shop::Container()->getAlertService()->getAlertlist())
       ->assign('dbgBarHead', $debugbarRenderer->renderHead())
       ->assign('dbgBarBody', $debugbarRenderer->render());
