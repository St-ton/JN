<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\AdminAccount;
use JTL\DB\DbInterface;
use JTL\Router\BackendRouter;
use JTL\Shop;
use JTL\Update\Updater;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class UpdateCheckMiddleware
 * @package JTL\Router\Middleware
 */
class UpdateCheckMiddleware implements MiddlewareInterface
{
    /**
     * @param DbInterface  $db
     * @param AdminAccount $account
     */
    public function __construct(private DbInterface $db, private AdminAccount $account)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            $updater = new Updater($this->db);
            if ($updater->hasPendingUpdates()) {
                $requestURI = $request->getUri();
                $path       = $requestURI->getPath();
                if (!\str_contains($path, BackendRouter::ROUTE_LOGOUT)
                    && !\str_contains($path, BackendRouter::ROUTE_DBUPDATER)
                    && !\str_ends_with($path, BackendRouter::ROUTE_IO)
                    && ($request->getQueryParams()['action'] ?? null) !== 'quick_change_language'
                    && $this->account->logged()
                ) {
                    return new RedirectResponse(Shop::getAdminURL() . '/' . BackendRouter::ROUTE_DBUPDATER);
                }
            }
        }

        return $handler->handle($request);
    }
}
