<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OPCCCController
 * @package JTL\Router\Controller\Backend
 */
class OPCCCController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::OPC_VIEW);
        $this->getText->loadAdminLocale('pages/opc-controlcenter');

        $action    = Request::verifyGPDataString('action');
        $opc       = Shop::Container()->getOPC();
        $opcPage   = Shop::Container()->getOPCPageService();
        $opcPageDB = Shop::Container()->getOPCPageDB();
        $pagesPagi = (new Pagination('pages'))
            ->setItemCount($opcPageDB->getPageCount())
            ->assemble();

        if (Form::validateToken()) {
            if ($action === 'restore') {
                $pageId = Request::verifyGPDataString('pageId');
                $opcPage->deletePage($pageId);
                $this->alertService->addNotice(\__('opcNoticePageReset'), 'opcNoticePageReset');
            } elseif ($action === 'discard') {
                $pageKey = Request::verifyGPCDataInt('pageKey');
                $opcPage->deleteDraft($pageKey);
                $this->alertService->addNotice(\__('opcNoticeDraftDelete'), 'opcNoticeDraftDelete');
            }
        }

        return $smarty->assign('opc', $opc)
            ->assign('opcPageDB', $opcPageDB)
            ->assign('pagesPagi', $pagesPagi)
            ->getResponse('opc-controlcenter.tpl');
    }
}
