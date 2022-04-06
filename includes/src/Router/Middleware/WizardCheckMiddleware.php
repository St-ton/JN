<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\AdminAccount;
use JTL\DB\DbInterface;
use JTL\Router\BackendRouter;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Update\Updater;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class WizardCheckMiddleware
 * @package JTL\Router\Middleware
 */
class WizardCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'GET'
            && !Backend::get('redirectedToWizard')
            && Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_wizard_done') === 'Y'
            && !\str_contains($request->getUri()->getPath(), BackendRouter::ROUTE_WIZARD)
        ) {
            return new RedirectResponse(Shop::getAdminURL() . '/' . BackendRouter::ROUTE_WIZARD);
        }

        return $handler->handle($request);
    }
}
