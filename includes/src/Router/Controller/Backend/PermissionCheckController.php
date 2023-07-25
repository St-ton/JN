<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Status;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Systemcheck\Platform\Filesystem;

/**
 * Class PermissionCheckController
 * @package JTL\Router\Controller\Backend
 */
class PermissionCheckController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::PERMISSIONCHECK_VIEW);
        $this->getText->loadAdminLocale('pages/permissioncheck');
        $this->cache->flush(Status::CACHE_ID_FOLDER_PERMISSIONS);
        $fsCheck = new Filesystem(\PFAD_ROOT); // to get all folders which need to be writable

        return $smarty->assign('cDirAssoc_arr', $fsCheck->getFoldersChecked())
            ->assign('oStat', $fsCheck->getFolderStats())
            ->getResponse('permissioncheck.tpl');
    }
}
