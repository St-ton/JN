<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LicenseController
 * @package JTL\Router\Controller\Backend
 */
class LicenseController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/licenses');
        $this->getText->loadAdminLocale('pages/pluginverwaltung');
        $checker = new Checker(Shop::Container()->getLogService(), $this->db, $this->cache);
        $manager = new Manager($this->db, $this->cache);
        $admin   = new Admin($manager, $this->db, $this->cache, $checker);
        if (Request::postVar('action') === 'code') {
            $admin->handleAuth();
            exit();
        }
        $this->checkPermissions(Permissions::LICENSE_MANAGER);
        $admin->handle($smarty);

        return $smarty->assign('route', $this->route)
            ->getResponse('licenses.tpl');
    }
}
