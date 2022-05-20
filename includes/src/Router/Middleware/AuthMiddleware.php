<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\AdminAccount;
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
            $uri = $request->getUri()->getPath();
            $url = !\str_contains(\basename($uri), 'logout')
                ? '/?uri=' . \base64_encode(\ltrim($uri, \PFAD_ADMIN))
                : '/';

            return new RedirectResponse(Shop::getAdminURL() . $url, 301);
        }

        return $handler->handle($request);
    }
}
