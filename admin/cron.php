<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global Smarty\JTLSmarty $smarty */

$admin = new Cron\Admin\Listing(Shop::Container()->getDB(), Shop::Container()->getLogService());
if (isset($_POST['reset']) && \Helpers\Form::validateToken()) {
    $admin->resetQueueEntry((int)$_POST['reset']);
}
$jobs = $admin->getJobs();
//if (!empty($_POST)) Shop::dbg($_POST, false, 'POST:');
//Shop::dbg($jobs);
$smarty->assign('jobs', $jobs)
//       ->assign('cateogries', $orphanedCategories)
       ->display('cron.tpl');
