<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Backend\AdminAccount;
use JTL\Backend\Menu;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Router\Controller\Backend\ActivationController;
use JTL\Router\Controller\Backend\AdminAccountController;
use JTL\Router\Controller\Backend\BannerController;
use JTL\Router\Controller\Backend\BoxController;
use JTL\Router\Controller\Backend\BrandingController;
use JTL\Router\Controller\Backend\CacheController;
use JTL\Router\Controller\Backend\CampaignController;
use JTL\Router\Controller\Backend\CategoryCheckController;
use JTL\Router\Controller\Backend\CheckboxController;
use JTL\Router\Controller\Backend\CodeController;
use JTL\Router\Controller\Backend\ComparelistController;
use JTL\Router\Controller\Backend\ConfigController;
use JTL\Router\Controller\Backend\ConsentController;
use JTL\Router\Controller\Backend\ContactFormsController;
use JTL\Router\Controller\Backend\CountryController;
use JTL\Router\Controller\Backend\CouponsController;
use JTL\Router\Controller\Backend\CouponStatsController;
use JTL\Router\Controller\Backend\CronController;
use JTL\Router\Controller\Backend\CustomerFieldsController;
use JTL\Router\Controller\Backend\CustomerImportController;
use JTL\Router\Controller\Backend\DashboardController;
use JTL\Router\Controller\Backend\DBCheckController;
use JTL\Router\Controller\Backend\DBManagerController;
use JTL\Router\Controller\Backend\DBUpdateController;
use JTL\Router\Controller\Backend\ElfinderController;
use JTL\Router\Controller\Backend\EmailBlocklistController;
use JTL\Router\Controller\Backend\EmailHistoryController;
use JTL\Router\Controller\Backend\EmailTemplateController;
use JTL\Router\Controller\Backend\ExportController;
use JTL\Router\Controller\Backend\ExportQueueController;
use JTL\Router\Controller\Backend\ExportStarterController;
use JTL\Router\Controller\Backend\FavsController;
use JTL\Router\Controller\Backend\FileCheckController;
use JTL\Router\Controller\Backend\FilesystemController;
use JTL\Router\Controller\Backend\GiftsController;
use JTL\Router\Controller\Backend\GlobalMetaDataController;
use JTL\Router\Controller\Backend\ImageManagementController;
use JTL\Router\Controller\Backend\ImagesController;
use JTL\Router\Controller\Backend\IOController;
use JTL\Router\Controller\Backend\LanguageController;
use JTL\Router\Controller\Backend\LicenseController;
use JTL\Router\Controller\Backend\LinkController;
use JTL\Router\Controller\Backend\LivesearchController;
use JTL\Router\Controller\Backend\LogoController;
use JTL\Router\Controller\Backend\LogoutController;
use JTL\Router\Controller\Backend\MarkdownController;
use JTL\Router\Controller\Backend\NavFilterController;
use JTL\Router\Controller\Backend\NewsController;
use JTL\Router\Controller\Backend\NewsletterController;
use JTL\Router\Controller\Backend\NewsletterImportController;
use JTL\Router\Controller\Backend\OPCCCController;
use JTL\Router\Controller\Backend\OPCController;
use JTL\Router\Controller\Backend\OrderController;
use JTL\Router\Controller\Backend\PackagingsController;
use JTL\Router\Controller\Backend\PasswordController;
use JTL\Router\Controller\Backend\PaymentMethodsController;
use JTL\Router\Controller\Backend\PermissionCheckController;
use JTL\Router\Controller\Backend\PersistentCartController;
use JTL\Router\Controller\Backend\PluginController;
use JTL\Router\Controller\Backend\PluginManagerController;
use JTL\Router\Controller\Backend\PremiumPluginController;
use JTL\Router\Controller\Backend\PriceHistoryController;
use JTL\Router\Controller\Backend\ProfilerController;
use JTL\Router\Controller\Backend\RedirectController;
use JTL\Router\Controller\Backend\ResetController;
use JTL\Router\Controller\Backend\ReviewController;
use JTL\Router\Controller\Backend\RSSController;
use JTL\Router\Controller\Backend\SearchConfigController;
use JTL\Router\Controller\Backend\SearchController;
use JTL\Router\Controller\Backend\SearchSpecialController;
use JTL\Router\Controller\Backend\SearchSpecialOverlayController;
use JTL\Router\Controller\Backend\SelectionWizardController;
use JTL\Router\Controller\Backend\SeparatorController;
use JTL\Router\Controller\Backend\ShippingMethodsController;
use JTL\Router\Controller\Backend\SitemapController;
use JTL\Router\Controller\Backend\SitemapExportController;
use JTL\Router\Controller\Backend\SliderController;
use JTL\Router\Controller\Backend\StatsController;
use JTL\Router\Controller\Backend\StatusController;
use JTL\Router\Controller\Backend\StatusMailController;
use JTL\Router\Controller\Backend\SyncController;
use JTL\Router\Controller\Backend\SystemCheckController;
use JTL\Router\Controller\Backend\SystemLogController;
use JTL\Router\Controller\Backend\TaCController;
use JTL\Router\Controller\Backend\TemplateController;
use JTL\Router\Controller\Backend\WarehousesController;
use JTL\Router\Controller\Backend\WishlistController;
use JTL\Router\Controller\Backend\WizardController;
use JTL\Router\Controller\Backend\ZipImportController;
use JTL\Router\Middleware\AuthMiddleware;
use JTL\Router\Middleware\RevisionMiddleware;
use JTL\Router\Middleware\UpdateCheckMiddleware;
use JTL\Router\Middleware\WizardCheckMiddleware;
use JTL\Router\Strategy\SmartyStrategy;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Exception\EmitterException;
use League\Container\Container;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteGroup;
use League\Route\Router;

