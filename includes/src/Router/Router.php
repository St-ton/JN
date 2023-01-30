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
use JTL\Router\Controller\MediaImageController;
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
use JTL\Router\Middleware\SSLRedirectMiddleware;
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
use League\Route\RouteGroup;
use League\Route\Router as BaseRouter;
use Psr\Http\Server\MiddlewareInterface;
use function Functional\first;

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
    private array $crncyGroups = ['/'];

    /**
     * @var string
     */
    private string $defaultLocale = 'de';

    /**
     * @var bool
     */
    private bool $isMultiDomain = false;

    /**
     * @var array
     */
    private array $hosts;

    /**
     * @var string
     */
    private string $path = '';

    /**
     * @var RouteGroup[]
     */
    private array $routes = [];

    /**
     * @var array[]
     */
    private array $customRoutes = [];

    /**
     * @var array
     */
    private array $languages = [];

    /**
     * @var ControllerInterface[]
     */
    private array $controllers;

    public const TYPE_CATEGORY             = 'categories';
    public const TYPE_CHARACTERISTIC_VALUE = 'characteristics';
    public const TYPE_MANUFACTURER         = 'manufacturers';
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
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected State $state,
        AlertServiceInterface $alert,
        private array $config
    ) {
        $this->router            = new BaseRouter();
        $this->defaultController = new DefaultController($db, $cache, $state, $this->config, $alert);
        $registeredDefault       = false;
        $middlewares             = [
            new MaintenanceModeMiddleware($this->config['global']),
            new SSLRedirectMiddleware($this->config['global']),
            new WishlistCheckMiddleware(),
            new CartcheckMiddleware(),
            new LocaleCheckMiddleware(),
            new CurrencyCheckMiddleware(),
            new OptinMiddleware(),
        ];

        $root              = new RootController($db, $cache, $state, $this->config, $alert);
        $consent           = new ConsentController();
        $io                = new IOController($db, $cache, $state, $this->config, $alert);
        $this->controllers = [
            ProductController::class             => new ProductController($db, $cache, $state, $this->config, $alert),
            CharacteristicValueController::class =>
                new CharacteristicValueController($db, $cache, $state, $this->config, $alert),
            CategoryController::class            => new CategoryController($db, $cache, $state, $this->config, $alert),
            SearchSpecialController::class       =>
                new SearchSpecialController($db, $cache, $state, $this->config, $alert),
            SearchQueryController::class         =>
                new SearchQueryController($db, $cache, $state, $this->config, $alert),
            ManufacturerController::class        =>
                new ManufacturerController($db, $cache, $state, $this->config, $alert),
            NewsController::class                => new NewsController($db, $cache, $state, $this->config, $alert),
            SearchController::class              => new SearchController($db, $cache, $state, $this->config, $alert),
            PageController::class                => new PageController($db, $cache, $state, $this->config, $alert),
            MediaImageController::class          => new MediaImageController($db, $cache, $state, $this->config, $alert)
        ];
        foreach ($this->collectHosts() as $data) {
            $host         = $data['host'];
            $locale       = $data['locale'];
            $localePrefix = $data['prefix'];
            $group        = new RouteGroup($localePrefix, function (RouteGroup $route) use (
                &$registeredDefault,
                $data,
                $io,
                $root,
                $consent,
                $locale
            ) {
                $dynName = $this->isMultiDomain === true ? ('_' . $locale) : '';
                if ($data['localized']) {
                    $dynName = '_LOCALIZED';
                }
                if ($data['currency']) {
                    $dynName .= '_CRNCY';
                }
                if (($this->isMultiDomain === true || $registeredDefault === false) && $route->getPrefix() === '/') {
                    // these routes must only be registered once per host
                    $registeredDefault = true;
                    $consent->register($route, $dynName);
                    $io->register($route, $dynName);
                    $root->register($route, $dynName);
                }
                foreach ($this->controllers as $controller) {
                    $controller->register($route, $dynName);
                }
            }, $this->router);
            $group->setHost($host)->setName($locale
                . '_grp'
                . ($data['localized'] ? '_LOCALIZED' : '')
                . ($data['currency'] ? '_CRNCY' : ''));
            foreach ($middlewares as $middleware) {
                $group->middleware($middleware);
            }
            $this->routes[] = $group;
        }
        if ($this->isMultiDomain === false) {
            $path = \parse_url(\URL_SHOP, \PHP_URL_PATH);
            if ($path !== null && $path !== '/') {
                $this->path = $path;
            }
        }
        $this->collectGroupRoutes();
    }

    /**
     * @return void
     */
    protected function collectGroupRoutes(): void
    {
        foreach ($this->routes as $group) {
            $group();
        }
    }

    /**
     * @param string                   $slug
     * @param callable                 $cb
     * @param string|null              $name
     * @param array                    $methods
     * @param MiddlewareInterface|null $middleware
     * @return Route[]
     */
    public function addRoute(
        string $slug,
        callable $cb,
        ?string $name = null,
        array $methods = ['GET'],
        ?MiddlewareInterface $middleware = null
    ): array {
        if (!\str_starts_with($slug, '/')) {
            $slug = '/' . $slug;
        }
        $name                      = $name ?? \uniqid();
        $routes                    = [];
        $methods                   = \array_map('\mb_strtoupper', $methods);
        $this->customRoutes[$name] = [];
        foreach ($methods as $method) {
            $this->customRoutes[$name][$method] = [];
        }
        foreach ($this->routes as $group) {
            $groupName = $group->getName();
            // routes are named <locale>_grp, <locale>_grp_LOCALIZED, <locale>_grp_CRNCY etc.
            $dynName = $this->isMultiDomain === true ? ('_' . \explode('_', $groupName)[0]) : '';
            if (\str_contains($groupName, '_LOCALIZED')) {
                $dynName = '_LOCALIZED';
            }
            if (\str_contains($groupName, '_CRNCY')) {
                $dynName .= '_CRNCY';
            }
            foreach ($methods as $method) {
                $route = $group->map($method, $slug, $cb);
                $route->setName($name . $dynName . $method);
                $this->customRoutes[$name][$method][] = $name . $dynName . $method;
                if ($middleware !== null) {
                    $route->middleware($middleware);
                }
                $routes[] = $route;
            }
        }


        return $routes;
    }

    /**
     * @return void
     */
    protected function registerDefaultController(): void
    {
        foreach ($this->routes as $group) {
            $dynName = '';
            if ($this->isMultiDomain === true) {
                $locale  = \mb_substr($group->getName(), 0, \mb_strpos($group->getName(), '_grp'));
                $dynName = '_' . $locale;
            }
            if (\str_contains($group->getName(), '_LOCALIZED')) {
                $dynName = '_LOCALIZED';
            }
            if (\str_contains($group->getName(), '_CURNCY')) {
                $dynName .= '_CRNCY';
            }
            $this->defaultController->register($group, $dynName);
        }
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public function getRequestUri(bool $decoded = false): string
    {
        $shopPath = \parse_url(Shop::getURL(), \PHP_URL_PATH) ?? '';
        $basePath = \parse_url($this->getRequestURL(), \PHP_URL_PATH);
        $uri      = $basePath ? \mb_substr($basePath, \mb_strlen($shopPath) + 1) : '';
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
        if (isset($replacements['name']) && $replacements['name'] === '') {
            unset($replacements['name']);
        }
        $isDefaultLocale = ($replacements['lang'] ?? '') === $this->defaultLocale;
        if (empty($replacements['lang'])) {
            $replacements['lang'] = $this->defaultLocale;
            $isDefaultLocale      = true;
        }
        $name   = $this->getRouteName($type, $replacements, $byName);
        $scheme = $isDefaultLocale
            ? ($this->config['global']['routing_default_language'] ?? 'F')
            : ($this->config['global']['routing_scheme'] ?? 'F');
        if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
            $scheme = 'F';
        }
        if ($scheme !== 'F' && $byName === true && empty($replacements['name'])) {
            $byName = false;
        }
        if ($byName === true) {
            if ($scheme === 'F') {
                if (!isset($replacements['name'])) {
                    $param       = $this->getFallbackParam($type);
                    $queryParams = [$param => $replacements['id']];
                    if (!$isDefaultLocale) {
                        $queryParams['lang'] = $this->languages[$replacements['lang']];
                    }
                    if (isset($replacements['currency'])) {
                        $queryParams['curr'] = $replacements['currency'];
                    }
                    $named = '?' . \http_build_query($queryParams);
                } else {
                    $named = ($replacements['name'] ?? $replacements['id']);
                }

                return '/' . $named
                    . (isset($replacements['currency']) ? '?curr=' . $replacements['currency'] : '');
            }
            if ($scheme === 'L') {
                return '/' . $replacements['lang'] . '/'
                    . ($replacements['name'] ?? $replacements['id'] ?? '')
                    . (isset($replacements['currency']) ? '?curr=' . $replacements['currency'] : '');
            }
        }

        return $this->path . $this->getNamedPath($name, $replacements);
    }

    /**
     * @param string     $type
     * @param array|null $replacements
     * @param bool       $byName
     * @param bool       $forceDynamic
     * @return string
     */
    public function getURLByType(
        string $type,
        ?array $replacements = null,
        bool $byName = true,
        bool $forceDynamic = false
    ): string {
        if (isset($replacements['name']) && $replacements['name'] === '') {
            unset($replacements['name']);
        }
        $isDefaultLocale = ($replacements['lang'] ?? '') === $this->defaultLocale;
        if (empty($replacements['lang'])) {
            $replacements['lang'] = $this->defaultLocale;
            $isDefaultLocale      = true;
        }
        $name = $this->getRouteName($type, $replacements, $byName);
        try {
            $route = $this->router->getNamedRoute($name);
        } catch (Exception) {
            return '';
        }
        $pfx = $this->getPrefix($route->getHost());
        if ($this->path !== '/') {
            $pfx .= $this->path;
        }
        $scheme = $isDefaultLocale
            ? ($this->config['global']['routing_default_language'] ?? 'F')
            : ($this->config['global']['routing_scheme'] ?? 'F');
        if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
            $scheme = 'F';
        }
        if ($scheme !== 'F' && $byName === true && empty($replacements['name'])) {
            $byName = false;
        }
        if ($forceDynamic === false && $byName === true) {
            if ($scheme === 'F') {
                if (!isset($replacements['name'])) {
                    $param       = $this->getFallbackParam($type);
                    $queryParams = [$param => $replacements['id']];
                    if (!$isDefaultLocale) {
                        $queryParams['lang'] = $this->languages[$replacements['lang']];
                    }
                    if (isset($replacements['currency'])) {
                        $queryParams['curr'] = $replacements['currency'];
                    }
                    $named = '?' . \http_build_query($queryParams);
                } else {
                    $named = ($replacements['name'] ?? $replacements['id']);
                }

                return $pfx . '/' . $named
                    . (isset($replacements['currency']) ? '?curr=' . $replacements['currency'] : '');
            }
            if ($scheme === 'L') {
                return $pfx . '/' . $replacements['lang'] . '/'
                    . ($replacements['name'] ?? $replacements['id'] ?? '')
                    . (isset($replacements['currency']) ? '?curr=' . $replacements['currency'] : '');
            }
        }

        return $pfx . $this->getPath($route->getPath(), $replacements);
    }

    /**
     * @param string|null $routeHost
     * @return string
     */
    private function getPrefix(?string $routeHost): string
    {
        if ($routeHost !== null) {
            foreach ($this->hosts as $host) {
                if ($host['host'] === $routeHost) {
                    return $host['scheme'] . '://' . $routeHost;
                }
            }
        }

        return Shop::getURL();
    }

    /**
     * @param string     $name
     * @param array|null $replacements
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getNamedPath(string $name, ?array $replacements = null): string
    {
        try {
            $path = $this->router->getNamedRoute($name)->getPath();
        } catch (Exception) {
            return '';
        }

        return $replacements === null ? $path : $this->getPath($path, $replacements);
    }

    /**
     * @param string     $type
     * @param array|null $replacements
     * @param bool       $byName
     * @return string
     */
    private function getRouteName(string $type, ?array $replacements = null, bool $byName = true): string
    {
        $name = match ($type) {
            self::TYPE_CATEGORY             => 'ROUTE_CATEGORY_BY_',
            self::TYPE_CHARACTERISTIC_VALUE => 'ROUTE_CHARACTERISTIC_BY_',
            self::TYPE_MANUFACTURER         => 'ROUTE_MANUFACTURER_BY_',
            self::TYPE_NEWS                 => 'ROUTE_NEWS_BY_',
            self::TYPE_PAGE                 => 'ROUTE_PAGE_BY_',
            self::TYPE_PRODUCT              => 'ROUTE_PRODUCT_BY_',
            self::TYPE_SEARCH_SPECIAL       => 'ROUTE_SEARCHSPECIAL_BY_',
            self::TYPE_SEARCH_QUERY         => 'ROUTE_SEARCHQUERY_BY_',
            default                         => $type
        };
        $proto        = '';
        $isNamedRoute = $name === $type;
        if ($isNamedRoute === false) {
            $name .= ($byName === true && !empty($replacements['name']) ? 'NAME' : 'ID');
        } elseif (isset($this->customRoutes[$name])) {
            $proto = first(\array_keys($this->customRoutes[$name]));
        }

        if ($this->isMultiDomain === true) {
            $name .= '_' . \mb_convert_case($replacements['lang'], \MB_CASE_LOWER);
        } else {
            $isDefaultLocale = ($replacements['lang'] ?? '') === $this->defaultLocale;
            $defaultScheme   = $this->config['global']['routing_default_language'] ?? 'F';
            $scheme          = $this->config['global']['routing_scheme'] ?? 'F';
            if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
                $scheme = 'F';
            }
            if (!$isDefaultLocale && ($scheme === 'LP' || $scheme === 'L')) {
                $name .= '_LOCALIZED';
            } elseif ($isDefaultLocale && ($defaultScheme === 'LP' || $defaultScheme === 'L')) {
                $name .= '_LOCALIZED';
            }
        }
        if ($this->isMulticrncy === true && isset($replacements['currency'])) {
            $name .= '_CRNCY';
        }
        if ($isNamedRoute === true) {
            $name .= $proto;
        }

        return $name;
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
        $body = $_POST;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'PUT') {
            \parse_str(\file_get_contents('php://input'), $body);
        }
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $body, $_COOKIE, $_FILES);
        if (\EXPERIMENTAL_MULTILANG_SHOP === true && \count($this->hosts) > 0) {
            $requestedHost = $request->getUri()->getHost();
            foreach ($this->hosts as $host) {
                if ($host['host'] === $requestedHost) {
                    $this->state->languageID = $host['id'];
                    Shop::updateLanguage($this->state->languageID, $host['iso']);
                }
            }
        }
        $shopPath = \parse_url(Shop::getURL(), \PHP_URL_PATH);
        $uri      = $request->getUri();
        if ($shopPath !== null) { // @todo find a better solution
            $basePath = \parse_url($this->getRequestURL(), \PHP_URL_PATH) ?? '';
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

        // this is added after HOOK_ROUTER_PRE_DISPATCH since plugins could register static routes
        // which would otherwise be shadowed by this
        $this->registerDefaultController();
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
     * @return array
     */
    private function collectHosts(): array
    {
        $hosts   = [];
        $locales = [];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $default   = $language->isShopDefault();
            $code      = $language->getCode();
            $locales[] = $language->getIso639();

            $this->languages[$language->getIso639()] = $code;
            if (\EXPERIMENTAL_MULTILANG_SHOP === false && $default) {
                $url     = \URL_SHOP;
                $host    = \parse_url($url);
                $hosts[] = [
                    'host'      => $host['host'],
                    'scheme'    => $host['scheme'],
                    'locale'    => $language->getIso639(),
                    'iso'       => $code,
                    'id'        => $language->getId(),
                    'default'   => true,
                    'prefix'    => '/',
                    'currency'  => false,
                    'localized' => false
                ];
            } elseif (\defined('URL_SHOP_' . \mb_convert_case($code, \MB_CASE_UPPER))) {
                $this->isMultiDomain = true;
                $url                 = \constant('URL_SHOP_' . \mb_convert_case($code, \MB_CASE_UPPER));
                $host                = \parse_url($url);
                $hosts[]             = [
                    'host'      => $host['host'],
                    'scheme'    => $host['scheme'],
                    'locale'    => $language->getIso639(),
                    'iso'       => $code,
                    'id'        => $language->getId(),
                    'default'   => $default,
                    'prefix'    => '/',
                    'currency'  => false,
                    'localized' => false
                ];
            }
            if ($default) {
                $this->defaultLocale = $language->getIso639();
            }
        }
        $defaultScheme = $this->config['global']['routing_default_language'] ?? 'xF';
        $otherSchemes  = $this->config['global']['routing_scheme'] ?? 'xF';
        if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
            $defaultScheme = 'xF';
            $otherSchemes  = 'xF';
        }
        if ($defaultScheme !== 'F' || $otherSchemes !== 'F') {
            if ($this->isMultiDomain === false && \count($locales) > 1) {
                $host2              = $hosts[0];
                $this->isMultilang  = true;
                $host2['prefix']    = '/{lang:(?:' . \implode('|', $locales) . ')}';
                $host2['localized'] = true;
                $hosts[]            = $host2;
            }
        } else {
            $this->router->middleware(new LocaleRedirectMiddleware($this->defaultLocale));
        }
        $currencies = \array_map(static function (Currency $e): string {
            return $e->getCode();
        }, Currency::loadAll());
        if (\count($currencies) > 1) {
            $currencyRegex       = '/{currency:(?:' . \implode('|', $currencies) . ')}';
            $this->crncyGroups[] = $currencyRegex;
            $this->isMulticrncy  = true;
            foreach ($hosts as $host) {
                $base             = $host;
                $base['prefix']   = \rtrim($currencyRegex . $base['prefix'], '/');
                $base['currency'] = true;
                $hosts[]          = $base;
            }
        }
        $this->hosts = \array_reverse($hosts);

        return $this->hosts;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getFallbackParam(string $type): string
    {
        return match ($type) {
            self::TYPE_CATEGORY             => 'k',
            self::TYPE_CHARACTERISTIC_VALUE => 'm',
            self::TYPE_MANUFACTURER         => 'h',
            self::TYPE_NEWS                 => 'n',
            self::TYPE_PAGE                 => 's',
            self::TYPE_PRODUCT              => 'a',
            self::TYPE_SEARCH_SPECIAL       => 'q',
            self::TYPE_SEARCH_QUERY         => 'l'
        };
    }

    /**
     * @return ControllerInterface[]
     */
    public function getControllers(): array
    {
        return $this->controllers;
    }

    /**
     * @param string $class
     * @return ControllerInterface|null
     */
    public function getControllerByClassName(string $class): ?ControllerInterface
    {
        return $this->controllers[$class] ?? null;
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

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @param State $state
     */
    public function setState(State $state): void
    {
        $this->state = $state;
    }
}
