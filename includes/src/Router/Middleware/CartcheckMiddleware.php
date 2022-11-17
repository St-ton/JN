<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Cart\CartHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CartcheckMiddleware
 * @package JTL\Router\Middleware
 */
class CartcheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        \executeHook(\HOOK_INDEX_NAVI_HEAD_POSTGET);
        CartHelper::checkAdditions();

        return $handler->handle($request);
    }
}
