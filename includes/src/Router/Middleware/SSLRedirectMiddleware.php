<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SSLRedirectMiddleware
 * @package JTL\Router\Middleware
 */
class SSLRedirectMiddleware implements MiddlewareInterface
{
    /**
     * @param array $globalConfig
     */
    public function __construct(private readonly array $globalConfig)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\PHP_SAPI === 'cli' || $this->globalConfig['kaufabwicklung_ssl_nutzen'] !== 'P') {
            return $handler->handle($request);
        }
        $params = $request->getServerParams();
        if (empty($params['HTTPS']) || $params['HTTPS'] === 'off') {
            $https = ($params['HTTP_X_FORWARDED_HOST'] ?? '' === 'ssl.webpack.de')
                || ($params['HTTP_X_FORWARDED_PROTO'] ?? '' === 'https')
                || \str_starts_with($params['SCRIPT_URI'] ?? '', 'ssl-id')
                || \str_starts_with($params['HTTP_X_FORWARDED_HOST'] ?? '', 'ssl');
            if (!$https) {
                return new RedirectResponse($request->getUri()->withScheme('https'), 301);
            }
        }

        return $handler->handle($request);
    }
}
