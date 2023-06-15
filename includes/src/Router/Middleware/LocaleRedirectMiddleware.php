<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class LocaleRedirectMiddleware
 * @package JTL\Router\Middleware
 */
class LocaleRedirectMiddleware implements MiddlewareInterface
{
    /**
     * @param string $defaultLocale
     */
    public function __construct(private readonly string $defaultLocale)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute('lang') === $this->defaultLocale) {
            $uri = $request->getUri();
            if (\str_starts_with($uri->getPath(), '/' . $this->defaultLocale)) {
                $path = \mb_substr($uri->getPath(), \strlen('/' . $this->defaultLocale));

                return new RedirectResponse($uri->withPath($path), 301);
            }
        }

        return $handler->handle($request);
    }
}
