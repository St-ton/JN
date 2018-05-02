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


/*
$hinweis       = '';
$fehler        = '';
$step          = 'uebersicht';
$links         = null;
$clearCache    = false;
$continue      = true;
$oPagiCmsLinks = null;

if ($step === 'uebersicht') {
    $lCount = Shop::DB()->query("SELECT COUNT(cPageId) AS anz FROM topcpage", 1);

    // Paginationen
    $oPagiCmsLinks = (new Pagination('cmsLinks'))
        ->setItemCount((int)$lCount->anz)
        ->setSortByOptions([
            ['kPage', 'ID'],
            ['dLastModified', 'Datum'],
            ['cPageURL', 'URL']
        ])
        ->assemble();

    $links = Shop::DB()->query(
        "SELECT *
            FROM topcpage
            ORDER BY " . $oPagiCmsLinks->getOrderSQL() . "
            LIMIT " . $oPagiCmsLinks->getLimitSQL(),
        2
    );
}


$smarty->assign('step', $step)
    ->assign('hinweis', $hinweis)
    ->assign('fehler', $fehler)
    ->assign('links', $links)
    ->assign('oPagiCmsLinks', $oPagiCmsLinks)
    ->display('opc-controlcenter.tpl');
*/