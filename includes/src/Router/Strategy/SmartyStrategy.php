<?php declare(strict_types=1);

namespace JTL\Router\Strategy;

use JTL\Router\Controller\PageController;
use JTL\Router\State;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use League\Route\Http\Exception\HttpExceptionInterface;
use League\Route\Route;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

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
        $response   = $controller($request, $route->getVars(), $this->smarty);

        return $this->decorateResponse($response);
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @return MiddlewareInterface
     * @todo
     */
    public function getThrowableHandler2(): MiddlewareInterface
    {
        return new class (
            $this->responseFactory->createResponse(),
            $this->smarty,
            $this->state
        ) implements MiddlewareInterface {
            /**
             * @param ResponseInterface $response
             * @param JTLSmarty         $smarty
             * @param State             $state
             */
            public function __construct(
                protected ResponseInterface $response,
                protected JTLSmarty $smarty,
                protected State $state
            ) {
            }

            /**
             * @param ServerRequestInterface  $request
             * @param RequestHandlerInterface $handler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    $response = $this->response;

                    if ($exception instanceof HttpExceptionInterface) {
                        $this->state->languageID = $this->state->languageID ?: Shop::getLanguageID();
                        $this->state->is404      = true;
                        $this->state->linkID     = Shop::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404)
                            ?: 0;
                        $pc                      = new PageController(
                            Shop::Container()->getDB(),
                            Shop::Container()->getCache(),
                            $this->state,
                            Shopsetting::getInstance()->getAll(),
                            Shop::Container()->getAlertService()
                        );
                        $pc->init();

                        return $pc->getResponse($request, [], $this->smarty)->withStatus(404);
                    }


                    $response = $response->withAddedHeader('content-type', 'text/html');
                    $response = $response->withStatus(500, \strtok($exception->getMessage(), "\n"));
                    if ($response->getBody()->isWritable()) {
                        $response->getBody()->write(
                            'Error ' . $response->getStatusCode() . "\n"
                            . $response->getReasonPhrase()
                        );
                    }

                    return $response;
                }
            }
        };
    }
}
