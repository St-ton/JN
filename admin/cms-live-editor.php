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

$cHinweis    = '';
$cFehler     = '';
$cPageIdHash = verifyGPDataString('cCmsPageIdHash');
$cAction     = verifyGPDataString('cAction');
$cPageUrl    = verifyGPDataString('cPageUrl');
$oCMS        = CMS::getInstance();
$oCMSPage    = $oCMS->getPage($cPageIdHash);

try {
    $oCMSPage->lock($oAccount->account()->cLogin);
} catch (Exception $e) {
    $cFehler = "Diese Seite wird bereits von '{$oCMSPage->cLockedBy}' bearbeitet.";
}

if ($cAction === 'restore_default') {
    $oCMSPage->remove();
    header('Location: ' . $cPageUrl);
    exit();
}

$oPortlet_arr  = $oCMS->getPortlets();
$oTemplate_arr = CMS::getTemplates();

$smarty
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
    ->assign('oPortlet_arr', $oPortlet_arr)
    ->assign('oTemplate_arr', $oTemplate_arr)
    ->assign('cAction', $cAction)
    ->assign('cPageUrl', $cPageUrl)
    ->assign('cPageIdHash', $cPageIdHash)
    ->assign('oCMSPage', $oCMSPage)
    ->display('cms-live-editor.tpl');
