<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global Smarty\JTLSmarty $smarty */

$admin = new Cron\Admin\Listing(Shop::Container()->getDB(), Shop::Container()->getLogService());
$jobs = $admin->getJobs();
Shop::dbg($jobs);
//$smarty->assign('passed', count($orphanedCategories) === 0)
//       ->assign('cateogries', $orphanedCategories)
//       ->display('categorycheck.tpl');
