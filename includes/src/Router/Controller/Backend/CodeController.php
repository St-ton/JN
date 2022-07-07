<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AuthToken;
use JTL\Helpers\Request;
use JTL\Router\Route;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CodeController
 * @package JTL\Router\Controller\Backend
 */
class CodeController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        if (empty($args['redir'])) {
            return $smarty->getResponse('string:');
        }
        if (Request::postVar('code') !== null || Request::postVar('token') !== null) {
            $auth = AuthToken::getInstance($this->db);
            $auth->responseToken();
        }

        return new RedirectResponse($this->baseURL . '/' . $this->getRedirectURL($args['redir']));
    }

    /**
     * @param string $redir
     * @return string
     */
    private function getRedirectURL(string $redir): string
    {
        return match ($redir) {
            'wizard'        => Route::WIZARD,
            'premiumplugin' => Route::PREMIUM_PLUGIN,
            default         => Route::LICENSE,
        };
    }
}
