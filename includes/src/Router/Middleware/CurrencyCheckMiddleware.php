<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Session\Frontend;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CurrencyCheckMiddleware
 * @package JTL\Router\Middleware
 */
class CurrencyCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($currencyCode = $request->getAttribute('currency')) !== null) {
            Frontend::updateCurrency($currencyCode);
        }

        return $handler->handle($request);
    }
}
