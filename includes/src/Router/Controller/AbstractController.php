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
use JTL\Customer\Visitor;
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
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Minify\MinifyService;
use JTL\Router\DefaultParser;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use League\Route\RouteGroup;
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
     * @var int|null
     */
    protected ?int $customerGroupID = null;

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
     * @var string
     */
    protected string $tseoSelector = '';

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param State                 $state
     * @param array                 $config
     * @param AlertServiceInterface $alertService
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected State $state,
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
        $this->languageID      = $this->state->languageID ?: Shop::getLanguageID();
        $this->customerGroupID = Frontend::getCustomerGroup()->getID();

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
        if ($this->state->productID > 0 && Product::isVariChild($this->state->productID)) {
            $this->state->childProductID = $this->state->productID;
            $this->state->productID      = Product::getParent($this->state->productID);
        }
        $this->updateShopParams($slug);

        return $this->updateProductFilter();
    }

    /**
     * @return State
     */
    public function updateProductFilter(): State
    {
        Shop::getProductFilter()->initStates($this->state->getAsParams());

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
        $languageID = $this->state->languageID ?: Shop::$kSprache;
        Shop::updateLanguage($languageID);
        Shop::$cCanonicalURL             = Shop::getURL() . '/' . $this->state->slug;
        Shop::$is404                     = $this->state->is404;
        Shop::$kSprache                  = $languageID;
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
     * @param int $id
     * @param int $languageID
     * @return State
     */
    protected function handleSeoError(int $id, int $languageID): State
    {
        return $this->state;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getRouteTypeByClassName(string $className): string
    {
        return match ($className) {
            CategoryController::class            => Router::TYPE_CATEGORY,
            CharacteristicValueController::class => Router::TYPE_CHARACTERISTIC_VALUE,
            ManufacturerController::class        => Router::TYPE_MANUFACTURER,
            NewsController::class                => Router::TYPE_NEWS,
            ProductController::class             => Router::TYPE_PRODUCT,
            SearchSpecialController::class       => Router::TYPE_SEARCH_SPECIAL,
            SearchQueryController::class         => Router::TYPE_SEARCH_QUERY,
            default                              => Router::TYPE_PAGE
        };
    }

    /**
     * @param array $args
     * @return State
     */
    public function getStateFromSlug(array $args): State
    {
        $id   = (int)($args['id'] ?? 0);
        $name = $args['name'] ?? null;
        if ($id < 1 && $name === null) {
            return $this->state;
        }
        if ($name !== null) {
            $parser    = new DefaultParser($this->db, $this->state);
            $routeType = $this->getRouteTypeByClassName(\get_class($this));
            $name      = $parser->parse($name, $args, $routeType);
        }
        $seo = $id > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND kKey = :kid
                      AND kSprache = :lid',
                ['key' => $this->tseoSelector, 'kid' => $id, 'lid' => $this->state->languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key AND cSeo = :seo',
                ['key' => $this->tseoSelector, 'seo' => $name]
            );
        if ($seo === null) {
            return $this->handleSeoError($id, $this->state->languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kKey     = (int)$seo->kKey;
        $seo->kSprache = (int)$seo->kSprache;

        return $this->updateState($seo, $slug);
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
        $categoryID               = Request::verifyGPCDataInt('kategorie');
        $this->currentCategory    = $this->currentCategory
            ?? new Kategorie($categoryID, $this->languageID, $this->customerGroupID, false, $this->db);
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
        $cartValueGros   = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true, true, $origin);
        $cartValueNet    = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], false, true, $origin);
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
            ->assign('Firma', new Firma(true, $this->db, $this->cache))
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

        $visitor = new Visitor($this->db, $this->cache);
        $visitor->generateData();
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
        $this->smarty->assign('bCookieErlaubt', isset($_COOKIE[Frontend::getSessionName()]))
            ->assign('Brotnavi', $this->getNavigation()->createNavigation())
            ->assign('nIsSSL', Request::checkSSL())
            ->assign('boxes', $boxesToShow)
            ->assign('boxesLeftActive', !empty($boxesToShow['left']))
            ->assign('consentItems', Shop::Container()->getConsentManager()->getActiveItems($this->languageID))
            ->assign('nZeitGebraucht', $nStartzeit === null ? 0 : (\microtime(true) - $nStartzeit))
            ->assign('Besucherzaehler', $visitorCount)
            ->assign('alertList', $this->alertService);
        $debugbar->getTimer()->stopMeasure('init');
        $this->smarty->assign('dbgBarHead', $debugbarRenderer->renderHead())
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
            ->assign('shopFaviconURL', $this->getFaviconURL($shopURL))
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('lang', Shop::getLanguageCode())
            ->assign('ShopHomeURL', $this->getHomeURL($shopURL))
            ->assign('ShopURLSSL', Shop::getURL(true))
            ->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('isAjax', Request::isAjaxRequest());
        $tplService->save();
    }

    /**
     * @param string $baseURL
     * @return string
     */
    public function getHomeURL(string $baseURL): string
    {
        $homeURL = $baseURL . '/';
        try {
            if (!LanguageHelper::isDefaultLanguageActive()) {
                $homeURL = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE)?->getURL();
            }
        } catch (SpecialPageNotFoundException $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }

        return $homeURL;
    }

    /**
     * @param string $baseURL
     * @return string
     */
    protected function getFaviconURL(string $baseURL): string
    {
        $templateDir      = $this->smarty->getTemplateDir($this->smarty->context);
        $shopTemplatePath = $this->smarty->getTemplateUrlPath();
        $faviconUrl       = $baseURL . '/';
        if (\file_exists($templateDir . 'themes/base/images/favicon.ico')) {
            $faviconUrl .= $shopTemplatePath . 'themes/base/images/favicon.ico';
        } elseif (\file_exists($templateDir . 'favicon.ico')) {
            $faviconUrl .= $shopTemplatePath . 'favicon.ico';
        } elseif (\file_exists(\PFAD_ROOT . 'favicon.ico')) {
            $faviconUrl .= 'favicon.ico';
        } else {
            $faviconUrl .= 'favicon-default.ico';
        }

        return $faviconUrl;
    }

    /**
     * @param LinkInterface $link
     * @return void
     */
    protected function assignMetaData(LinkInterface $link): void
    {
        $maxLength       = (int)$this->config['metaangaben']['global_meta_maxlaenge_title'];
        $metaTitle       = Metadata::prepareMeta($this->metaTitle ?? $link->getMetaTitle(), null, $maxLength);
        $metaDescription = $this->metaDescription ?? $link->getMetaDescription();
        $metaKeywords    = $this->metaKeywords ?? $link->getMetaKeyword();
        if ($this->currentProduct !== null) {
            $metaTitle       = $this->currentProduct->getMetaTitle();
            $metaDescription = $this->currentProduct->getMetaDescription($this->expandedCategories);
            $metaKeywords    = $this->currentProduct->getMetaKeywords();
        }
        $globalMetaData = Metadata::getGlobalMetaData()[$this->languageID] ?? null;
        if (empty($metaTitle)) {
            $metaTitle = Metadata::prepareMeta($globalMetaData->Title ?? '', null, $maxLength);
        }
        if (empty($metaDescription)) {
            $metaDescription = $globalMetaData->Meta_Description ?? null;
        }
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

    /**
     * @param int $languageID
     * @return string
     */
    protected function getLocaleFromLanguageID(int $languageID): string
    {
        foreach (LanguageHelper::getAllLanguages() as $languageModel) {
            if ($languageID === $languageModel->getId()) {
                return $languageModel->getIso639();
            }
        }

        return 'de';
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
    }
}
