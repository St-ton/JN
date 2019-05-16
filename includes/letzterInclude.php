<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Cart\Warenkorb;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Navigation;
use JTL\Catalog\NavigationEntry;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wunschliste;
use JTL\DB\ReturnType;
use JTL\ExtensionPoint;
use JTL\Filter\Metadata;
use JTL\Filter\SearchResults;
use JTL\Firma;
use JTL\Helpers\Category;
use JTL\Helpers\Form;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Kampagne;
use JTL\Link\Link;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Template;
use JTL\Visitor;

$smarty     = Shop::Smarty();
$template   = Template::getInstance();
$tplDir     = PFAD_TEMPLATES . $template->getDir() . '/';
$shopURL    = Shop::getURL();
$cart       = $_SESSION['Warenkorb'] ?? new Warenkorb();
$conf       = Shopsetting::getInstance()->getAll();
$linkHelper = Shop::Container()->getLinkService();
$link       = $linkHelper->getLinkByID(Shop::$kLink ?? 0);
$themeDir   = empty($conf['template']['theme']['theme_default'])
    ? 'evo'
    : $conf['template']['theme']['theme_default'];
$minify     = $template->getMinifyArray();
$css        = $minify["{$themeDir}.css"] ?? [];
$js         = $minify['jtl3.js'] ?? [];
executeHook(HOOK_LETZTERINCLUDE_CSS_JS, [
    'cCSS_arr'          => &$css,
    'cJS_arr'           => &$js,
    'cPluginCss_arr'    => &$minify['plugin_css'],
    'cPluginJsHead_arr' => &$minify['plugin_js_head'],
    'cPluginJsBody_arr' => &$minify['plugin_js_body']
]);

$expandedCategories = $expandedCategories ?? new KategorieListe();
$debugbar           = Shop::Container()->getDebugBar();
$debugbarRenderer   = $debugbar->getJavascriptRenderer();
$customerGroupID    = ($id = Frontend::getCustomer()->kKundengruppe) > 0
    ? $id
    : Frontend::getCustomerGroup()->getID();
$globalMetaData     = $globalMetaData[Shop::getLanguageID()] ?? null;
$pagetType          = Shop::getPageType();
$specialPageTypes   = [
    PAGE_REGISTRIERUNG,
    PAGE_WARENKORB,
    PAGE_PASSWORTVERGESSEN,
    PAGE_NEWSLETTER,
    PAGE_KONTAKT,
    PAGE_MEINKONTO,
    PAGE_LOGIN
];
if ($link !== null) {
    $cMetaTitle       = $link->getMetaTitle();
    $cMetaDescription = $link->getMetaDescription();
    $cMetaKeywords    = $link->getMetaKeyword();
}
if (is_object($globalMetaData)) {
    if (empty($cMetaTitle)) {
        $cMetaTitle = $globalMetaData->Title;
    }
    if (empty($cMetaDescription)) {
        $cMetaDescription = $globalMetaData->Meta_Description;
    }
    if (empty($cMetaKeywords)) {
        $cMetaKeywords = $globalMetaData->Meta_Keywords;
    }
    $cMetaTitle       = Metadata::prepareMeta(
        $cMetaTitle,
        null,
        (int)$conf['metaangaben']['global_meta_maxlaenge_title']
    );
    $cMetaDescription = Metadata::prepareMeta(
        $cMetaDescription,
        null,
        (int)$conf['metaangaben']['global_meta_maxlaenge_description']
    );
    if (empty($cMetaKeywords) && $link !== null && !empty($link->getContent())) {
        $cMetaKeywords = Metadata::getTopMetaKeywords($link->getContent());
    }
}
if (!isset($AktuelleKategorie)) {
    $AktuelleKategorie = new Kategorie(Request::verifyGPCDataInt('kategorie'));
}
$expandedCategories->getOpenCategories($AktuelleKategorie);
if (!isset($NaviFilter)) {
    $NaviFilter = Shop::run();
}
$linkHelper->activate($pagetType);
$origin = (isset($_SESSION['Kunde']->cLand) && mb_strlen($_SESSION['Kunde']->cLand) > 0)
    ? $_SESSION['Kunde']->cLand
    : '';
