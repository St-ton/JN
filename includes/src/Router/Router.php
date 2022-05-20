<?php declare(strict_types=1);

namespace JTL\Router;

use FastRoute\Dispatcher;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher as CoreDispatcher;
use JTL\Events\Event;
use JTL\Language\LanguageHelper;
use JTL\Router\Controller\CategoryController;
use JTL\Router\Controller\ConsentController;
use JTL\Router\Controller\ControllerInterface;
use JTL\Router\Controller\DefaultController;
use JTL\Router\Controller\IOController;
use JTL\Router\Controller\ManufacturerController;
use JTL\Router\Controller\NewsController;
use JTL\Router\Controller\PageController;
use JTL\Router\Controller\ProductController;
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
     * @var bool
     */
    private bool $isMultilang = false;

    /**
     * @var string
     */
    private string $currentLanguageCode;

    public const TYPE_CATEGORY      = 'categories';
    public const TYPE_PRODUCT       = 'products';
    public const TYPE_PAGE          = 'pages';
    public const TYPE_NEWS          = 'news';
    public const TYPE_MANUFACTURERS = 'manufacturers';

    /**
     * @var ControllerInterface
     */
    private ControllerInterface $defaultController;

    /**
     * @param DbInterface $db
     * @param             $cache
     * @param State       $state
     */
    public function __construct(protected DbInterface $db, protected $cache, protected State $state)
    {
        $cgid       = Frontend::getCustomer()->getGroupID();
        $conf       = Shopsetting::getInstance()->getAll();
        $alert      = Shop::Container()->getAlertService();
        $groups     = [''];
        $codes      = [];
        $currencies = [];

        $productController      = new ProductController($db, $cache, $state, $cgid, $conf, $alert);
        $categoryController     = new CategoryController($db, $cache, $state, $cgid, $conf, $alert);
        $manufacturerController = new ManufacturerController($db, $cache, $state, $cgid, $conf, $alert);
        $newsController         = new NewsController($db, $cache, $state, $cgid, $conf, $alert);
        $pageController         = new PageController($db, $cache, $state, $cgid, $conf, $alert);
        $defaultController      = new DefaultController($db, $cache, $state, $cgid, $conf, $alert);
        $rootController         = new RootController($db, $cache, $state, $cgid, $conf, $alert);
        $consentController      = new ConsentController();
        $ioController           = new IOController($this->db, $cache, $state, $cgid, $conf, $alert);

        $this->router = new BaseRouter();
        $this->router->middleware(new MaintenanceModeMiddleware());
        $this->router->middleware(new WishlistCheckMiddleware());
        $this->router->middleware(new CartcheckMiddleware());
        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();
        $visibilityMiddleware   = new VisibilityMiddleware();

        foreach (LanguageHelper::getAllLanguages() as $language) {
            if (!\defined('URL_SHOP_' . \mb_convert_case($language->getIso(), \MB_CASE_UPPER))) {
                $codes[] = $language->getIso639();
            }
        }
        if (\count($codes) > 1) {
            $this->isMultilang = true;

            $groups[] = '/{lang:(?:' . \implode('|', $codes) . ')}';
        }
        foreach (Frontend::getCurrencies() as $currency) {
            $currencies[] = $currency->getCode();
        }
        $currencyPath = \count($currencies) > 1
            ? '[/{currency:(?:' . \implode('|', $currencies) . ')}]'
            : '';
        foreach ($groups as $localized) {
            $this->router->get($localized . '/products/{id:\d+}' . $currencyPath, [$productController, 'getResponse'])
                ->setName('ROUTE_PRODUCT_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''))
                ->middleware($visibilityMiddleware);
            $this->router->get($localized . '/products/{name}' . $currencyPath, [$productController, 'getResponse'])
                ->setName('ROUTE_PRODUCT_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''))
                ->middleware($visibilityMiddleware);
            $this->router->post($localized . '/products/{id:\d+}', [$productController, 'getResponse'])
                ->middleware($visibilityMiddleware);
            $this->router->post($localized . '/products/{name}', [$productController, 'getResponse'])
                ->middleware($visibilityMiddleware);

            $this->router->get($localized . '/categories/{id:\d+}', [$categoryController, 'getResponse'])
                ->setName('ROUTE_CATEGORY_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->get($localized . '/categories/{name}', [$categoryController, 'getResponse'])
                ->setName('ROUTE_CATEGORY_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->post($localized . '/categories/{id:\d+}', [$categoryController, 'getResponse']);
            $this->router->post($localized . '/categories/{name}', [$categoryController, 'getResponse']);

            $this->router->get($localized . '/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse'])
                ->setName('ROUTE_MANUFACTURER_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->get($localized . '/manufacturers/{name}', [$manufacturerController, 'getResponse'])
                ->setName('ROUTE_MANUFACTURER_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->post($localized . '/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse']);
            $this->router->post($localized . '/manufacturers/{name}', [$manufacturerController, 'getResponse']);

            $this->router->get($localized . '/news/{id:\d+}', [$newsController, 'getResponse'])
                ->setName('ROUTE_NEWS_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->get($localized . '/news/{name}', [$newsController, 'getResponse'])
                ->setName('ROUTE_NEWS_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->post($localized . '/news/{id:\d+}', [$newsController, 'getResponse']);
            $this->router->post($localized . '/news/{name}', [$newsController, 'getResponse']);

            $this->router->get($localized . '/pages/{id:\d+}', [$pageController, 'getResponse'])
                ->setName('ROUTE_PAGE_BY_ID' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->get($localized . '/pages/{name}', [$pageController, 'getResponse'])
                ->setName('ROUTE_PAGE_BY_NAME' . ($localized !== '' ? '_LOCALIZED' : ''));
            $this->router->post($localized . '/pages/{id:\d+}', [$pageController, 'getResponse']);
            $this->router->post($localized . '/pages/{name}', [$pageController, 'getResponse']);
        }
        $this->router->post('/_updateconsent', [$consentController, 'getResponse'])
            ->setStrategy(new JsonStrategy(new ResponseFactory()));

        $this->router->get('/io', [$ioController, 'getResponse']);
        $this->router->post('/io', [$ioController, 'getResponse']);

        $this->router->get('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);
        $this->router->post('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);

        $this->router->get('/', [$rootController, 'getResponse']);
        $this->router->post('/', [$rootController, 'getResponse']);

//        $this->router->get('/{any:.*}', [$defaultController, 'getResponse']);
//        $this->router->post('/{any:.*}', [$defaultController, 'getResponse']);

        $this->defaultController = $defaultController;
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
     * @param string     $type
     * @param array|null $replacements
     * @param bool       $byName
     * @return string
     */
    public function getPathByType(string $type, ?array $replacements = null, bool $byName = true): string
    {
        $name = match ($type) {
            self::TYPE_CATEGORY => 'ROUTE_CATEGORY_BY_',
            self::TYPE_PRODUCT => 'ROUTE_PRODUCT_BY_',
            self::TYPE_MANUFACTURERS => 'ROUTE_MANUFACTURER_BY_',
            self::TYPE_NEWS => 'ROUTE_NEWS_BY_',
            self::TYPE_PAGE => 'ROUTE_PAGE_BY_',
            default => 'ROUTE_XXX_BY_'
        };
        $name .= ($byName === true ? 'NAME' : 'ID');
        $name .= ($this->isMultilang === true ? '_LOCALIZED' : '');

        return $this->getNamedPath($name, $replacements);
    }

    /**
     * @param int        $linkType
     * @param array|null $replacements
     * @param bool       $byName
     * @return string
     */
    public function getPathByLinkType(int $linkType, ?array $replacements = null, bool $byName = true): string
    {
        $name = match ($linkType) {
//            \LINKTYP_NEWS => 'ROUTE_NEWS_BY_',
//            \LINKTYP_WARENKORB => 'ROUTE_CART_BY_',
//            \LINKTYP_BESTELLVORGANG => 'ROUTE_CHECKOUT_BY_',
            default => 'ROUTE_PAGE_BY_'
        };
        $name .= ($byName === true ? 'NAME' : 'ID');
        $name .= ($this->isMultilang === true ? '_LOCALIZED' : '');

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
            $quoted[] = preg_quote($key);
        }
        $isOptionalRegex = '/(.*)?{('
            . \implode('|', $quoted)
            . ')(:.*)?}(.*)?/';
//        Shop::dbg($matches['keys'], false, 'mathcKey');

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
                $item = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
//                Shop::dbg($item, false,'add@0');
                $segments[] = $item;
                continue;
            }

            // segment is a required wildcard, no replacement, still gets added
//            Shop::dbg($isOptionalRegex, false, 'optRegEx:');
//            Shop::dbg($segment,false,'seg:');
            if (!\preg_match($isOptionalRegex, $segment)) {
                $item = \preg_replace(['/\[$/', '/\]+$/'], '', $segment);
                Shop::dbg($item, false, 'add@1');
                $segments[] = $item;
                continue;
            }

            // segment is completely optional with no replacement, strip it and break
            if (\preg_match($isOptionalRegex, $segment) && !\preg_match($hasReplacementRegex, $segment)) {
                break;
            }
        }
//        Shop::dbg($segments, false, 'segments:');

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
        $strategy = new SmartyStrategy(new ResponseFactory(), Shop::Smarty(), $this->state);
        $this->router->setStrategy($strategy);
        $request     = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        $shopURLdata = \parse_url(Shop::getURL());
        if (isset($shopURLdata['path'])) { // @todo find a better solution
            $baseURLdata = \parse_url($this->getRequestURL());
            $path        = '/' . \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path'] ?? '') + 1);
            $uri         = $request->getUri();
            $request     = $request->withUri($uri->withPath($path));
        }
        try {
            $response = $this->router->dispatch($request);
        } catch (\Exception $e) {
            $response = $this->defaultController->getResponse($request, [], Shop::Smarty());
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
