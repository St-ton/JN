<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$pageKey      = RequestHelper::verifyGPCDataInt('pageKey');
$pageId       = RequestHelper::verifyGPDataString('pageId');
$pageUrl      = RequestHelper::verifyGPDataString('pageUrl');
$adoptFromKey = RequestHelper::verifyGPCDataInt('adoptFromKey');
$action       = RequestHelper::verifyGPDataString('action');
$shopUrl      = Shop::getURL();
$error        = null;

$opc       = Shop::Container()->getOPC();
$opcPage   = Shop::Container()->getOPCPageService();
$opcPageDB = Shop::Container()->getOPCPageDB();

$templateUrl = $shopUrl . '/' . PFAD_ADMIN . $currentTemplateDir;
$fullPageUrl = $shopUrl . $pageUrl;

$smarty->assign('shopUrl', $shopUrl)
       ->assign('templateUrl', $templateUrl)
       ->assign('pageKey', $pageKey)
       ->assign('opc', $opc);

if ($opc->isOPCInstalled() === false) {
    // OPC not installed correctly
    $smarty->assign('error', 'The OPC update is not installed properly. Please update your migrations.')
           ->display('onpage-composer.tpl');
} elseif ($action === 'edit') {
    // Enter OPC to edit a page
    try {
        $page = $opcPage->getDraft($pageKey);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    $smarty->assign('error', $error)
           ->display('onpage-composer.tpl');
} elseif ($action !== '' && FormHelper::validateToken() === false) {
    // OPC action while XSRF validation failed
    $error = 'Wrong XSRF token.';
} elseif ($action === 'replace' || $action === 'extend') {
    // Create a new OPC page draft
    try {
        $newName = 'Entwurf ' . ($opcPageDB->getDraftCount($pageId) + 1)
            . ($action === 'extend' ? ' (erweitert)' : ' (ersetzt)');
        $page    = $opcPage
            ->createDraft($pageId)
            ->setUrl($pageUrl)
            ->setReplace($action === 'replace')
            ->setName($newName);
        $opcPageDB->saveDraft($page);
        $pageKey = $page->getKey();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    header('Location: ' . $shopUrl . '/' . PFAD_ADMIN . 'onpage-composer.php?pageKey=' . $pageKey . '&action=edit');
    exit();
} elseif ($action === 'adopt') {
    // Adopt new draft from another draft
    try {
        $adoptFromDraft = $opcPage->getDraft($adoptFromKey);
        $isReplace      = $adoptFromDraft->isReplace();
        $newName        = 'Entwurf ' . ($opcPageDB->getDraftCount($pageId) + 1)
            . ($isReplace ? ' (erweitert)' : ' (ersetzt)');
        $page           = $opcPage
            ->createDraft($pageId)
            ->setUrl($pageUrl)
            ->setReplace($isReplace)
            ->setName($newName)
            ->setAreaList($adoptFromDraft->getAreaList());
        $opcPageDB->saveDraft($page);
        $pageKey = $page->getKey();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    header('Location: ' . $shopUrl . '/' . PFAD_ADMIN . 'onpage-composer.php?pageKey=' . $pageKey . '&action=edit');
    exit();
} elseif ($action === 'discard') {
    // Discard a OPC page draft
    $opcPage->deleteDraft($pageKey);
    exit('ok');
} elseif ($action === 'restore') {
    // Discard all OPC page drafts for one page
    $opcPage->deletePage($pageId);
    exit('ok');
}
