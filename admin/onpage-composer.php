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

$pageId      = verifyGPDataString('pageId');
$pageUrl     = verifyGPDataString('pageUrl');
$action      = verifyGPDataString('action');
$shopUrl     = \Shop::getURL();
$opc         = \Shop::Container()->getOPC();
$opcDB       = \Shop::Container()->getOPCDB();
$templateUrl = $shopUrl . '/' . PFAD_ADMIN . $currentTemplateDir;

if ($action === 'restore') {
    $opc->deletePage($pageId);
    header('Location: ' . $shopUrl . "/" . $pageUrl);
    exit();
}

$page = $opc->getPage($pageId)->setUrl($pageUrl);

if ($action === 'edit') {
    $replace = $page->isReplace();
} elseif ($action === 'replace') {
    $page->setReplace(true);
    $opcDB->savePage($page);
} else {
    $page->setReplace(false);
    $opcDB->savePage($page);
}

$smarty
    ->assign('shopUrl', $shopUrl)
    ->assign('pageUrl', $pageUrl)
    ->assign('pageId', $pageId)
    ->assign('templateUrl', $templateUrl)
    ->assign('opc', $opc)
    ->display('onpage-composer.tpl');
