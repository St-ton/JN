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
require_once __DIR__ . '/includes/editpage_inc.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$cKey     = verifyGPDataString('cKey');
$kKey     = verifyGPCDataInteger('kKey');
$kSprache = verifyGPCDataInteger('kSprache');
$oSeo     = Shop::DB()->select('tseo', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache]);

$oPortlet_arr = getPortlets();

$smarty
    ->assign('oSeo', $oSeo)
    ->assign('oPortlet_arr', $oPortlet_arr)
    ->display('editpage.tpl');