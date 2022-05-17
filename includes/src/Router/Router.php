<?php declare(strict_types=1);

namespace JTL\Router;

use FastRoute\Dispatcher;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher as CoreDispatcher;
use JTL\Events\Event;
use JTL\Language\LanguageHelper;
use JTL\Router\Controller\CategoryController;
use JTL\Router\Controller\ConsentController;
use JTL\Router\Controller\DefaultController;
use JTL\Router\Controller\IOController;
use JTL\Router\Controller\ManufacturerController;
use JTL\Router\Controller\NewsController;
use JTL\Router\Controller\NewsHandler;
use JTL\Router\Controller\PageController;
use JTL\Router\Controller\PageHandler;
use JTL\Router\Controller\ProductController;
use JTL\Router\Controller\ProductHandler;
use JTL\Router\Controller\RootController;
use JTL\Router\Middleware\CartcheckMiddleware;
use JTL\Router\Middleware\MaintenanceModeMiddleware;
use JTL\Router\Middleware\PhpFileCheckMiddleware;
use JTL\Router\Middleware\VisibilityMiddleware;
use JTL\Router\Middleware\WishlistCheckMiddleware;
use JTL\Router\Strategy\SmartyStrategy;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Exception\EmitterException;
use League\Route\Route;
use League\Route\RouteGroup;
use League\Route\Router as BaseRouter;
use League\Route\Strategy\JsonStrategy;

/**
 * Class Router
 * @package JTL\Router
 */
class Router
{
    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * @var string
     */
    private string $uri = '';

    /**
     * @var BaseRouter
     */
    private BaseRouter $router;

