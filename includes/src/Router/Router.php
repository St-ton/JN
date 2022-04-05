<?php declare(strict_types=1);

namespace JTL\Router;

use FastRoute\Dispatcher;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher as CoreDispatcher;
use JTL\Events\Event;
use JTL\Router\Controller\Backend\BlubbController;
use JTL\Router\Controller\Backend\FooController;
use JTL\Router\Controller\ConsentController;
use JTL\Router\Handler\CategoryHandler;
use JTL\Router\Handler\DefaultHandler;
use JTL\Router\Handler\ManufacturerHandler;
use JTL\Router\Handler\NewsHandler;
use JTL\Router\Handler\PageHandler;
use JTL\Router\Handler\ProductHandler;
use JTL\Router\Handler\RootHandler;
use JTL\Router\Middleware\CartcheckMiddleware;
use JTL\Router\Middleware\MaintenanceModeMiddleware;
use JTL\Router\Middleware\PhpFileCheckMiddleware;
use JTL\Router\Middleware\VisibilityMiddleware;
use JTL\Router\Middleware\WishlistCheckMiddleware;
use JTL\Router\Strategy\SmartyStrategy;
use JTL\Shop;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
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
     * @var State
     */
    private State $state;

    /**
     * @var BaseRouter
     */
    private BaseRouter $router;

    /**
     * @param DbInterface $db
     * @param State       $state
     */
    public function __construct(DbInterface $db, State $state)
    {
        $this->state = $state;

        $productHandler      = new ProductHandler($db, $this->state);
        $categoryHandler     = new CategoryHandler($db, $this->state);
        $manufacturerHandler = new ManufacturerHandler($db, $this->state);
        $newsHandler         = new NewsHandler($db, $this->state);
        $pageHandler         = new PageHandler($db, $this->state);
        $defaultHandler      = new DefaultHandler($db, $this->state);
        $rootHandler         = new RootHandler($db, $this->state);
        $consentController   = new ConsentController();

        $router = new BaseRouter();


        $responseFactory = new ResponseFactory();

        $strategy = new SmartyStrategy($responseFactory, Shop::Smarty(), $state);

        $router->setStrategy($strategy);

        $router->middleware(new MaintenanceModeMiddleware());
        $router->middleware(new WishlistCheckMiddleware());
        $router->middleware(new CartcheckMiddleware());
        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();

        $router->get('/products/{id:\d+}', [$productHandler, 'handle'])->middleware(new VisibilityMiddleware());
        $router->post('/products/{id:\d+}', [$productHandler, 'handle']);

        $router->post('/_updateconsent', [$consentController, 'handle'])
            ->setStrategy(new JsonStrategy($responseFactory));

        $router->get('/categories/{id:\d+}', [$categoryHandler, 'handle']);
        $router->post('/categories/{id:\d+}', [$categoryHandler, 'handle']);

        $router->get('/manufacturers/{id:\d+}', [$manufacturerHandler, 'handle']);
        $router->post('/manufacturers/{id:\d+}', [$manufacturerHandler, 'handle']);

        $router->get('/news/{id:\d+}', [$newsHandler, 'handle']);
        $router->post('/news/{id:\d+}', [$newsHandler, 'handle']);

        $router->get('/page/{id:\d+}', [$pageHandler, 'handle']);
        $router->post('/page/{id:\d+}', [$pageHandler, 'handle']);

        $router->get('/{slug}', [$defaultHandler, 'handle'])->middleware($phpFileCheckMiddleware);
        $router->post('/{slug}', [$defaultHandler, 'handle'])->middleware($phpFileCheckMiddleware);

        $router->get('/', [$rootHandler, 'handle']);
        $router->post('/', [$rootHandler, 'handle']);

        $router->get('/{any:.*}', [$defaultHandler, 'handle']);
        $router->post('/{any:.*}', [$defaultHandler, 'handle']);

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
        (new SapiEmitter())->emit($response);
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
