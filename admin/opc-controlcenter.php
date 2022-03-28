<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('OPC_VIEW', true, true);

$action      = Request::verifyGPDataString('action');
$alertHelper = Shop::Container()->getAlertService();
$opc         = Shop::Container()->getOPC();
$opcPage     = Shop::Container()->getOPCPageService();
$opcPageDB   = Shop::Container()->getOPCPageDB();
$pagesPagi   = (new Pagination('pages'))
    ->setItemCount($opcPageDB->getPageCount())
    ->assemble();

if (Form::validateToken()) {
    if ($action === 'restore') {
        $pageId = Request::verifyGPDataString('pageId');
        $opcPage->deletePage($pageId);
        $alertHelper->addNotice(__('opcNoticePageReset'), 'opcNoticePageReset');
    } elseif ($action === 'discard') {
        $pageKey = Request::verifyGPCDataInt('pageKey');
        $opcPage->deleteDraft($pageKey);
        $alertHelper->addNotice(__('opcNoticeDraftDelete'), 'opcNoticeDraftDelete');
    }
}

$smarty->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->display('opc-controlcenter.tpl');
