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
$opc    = \Shop::Container()->getOPC();
$opcdb  = \Shop::Container()->getOPCDB();

$pagesPagi = (new Pagination('pages'))
    ->setItemCount($opcdb->getPageCount())
    ->assemble();

if (validateToken() && $action === 'restore') {
    $pageId = verifyGPDataString('pageId');
    $page   = $opc->getPage($pageId);
    $opc->deletePage($pageId);
    $notice = 'Der Composer-Inhalt für die Seite "' . $page->getUrl() . '" wurde zurückgesetzt.';
}

$smarty
    ->assign('opc', $opc)
    ->assign('pagesPagi', $pagesPagi)
    ->assign('cHinweis', $notice)
    ->assign('cFehler', $error)
    ->display('opc-controlcenter.tpl');
