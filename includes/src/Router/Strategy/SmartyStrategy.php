<?php declare(strict_types=1);

namespace JTL\Router\Strategy;

use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SmartyStrategy
 * @package JTL\Router\Strategy
 */
class SmartyStrategy extends ApplicationStrategy
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param JTLSmarty                $smarty
     * @param State                    $state
     */
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected JTLSmarty $smarty,
        protected State $state
    ) {
    }

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());

        return $this->decorateResponse($controller($request, $route->getVars(), $this->smarty));
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }
}
