<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Shop;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class PhpFileCheckMiddleware
 * @package JTL\Router\Middleware
 */
class PhpFileCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $slug = $request->getAttribute('slug');
        if ($slug !== null && \str_ends_with($slug, '.php')) {
            $url = Shop::Container()->getLinkService()->getStaticRoute($slug);
            if (!\str_ends_with($url, '.php')) {
                $query = '';
                if (\count($request->getQueryParams()) > 0) {
                    $query = '?' . \http_build_query($request->getQueryParams());
                }

                return new RedirectResponse($url . $query, 301);
            }
        }

        return $handler->handle($request);
    }
}
