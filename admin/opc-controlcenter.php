<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$notice = '';
$error  = '';
$action = verifyGPDataString('action');

$opc       = \Shop::Container()->getOPC();
$opcPage   = \Shop::Container()->getOPCPageService();
$opcPageDB = \Shop::Container()->getOPCPageDB();

$pagesPagi = (new Pagination('pages'))
    ->setItemCount($opcPageDB->getPageCount())
    ->assemble();

if (validateToken()) {
    if ($action === 'restore') {
        $pageId = verifyGPDataString('pageId');
        $opcPage->deletePage($pageId);
        $notice = 'Der Composer-Inhalt für die Seite wurde zurückgesetzt.';
    } elseif ($action === 'discard') {
        $pageKey = verifyGPDataString('pageKey');
        $opcPage->deleteDraft($pageKey);
        $notice = 'Der Entwurf wurde gelöscht.';
    }
}

$smarty
    ->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->assign('cHinweis', $notice)
    ->assign('cFehler', $error)
    ->display('opc-controlcenter.tpl');
