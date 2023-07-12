<?php declare(strict_types=1);

namespace JTL\Router\Strategy;

use JTL\Router\Controller\Backend\ControllerInterface as BackendControllerInterface;
use JTL\Router\Controller\ControllerInterface as FrontendControllerInterface;
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
        protected JTLSmarty                $smarty,
        protected State                    $state
    ) {
    }

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        /** @var \Closure $controller */
        $controller = $route->getCallable($this->getContainer());
        if (\is_array($controller) && \count($controller) === 2) {
            $instance = $controller[0];
            if ($instance instanceof BackendControllerInterface || $instance instanceof FrontendControllerInterface) {
                $instance->initController($request, $this->smarty);
            }
        }

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
