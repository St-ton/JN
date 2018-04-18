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

/*
$opc         = OPC::getInstance()->setAdminAccount($oAccount);
$opcPage     = null;
$portlets    = $opc->getPortlets();
$templates   = $opc->getTemplates();
$cPageIdHash = verifyGPDataString('cCmsPageIdHash');
$cAction     = verifyGPDataString('cAction');
$cPageUrl    = verifyGPDataString('cPageUrl');

if (empty($cPageIdHash) || empty($cAction) || empty($cPageUrl)) {
    $cFehler = 'Einige Parameter für den Editor wurden nicht gesetzt.';
} else {
    $opcPage = $opc->getPage($cPageIdHash);

    if ($opcPage->lock($oAccount->account()->cLogin) === false) {
        $cFehler = "Diese Seite wird bereits von '{$opcPage->cLockedBy}' bearbeitet.";
    } elseif ($cAction === 'restore_default') {
        $opcPage->remove();
        header('Location: ' . $cPageUrl);
        exit();
    }
}

$smarty
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
    ->assign('oPortlet_arr', $portlets)
    ->assign('oTemplate_arr', $templates)
    ->assign('cAction', $cAction)
    ->assign('cPageUrl', $cPageUrl)
    ->assign('cPageIdHash', $cPageIdHash)
    ->assign('opcPage', $opcPage)
    ->display('onpage-composer.tpl');
*/
