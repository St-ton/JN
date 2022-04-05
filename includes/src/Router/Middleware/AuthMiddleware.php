<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Cart\CartHelper;
use JTL\Shop;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthMiddleware
 * @package JTL\Router\Middleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $oAccount = Shop::Container()->getAdminAccount();
        if (!$oAccount->logged()) {
            return new RedirectResponse(Shop::getAdminURL(), 301);
        }

        return $handler->handle($request);
    }
}
