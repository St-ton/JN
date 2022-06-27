<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\AdminAccount;
use JTL\DB\DbInterface;
use JTL\Router\BackendRouter;
use JTL\Session\Backend;
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
        if ($request->getMethod() !== 'GET') {
            return $handler->handle($request);
        }
        if ((new Updater($this->db))->hasPendingUpdates()) {
            $path = $request->getUri()->getPath();
            if (!\str_contains($path, BackendRouter::ROUTE_LOGOUT)
                && !\str_contains($path, BackendRouter::ROUTE_DBUPDATER)
                && !\str_ends_with($path, BackendRouter::ROUTE_IO)
                && ($request->getQueryParams()['action'] ?? null) !== 'quick_change_language'
                && $this->account->logged()
            ) {
                return new RedirectResponse(Shop::getAdminURL() . '/' . BackendRouter::ROUTE_DBUPDATER);
            }
        } elseif (!empty($_COOKIE['JTLSHOP']) && empty($_SESSION['frontendUpToDate'])) {
            $adminToken   = $_SESSION['jtl_token'];
            $adminLangTag = $_SESSION['AdminAccount']->language ?? 'de-DE';
            $sessionID    = \session_id();
            \session_write_close();
            \session_name('JTLSHOP');
            \session_id($_COOKIE['JTLSHOP']);
            \session_start();
            $_SESSION['loggedAsAdmin'] = $this->account->logged();
            $_SESSION['adminToken']    = $adminToken;
            $_SESSION['adminLangTag']  = $adminLangTag;
            \session_write_close();
            \session_name('eSIdAdm');
            \session_id($sessionID);
            $session = new Backend();
            $session::set('frontendUpToDate', true);
        }

        return $handler->handle($request);
    }
}
