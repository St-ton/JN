<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Pagination\Pagination;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$notice = '';
$error  = '';
$action = Request::verifyGPDataString('action');

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
        $notice = __('opcNoticePageReset');
    } elseif ($action === 'discard') {
        $pageKey = Request::verifyGPCDataInt('pageKey');
        $opcPage->deleteDraft($pageKey);
        $notice = __('opcNoticeDraftDelete');
    }
}

$smarty
    ->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->assign('cHinweis', $notice)
    ->assign('cFehler', $error)
    ->display('opc-controlcenter.tpl');
