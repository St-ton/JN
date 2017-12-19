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

$cPageIdHash = verifyGPDataString('cCmsPageIdHash');
$cAction     = verifyGPDataString('cAction');
$cPageUrl    = verifyGPDataString('cPageUrl');
$oCMSPage    = CMS::getCmsPage($cPageIdHash);

if ($cAction === 'restore_default') {
    $oCMSPage->remove();
    header('Location: ' . $cPageUrl);
    exit();
}

$oPortlet_arr = CMS::getPortlets();

$smarty
    ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
    ->assign('oPortlet_arr', $oPortlet_arr)
    ->assign('cAction', $cAction)
    ->assign('cPageUrl', $cPageUrl)
    ->assign('cPageIdHash', $cPageIdHash)
    ->assign('oCMSPage', $oCMSPage)
    ->display('cms-live-editor.tpl');
