<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Status;
use JTL\Network\JTLApi;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class StatusMailController
 * @package JTL\Router\Controller\Backend
 */
class StatusMailController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('DIAGNOSTIC_VIEW');
        $this->getText->loadAdminLocale('pages/status');

        return $smarty->assign('status', Status::getInstance($this->db, $this->cache, true))
            ->assign('sub', Shop::Container()->get(JTLApi::class)->getSubscription())
            ->assign('route', $this->route)
            ->getResponse('status.tpl');
    }
}
