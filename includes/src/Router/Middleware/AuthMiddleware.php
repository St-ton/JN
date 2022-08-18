<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\AdminAccount;
use JTL\Backend\AdminLoginStatus;
use JTL\Session\Backend;
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
     * @param AdminAccount $account
     */
    public function __construct(private AdminAccount $account)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->account->logged()) {
            $uri      = $request->getUri()->getPath();
            $basePath = (\parse_url(Shop::getURL(), \PHP_URL_PATH) ?? '') . '/' . \PFAD_ADMIN;
            $url      = !\str_contains(\basename($uri), 'logout')
                ? '/?uri=' . \base64_encode(\str_replace($basePath, '', $uri))
                : '/';

            return new RedirectResponse(Shop::getAdminURL() . $url, 301);
        }
        if (isset($GLOBALS['plgSafeMode'])) {
            if ($GLOBALS['plgSafeMode']) {
                \touch(\SAFE_MODE_LOCK);
            } elseif (\file_exists(\SAFE_MODE_LOCK)) {
                \unlink(\SAFE_MODE_LOCK);
            }
        }
        if (!Backend::getInstance()->isValid()) {
            $this->account->logout();

            return new RedirectResponse(Shop::getAdminURL() . '/?errCode=' . AdminLoginStatus::ERROR_SESSION_INVALID);
        }

        return $handler->handle($request);
    }
}
