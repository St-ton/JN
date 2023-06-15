<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\DB\DbInterface;
use JTL\Router\Route;
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
     * @param DbInterface $db
     */
    public function __construct(private DbInterface $db)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'GET'
            && Shop::isAdmin()
            && !Backend::get('redirectedToWizard')
            && Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_wizard_done') === 'N'
            && !\str_contains($request->getUri()->getPath(), Route::WIZARD)
            && (new Updater($this->db))->hasPendingUpdates() === false
        ) {
            return new RedirectResponse(Shop::getAdminURL() . '/' . Route::WIZARD);
        }

        return $handler->handle($request);
    }
}
