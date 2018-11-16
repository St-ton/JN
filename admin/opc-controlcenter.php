<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global Smarty\JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

GetText::getInstance()->loadAdminLocale('opc-controlcenter');

$notice = '';
$error  = '';
$action = RequestHelper::verifyGPDataString('action');

$opc       = Shop::Container()->getOPC();
$opcPage   = Shop::Container()->getOPCPageService();
$opcPageDB = Shop::Container()->getOPCPageDB();

$pagesPagi = (new Pagination('pages'))
    ->setItemCount($opcPageDB->getPageCount())
    ->assemble();

if (FormHelper::validateToken()) {
    if ($action === 'restore') {
        $pageId = RequestHelper::verifyGPDataString('pageId');
        $opcPage->deletePage($pageId);
        $notice = __('The OPC content for this page has been reset.');
    } elseif ($action === 'discard') {
        $pageKey = RequestHelper::verifyGPCDataInt('pageKey');
        $opcPage->deleteDraft($pageKey);
        $notice = __('The draft has been deleted.');
    }
}

$smarty
    ->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->assign('cHinweis', $notice)
    ->assign('cFehler', $error)
    ->display('opc-controlcenter.tpl');
