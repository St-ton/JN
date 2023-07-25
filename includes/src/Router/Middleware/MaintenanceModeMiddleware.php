<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class MaintenanceModeMiddleware
 * @package JTL\Router\Middleware
 */
class MaintenanceModeMiddleware implements MiddlewareInterface
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
        if (\JTL_INCLUDE_ONLY_DB || \defined('CLI_BATCHRUN')) {
            return $handler->handle($request);
        }
        if ((\SAFE_MODE === true || $this->globalConfig['wartungsmodus_aktiviert'] === 'Y')
            && $request->getRequestTarget() !== '/wartung.php'
            && !Shop::isAdmin(true)
        ) {
            Shop::getState()->fileName = 'wartung.php';
            $request                   = $request->withUri($request->getUri()->withPath('/wartung.php'));
        }

        return $handler->handle($request);
    }
}
