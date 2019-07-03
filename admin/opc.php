<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$pageKey      = Request::verifyGPCDataInt('pageKey');
$pageId       = Request::verifyGPDataString('pageId');
$pageUrl      = Request::verifyGPDataString('pageUrl');
$pageName     = Request::verifyGPDataString('pageName');
$adoptFromKey = Request::verifyGPCDataInt('adoptFromKey');
$action       = Request::verifyGPDataString('action');
$draftKeys    = array_map('intval', $_POST['draftKeys'] ?? []);
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
    $smarty->assign('error', __('The OPC update is not installed properly. Please update your migrations.'))
           ->display(PFAD_ROOT . PFAD_ADMIN . '/opc/tpl/editor.tpl');
} elseif ($action === 'edit') {
    // Enter OPC to edit a page
    try {
        $page = $opcPage->getDraft($pageKey);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    $smarty->assign('error', $error)
           ->assign('page', $page)
           ->display(PFAD_ROOT . PFAD_ADMIN . '/opc/tpl/editor.tpl');
} elseif ($action !== '' && Form::validateToken() === false) {
    // OPC action while XSRF validation failed
    $error = __('Wrong XSRF token.');
} elseif ($action === 'extend') {
    // Create a new OPC page draft
    try {
        $newName = __('Draft') . ' ' . ($opcPageDB->getDraftCount($pageId) + 1);
        $page    = $opcPage
            ->createDraft($pageId)
            ->setUrl($pageUrl)
            ->setName($newName);
        $opcPageDB->saveDraft($page);
        $pageKey = $page->getKey();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    header('Location: ' . $shopUrl . '/' . PFAD_ADMIN . 'opc.php?pageKey=' . $pageKey . '&action=edit');
    exit();
} elseif ($action === 'adopt') {
    // Adopt new draft from another draft
    try {
        $adoptFromDraft = $opcPage->getDraft($pageKey);
        $page           = $opcPage
            ->createDraft($pageId)
            ->setUrl($pageUrl)
            ->setName($pageName)
            ->setPublishFrom($adoptFromDraft->getPublishFrom())
            ->setPublishTo($adoptFromDraft->getPublishTo())
            ->setAreaList($adoptFromDraft->getAreaList());
        $opcPageDB->saveDraft($page);
        $pageKey = $page->getKey();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    header('Location: ' . $shopUrl . '/' . PFAD_ADMIN . 'opc.php?pageKey=' . $pageKey . '&action=edit');
    exit();
} elseif ($action === 'duplicate-bulk') {
    // duplicate multiple drafts from existing drafts
    try {
        foreach ($draftKeys as $draftKey) {
            $adoptFromDraft = $opcPage->getDraft($draftKey);
            $newName        = $adoptFromDraft->getName() . ' (Copy)';
            $curPageId      = $adoptFromDraft->getId();
            $page           = $opcPage
                ->createDraft($adoptFromDraft->getId())
                ->setUrl($adoptFromDraft->getUrl())
                ->setName($newName)
                ->setAreaList($adoptFromDraft->getAreaList());
            $opcPageDB->saveDraft($page);
            $pageKey = $page->getKey();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    exit('ok');
} elseif ($action === 'discard') {
    // Discard a OPC page draft
    $opcPage->deleteDraft($pageKey);
    exit('ok');
} elseif ($action === 'discard-bulk') {
    // Discard multiple OPC page drafts
    foreach ($draftKeys as $draftKey) {
        $opcPage->deleteDraft($draftKey);
    }
    exit('ok');
}
