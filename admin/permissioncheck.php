<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('PERMISSIONCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'permissioncheck_inc.php';

$cHinweis      = '';
$cFehler       = '';
$cDirAssoc_arr = checkWriteables();

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('cDirAssoc_arr', $cDirAssoc_arr)
       ->assign('oStat', getPermissionStats($cDirAssoc_arr))
       ->display('permissioncheck.tpl');