/**
 * Class BackendRouter
 * @package JTL\Router
 */
class BackendRouter
{
    public const ROUTE_TAC                   = 'tac';
    public const ROUTE_FAVS                  = 'favs';
    public const ROUTE_PAYMENT_METHODS       = 'paymentmethods';
    public const ROUTE_SELECTION_WIZARD      = 'selectionwizard';
    public const ROUTE_BANNER                = 'banner';
    public const ROUTE_ORDERS                = 'orders';
    public const ROUTE_IMAGES                = 'images';
    public const ROUTE_PACKAGINGS            = 'packagings';
    public const ROUTE_CONTACT_FORMS         = 'contactforms';
    public const ROUTE_SYNC                  = 'sync';
    public const ROUTE_SHIPPING_METHODS      = 'shippingmethods';
    public const ROUTE_COMPARELIST           = 'comparelist';
    public const ROUTE_SYSTEMLOG             = 'systemlog';
    public const ROUTE_SYSTEMCHECK           = 'systemcheck';
    public const ROUTE_STATUSMAIL            = 'statusmail';
    public const ROUTE_SEARCHSPECIAL         = 'searchspecials';
    public const ROUTE_SEARCHSPECIALOVERLAYS = 'searchspecialoverlays';
    public const ROUTE_STATUS                = 'status';
    public const ROUTE_STATS                 = 'stats';
    public const ROUTE_LANGUAGE              = 'language';
    public const ROUTE_RESET                 = 'reset';
    public const ROUTE_SITEMAP               = 'sitemap';
    public const ROUTE_LOGO                  = 'logo';
    public const ROUTE_RSS                   = 'rss';
    public const ROUTE_META                  = 'meta';
    public const ROUTE_PROFILER              = 'profiler';
    public const ROUTE_PRICEHISTORY          = 'pricehistory';
    public const ROUTE_PERMISSIONCHECK       = 'permissioncheck';
    public const ROUTE_SLIDERS               = 'sliders';
    public const ROUTE_CUSTOMERFIELDS        = 'customerfields';
    public const ROUTE_COUPONS               = 'coupons';
    public const ROUTE_FILESYSTEM            = 'filesystem';
    public const ROUTE_DBCHECK               = 'dbcheck';
    public const ROUTE_CATEGORYCHECK         = 'categorycheck';
    public const ROUTE_USERS                 = 'users';
    public const ROUTE_REVIEWS               = 'reviews';
    public const ROUTE_IMAGE_MANAGEMENT      = 'imagemanagement';
    public const ROUTE_BOXES                 = 'boxes';
    public const ROUTE_BRANDING              = 'branding';
    public const ROUTE_CACHE                 = 'cache';
    public const ROUTE_COUNTRIES             = 'countries';
    public const ROUTE_DBMANAGER             = 'dbmanager';
    public const ROUTE_DBUPDATER             = 'dbupdater';
    public const ROUTE_EMAILBLOCKLIST        = 'emailblocklist';
    public const ROUTE_ACTIVATE              = 'activate';
    public const ROUTE_LINKS                 = 'links';
    public const ROUTE_EMAILHISTORY          = 'emailhistory';
    public const ROUTE_EMAILTEMPLATES        = 'emailtemplates';
    public const ROUTE_CRON                  = 'cron';
    public const ROUTE_CHECKBOX              = 'checkbox';
    public const ROUTE_NEWS                  = 'news';
    public const ROUTE_REDIRECT              = 'redirect';
    public const ROUTE_WAREHOUSES            = 'warehouses';
    public const ROUTE_PASS                  = 'pass';
    public const ROUTE_DASHBOARD             = 'dashboard';
    public const ROUTE_SEPARATOR             = 'separator';
    public const ROUTE_CONSENT               = 'consent';
    public const ROUTE_EXPORT                = 'export';
    public const ROUTE_EXPORT_START          = 'startexport';
    public const ROUTE_FILECHECK             = 'filecheck';
    public const ROUTE_GIFTS                 = 'gifts';
    public const ROUTE_CAMPAIGN              = 'campaign';
    public const ROUTE_CUSTOMER_IMPORT       = 'customerimport';
    public const ROUTE_COUPON_STATS          = 'couponstats';
    public const ROUTE_LICENSE               = 'licenses';
    public const ROUTE_LOGOUT                = 'logout';
    public const ROUTE_NAVFILTER             = 'navfilter';
    public const ROUTE_NEWSLETTER            = 'newsletter';
    public const ROUTE_NEWSLETTER_IMPORT     = 'newsletterimport';
    public const ROUTE_OPC                   = 'onpagecomposer';
    public const ROUTE_OPCCC                 = 'onpagecomposercc';
    public const ROUTE_ZIP_IMPORT            = 'zipimport';
    public const ROUTE_TEMPLATE              = 'template';
    public const ROUTE_SITEMAP_EXPORT        = 'sitemapexport';
    public const ROUTE_PERSISTENT_CART       = 'persistentcart';
    public const ROUTE_WIZARD                = 'wizard';
    public const ROUTE_WISHLIST              = 'wishlist';
    public const ROUTE_LIVESEARCH            = 'livesearch';
    public const ROUTE_PLUGIN_MANAGER        = 'pluginmanager';
    public const ROUTE_CONFIG                = 'config';
    public const ROUTE_MARKDOWN              = 'markdown';
    public const ROUTE_EXPORT_QUEUE          = 'exportqueue';
    public const ROUTE_PLUGIN                = 'plugin';
    public const ROUTE_PREMIUM_PLUGIN        = 'premiumplugin';
    public const ROUTE_SEARCHCONFIG          = 'searchconfig';
    public const ROUTE_IO                    = 'io';
    public const ROUTE_SEARCHRESULTS         = 'searchresults';
    public const ROUTE_ELFINDER              = 'elfinder';
    public const ROUTE_CODE                  = 'code';

