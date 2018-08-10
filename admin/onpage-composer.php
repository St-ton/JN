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

$pageKey = RequestHelper::verifyGPCDataInt('pageKey');
$pageId  = RequestHelper::verifyGPDataString('pageId');
$pageUrl = RequestHelper::verifyGPDataString('pageUrl');
$action  = RequestHelper::verifyGPDataString('action');
$async   = RequestHelper::verifyGPDataString('async');
$shopUrl = Shop::getURL();
$error   = null;

$opc       = Shop::Container()->getOPC();
$opcPage   = Shop::Container()->getOPCPageService();
$opcPageDB = Shop::Container()->getOPCPageDB();

$templateUrl = $shopUrl . '/' . PFAD_ADMIN . $currentTemplateDir;
$fullPageUrl = $shopUrl . $pageUrl;

if ($opc->isOPCInstalled() === false) {
    $error = 'The OPC update is not installed properly. Please update your migrations.';
} else {
    try {
        if ($action === 'edit') {
            $page = $opcPage->getDraft($pageKey);
        } elseif ($action === 'replace' || $action === 'extend') {
            $page = $opcPage->createDraft($pageId)
                            ->setUrl($pageUrl)
                            ->setReplace($action === 'replace')
                            ->setName(
                                'Entwurf ' . ($opcPageDB->getDraftCount($pageId) + 1)
                                . ($action === 'extend' ? ' (erweitert)' : ' (ersetzt)')
                            );
            $opcPageDB->saveDraft($page);
            $pageKey = $page->getKey();

            header(
                'Location: ' . $shopUrl . '/' . PFAD_ADMIN. 'onpage-composer.php?token='
                . RequestHelper::verifyGPDataString('token') . '&pageKey=' . $pageKey . '&action=edit'
            );
            exit();
        } elseif ($action === 'discard') {
            $opcPage->deleteDraft($pageKey);

            if ($async === 'yes') {
                exit('ok');
            }

            header('Location: ' . $fullPageUrl);
            exit();
        }
        if ($action === 'restore') {
            $opcPage->deletePage($pageId);

            if ($async === 'yes') {
                exit('ok');
            }

            header('Location: ' . $fullPageUrl);
            exit();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$smarty->assign('shopUrl', $shopUrl)
       ->assign('templateUrl', $templateUrl)
       ->assign('pageKey', $pageKey)
       ->assign('opc', $opc)
       ->assign('error', $error)
       ->display('onpage-composer.tpl');
