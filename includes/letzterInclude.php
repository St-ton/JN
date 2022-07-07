<?php declare(strict_types=1);

use JTL\Campaign;
use JTL\Cart\Cart;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Navigation;
use JTL\Catalog\NavigationEntry;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\ExtensionPoint;
use JTL\Filter\Items\Availability;
use JTL\Filter\Metadata;
use JTL\Filter\SearchResults;
use JTL\Firma;
use JTL\Helpers\Category;
use JTL\Helpers\Form;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Link\Link;
use JTL\Minify\MinifyService;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Visitor;

$smarty             = Shop::Smarty();
$db                 = Shop::Container()->getDB();
$tplService         = Shop::Container()->getTemplateService();
$template           = $tplService->getActiveTemplate();
$paths              = $template->getPaths();
$shopURL            = Shop::getURL();
$cart               = Frontend::getCart();
$conf               = Shopsetting::getInstance()->getAll();
$linkHelper         = Shop::Container()->getLinkService();
$link               = $linkHelper->getLinkByID(Shop::$kLink ?? 0);
$languageID         = Shop::getLanguageID();
$device             = new Mobile_Detect();
$expandedCategories = $expandedCategories ?? new KategorieListe();
$debugbar           = Shop::Container()->getDebugBar();
$debugbarRenderer   = $debugbar->getJavascriptRenderer();
$customerGroupID    = ($id = Frontend::getCustomer()->kKundengruppe) > 0
    ? $id
    : Frontend::getCustomerGroup()->getID();
$globalMetaData     = $globalMetaData[$languageID] ?? null;
$pageType           = Shop::getPageType();
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
}
if (!isset($AktuelleKategorie)) {
    $AktuelleKategorie = new Kategorie(Request::verifyGPCDataInt('kategorie'), $languageID, $customerGroupID);
}
$expandedCategories->getOpenCategories($AktuelleKategorie);
if (!isset($NaviFilter)) {
    $NaviFilter = Shop::run();
}
// put availability on top
$filters = $NaviFilter->getAvailableContentFilters();
foreach ($filters as $key => $filter) {
    if ($filter->getClassName() === Availability::class) {
        unset($filters[$key]);
        array_unshift($filters, $filter);
        break;
    }
}
$NaviFilter->setAvailableFilters($filters);
$linkHelper->activate($pageType);
$origin = Frontend::getCustomer()->cLand ?? '';
(new MinifyService())->buildURIs($smarty, $template, $paths->getThemeDirName());
$shippingFreeMin = ShippingMethod::getFreeShippingMinimum($customerGroupID, $origin);
$cartValueGros   = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true, true, $origin);
$cartValueNet    = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false, true, $origin);

