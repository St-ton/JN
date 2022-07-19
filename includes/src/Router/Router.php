<?php declare(strict_types=1);

namespace JTL\Router;

use Exception;
use FastRoute\BadRouteException;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Currency;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher as CoreDispatcher;
use JTL\Events\Event;
use JTL\Language\LanguageHelper;
use JTL\Router\Controller\CategoryController;
use JTL\Router\Controller\CharacteristicValueController;
use JTL\Router\Controller\ConsentController;
use JTL\Router\Controller\ControllerInterface;
use JTL\Router\Controller\DefaultController;
use JTL\Router\Controller\IOController;
use JTL\Router\Controller\ManufacturerController;
use JTL\Router\Controller\NewsController;
use JTL\Router\Controller\PageController;
use JTL\Router\Controller\ProductController;
use JTL\Router\Controller\RootController;
use JTL\Router\Controller\SearchController;
use JTL\Router\Controller\SearchQueryController;
use JTL\Router\Controller\SearchSpecialController;
use JTL\Router\Middleware\CartcheckMiddleware;
use JTL\Router\Middleware\CurrencyCheckMiddleware;
use JTL\Router\Middleware\LocaleCheckMiddleware;
use JTL\Router\Middleware\LocaleRedirectMiddleware;
use JTL\Router\Middleware\MaintenanceModeMiddleware;
use JTL\Router\Middleware\OptinMiddleware;
use JTL\Router\Middleware\PhpFileCheckMiddleware;
use JTL\Router\Middleware\SSLRedirectMiddleware;
use JTL\Router\Middleware\VisibilityMiddleware;
use JTL\Router\Middleware\WishlistCheckMiddleware;
use JTL\Router\Strategy\SmartyStrategy;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Exception\EmitterException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use League\Route\Router as BaseRouter;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Router
 * @package JTL\Router
 * @since 5.2.0
 */
class Router
{
    /**
     * @var string
     */
    private string $uri = '';

    /**
     * @var BaseRouter
     */
    private BaseRouter $router;

    /**
     * @var bool
     */
    private bool $isMultilang = false;

    /**
     * @var bool
     */
    private bool $isMulticrncy = false;

    /**
     * @var string[]
     */
    private array $langGroups = [''];

    /**
     * @var string[]
     */
    private array $crncyGroups = [''];

    /**
     * @var string
     */
    private string $defaultLocale = 'de';

    /**
     * @var bool
     */
    private bool $ignoreDefaultLocale = false;

    public const TYPE_CATEGORY             = 'categories';
    public const TYPE_CHARACTERISTIC_VALUE = 'characteristics';
    public const TYPE_MANUFACTURERS        = 'manufacturers';
    public const TYPE_NEWS                 = 'news';
    public const TYPE_PAGE                 = 'pages';
    public const TYPE_PRODUCT              = 'products';
    public const TYPE_SEARCH_SPECIAL       = 'searchspecials';
    public const TYPE_SEARCH_QUERY         = 'searchqueries';

    /**
     * @var ControllerInterface
     */
    private ControllerInterface $defaultController;

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param State                 $state
     * @param AlertServiceInterface $alert
     * @param array                 $config
     */
    public function __construct(
        protected DbInterface       $db,
        protected JTLCacheInterface $cache,
        protected State             $state,
        AlertServiceInterface       $alert,
        private array               $config
    ) {
        $products        = new ProductController($db, $cache, $state, $this->config, $alert);
        $specials        = new SearchSpecialController($db, $cache, $state, $this->config, $alert);
        $queries         = new SearchQueryController($db, $cache, $state, $this->config, $alert);
        $characteristics = new CharacteristicValueController($db, $cache, $state, $this->config, $alert);
        $categories      = new CategoryController($db, $cache, $state, $this->config, $alert);
        $manufacturers   = new ManufacturerController($db, $cache, $state, $this->config, $alert);
        $news            = new NewsController($db, $cache, $state, $this->config, $alert);
        $pages           = new PageController($db, $cache, $state, $this->config, $alert);
        $search          = new SearchController($db, $cache, $state, $this->config, $alert);
        $default         = new DefaultController($db, $cache, $state, $this->config, $alert);
        $root            = new RootController($db, $cache, $state, $this->config, $alert);
        $consent         = new ConsentController();
        $io              = new IOController($this->db, $cache, $state, $this->config, $alert);

        $this->router = new BaseRouter();

        $codes = [];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            if (!\defined('URL_SHOP_' . \mb_convert_case($language->getIso(), \MB_CASE_UPPER))) {
                $codes[] = $language->getIso639();
            }
            if ($language->isShopDefault()) {
                $this->defaultLocale = $language->getIso639();
            }
        }
        if (($this->config['global']['routing_scheme'] ?? 'F') !== 'F') {
            $this->ignoreDefaultLocale = $this->config['global']['routing_default_language'] === 'F';
            if (\count($codes) > 1) {
                $this->isMultilang  = true;
                $this->langGroups[] = '/{lang:(?:' . \implode('|', $codes) . ')}';
            }
            if ($this->ignoreDefaultLocale === true) {
                $this->router->middleware(new LocaleRedirectMiddleware($this->defaultLocale));
            }
        }
        $this->router->middleware(new MaintenanceModeMiddleware($this->config['global']));
        $this->router->middleware(new SSLRedirectMiddleware($this->config['global']));
        $this->router->middleware(new WishlistCheckMiddleware());
        $this->router->middleware(new CartcheckMiddleware());
        $this->router->middleware(new LocaleCheckMiddleware());
        $this->router->middleware(new CurrencyCheckMiddleware());
        $this->router->middleware(new OptinMiddleware());
        $visibilityMiddleware = new VisibilityMiddleware();
        $currencies           = \array_map(static function (Currency $e): string {
            return $e->getCode();
        }, Currency::loadAll());
        if (\count($currencies) > 1) {
            $this->crncyGroups[] = '/{currency:(?:' . \implode('|', $currencies) . ')}';
            $this->isMulticrncy  = true;
        }
        $name = \SLUG_ALLOW_SLASHES ? 'name:.+' : 'name';

