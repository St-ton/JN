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

$pageKey = verifyGPCDataInteger('pageKey');
$pageId  = verifyGPDataString('pageId');
$pageUrl = verifyGPDataString('pageUrl');
$action  = verifyGPDataString('action');
$shopUrl = rtrim(\Shop::getURL(), '/');
$opc     = \Shop::Container()->getOPC();
$opcPage = \Shop::Container()->getOPCPageService();
$opcDB   = \Shop::Container()->getOPCDB();
$error   = null;

$templateUrl = $shopUrl . '/' . PFAD_ADMIN . $currentTemplateDir;
$fullPageUrl = $shopUrl . $pageUrl;

try {
    if ($action === 'edit') {
        $page = $opcPage->getDraft($pageKey);
    } elseif ($action === 'replace' || $action === 'extend') {
        $page = $opcPage->createDraft($pageId)
            ->setUrl($pageUrl)
            ->setReplace($action === 'replace')
            ->setName(
                'Entwurf ' . ($opcDB->getPageDraftCount($pageId) + 1)
                . ($action === 'extend' ? ' (erweitert)' : ' (ersetzt)')
            );
        $opcDB->savePage($page);
        $pageKey = $page->getKey();
    } elseif ($action === 'discard') {
        $opcPage->deleteDraft($pageKey);
        header('Location: ' . $fullPageUrl);
        exit();
    } elseif ($action === 'restore') {
        $opcPage->deletePage($pageId);
        header('Location: ' . $fullPageUrl);
        exit();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$smarty
    ->assign('shopUrl', $shopUrl)
    ->assign('templateUrl', $templateUrl)
    ->assign('pageKey', $pageKey)
    ->assign('opc', $opc)
    ->assign('error', $error)
    ->display('onpage-composer.tpl');
