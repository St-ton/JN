<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LogoutController
 * @package JTL\Router\Controller\Backend
 */
class LogoutController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if ($this->tokenIsValid) {
            $this->account->logout();
        }

        return new RedirectResponse($this->baseURL . '/');
    }
}
