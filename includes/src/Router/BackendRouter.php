<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Backend\AdminAccount;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Router\Controller\Backend\ActivationController;
use JTL\Router\Controller\Backend\AdminAccountController;
use JTL\Router\Controller\Backend\BannerController;
use JTL\Router\Controller\Backend\BoxController;
use JTL\Router\Controller\Backend\BrandingController;
use JTL\Router\Controller\Backend\CacheController;
use JTL\Router\Controller\Backend\CategoryCheckController;
use JTL\Router\Controller\Backend\CheckboxController;
use JTL\Router\Controller\Backend\ComparelistController;
use JTL\Router\Controller\Backend\ContactFormsController;
use JTL\Router\Controller\Backend\CountryController;
use JTL\Router\Controller\Backend\CouponsController;
use JTL\Router\Controller\Backend\CronController;
use JTL\Router\Controller\Backend\CustomerFieldsController;
use JTL\Router\Controller\Backend\DashboardController;
use JTL\Router\Controller\Backend\DBCheckController;
use JTL\Router\Controller\Backend\DBManagerController;
use JTL\Router\Controller\Backend\DBUpdateController;
use JTL\Router\Controller\Backend\EmailBlocklistController;
use JTL\Router\Controller\Backend\EmailHistoryController;
use JTL\Router\Controller\Backend\EmailTemplateController;
use JTL\Router\Controller\Backend\FavsController;
use JTL\Router\Controller\Backend\FilesystemController;
use JTL\Router\Controller\Backend\GlobalMetaDataController;
use JTL\Router\Controller\Backend\ImageManagementController;
use JTL\Router\Controller\Backend\ImagesController;
use JTL\Router\Controller\Backend\LanguageController;
use JTL\Router\Controller\Backend\LinkController;
use JTL\Router\Controller\Backend\LogoController;
use JTL\Router\Controller\Backend\NewsController;
use JTL\Router\Controller\Backend\OrderController;
use JTL\Router\Controller\Backend\PackagingsController;
use JTL\Router\Controller\Backend\PasswordController;
use JTL\Router\Controller\Backend\PaymentMethodsController;
use JTL\Router\Controller\Backend\PermissionCheckController;
use JTL\Router\Controller\Backend\PriceHistoryController;
use JTL\Router\Controller\Backend\ProfilerController;
use JTL\Router\Controller\Backend\RedirectController;
use JTL\Router\Controller\Backend\ResetController;
use JTL\Router\Controller\Backend\ReviewController;
use JTL\Router\Controller\Backend\RSSController;
use JTL\Router\Controller\Backend\SearchSpecialOverlayController;
use JTL\Router\Controller\Backend\SelectionWizardController;
use JTL\Router\Controller\Backend\SeparatorController;
use JTL\Router\Controller\Backend\ShippingMethodsController;
use JTL\Router\Controller\Backend\SitemapController;
use JTL\Router\Controller\Backend\SliderController;
use JTL\Router\Controller\Backend\StatsController;
use JTL\Router\Controller\Backend\StatusController;
use JTL\Router\Controller\Backend\StatusMailController;
use JTL\Router\Controller\Backend\SyncController;
use JTL\Router\Controller\Backend\SystemCheckController;
use JTL\Router\Controller\Backend\SystemLogController;
use JTL\Router\Controller\Backend\TaCController;
use JTL\Router\Controller\Backend\WarehousesController;
use JTL\Router\Middleware\AuthMiddleware;
use JTL\Router\Strategy\SmartyStrategy;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
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
    public const ROUTE_SEARCHSPECIALOVERLAYS = 'searchspecials';
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
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AdminAccount $account,
        AlertServiceInterface $alertService,
        GetText $getText
    ) {
        $authMiddleware = new AuthMiddleware();
        $this->router   = new Router();
        $strategy       = new SmartyStrategy(new ResponseFactory(), Shop::Smarty(), new State());
        $container      = new Container();

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
        ];
        foreach ($controllers as $route => $controller) {
            $container->add($controller, function () use ($controller, $db, $cache, $alertService, $account, $getText, $route) {
                $controller = new $controller($db, $cache, $alertService, $account, $getText);
                $controller->setRoute('/' . $route);

                return $controller;
            });
        }
        $strategy->setContainer($container);
        $this->router->setStrategy($strategy);

        $this->router->group('/' . \rtrim(\PFAD_ADMIN, '/'), function (RouteGroup $route) use ($controllers) {
            foreach ($controllers as $slug => $controller) {
                if ($slug === self::ROUTE_PASS || $slug === self::ROUTE_DASHBOARD) {
                    continue;
                }
                $route->get('/' . $slug, $controller . '::getResponse')->setName($slug);
                $route->post('/' . $slug, $controller . '::getResponse')->setName('post' . $slug);
            }
        })->middleware($authMiddleware);

        $this->router->get('/' . \PFAD_ADMIN . self::ROUTE_PASS, PasswordController::class . '::getResponse')
            ->setName(self::ROUTE_PASS);
        $this->router->post('/' . \PFAD_ADMIN . self::ROUTE_PASS, PasswordController::class . '::getResponse')
            ->setName('post' . self::ROUTE_PASS);

        $this->router->get('/' . \PFAD_ADMIN, DashboardController::class . '::getResponse')
            ->setName(self::ROUTE_DASHBOARD);
        $this->router->post('/' . \PFAD_ADMIN, DashboardController::class . '::getResponse')
            ->setName('post' . self::ROUTE_DASHBOARD);
    }

    public function dispatch(): void
    {
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        (new SapiEmitter())->emit($this->router->dispatch($request));
        exit();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
