<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
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
        $this->checkPermissions(Permissions::OPC_VIEW);
        $this->getText->loadAdminLocale('pages/opc-controlcenter');

        $action    = $this->request->request('action');
        $opc       = Shop::Container()->getOPC();
        $opcPage   = Shop::Container()->getOPCPageService();
        $opcPageDB = Shop::Container()->getOPCPageDB();
        $pagesPagi = (new Pagination('pages'))
            ->setItemCount($opcPageDB->getPageCount())
            ->assemble();

        if ($this->tokenIsValid) {
            if ($action === 'restore') {
                $pageId = $this->request->request('pageId');
                $opcPage->deletePage($pageId);
                $this->alertService->addNotice(\__('opcNoticePageReset'), 'opcNoticePageReset');
            } elseif ($action === 'discard') {
                $pageKey = $this->request->requestInt('pageKey');
                $opcPage->deleteDraft($pageKey);
                $this->alertService->addNotice(\__('opcNoticeDraftDelete'), 'opcNoticeDraftDelete');
            }
        }

        return $this->smarty->assign('opc', $opc)
            ->assign('opcPageDB', $opcPageDB)
            ->assign('pagesPagi', $pagesPagi)
            ->getResponse('opc-controlcenter.tpl');
    }
}
