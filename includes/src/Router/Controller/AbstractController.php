<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Cache\JTLCacheInterface;
use JTL\Campaign;
use JTL\Cart\Cart;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Navigation;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\DB\DbInterface;
use JTL\ExtensionPoint;
use JTL\Filter\Items\Availability;
use JTL\Filter\Metadata;
use JTL\Filter\ProductFilter;
use JTL\Filter\SearchResults;
use JTL\Firma;
use JTL\Helpers\Category;
use JTL\Helpers\Form;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Minify\MinifyService;
use JTL\Router\State;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Visitor;
use Mobile_Detect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class AbstractController
 * @package JTL\Router\Controller
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * @var JTLSmarty
     */
    protected JTLSmarty $smarty;

    /**
     * @var int|null
     */
    protected ?int $languageID = null;

    /**
     * @var Artikel|null
     */
    protected ?Artikel $currentProduct = null;

    /**
     * @var Kategorie|null
     */
    protected ?Kategorie $currentCategory = null;

    /**
     * @var LinkInterface|null
     */
    protected ?LinkInterface $currentLink = null;

    /**
     * @var ProductFilter
     */
    protected ProductFilter $productFilter;

    /**
     * @var KategorieListe
     */
    protected KategorieListe $expandedCategories;

    /**
     * @var string|null
     */
    protected ?string $canonicalURL = null;

    /**
     * @var SearchResults
     */
    protected SearchResults $searchResults;

    /**
     * @var string|null
     */
    protected ?string $metaDescription = null;

    /**
     * @var string|null
     */
    protected ?string $metaTitle = null;

    /**
     * @var string|null
     */
    protected ?string $metaKeywords = null;

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param State                 $state
     * @param int                   $customerGroupID
     * @param array                 $config
     * @param AlertServiceInterface $alertService
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected State $state,
        protected int $customerGroupID,
        protected array $config,
        protected AlertServiceInterface $alertService
    ) {
        $this->searchResults      = new SearchResults();
        $this->expandedCategories = new KategorieListe();
        $this->productFilter      = Shop::getProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        $this->languageID = $this->state->languageID ?: Shop::getLanguageID();

        return true;
    }

    /**
     * @param stdClass $seo
     * @param string   $slug
     * @return State
     */
    public function updateState(stdClass $seo, string $slug): State
    {
        $this->state->slug = $seo->cSeo ?? $slug;
        if (isset($seo->kSprache, $seo->kKey)) {
            $this->state->languageID = (int)$seo->kSprache;
            $this->state->itemID     = (int)$seo->kKey;
            $this->state->type       = $seo->cKey;
            $mapping                 = $this->state->getMapping();
            if (isset($mapping[$seo->cKey])) {
                $this->state->{$mapping[$seo->cKey]} = $this->state->itemID;
            }
        }
        $this->updateShopParams($slug);

        return $this->updateProductFilter();
    }

    /**
     * @return State
     */
    protected function updateProductFilter(): State
    {
        Shop::getProductFilter()->initStates($this->state->getAsParams());
        \executeHook(\HOOK_INDEX_NAVI_HEAD_POSTGET);

        return $this->state;
    }

    /**
     * @param string $slug
     * @return void
     */
    protected function updateShopParams(string $slug): void
    {
        if (\strcasecmp($this->state->slug, $slug) !== 0) {
            return;
        }
        if ($slug !== $this->state->slug) {
            \http_response_code(301);
            \header('Location: ' . Shop::getURL() . '/' . $this->state->slug);
            exit;
        }
        Shop::updateLanguage($this->state->languageID);
        Shop::$cCanonicalURL             = Shop::getURL() . '/' . $this->state->slug;
        Shop::$is404                     = $this->state->is404;
        Shop::$kSprache                  = $this->state->languageID ?: Shop::$kSprache;
        Shop::$kSeite                    = $this->state->pageID;
        Shop::$kKategorieFilter          = $this->state->categoryFilterID;
        Shop::$customFilters             = $this->state->customFilters;
        Shop::$manufacturerFilterIDs     = $this->state->manufacturerFilterIDs;
        Shop::$kHerstellerFilter         = $this->state->manufacturerFilterID;
        Shop::$bHerstellerFilterNotFound = $this->state->manufacturerFilterNotFound;
        Shop::$bKatFilterNotFound        = $this->state->categoryFilterNotFound;
        Shop::$bSEOMerkmalNotFound       = $this->state->characteristicNotFound;
        Shop::$MerkmalFilter             = $this->state->characteristicFilterIDs;
        Shop::$SuchFilter                = $this->state->searchFilterIDs;
        Shop::$categoryFilterIDs         = $this->state->categoryFilterIDs;
        if ($this->state->type !== '') {
            $mapped = $this->state->type;
            if ($mapped === 'suchspecial') {
                $mapped = 'kSuchspecial';
            }
            Shop::${$mapped} = $this->state->itemID;
        }
        \executeHook(\HOOK_SEOCHECK_ENDE);
    }

    /**
     * @param array $args
     * @return State
     */
    public function getStateFromSlug(array $args): State
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        if ($this->state->languageID === 0) {
            $this->state->languageID = Shop::getLanguageID();
        }
        $this->state->is404  = true;
        $this->state->linkID = Shop::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404) ?: 0;
        $pc                  = new PageController(
            $this->db,
            $this->cache,
            $this->state,
            $this->customerGroupID,
            $this->config,
            $this->alertService
        );
        $pc->init();

        return $pc->getResponse($request, $args, $smarty)->withStatus(404);
    }

    /**
     * @return void
     */
    public function preRender(): void
    {
        global $nStartzeit;
        $this->config             = Shopsetting::getInstance()->getAll();
        $cart                     = Frontend::getCart();
        $linkHelper               = Shop::Container()->getLinkService();
        $this->expandedCategories = $this->expandedCategories ?? new KategorieListe();
        $debugbar                 = Shop::Container()->getDebugBar();
        $debugbarRenderer         = $debugbar->getJavascriptRenderer();
        $pageType                 = Shop::getPageType();
        $link                     = $this->currentLink ?? new Link($this->db);
        $this->currentCategory    = $this->currentCategory
            ?? new Kategorie(Request::verifyGPCDataInt('kategorie'), $this->languageID, $this->customerGroupID);
        $this->expandedCategories->getOpenCategories($this->currentCategory, $this->customerGroupID, $this->languageID);
        // put availability on top
        $filters = $this->productFilter->getAvailableContentFilters();
        foreach ($filters as $key => $filter) {
            if ($filter->getClassName() === Availability::class) {
                unset($filters[$key]);
                \array_unshift($filters, $filter);
                break;
            }
        }
        $this->productFilter->setAvailableFilters($filters);
        $linkHelper->activate($pageType);

        $origin          = Frontend::getCustomer()->cLand ?? '';
        $shippingFreeMin = ShippingMethod::getFreeShippingMinimum($this->customerGroupID, $origin);
        $cartValueGros   = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true, true, $origin);
        $cartValueNet    = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false, true, $origin);
        $this->smarty->assign('linkgroups', $linkHelper->getVisibleLinkGroups())
            ->assign('NaviFilter', $this->productFilter)
            ->assign('manufacturers', Manufacturer::getInstance()->getManufacturers())
            ->assign('oUnterKategorien_arr', Category::getSubcategoryList(
                $this->currentCategory->getID() ?? -1,
                $this->currentCategory->getLeft() ?? -1,
                $this->currentCategory->getRight() ?? -1,
            ))
            ->assign('session_name', \session_name())
            ->assign('session_id', \session_id())
            ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
            ->assign('KaufabwicklungsURL', $linkHelper->getStaticRoute('bestellvorgang.php'))
            ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt([\C_WARENKORBPOS_TYP_ARTIKEL]))
            ->assign('WarenkorbWarensumme', [
                0 => Preise::getLocalizedPriceString(
                    $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                ),
                1 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL]))
            ])
            ->assign('WarenkorbGesamtsumme', [
                0 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren(true)),
                1 => Preise::getLocalizedPriceString($cart->gibGesamtsummeWaren())
            ])
            ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
            ->assign('Warenkorbtext', \lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
            ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
            ->assign(
                'WarenkorbVersandkostenfreiHinweis',
                ShippingMethod::getShippingFreeString($shippingFreeMin, $cartValueGros, $cartValueNet)
            )
            ->assign('oSpezialseiten_arr', $linkHelper->getSpecialPages())
            ->assign('bAjaxRequest', Request::isAjaxRequest())
            ->assign('jtl_token', Form::getTokenInput())
            ->assign('nSeitenTyp', $pageType)
            ->assign('bExclusive', isset($_GET['exclusive_content']))
            ->assign('bAdminWartungsmodus', $this->config['global']['wartungsmodus_aktiviert'] === 'Y')
            ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
            ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
            ->assign('FavourableShipping', $cart->getFavourableShipping(
                $shippingFreeMin !== 0
                && ShippingMethod::getShippingFreeDifference($shippingFreeMin, $cartValueGros, $cartValueNet) <= 0
                    ? (int)$shippingFreeMin->kVersandart
                    : null
            ))
            ->assign('favourableShippingString', $cart->favourableShippingString)
            ->assign('Einstellungen', $this->config)
            ->assign('deletedPositions', Cart::$deletedPositions)
            ->assign('updatedPositions', Cart::$updatedPositions)
            ->assign('Firma', new Firma(true, $this->db))
            ->assign('showLoginCaptcha', isset($_SESSION['showLoginCaptcha']) && $_SESSION['showLoginCaptcha'])
            ->assign('AktuelleKategorie', $this->currentCategory)
            ->assign('Suchergebnisse', $this->searchResults)
            ->assign('cSessionID', \session_id())
            ->assign('opc', Shop::Container()->getOPC())
            ->assign('opcPageService', Shop::Container()->getOPCPageService())
            ->assign('wishlists', Wishlist::getWishlists())
            ->assign('shippingCountry', $cart->getShippingCountry())
            ->assign('countries', Shop::Container()->getCountryService()->getCountrylist())
            ->assign('Link', $this->smarty->getTemplateVars('Link') ?? $link);

        $this->assignTemplateData();
        $this->assignMetaData($link);

        Visitor::generateData();
        Campaign::checkCampaignParameters();
        Shop::Lang()->generateLanguageAndCurrencyLinks();
        $ep = new ExtensionPoint($pageType, Shop::getParameters(), $this->languageID, $this->customerGroupID);
        $ep->load($this->db);
        \executeHook(\HOOK_LETZTERINCLUDE_INC);
        $boxes       = Shop::Container()->getBoxService();
        $boxesToShow = $boxes->render($boxes->buildList($pageType), $pageType);
        if ($this->currentProduct !== null && $this->currentProduct->kArtikel > 0) {
            $boxes->addRecentlyViewed($this->currentProduct->kArtikel);
        }
        $visitorCount = $this->config['global']['global_zaehler_anzeigen'] === 'Y'
            ? $this->db->getSingleInt('SELECT nZaehler FROM tbesucherzaehler', 'nZaehler')
            : 0;
        $debugbar->getTimer()->stopMeasure('init');

        $this->smarty->assign('bCookieErlaubt', isset($_COOKIE[Frontend::getSessionName()]))
            ->assign('Brotnavi', $this->getNavigation()->createNavigation())
            ->assign('nIsSSL', Request::checkSSL())
            ->assign('boxes', $boxesToShow)
            ->assign('boxesLeftActive', !empty($boxesToShow['left']))
            ->assign('consentItems', Shop::Container()->getConsentManager()->getActiveItems($this->languageID))
            ->assign('nZeitGebraucht', $nStartzeit === null ? 0 : (\microtime(true) - $nStartzeit))
            ->assign('Besucherzaehler', $visitorCount)
            ->assign('alertList', $this->alertService)
            ->assign('dbgBarHead', $debugbarRenderer->renderHead())
            ->assign('dbgBarBody', $debugbarRenderer->render());
    }

    /**
     * @return Navigation
     */
    protected function getNavigation(): Navigation
    {
        $nav = new Navigation(Shop::Lang(), Shop::Container()->getLinkService());
        $nav->setPageType(Shop::getPageType());
        $nav->setProductFilter($this->productFilter);
        $nav->setCategoryList($this->expandedCategories);
        if ($this->currentProduct !== null) {
            $nav->setProduct($this->currentProduct);
        }
        if ($this->currentLink) {
            $nav->setLink($this->currentLink);
        }

        return $nav;
    }

    /**
     * @return void
     */
    protected function assignTemplateData(): void
    {
        $tplService = Shop::Container()->getTemplateService();
        $template   = $tplService->getActiveTemplate();
        $paths      = $template->getPaths();
        (new MinifyService())->buildURIs($this->smarty, $template, $paths->getThemeDirName());
        $shopURL = Shop::getURL();
        $device  = new Mobile_Detect();
        $this->smarty->assign('device', $device)
            ->assign('isMobile', $device->isMobile())
            ->assign('isTablet', $device->isTablet())
            ->assign('ShopURL', $shopURL)
            ->assign('opcDir', \PFAD_ROOT . \PFAD_ADMIN . 'opc/')
            ->assignDeprecated('PFAD_SLIDER', $shopURL . '/' . \PFAD_BILDER_SLIDER, '5.2.0')
            ->assign('isNova', ($this->config['template']['general']['is_nova'] ?? 'N') === 'Y')
            ->assign('nTemplateVersion', $template->getVersion())
            ->assign('currentTemplateDir', $paths->getBaseRelDir())
            ->assign('currentTemplateDirFull', $paths->getBaseURL())
            ->assign('currentTemplateDirFullPath', $paths->getBaseDir())
            ->assign('currentThemeDir', $paths->getRealRelThemeDir())
            ->assign('currentThemeDirFull', $paths->getRealThemeURL())
            ->assign('isFluidTemplate', ($this->config['template']['theme']['pagelayout'] ?? '') === 'fluid')
            ->assign('shopFaviconURL', Shop::getFaviconURL())
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('lang', Shop::getLanguageCode())
            ->assign('ShopHomeURL', Shop::getHomeURL())
            ->assign('ShopURLSSL', Shop::getURL(true))
            ->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('isAjax', Request::isAjaxRequest());
        $tplService->save();
    }

    /**
     * @param LinkInterface $link
     * @return void
     */
    protected function assignMetaData(LinkInterface $link): void
    {
        $metaTitle       = $this->metaTitle ?? $link->getMetaTitle();
        $metaDescription = $this->metaDescription ?? $link->getMetaDescription();
        $metaKeywords    = $this->metaKeywords ?? $link->getMetaKeyword();
        if ($this->currentProduct !== null) {
            $metaTitle       = $this->currentProduct->getMetaTitle();
            $metaDescription = $this->currentProduct->getMetaDescription($this->expandedCategories);
            $metaKeywords    = $this->currentProduct->getMetaKeywords();
        }
        $globalMetaData = Metadata::getGlobalMetaData()[$this->languageID] ?? null;
        if (empty($metaTitle)) {
            $metaTitle = $globalMetaData->Title ?? null;
        }
        if (empty($metaDescription)) {
            $metaDescription = $globalMetaData->Meta_Description ?? null;
        }
        $metaTitle       = Metadata::prepareMeta(
            $metaTitle ?? '',
            null,
            (int)$this->config['metaangaben']['global_meta_maxlaenge_title']
        );
        $metaDescription = Metadata::prepareMeta(
            $metaDescription ?? '',
            null,
            (int)$this->config['metaangaben']['global_meta_maxlaenge_description']
        );
        $this->smarty->assign('meta_title', $metaTitle ?? '')
            ->assign('meta_description', $metaDescription ?? '')
            ->assign('meta_keywords', $metaKeywords ?? '')
            ->assign('meta_publisher', $this->config['metaangaben']['global_meta_publisher'])
            ->assign('meta_copyright', $this->config['metaangaben']['global_meta_copyright'])
            ->assign('meta_language', Text::convertISO2ISO639($_SESSION['cISOSprache']))
            ->assign('bNoIndex', $this->productFilter->getMetaData()->checkNoIndex())
            ->assign('cCanonicalURL', $this->canonicalURL)
            ->assign('robotsContent', $this->smarty->getTemplateVars('robotsContent'))
            ->assign('cShopName', $this->config['global']['global_shopname']);
    }

    /**
     * @param array $args
     * @param int   $default
     * @return int
     */
    protected function parseLanguageFromArgs(array $args, int $default): int
    {
        if (!isset($args['lang'])) {
            return $default;
        }
        foreach (LanguageHelper::getAllLanguages() as $languageModel) {
            if ($args['lang'] === $languageModel->getIso639()) {
                return $languageModel->getId();
            }
        }

        return $default;
    }
}
