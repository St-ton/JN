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
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * @var JTLSmarty
     */
    protected JTLSmarty $smarty;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param JTLSmarty                $smarty
     * @param State                    $state
     */
    public function __construct(ResponseFactoryInterface $responseFactory, JTLSmarty $smarty, State $state)
    {
        $this->responseFactory = $responseFactory;
        $this->smarty          = $smarty;
        $this->state           = $state;
    }

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());
        $body       = $controller($request, $route->getVars(), $this->smarty);
        $response   = $this->responseFactory->createResponse();
        $response->getBody()->write($body);

        return $this->decorateResponse($response);
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }
}