    /**
     * @var Router
     */
    private Router $router;

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AdminAccount          $account
     * @param AlertServiceInterface $alertService
     * @param GetText               $getText
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected AdminAccount $account,
        protected AlertServiceInterface $alertService,
        protected GetText $getText,
        protected JTLSmarty $smarty
    ) {
        $this->router = new Router();
        $strategy     = new SmartyStrategy(new ResponseFactory(), $smarty, new State());
        $container    = new Container();

        $controllers = [
            self::ROUTE_BANNER                => BannerController::class,
            self::ROUTE_ORDERS                => OrderController::class,
            self::ROUTE_IMAGES                => ImagesController::class,
            self::ROUTE_PACKAGINGS            => PackagingsController::class,
            self::ROUTE_CONTACT_FORMS         => ContactFormsController::class,
            self::ROUTE_SYNC                  => SyncController::class,
            self::ROUTE_SHIPPING_METHODS      => ShippingMethodsController::class,
            self::ROUTE_COMPARELIST           => ComparelistController::class,
            self::ROUTE_SYSTEMLOG             => SystemLogController::class,
            self::ROUTE_SYSTEMCHECK           => SystemCheckController::class,
            self::ROUTE_STATUSMAIL            => StatusMailController::class,
            self::ROUTE_SEARCHSPECIAL         => SearchSpecialController::class,
            self::ROUTE_SEARCHSPECIALOVERLAYS => SearchSpecialOverlayController::class,
            self::ROUTE_STATUS                => StatusController::class,
            self::ROUTE_STATS                 => StatsController::class,
            self::ROUTE_LANGUAGE              => LanguageController::class,
            self::ROUTE_SITEMAP               => SitemapController::class,
            self::ROUTE_LOGO                  => LogoController::class,
            self::ROUTE_RSS                   => RSSController::class,
            self::ROUTE_META                  => GlobalMetaDataController::class,
            self::ROUTE_PROFILER              => ProfilerController::class,
            self::ROUTE_PRICEHISTORY          => PriceHistoryController::class,
            self::ROUTE_PERMISSIONCHECK       => PermissionCheckController::class,
            self::ROUTE_PASS                  => PasswordController::class,
            self::ROUTE_CUSTOMERFIELDS        => CustomerFieldsController::class,
            self::ROUTE_COUPONS               => CouponsController::class,
            self::ROUTE_FILESYSTEM            => FilesystemController::class,
            self::ROUTE_DBCHECK               => DBCheckController::class,
            self::ROUTE_CATEGORYCHECK         => CategoryCheckController::class,
            self::ROUTE_USERS                 => AdminAccountController::class,
            self::ROUTE_REVIEWS               => ReviewController::class,
            self::ROUTE_SLIDERS               => SliderController::class,
            self::ROUTE_IMAGE_MANAGEMENT      => ImageManagementController::class,
            self::ROUTE_BOXES                 => BoxController::class,
            self::ROUTE_BRANDING              => BrandingController::class,
            self::ROUTE_CACHE                 => CacheController::class,
            self::ROUTE_CHECKBOX              => CheckboxController::class,
            self::ROUTE_COUNTRIES             => CountryController::class,
            self::ROUTE_DBMANAGER             => DBManagerController::class,
            self::ROUTE_DBUPDATER             => DBUpdateController::class,
            self::ROUTE_EMAILBLOCKLIST        => EmailBlocklistController::class,
            self::ROUTE_ACTIVATE              => ActivationController::class,
            self::ROUTE_LINKS                 => LinkController::class,
            self::ROUTE_EMAILHISTORY          => EmailHistoryController::class,
            self::ROUTE_EMAILTEMPLATES        => EmailTemplateController::class,
            self::ROUTE_CRON                  => CronController::class,
            self::ROUTE_NEWS                  => NewsController::class,
            self::ROUTE_PAYMENT_METHODS       => PaymentMethodsController::class,
            self::ROUTE_REDIRECT              => RedirectController::class,
            self::ROUTE_FAVS                  => FavsController::class,
            self::ROUTE_WAREHOUSES            => WarehousesController::class,
            self::ROUTE_DASHBOARD             => DashboardController::class,
            self::ROUTE_SELECTION_WIZARD      => SelectionWizardController::class,
            self::ROUTE_TAC                   => TaCController::class,
            self::ROUTE_RESET                 => ResetController::class,
            self::ROUTE_SEPARATOR             => SeparatorController::class,
            self::ROUTE_CONSENT               => ConsentController::class,
            self::ROUTE_EXPORT                => ExportController::class,
            self::ROUTE_EXPORT_START          => ExportStarterController::class,
            self::ROUTE_FILECHECK             => FileCheckController::class,
            self::ROUTE_GIFTS                 => GiftsController::class,
            self::ROUTE_CAMPAIGN              => CampaignController::class,
            self::ROUTE_CUSTOMER_IMPORT       => CustomerImportController::class,
            self::ROUTE_COUPON_STATS          => CouponStatsController::class,
            self::ROUTE_LICENSE               => LicenseController::class,
            self::ROUTE_LOGOUT                => LogoutController::class,
            self::ROUTE_NAVFILTER             => NavFilterController::class,
            self::ROUTE_NEWSLETTER            => NewsletterController::class,
            self::ROUTE_NEWSLETTER_IMPORT     => NewsletterImportController::class,
            self::ROUTE_OPC                   => OPCController::class,
            self::ROUTE_OPCCC                 => OPCCCController::class,
            self::ROUTE_ZIP_IMPORT            => ZipImportController::class,
            self::ROUTE_TEMPLATE              => TemplateController::class,
            self::ROUTE_SITEMAP_EXPORT        => SitemapExportController::class,
            self::ROUTE_PERSISTENT_CART       => PersistentCartController::class,
            self::ROUTE_WIZARD                => WizardController::class,
            self::ROUTE_WISHLIST              => WishlistController::class,
            self::ROUTE_LIVESEARCH            => LivesearchController::class,
            self::ROUTE_PLUGIN_MANAGER        => PluginManagerController::class,
            self::ROUTE_CONFIG                => ConfigController::class,
            self::ROUTE_MARKDOWN              => MarkdownController::class,
            self::ROUTE_EXPORT_QUEUE          => ExportQueueController::class,
            self::ROUTE_PLUGIN . '/{id}'      => PluginController::class,
            self::ROUTE_PREMIUM_PLUGIN        => PremiumPluginController::class,
            self::ROUTE_SEARCHCONFIG          => SearchConfigController::class,
            self::ROUTE_IO                    => IOController::class,
            self::ROUTE_SEARCHRESULTS         => SearchController::class,
            self::ROUTE_ELFINDER              => ElfinderController::class,
            self::ROUTE_CODE                  => CodeController::class,

        ];
        foreach ($controllers as $route => $controller) {
            $container->add($controller, function () use (
                $controller,
                $db,
                $cache,
                $alertService,
                $account,
                $getText,
                $route
            ) {
                $controller = new $controller($db, $cache, $alertService, $account, $getText);
                $controller->setRoute('/' . $route);

                return $controller;
            });
        }
        $strategy->setContainer($container);
        $this->router->setStrategy($strategy);
        $updateCheckMiddleWare = new UpdateCheckMiddleware($db, $account);

        $this->router->group('/' . \rtrim(\PFAD_ADMIN, '/'), function (RouteGroup $route) use ($controllers) {
            $revisionMiddleware = new RevisionMiddleware($this->db);
            foreach ($controllers as $slug => $controller) {
                if ($slug === self::ROUTE_PASS || $slug === self::ROUTE_DASHBOARD || $slug === self::ROUTE_CODE) {
                    continue;
                }
                $route->get('/' . $slug, $controller . '::getResponse')->setName($slug);
                $route->post('/' . $slug, $controller . '::getResponse')
                    ->middleware($revisionMiddleware)
                    ->setName('post' . $slug);
            }
        })->middleware(new AuthMiddleware($account))
            ->middleware($updateCheckMiddleWare)
            ->middleware(new WizardCheckMiddleware($this->db));

        $this->router->get('/' . \PFAD_ADMIN . self::ROUTE_PASS, PasswordController::class . '::getResponse')
            ->setName(self::ROUTE_PASS);
        $this->router->post('/' . \PFAD_ADMIN . self::ROUTE_PASS, PasswordController::class . '::getResponse')
            ->setName('post' . self::ROUTE_PASS);

        $this->router->get('/' . \PFAD_ADMIN . self::ROUTE_CODE . '/{redir}', CodeController::class . '::getResponse')
            ->setName(self::ROUTE_CODE);
        $this->router->post('/' . \PFAD_ADMIN . self::ROUTE_CODE . '/{redir}', CodeController::class . '::getResponse')
            ->setName('post' . self::ROUTE_CODE);

        $this->router->get('/' . \PFAD_ADMIN, DashboardController::class . '::getResponse')
            ->setName(self::ROUTE_DASHBOARD)
            ->middleware($updateCheckMiddleWare);
        $this->router->post('/' . \PFAD_ADMIN, DashboardController::class . '::getResponse')
            ->setName('post' . self::ROUTE_DASHBOARD)
            ->middleware($updateCheckMiddleWare);
    }

    public function dispatch(): void
    {
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $menu    = new Menu($this->db, $this->account, $this->getText);
        $data    = $menu->build($request);
        $this->smarty->assign('oLinkOberGruppe_arr', $data);
        try {
            $response = $this->router->dispatch($request);
        } catch (NotFoundException) {
            $response = (new Response())->withStatus(404);
        }
        try {
            (new SapiEmitter())->emit($response);
        } catch (EmitterException) {
            echo $response->getBody();
        }
        exit();
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
