<?php declare(strict_types=1);

namespace JTL\Router;

use FastRoute\Dispatcher;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher as CoreDispatcher;
use JTL\Events\Event;
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

        $router = new BaseRouter();

        $responseFactory = new ResponseFactory();

        $strategy = new SmartyStrategy($responseFactory, Shop::Smarty(), $state);

        $router->setStrategy($strategy);

        $router->middleware(new MaintenanceModeMiddleware());
        $router->middleware(new WishlistCheckMiddleware());
        $router->middleware(new CartcheckMiddleware());
        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();
        $visibilityMiddleware   = new VisibilityMiddleware();

        $router->get('/products/{id:\d+}', [$productController, 'getResponse'])->middleware($visibilityMiddleware);
        $router->get('/products/{name}', [$productController, 'getResponse'])->middleware($visibilityMiddleware);
        $router->post('/products/{id:\d+}', [$productController, 'getResponse'])->middleware($visibilityMiddleware);
        $router->post('/products/{name}', [$productController, 'getResponse'])->middleware($visibilityMiddleware);

        $router->post('/_updateconsent', [$consentController, 'getResponse'])
            ->setStrategy(new JsonStrategy($responseFactory));

        $router->get('/categories/{id:\d+}', [$categoryController, 'getResponse']);
        $router->post('/categories/{id:\d+}', [$categoryController, 'getResponse']);
        $router->get('/categories/{name}', [$categoryController, 'getResponse']);
        $router->post('/categories/{name}', [$categoryController, 'getResponse']);

        $router->get('/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse']);
        $router->post('/manufacturers/{id:\d+}', [$manufacturerController, 'getResponse']);
        $router->get('/manufacturers/{name}', [$manufacturerController, 'getResponse']);
        $router->post('/manufacturers/{name}', [$manufacturerController, 'getResponse']);

        $router->get('/news/{id:\d+}', [$newsController, 'getResponse']);
        $router->post('/news/{id:\d+}', [$newsController, 'getResponse']);

        $router->get('/page/{id:\d+}', [$pageController, 'getResponse']);
        $router->post('/page/{id:\d+}', [$pageController, 'getResponse']);
        $router->get('/page/{name}', [$pageController, 'getResponse']);
        $router->post('/page/{name}', [$pageController, 'getResponse']);

        $router->get('/io', [$ioController, 'getResponse']);
        $router->post('/io', [$ioController, 'getResponse']);

        $router->get('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);
        $router->post('/{slug}', [$defaultController, 'getResponse'])->middleware($phpFileCheckMiddleware);

        $router->get('/', [$rootController, 'getResponse']);
        $router->post('/', [$rootController, 'getResponse']);

        $router->get('/{any:.*}', [$defaultController, 'getResponse']);
        $router->post('/{any:.*}', [$defaultController, 'getResponse']);

        $this->router = $router;
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