        foreach ($this->langGroups as $loc) {
            foreach ($this->crncyGroups as $crncy) {
                $dyn     = $loc . $crncy;
                $dynName = ($loc === '' ? '' : '_LOCALIZED') . ($crncy === '' ? '' : '_CRNCY');
                $this->router->get($dyn . '/products/{id:\d+}', [$products, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_ID' . $dynName)
                    ->middleware($visibilityMiddleware);
                $this->router->get($dyn . '/products/{' . $name . '}', [$products, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_NAME' . $dynName)
                    ->middleware($visibilityMiddleware);
                $this->router->post($dyn . '/products/{id:\d+}', [$products, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_ID' . $dynName . 'POST')
                    ->middleware($visibilityMiddleware);
                $this->router->post($dyn . '/products/{' . $name . '}', [$products, 'getResponse'])
                    ->setName('ROUTE_PRODUCT_BY_NAME' . $dynName . 'POST')
                    ->middleware($visibilityMiddleware);

                $this->router->get($dyn . '/characteristics/{id:\d+}', [$characteristics, 'getResponse'])
                    ->setName('ROUTE_CHARACTERISTIC_BY_ID' . $dynName);
                $this->router->get($dyn . '/characteristics/{' . $name . '}', [$characteristics, 'getResponse'])
                    ->setName('ROUTE_CHARACTERISTIC_BY_NAME' . $dynName);
                $this->router->post($dyn . '/characteristics/{id:\d+}', [$characteristics, 'getResponse'])
                    ->setName('ROUTE_CHARACTERISTIC_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/characteristics/{' . $name . '}', [$characteristics, 'getResponse'])
                    ->setName('ROUTE_CHARACTERISTIC_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/categories/{id:\d+}', [$categories, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_ID' . $dynName);
                $this->router->get($dyn . '/categories/{' . $name . '}', [$categories, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_NAME' . $dynName);
                $this->router->post($dyn . '/categories/{id:\d+}', [$categories, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/categories/{' . $name . '}', [$categories, 'getResponse'])
                    ->setName('ROUTE_CATEGORY_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/searchspecials/{id:\d+}', [$specials, 'getResponse'])
                    ->setName('ROUTE_SEARCHSPECIAL_BY_ID' . $dynName);
                $this->router->get($dyn . '/searchspecials/{' . $name . '}', [$specials, 'getResponse'])
                    ->setName('ROUTE_SEARCHSPECIAL_BY_NAME' . $dynName);
                $this->router->post($dyn . '/searchspecials/{id:\d+}', [$specials, 'getResponse'])
                    ->setName('ROUTE_SEARCHSPECIAL_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/searchspecials/{' . $name . '}', [$specials, 'getResponse'])
                    ->setName('ROUTE_SEARCHSPECIAL_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/searchqueries/{id:\d+}', [$queries, 'getResponse'])
                    ->setName('ROUTE_SEARCHQUERY_BY_ID' . $dynName);
                $this->router->get($dyn . '/searchqueries/{' . $name . '}', [$queries, 'getResponse'])
                    ->setName('ROUTE_SEARCHQUERY_BY_NAME' . $dynName);
                $this->router->post($dyn . '/searchqueries/{id:\d+}', [$queries, 'getResponse'])
                    ->setName('ROUTE_SEARCHQUERY_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/searchqueries/{' . $name . '}', [$queries, 'getResponse'])
                    ->setName('ROUTE_SEARCHQUERY_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/manufacturers/{id:\d+}', [$manufacturers, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_ID' . $dynName);
                $this->router->get($dyn . '/manufacturers/{' . $name . '}', [$manufacturers, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_NAME' . $dynName);
                $this->router->post($dyn . '/manufacturers/{id:\d+}', [$manufacturers, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/manufacturers/{' . $name . '}', [$manufacturers, 'getResponse'])
                    ->setName('ROUTE_MANUFACTURER_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/news/{id:\d+}', [$news, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_ID' . $dynName);
                $this->router->get($dyn . '/news[/{' . $name . '}]', [$news, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_NAME' . $dynName);
                $this->router->post($dyn . '/news/{id:\d+}', [$news, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/news/{' . $name . '}', [$news, 'getResponse'])
                    ->setName('ROUTE_NEWS_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/pages/{id:\d+}', [$pages, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_ID' . $dynName);
                $this->router->get($dyn . '/pages/{' . $name . '}', [$pages, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_NAME' . $dynName);
                $this->router->post($dyn . '/pages/{id:\d+}', [$pages, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_ID' . $dynName . 'POST');
                $this->router->post($dyn . '/pages/{' . $name . '}', [$pages, 'getResponse'])
                    ->setName('ROUTE_PAGE_BY_NAME' . $dynName . 'POST');

                $this->router->get($dyn . '/search/{query:.+}', [$search, 'getResponse'])
                    ->setName('ROUTE_SEARCH' . $dynName);
                $this->router->post($dyn . '/search/{query:.+}', [$search, 'getResponse'])
                    ->setName('ROUTE_SEARCH' . $dynName . 'POST');
            }
        }
        $this->router->post('/_updateconsent', [$consent, 'getResponse'])
            ->setName('ROUTE_UPDATE_CONSENTPOST')
            ->setStrategy(new JsonStrategy(new ResponseFactory()));

        $this->router->get('/io', [$io, 'getResponse'])->setName('ROUTE_IO');
        $this->router->post('/io', [$io, 'getResponse'])->setName('ROUTE_IOPOST');

        $this->router->get('/', [$root, 'getResponse'])->setName('ROUTE_ROOT');
        $this->router->post('/', [$root, 'getResponse'])->setName('ROUTE_ROOTPOST');

        $this->defaultController = $default;
    }

    /**
     * @param string                   $slug
     * @param callable                 $cb
     * @param array                    $methods
     * @param string|null              $name
     * @param MiddlewareInterface|null $middleware
     * @return Route[]
     */
    public function addRoute(
        string               $slug,
        callable             $cb,
        array                $methods = ['GET'],
        string               $name = null,
        ?MiddlewareInterface $middleware = null
    ): array {
        if (!\str_starts_with($slug, '/')) {
            $slug = '/' . $slug;
        }
        $routes  = [];
        $methods = \array_map('\mb_strtoupper', $methods);
        foreach ($this->langGroups as $group) {
            foreach ($methods as $method) {
                $route = $this->router->map($method, $group . $slug, $cb);
                if ($name !== null) {
                    $route->setName($name . $method);
                }
                if ($middleware !== null) {
                    $route->middleware($middleware);
                }
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public function getRequestUri(bool $decoded = false): string
    {
        $shopPath = \parse_url(Shop::getURL(), \PHP_URL_PATH);
        $basePath = \parse_url($this->getRequestURL(), \PHP_URL_PATH);
        $uri      = $basePath
            ? \mb_substr($basePath, \mb_strlen($shopPath) + 1)
            : '';
        $uri      = '/' . $uri;
        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @param string     $type
     * @param array|null $replacements
     * @param bool       $byName
     * @return string
     */
    public function getPathByType(string $type, ?array $replacements = null, bool $byName = true): string
    {
        $name = match ($type) {
            self::TYPE_CATEGORY             => 'ROUTE_CATEGORY_BY_',
            self::TYPE_CHARACTERISTIC_VALUE => 'ROUTE_CHARACTERISTIC_BY_',
            self::TYPE_MANUFACTURERS        => 'ROUTE_MANUFACTURER_BY_',
            self::TYPE_NEWS                 => 'ROUTE_NEWS_BY_',
            self::TYPE_PAGE                 => 'ROUTE_PAGE_BY_',
            self::TYPE_PRODUCT              => 'ROUTE_PRODUCT_BY_',
            self::TYPE_SEARCH_SPECIAL       => 'ROUTE_SEARCHSPECIAL_BY_',
            self::TYPE_SEARCH_QUERY         => 'ROUTE_SEARCHQUERY_BY_',
            default                         => 'ROUTE_XXX_BY_'
        };

        $isDefaultLocale = ($replacements['lang'] ?? '') === $this->defaultLocale;

        $name .= ($byName === true && !empty($replacements['name']) ? 'NAME' : 'ID');
        if ($this->isMultilang === true
            && ($this->ignoreDefaultLocale === false || !$isDefaultLocale)
        ) {
            if (empty($replacements['lang'])) {
                $replacements['lang'] = $this->defaultLocale;
            }
            $name .= '_LOCALIZED';
        }
        if ($this->isMulticrncy === true && isset($replacements['currency'])) {
            $name .= '_CRNCY';
        }
        if ($byName === true) {
            if ($isDefaultLocale && (($this->config['global']['routing_default_language'] ?? 'F') === 'F')) {
                return '/' . $replacements['name'];
            }
            if (!$isDefaultLocale && (($this->config['global']['routing_scheme'] ?? 'F') === 'F')) {
                return '/' . $replacements['name'];
            }
        }

        return $this->getNamedPath($name, $replacements);
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

        $quoted = [];
        foreach ($matches['keys'] as $key) {
            $quoted[] = \preg_quote($key, '/');
        }
        $isOptionalRegex = '/(.*)?{('
            . \implode('|', $quoted)
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
            $c0 = !\preg_match('/{(.*?)}/', $segment);
            $c1 = \preg_match($hasReplacementRegex, $segment);
            if ($c0 || $c1) {
                $item       = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
                $segments[] = $item;
                continue;
            }

            // segment is a required wildcard, no replacement, still gets added
            if (!\preg_match($isOptionalRegex, $segment)) {
                $item       = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
                $segments[] = $item;
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

    /**
     * @param JTLSmarty $smarty
     * @return void
     */
    public function dispatch(JTLSmarty $smarty): void
    {
        $strategy = new SmartyStrategy(new ResponseFactory(), $smarty, $this->state);
        $this->router->setStrategy($strategy);
        $request  = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $shopPath = \parse_url(Shop::getURL(), \PHP_URL_PATH);
        $uri      = $request->getUri();
        if ($shopPath !== null) { // @todo find a better solution
            $basePath = \parse_url($this->getRequestURL(), \PHP_URL_PATH);
            $path     = '/' . \mb_substr($basePath, \mb_strlen($shopPath) + 1);
            $request  = $request->withUri($uri->withPath($path));
        }
        $uriPath = $request->getUri()->getPath();
        $oldURI  = $uriPath;
        \executeHook(\HOOK_SEOCHECK_ANFANG, ['uri' => &$uriPath]);
        if ($oldURI !== $uriPath) {
            $request = $request->withUri($uri->withPath($uriPath));
        }
        \executeHook(\HOOK_ROUTER_PRE_DISPATCH, ['router' => $this]);

        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();
        // this is added after HOOK_ROUTER_PRE_DISPATCH since plugins could register static routes
        // which would otherwise be shadowed by this
        $this->router->get('/{slug:.+}', [$this->defaultController, 'getResponse'])
            ->setName('catchall')
            ->middleware($phpFileCheckMiddleware);
        $this->router->post('/{slug:.+}', [$this->defaultController, 'getResponse'])
            ->setName('catchallPOST')
            ->middleware($phpFileCheckMiddleware);
        try {
            $response = $this->router->dispatch($request);
        } catch (BadRouteException $e) {
            throw $e;
        } catch (NotFoundException) {
            $response = $this->defaultController->getResponse($request, [], $smarty);
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error('Routing error: ' . $e->getMessage());
            $response = $this->defaultController->getResponse($request, [], $smarty);
        }
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

    /**
     * @return BaseRouter
     */
    public function getRouter(): BaseRouter
    {
        return $this->router;
    }

    /**
     * @param BaseRouter $router
     */
    public function setRouter(BaseRouter $router): void
    {
        $this->router = $router;
    }

    /**
     * @return array|string[]
     */
    public function getLangGroups(): array
    {
        return $this->langGroups;
    }

    /**
     * @param array|string[] $langGroups
     */
    public function setLangGroups(array $langGroups): void
    {
        $this->langGroups = $langGroups;
    }
}
