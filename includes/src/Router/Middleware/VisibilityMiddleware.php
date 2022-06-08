<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Services\JTL\LinkService;
use JTL\Session\Frontend;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class VisibilityMiddleware
 * @package JTL\Router\Middleware
 */
class VisibilityMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $state    = $handler->getStrategy()?->getState();
        if (($state->productID > 0 || $state->categoryID > 0) && !Frontend::getCustomerGroup()->mayViewCategories()) {
            // falls Artikel/Kategorien nicht gesehen werden duerfen -> login
            return new RedirectResponse(LinkService::getInstance()->getStaticRoute('jtl.php') . '?li=1', 303);
        }

        return $response;
    }
}