$smarty->assign('linkgroups', $linkHelper->getLinkGroups())
       ->assign('NaviFilter', $NaviFilter)
       ->assign('manufacturers', Manufacturer::getInstance()->getManufacturers())
       ->assign('cPluginCss_arr', $minify['plugin_css'])
       ->assign('oUnterKategorien_arr', Category::getSubcategoryList($AktuelleKategorie->kKategorie ?? -1))
       ->assign('maxProductPriceCategory', Category::getMaxProductPrice($AktuelleKategorie->kKategorie ?? -1))
       ->assign('cPluginJsHead_arr', $minify['plugin_js_head'])
       ->assign('cPluginJsBody_arr', $minify['plugin_js_body'])
       ->assign('cCSS_arr', $css)
       ->assign('cJS_arr', $js)
       ->assign('nTemplateVersion', $template->getVersion())
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
       ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
       ->assign('cShopName', $conf['global']['global_shopname'])
       ->assign('KaufabwicklungsURL', $linkHelper->getStaticRoute('bestellvorgang.php'))
       ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ->assign('WarenkorbWarensumme', [
           0 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)),
           1 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL]))
       ])
       ->assign('WarenkorbGesamtsumme', [
           0 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren(true)),
           1 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren())
       ])
       ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
       ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
       ->assign(
           'WarenkorbVersandkostenfreiHinweis',
           ShippingMethod::getShippingFreeString(
               ShippingMethod::getFreeShippingMinimum($customerGroupID, $origin),
               $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
           )
       )
       ->assign('meta_title', $cMetaTitle ?? '')
       ->assign('meta_description', $cMetaDescription ?? '')
       ->assign('meta_keywords', $cMetaKeywords ?? '')
       ->assign('meta_publisher', $conf['metaangaben']['global_meta_publisher'])
       ->assign('meta_copyright', $conf['metaangaben']['global_meta_copyright'])
       ->assign('meta_language', Text::convertISO2ISO639($_SESSION['cISOSprache']))
       ->assign('oSpezialseiten_arr', $linkHelper->getSpecialPages())
       ->assign('bNoIndex', $NaviFilter->getMetaData()->checkNoIndex())
       ->assign('bAjaxRequest', Request::isAjaxRequest())
       ->assign('jtl_token', Form::getTokenInput())
       ->assign('ShopLogoURL', Shop::getLogo(true))
       ->assign('nSeitenTyp', $pagetType)
       ->assign('bExclusive', isset($_GET['exclusive_content']))
       ->assign('bAdminWartungsmodus', isset($bAdminWartungsmodus) && $bAdminWartungsmodus)
       ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
       ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
       ->assign('FavourableShipping', $cart->getFavourableShipping())
       ->assign('Einstellungen', $conf)
       ->assign('isFluidTemplate', isset($conf['template']['theme']['pagelayout'])
           && $conf['template']['theme']['pagelayout'] === 'fluid')
       ->assign('deletedPositions', Warenkorb::$deletedPositions)
       ->assign('updatedPositions', Warenkorb::$updatedPositions)
       ->assign('cCanonicalURL', $cCanonicalURL ?? null)
       ->assign('Firma', new Firma())
       ->assign('AktuelleKategorie', $AktuelleKategorie)
       ->assign('showLoginCaptcha', isset($_SESSION['showLoginCaptcha']) && $_SESSION['showLoginCaptcha'])
       ->assign('PFAD_SLIDER', $shopURL . '/' . PFAD_BILDER_SLIDER)
       ->assign('Suchergebnisse', $oSuchergebnisse ?? new SearchResults())
       ->assign('cSessionID', session_id())
       ->assign('opc', Shop::Container()->getOPC())
       ->assign('opcPageService', Shop::Container()->getOPCPageService())
       ->assign('shopFaviconURL', Shop::getFaviconURL())
       ->assign('wishlists', Wunschliste::getWishlists());

