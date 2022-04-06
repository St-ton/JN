<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Consent\Admin;
use JTL\Consent\ConsentModel;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConsentController
 * @package JTL\Router\Controller\Backend
 */
class ConsentController extends AbstractBackendController
{
    /**
     * @inheritdoc
     * @todo!
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('CONTENT_PAGE_VIEW');
        $this->getText->loadAdminLocale('pages/consent');
        $this->smarty->assign('route', $this->route);

        $admin = new Admin(
            new ConsentModel(),
            \ltrim($this->route, '/'),
            $this->db,
            $this->alertService
        );
        $admin->handle();

        return $admin->display($smarty, 'consent.tpl');
    }
}
