<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Helpers\Text;
use JTL\Services\JTL\LinkService;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class WishlistCheckMiddleware
 * @package JTL\Router\Middleware
 */
class WishlistCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $params   = $request->getQueryParams();
        if (($params['wlid'] ?? '') !== '' && ($params['error'] ?? '') === '') {
            $state = $handler->getStrategy()?->getState();
            if ($state === null || $state->wishlistID === 0) {
                return new RedirectResponse(LinkService::getInstance()->getStaticRoute('wunschliste.php')
                    . '?wlid=' . Text::filterXSS($params['wlid']) . '&error=1', 303);
            }
        }

        return $response;
    }
}
