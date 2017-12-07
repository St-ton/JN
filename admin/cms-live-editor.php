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
$cPageUrl    = $_SERVER['HTTP_REFERER'];

if ($cAction === 'restore_default') {
    $oCMSPage = CMS::getCmsPage($cPageIdHash);
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
    ->display('cms-live-editor.tpl');
