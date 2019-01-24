<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global Smarty\JTLSmarty $smarty */

$admin    = new Cron\Admin\Controller(
    Shop::Container()->getDB(),
    Shop::Container()->getLogService(),
    new \Cron\JobHydrator()
);
$deleted  = 0;
$updated  = 0;
$inserted = 0;
if (\Helpers\Form::validateToken()) {
    if (isset($_POST['reset'])) {
        $updated = $admin->resetQueueEntry((int)$_POST['reset']);
    } elseif (isset($_POST['delete'])) {
        $deleted = $admin->deleteQueueEntry((int)$_POST['delete']);
    } elseif (isset($_POST['add-cron']) && (int)$_POST['add-cron'] === 1) {
        $inserted = $admin->addQueueEntry($_POST);
    }
}
$smarty->assign('jobs', $admin->getJobs())
       ->assign('deleted', $deleted)
       ->assign('updated', $updated)
       ->assign('inserted', $inserted)
       ->assign('available', $admin->getAvailableCronJobs())
       ->display('cron.tpl');
