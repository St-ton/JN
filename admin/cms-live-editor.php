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

$cKey     = verifyGPDataString('cKey');
$kKey     = verifyGPCDataInteger('kKey');
$kSprache = verifyGPCDataInteger('kSprache');
$cAction  = verifyGPDataString('cAction');
$oSeo     = Shop::DB()->select('tseo', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache]);

if ($cAction === 'restore_default') {
    $oCMSPage = CMS::getCMSPage($cKey, $kKey, $kSprache);

    if ($oCMSPage->remove() !== -1) {
        // erfolg
    } else {
        //fehler
    }

    header('Location: ' . URL_SHOP . '/' . $oSeo->cSeo);
    exit();
}

$oPortlet_arr = CMS::getPortlets();

$smarty
    ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
    ->assign('oSeo', $oSeo)
    ->assign('oPortlet_arr', $oPortlet_arr)
    ->assign('cAction', $cAction)
    ->assign('cKey', $cKey)
    ->assign('kKey', $kKey)
    ->assign('kSprache', $kSprache)
    ->display('cms-live-editor.tpl');
