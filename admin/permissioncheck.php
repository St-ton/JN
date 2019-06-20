<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PERMISSIONCHECK_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$fsCheck = new Systemcheck_Platform_Filesystem(PFAD_ROOT); // to get all folders which need to be writable

$smarty->assign('cDirAssoc_arr', $fsCheck->getFoldersChecked())
       ->assign('oStat', $fsCheck->getFolderStats())
       ->display('permissioncheck.tpl');