$smarty->assign('linkgroups', $linkHelper->getVisibleLinkGroups())
    ->assign('NaviFilter', $NaviFilter)
    ->assign('manufacturers', Manufacturer::getInstance()->getManufacturers())
    ->assign('oUnterKategorien_arr', Category::getSubcategoryList(
        $AktuelleKategorie->kKategorie ?? -1,
        $AktuelleKategorie->lft ?? -1,
        $AktuelleKategorie->rght ?? -1,
    ))
    ->assign('nTemplateVersion', $template->getVersion())
    ->assign('currentTemplateDir', $paths->getBaseRelDir())
    ->assign('currentTemplateDirFull', $paths->getBaseURL())
    ->assign('currentTemplateDirFullPath', $paths->getBaseDir())
    ->assign('currentThemeDir', $paths->getRealRelThemeDir())
    ->assign('currentThemeDirFull', $paths->getRealThemeURL())
    ->assign('opcDir', PFAD_ROOT . PFAD_ADMIN . 'opc/')
    ->assign('session_name', session_name())
    ->assign('session_id', session_id())
    ->assign('lang', Shop::getLanguageCode())
    ->assign('ShopURL', $shopURL)
    ->assign('ShopHomeURL', Shop::getHomeURL())
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
        ShippingMethod::getShippingFreeString($shippingFreeMin, $cartValueGros, $cartValueNet)
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
    ->assign('nSeitenTyp', $pageType)
    ->assign('bExclusive', isset($_GET['exclusive_content']))
    ->assign('bAdminWartungsmodus', isset($bAdminWartungsmodus) && $bAdminWartungsmodus)
    ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
    ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
    ->assign('FavourableShipping', $cart->getFavourableShipping(
        $shippingFreeMin !== 0
        && ShippingMethod::getShippingFreeDifference($shippingFreeMin, $cartValueGros, $cartValueNet) <= 0
            ? (int)$shippingFreeMin->kVersandart
            : null
    ))
    ->assign('favourableShippingString', $cart->favourableShippingString)
    ->assign('Einstellungen', $conf)
    ->assign('isFluidTemplate', isset($conf['template']['theme']['pagelayout'])
        && $conf['template']['theme']['pagelayout'] === 'fluid')
    ->assign('deletedPositions', Cart::$deletedPositions)
    ->assign('updatedPositions', Cart::$updatedPositions)
    ->assign('cCanonicalURL', $cCanonicalURL ?? null)
    ->assign('Firma', new Firma(true, $db))
    ->assign('AktuelleKategorie', $AktuelleKategorie)
    ->assign('showLoginCaptcha', isset($_SESSION['showLoginCaptcha']) && $_SESSION['showLoginCaptcha'])
    ->assign('PFAD_SLIDER', $shopURL . '/' . PFAD_BILDER_SLIDER)
    ->assign('Suchergebnisse', $oSuchergebnisse ?? new SearchResults())
    ->assign('cSessionID', session_id())
    ->assign('opc', Shop::Container()->getOPC())
    ->assign('opcPageService', Shop::Container()->getOPCPageService())
    ->assign('shopFaviconURL', Shop::getFaviconURL())
    ->assign('wishlists', Wishlist::getWishlists())
    ->assign('shippingCountry', $cart->getShippingCountry())
    ->assign('robotsContent', $smarty->getTemplateVars('robotsContent'))
    ->assign('device', $device)
    ->assign('isMobile', $device->isMobile())
    ->assign('isTablet', $device->isTablet())
    ->assign('isNova', ($conf['template']['general']['is_nova'] ?? 'N') === 'Y')
    ->assign('isAjax', Request::isAjaxRequest())
    ->assign('countries', Shop::Container()->getCountryService()->getCountrylist());

if ($smarty->getTemplateVars('Link') === null) {
    $smarty->assign('Link', $link ?? new Link($db));
}

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

Visitor::generateData();
Campaign::checkCampaignParameters();
Shop::Lang()->generateLanguageAndCurrencyLinks();
(new ExtensionPoint($pageType, Shop::getParameters(), $languageID, $customerGroupID))->load($db);
executeHook(HOOK_LETZTERINCLUDE_INC);
$boxes       = Shop::Container()->getBoxService();
$boxesToShow = $boxes->render($boxes->buildList($pageType), $pageType);
/* @global null|Artikel $AktuellerArtikel */
if (isset($AktuellerArtikel->kArtikel) && $AktuellerArtikel->kArtikel > 0) {
    $boxes->addRecentlyViewed($AktuellerArtikel->kArtikel);
}
$visitorCount = $conf['global']['global_zaehler_anzeigen'] === 'Y'
    ? $db->getSingleInt('SELECT nZaehler FROM tbesucherzaehler', 'nZaehler')
    : 0;
$debugbar->getTimer()->stopMeasure('init');
$tplService->save();
$smarty->assign('bCookieErlaubt', isset($_COOKIE[Frontend::getSessionName()]))
    ->assign('Brotnavi', $nav->createNavigation())
    ->assign('nIsSSL', Request::checkSSL())
    ->assign('boxes', $boxesToShow)
    ->assign('boxesLeftActive', !empty($boxesToShow['left']))
    ->assign('consentItems', Shop::Container()->getConsentManager()->getActiveItems($languageID))
    ->assign('nZeitGebraucht', isset($nStartzeit) ? (microtime(true) - $nStartzeit) : 0)
    ->assign('Besucherzaehler', $visitorCount)
    ->assign('alertList', Shop::Container()->getAlertService())
    ->assign('dbgBarHead', $debugbarRenderer->renderHead())
    ->assign('dbgBarBody', $debugbarRenderer->render());
