<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Pagination;

/**
 * @global \Smarty\JTLSmarty     $smarty
 * @global \Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$action      = Request::verifyGPDataString('action');
$alertHelper = Shop::Container()->getAlertService();

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
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('opcNoticePageReset'), 'opcNoticePageReset');
    } elseif ($action === 'discard') {
        $pageKey = Request::verifyGPCDataInt('pageKey');
        $opcPage->deleteDraft($pageKey);
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('opcNoticeDraftDelete'), 'opcNoticeDraftDelete');
    }
}

$smarty
    ->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->display('opc-controlcenter.tpl');
