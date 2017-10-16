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
$oSeo     = Shop::DB()->select('tseo', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache]);

$smarty
    ->assign('oSeo', $oSeo)
    ->display('editpage.tpl');