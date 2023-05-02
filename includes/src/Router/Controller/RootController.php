<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\ControllerFactory;
use JTL\Smarty\JTLSmarty;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RootController
 * @package JTL\Router\Controller
 */
class RootController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $route->get('/', [$this, 'getResponse'])->setName('ROUTE_ROOT' . $dynName);
        $route->post('/', [$this, 'getResponse'])->setName('ROUTE_ROOTPOST' . $dynName);
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->state->pageType = \PAGE_STARTSEITE;
        $this->state->linkType = \LINKTYP_STARTSEITE;

        $factory    = new ControllerFactory($this->state, $this->db, $this->cache, $smarty);
        $controller = $factory->getEntryPoint($request);
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
