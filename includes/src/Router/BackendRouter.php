<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Backend\AdminAccount;
use JTL\Backend\Menu;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Exceptions\PermissionException;
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
use JTL\Router\Controller\Backend\LocalizationController;
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
        protected DbInterface           $db,
        protected JTLCacheInterface     $cache,
        protected AdminAccount          $account,
        protected AlertServiceInterface $alertService,
        protected GetText               $getText,
        protected JTLSmarty             $smarty
    ) {
        $this->router = new Router();
        $strategy     = new SmartyStrategy(new ResponseFactory(), $smarty, new State());
        $container    = new Container();
        $controllers  = [
            Route::BANNER                => BannerController::class,
            Route::ORDERS                => OrderController::class,
            Route::IMAGES                => ImagesController::class,
            Route::PACKAGINGS            => PackagingsController::class,
            Route::CONTACT_FORMS         => ContactFormsController::class,
            Route::SYNC                  => SyncController::class,
            Route::SHIPPING_METHODS      => ShippingMethodsController::class,
            Route::COMPARELIST           => ComparelistController::class,
            Route::SYSTEMLOG             => SystemLogController::class,
            Route::SYSTEMCHECK           => SystemCheckController::class,
            Route::STATUSMAIL            => StatusMailController::class,
            Route::SEARCHSPECIAL         => SearchSpecialController::class,
            Route::SEARCHSPECIALOVERLAYS => SearchSpecialOverlayController::class,
            Route::STATUS                => StatusController::class,
            Route::STATS . '[/{id}]'     => StatsController::class,
            Route::LANGUAGE              => LanguageController::class,
            Route::SITEMAP               => SitemapController::class,
            Route::LOGO                  => LogoController::class,
            Route::RSS                   => RSSController::class,
            Route::META                  => GlobalMetaDataController::class,
            Route::PROFILER              => ProfilerController::class,
            Route::PRICEHISTORY          => PriceHistoryController::class,
            Route::PERMISSIONCHECK       => PermissionCheckController::class,
            Route::PASS                  => PasswordController::class,
            Route::CUSTOMERFIELDS        => CustomerFieldsController::class,
            Route::COUPONS               => CouponsController::class,
            Route::FILESYSTEM            => FilesystemController::class,
            Route::DBCHECK               => DBCheckController::class,
            Route::CATEGORYCHECK         => CategoryCheckController::class,
            Route::USERS                 => AdminAccountController::class,
            Route::REVIEWS               => ReviewController::class,
            Route::SLIDERS               => SliderController::class,
            Route::IMAGE_MANAGEMENT      => ImageManagementController::class,
            Route::BOXES                 => BoxController::class,
            Route::BRANDING . '[/{id}]'  => BrandingController::class,
            Route::CACHE                 => CacheController::class,
            Route::CHECKBOX              => CheckboxController::class,
            Route::COUNTRIES             => CountryController::class,
            Route::DBMANAGER             => DBManagerController::class,
            Route::DBUPDATER             => DBUpdateController::class,
            Route::EMAILBLOCKLIST        => EmailBlocklistController::class,
            Route::ACTIVATE              => ActivationController::class,
            Route::LINKS                 => LinkController::class,
            Route::EMAILHISTORY          => EmailHistoryController::class,
            Route::EMAILTEMPLATES        => EmailTemplateController::class,
            Route::CRON                  => CronController::class,
            Route::NEWS                  => NewsController::class,
            Route::PAYMENT_METHODS       => PaymentMethodsController::class,
            Route::REDIRECT              => RedirectController::class,
            Route::FAVS                  => FavsController::class,
            Route::WAREHOUSES            => WarehousesController::class,
            Route::DASHBOARD             => DashboardController::class,
            Route::SELECTION_WIZARD      => SelectionWizardController::class,
            Route::TAC                   => TaCController::class,
            Route::RESET                 => ResetController::class,
            Route::SEPARATOR             => SeparatorController::class,
            Route::CONSENT               => ConsentController::class,
            Route::EXPORT                => ExportController::class,
            Route::EXPORT_START          => ExportStarterController::class,
            Route::FILECHECK             => FileCheckController::class,
            Route::GIFTS                 => GiftsController::class,
            Route::CAMPAIGN              => CampaignController::class,
            Route::CUSTOMER_IMPORT       => CustomerImportController::class,
            Route::COUPON_STATS          => CouponStatsController::class,
            Route::LICENSE               => LicenseController::class,
            Route::LOGOUT                => LogoutController::class,
            Route::NAVFILTER             => NavFilterController::class,
            Route::NEWSLETTER            => NewsletterController::class,
            Route::NEWSLETTER_IMPORT     => NewsletterImportController::class,
            Route::OPC                   => OPCController::class,
            Route::OPCCC                 => OPCCCController::class,
            Route::ZIP_IMPORT            => ZipImportController::class,
            Route::TEMPLATE              => TemplateController::class,
            Route::SITEMAP_EXPORT        => SitemapExportController::class,
            Route::PERSISTENT_CART       => PersistentCartController::class,
            Route::WIZARD                => WizardController::class,
            Route::WISHLIST              => WishlistController::class,
            Route::LIVESEARCH            => LivesearchController::class,
            Route::PLUGIN_MANAGER        => PluginManagerController::class,
            Route::CONFIG . '[/{id}]'    => ConfigController::class,
            Route::MARKDOWN              => MarkdownController::class,
            Route::EXPORT_QUEUE          => ExportQueueController::class,
            Route::PLUGIN . '/{id}'      => PluginController::class,
            Route::PREMIUM_PLUGIN        => PremiumPluginController::class,
            Route::SEARCHCONFIG          => SearchConfigController::class,
            Route::IO                    => IOController::class,
            Route::SEARCHRESULTS         => SearchController::class,
            Route::ELFINDER              => ElfinderController::class,
            Route::CODE                  => CodeController::class,
            Route::LOCALIZATION_CHECK    => LocalizationController::class,
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

        $basePath = (\parse_url(\URL_SHOP, \PHP_URL_PATH) ?? '') . '/' . \PFAD_ADMIN;
        $this->router->group(\rtrim($basePath, '/'), function (RouteGroup $route) use ($controllers) {
            $revisionMiddleware = new RevisionMiddleware($this->db);
            foreach ($controllers as $slug => $controller) {
                if ($slug === Route::PASS || $slug === Route::DASHBOARD || $slug === Route::CODE) {
                    continue;
                }
                $route->get('/' . $slug, $controller . '::getResponse')->setName($slug);
                $route->post('/' . $slug, $controller . '::getResponse')
                    ->middleware($revisionMiddleware)
                    ->setName($slug . 'POST');
            }
        })->middleware(new AuthMiddleware($account))
            ->middleware($updateCheckMiddleWare)
            ->middleware(new WizardCheckMiddleware($this->db));
        $this->router->get($basePath . Route::PASS, PasswordController::class . '::getResponse')
            ->setName(Route::PASS);
        $this->router->post($basePath . Route::PASS, PasswordController::class . '::getResponse')
            ->setName(Route::PASS . 'POST');

        $this->router->get($basePath . Route::CODE . '/{redir}', CodeController::class . '::getResponse')
            ->setName(Route::CODE);
        $this->router->post($basePath . Route::CODE . '/{redir}', CodeController::class . '::getResponse')
            ->setName(Route::CODE . 'POST');

        $this->router->get($basePath . 'index.php', DashboardController::class . '::getResponse')
            ->setName(Route::DASHBOARD . 'php')
            ->middleware($updateCheckMiddleWare);
        $this->router->get($basePath, DashboardController::class . '::getResponse')
            ->setName(Route::DASHBOARD)
            ->middleware($updateCheckMiddleWare)
            ->middleware(new WizardCheckMiddleware($this->db));
        $this->router->post($basePath, DashboardController::class . '::getResponse')
            ->setName(Route::DASHBOARD . 'POST')
            ->middleware($updateCheckMiddleWare);
        $this->router->post($basePath . 'index.php', DashboardController::class . '::getResponse')
            ->setName(Route::DASHBOARD . 'POSTphp')
            ->middleware($updateCheckMiddleWare);
    }

    /**
     * @return void
     */
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
        } catch (PermissionException) {
            $response = $this->smarty->getResponse('tpl_inc/berechtigung.tpl');
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