    /**
     * @param DbInterface $db
     * @param             $cache
     * @param State       $state
     */
    public function __construct(protected DbInterface $db, protected $cache, protected State $state)
    {
        $cgid                   = Frontend::getCustomer()->getGroupID();
        $conf                   = Shopsetting::getInstance()->getAll();
        $alert                  = Shop::Container()->getAlertService();
        $productController      = new ProductController($db, $cache, $state, $cgid, $conf, $alert);
        $categoryController     = new CategoryController($db, $cache, $state, $cgid, $conf, $alert);
        $manufacturerController = new ManufacturerController($db, $cache, $state, $cgid, $conf, $alert);
        $newsController         = new NewsController($db, $cache, $state, $cgid, $conf, $alert);
        $pageController         = new PageController($db, $cache, $state, $cgid, $conf, $alert);
        $defaultController      = new DefaultController($db, $cache, $state, $cgid, $conf, $alert);
        $rootController         = new RootController($db, $cache, $state, $cgid, $conf, $alert);
        $consentController      = new ConsentController();
        $ioController           = new IOController($this->db, $cache, $state, $cgid, $conf, $alert);

        $router          = new BaseRouter();
        $responseFactory = new ResponseFactory();
        $strategy        = new SmartyStrategy($responseFactory, Shop::Smarty(), $state);
        $router->setStrategy($strategy);

        $router->middleware(new MaintenanceModeMiddleware());
        $router->middleware(new WishlistCheckMiddleware());
        $router->middleware(new CartcheckMiddleware());
        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();
        $visibilityMiddleware   = new VisibilityMiddleware();

        $this->router  = $router;
        $groups        = [''];
        $languageCodes = [];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            if (!\defined('URL_SHOP_' . mb_convert_case($language->getIso(), \MB_CASE_UPPER))) {
                $languageCodes[] = $language->getIso639();
            }
        }
        if (\count($languageCodes) > 1) {
            $groups[] = '/{lang:(?:' . \implode('|', $languageCodes) . ')}';
        }
        foreach ($groups as $localized) {
            $router->group($localized, function (RouteGroup $route) use (
                $productController,
                $visibilityMiddleware,
                $categoryController,
                $manufacturerController,
                $newsController,
                $pageController,
                $localized
            ) {
                $route->get('/products/{id:\d+}', [$productController, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''))
                    ->middleware($visibilityMiddleware);
                $route->get('/products/{name}', [$productController, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''))
                    ->middleware($visibilityMiddleware);
                $route->post('/products/{id:\d+}', [$productController, 'getResponse'])
                    ->middleware($visibilityMiddleware);
                $route->post('/products/{name}', [$productController, 'getResponse'])
                    ->middleware($visibilityMiddleware);

                $route->get('/categories/{id:\d+}', [$categoryController, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->get('/categories/{name}', [$categoryController, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->post('/categories/{id:\d+}', [$categoryController, 'getResponse']);
                $route->post('/categories/{name}', [$categoryController, 'getResponse']);

                $route->get('/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->get('/manufacturers/{name}', [$manufacturerController, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->post('/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse']);
                $route->post('/manufacturers/{name}', [$manufacturerController, 'getResponse']);

                $route->get('/news/{id:\d+}', [$newsController, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->get('/news/{name}', [$newsController, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->post('/news/{id:\d+}', [$newsController, 'getResponse']);
                $route->post('/news/{name}', [$newsController, 'getResponse']);

                $route->get('/page/{id:\d+}', [$pageController, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->get('/page/{name}', [$pageController, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
                $route->post('/page/{id:\d+}', [$pageController, 'getResponse']);
                $route->post('/page/{name}', [$pageController, 'getResponse']);
            });
        }
        $router->post('/updateconsent', [$consentController, 'getResponse'])
            ->setStrategy(new JsonStrategy($responseFactory));

        $router->get('/io', [$ioController, 'getResponse']);
        $router->post('/io', [$ioController, 'getResponse']);

        $router->get('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);
        $router->post('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);

        $router->get('/', [$rootController, 'getResponse']);
        $router->post('/', [$rootController, 'getResponse']);

//        $router->get('/{any:.*}', [$defaultController, 'getResponse']);
//        $router->post('/{any:.*}', [$defaultController, 'getResponse']);
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public function getRequestUri(bool $decoded = false): string
    {
        $shopURLdata = \parse_url(Shop::getURL());
        $baseURLdata = \parse_url($this->getRequestURL());

        $uri = isset($baseURLdata['path'])
            ? \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path'] ?? '') + 1)
            : '';
        $uri = '/' . $uri;

        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @param string     $name
     * @param array|null $replacements
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getNamedPath(string $name, ?array $replacements = null): string
    {
        $path = $this->router->getNamedRoute($name)->getPath();

        return $replacements === null ? $path : $this->getPath($path, $replacements);
    }

    /**
     * fixed version of League\Route::getPath() with replacements
     *
     * @param string $path
     * @param array  $replacements
     * @return string
     */
    protected function getPath(string $path, array $replacements): string
    {
        $hasReplacementRegex = '/{(' . \implode('|', \array_keys($replacements)) . ')(:.*)?}/';

        \preg_match_all('/\[(.*?)?{(?<keys>.*?)}/', $path, $matches);

        $isOptionalRegex = '/(.*)?{('
            . \implode('|', $matches['keys'])
            . ')(:.*)?}(.*)?/';

        $isPartiallyOptionalRegex = '/^([^\[\]{}]+)?\[((?:.*)?{(?:'
            . \implode('|', $matches['keys'])
            . ')(?::.*)?}(?:.*)?)\]?([^\[\]{}]+)?(?:[\[\]]+)?$/';

        $toReplace = [];

        foreach ($replacements as $wildcard => $actual) {
            $toReplace['/{' . \preg_quote($wildcard, '/') . '(:[^\}]*)?}/'] = $actual;
        }
        $segments = [];

        foreach (\array_filter(\explode('/', $path)) as $segment) {
            // segment is partially optional with a wildcard, strip it if no match, tidy up if match
            if (\preg_match($isPartiallyOptionalRegex, $segment)) {
                $segment = \preg_match($hasReplacementRegex, $segment)
                    ? \preg_replace($isPartiallyOptionalRegex, '$1$2$3', $segment)
                    : \preg_replace($isPartiallyOptionalRegex, '$1', $segment);
            }

            // segment either isn't a wildcard or there is a replacement
            if (!\preg_match('/{(.*?)}/', $segment) || \preg_match($hasReplacementRegex, $segment)) {
                $segments[] = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
                continue;
            }

            // segment is a required wildcard, no replacement, still gets added
            if (!\preg_match($isOptionalRegex, $segment)) {
                $segments[] = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
                continue;
            }

            // segment is completely optional with no replacement, strip it and break
            if (\preg_match($isOptionalRegex, $segment) && !\preg_match($hasReplacementRegex, $segment)) {
                break;
            }
        }

        return \preg_replace(\array_keys($toReplace), \array_values($toReplace), '/' . \implode('/', $segments));
    }

    /**
     * @return string
     */
    public function getRequestURL(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'] ?? '');
    }

    public function dispatch(): void
    {
        $request     = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $shopURLdata = \parse_url(Shop::getURL());
        if (isset($shopURLdata['path'])) { // @todo find a better solution
            $baseURLdata = \parse_url($this->getRequestURL());
            $path        = '/' . \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path'] ?? '') + 1);
            $uri         = $request->getUri();
            $request     = $request->withUri($uri->withPath($path));
        }

        $response = $this->router->dispatch($request);
        CoreDispatcher::getInstance()->fire(Event::EMIT);
        try {
            (new SapiEmitter())->emit($response);
        } catch (EmitterException) {
            echo $response->getBody();
        }
        exit();
    }

    /**
     * @return State
     */
    public function init(): State
    {
        $this->state->initFromRequest();

        return $this->state;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];
        foreach ($this->state->getMapping() as $old => $new) {
            $params[$old] = $this->state->{$new};
        }

        return $params;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
