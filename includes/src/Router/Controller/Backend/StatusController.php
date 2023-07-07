<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Status;
use JTL\Network\JTLApi;
use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class StatusController
 * @package JTL\Router\Controller\Backend
 */
class StatusController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::DIAGNOSTIC_VIEW);
        $this->getText->loadAdminLocale('pages/status');

        return $this->smarty->assign('status', Status::getInstance($this->db, $this->cache, true))
            ->assign('sub', Shop::Container()->get(JTLApi::class)->getSubscription())
            ->getResponse('status.tpl');
    }
}
