<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Status;
use JTL\Export\Admin;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ExportController
 * @package JTL\Router\Controller\Backend
 */
class ExportController extends AbstractBackendController
{
    /**
     * @inheritdoc
     * @todo!
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('EXPORT_FORMATS_VIEW');
        $this->getText->loadAdminLocale('pages/exportformate');
        $this->getText->loadConfigLocales(true, true);
        $this->cache->flushTags([Status::CACHE_ID_EXPORT_SYNTAX_CHECK]);
        $smarty->assign('route', $this->route);
        $admin = new Admin($this->db, $this->alertService, $smarty);
        $admin->getAction();

        return $admin->display();
    }
}