$nav = new Navigation(Shop::Lang(), Shop::Container()->getLinkService());
$nav->setPageType(Shop::getPageType());
$nav->setProductFilter($NaviFilter);
$nav->setCategoryList($expandedCategories);
if (isset($AktuellerArtikel) && $AktuellerArtikel instanceof Artikel) {
    $nav->setProduct($AktuellerArtikel);
}
if (isset($link) && $link instanceof Link) {
    $nav->setLink($link);
}
if (isset($breadCrumbName, $breadCrumbURL)) {
    $breadCrumbEntry = new NavigationEntry();
    $breadCrumbEntry->setURL($breadCrumbURL);
    $breadCrumbEntry->setName($breadCrumbName);
    $breadCrumbEntry->setURLFull($breadCrumbURL);
    $nav->setCustomNavigationEntry($breadCrumbEntry);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'besucher.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
Visitor::generateData();
Kampagne::checkCampaignParameters();
Shop::Lang()->generateLanguageAndCurrencyLinks();
$ep = new ExtensionPoint($pagetType, Shop::getParameters(), Shop::getLanguageID(), $customerGroupID);
$ep->load();
executeHook(HOOK_LETZTERINCLUDE_INC);
$boxes            = Shop::Container()->getBoxService();
$boxesToShowList  = $boxes->buildList($pagetType);
$boxesToShow      = $boxes->render($boxesToShowList, $pagetType);
$boxesLeftFilters = Functional\filter($boxesToShowList['left'], function ($boxTMP) {
    return strpos(get_class($boxTMP), 'Filter') !== false;
});
$boxesLeft        = Functional\filter($boxesToShowList['left'], function ($boxTMP) {
    return strpos(get_class($boxTMP), 'Filter') === false;
});
/* @global null|Artikel $AktuellerArtikel */
if (isset($AktuellerArtikel->kArtikel) && $AktuellerArtikel->kArtikel > 0) {
    $boxes->addRecentlyViewed($AktuellerArtikel->kArtikel);
}
$visitorCount = $conf['global']['global_zaehler_anzeigen'] === 'Y'
    ? (int)Shop::Container()->getDB()->query(
        'SELECT nZaehler FROM tbesucherzaehler',
        ReturnType::SINGLE_OBJECT
    )->nZaehler
    : 0;
$debugbar->getTimer()->stopMeasure('init');

// backwards compatibility, create error and note messages for previously used globals $cFehler, $cHinweis, $hinweis
// since 5.0.0: the new AlertService should be used instead
$alertHelper = Shop::Container()->getAlertService();
if (isset($cFehler)) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, $cFehler, 'miscFehler');
    trigger_error('global $cFehler is deprecated.', \E_USER_DEPRECATED);
}
if (isset($cHinweis)) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, $cHinweis, 'miscCHinweis');
    trigger_error('global $cHinweis is deprecated.', \E_USER_DEPRECATED);
}
if (isset($hinweis)) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, $hinweis, 'miscHinweis');
    trigger_error('global $hinweis is deprecated.', \E_USER_DEPRECATED);
}

$smarty->assign('bCookieErlaubt', isset($_COOKIE['JTLSHOP']))
       ->assign('Brotnavi', $nav->createNavigation())
       ->assign('nIsSSL', Request::checkSSL())
       ->assign('boxes', $boxesToShow)
       ->assign('boxesLeft', $boxesLeft)
       ->assign('boxesLeftFilters', $boxesLeftFilters)
       ->assign('nZeitGebraucht', isset($nStartzeit) ? (microtime(true) - $nStartzeit) : 0)
       ->assign('Besucherzaehler', $visitorCount)
       ->assign('alertList', Shop::Container()->getAlertService())
       ->assign('dbgBarHead', $debugbarRenderer->renderHead())
       ->assign('dbgBarBody', $debugbarRenderer->render());
