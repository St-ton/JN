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

$cHinweis      = '';
$cFehler       = '';
$oCMS          = CMS::getInstance()->setAdminAccount($oAccount);
$oCMSPage      = null;
$oPortlet_arr  = $oCMS->getPortlets();
$oTemplate_arr = $oCMS->getTemplates();
$cPageIdHash   = verifyGPDataString('cCmsPageIdHash');
$cAction       = verifyGPDataString('cAction');
$cPageUrl      = verifyGPDataString('cPageUrl');

if (empty($cPageIdHash) || empty($cAction) || empty($cPageUrl)) {
    $cFehler = 'Einige Parameter für den Editor wurden nicht gesetzt.';
} else {
    $oCMSPage = $oCMS->getPage($cPageIdHash);

    if ($oCMSPage->lock($oAccount->account()->cLogin) === false) {
        $cFehler = "Diese Seite wird bereits von '{$oCMSPage->cLockedBy}' bearbeitet.";
    } elseif ($cAction === 'restore_default') {
        $oCMSPage->remove();
        header('Location: ' . $cPageUrl);
        exit();
    }
}

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
