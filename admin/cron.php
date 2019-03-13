<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert;
use JTL\Cron\Admin\Controller;
use JTL\Helpers\Form;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global \JTL\Smarty\JTLSmarty $smarty */
$admin    = Shop::Container()->get(Controller::class);
$deleted  = 0;
$updated  = 0;
$inserted = 0;
$tab      = 'overview';
if (Form::validateToken()) {
    if (isset($_POST['reset'])) {
        $updated = $admin->resetQueueEntry((int)$_POST['reset']);
    } elseif (isset($_POST['delete'])) {
        $deleted = $admin->deleteQueueEntry((int)$_POST['delete']);
    } elseif (isset($_POST['add-cron']) && (int)$_POST['add-cron'] === 1) {
        $inserted = $admin->addQueueEntry($_POST);
        $tab      = 'add-cron';
    } elseif (isset($_POST['a']) && $_POST['a'] === 'saveSettings') {
        $tab = 'settings';
        if (isset($_POST['cron_freq'])) {
            $_POST['cron_freq'] = max(1, $_POST['cron_freq']);
        }
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_SUCCESS,
            saveAdminSectionSettings(CONF_CRON, $_POST),
            'saveSettings'
        );
    }
}
$smarty->assign('jobs', $admin->getJobs())
       ->assign('deleted', $deleted)
       ->assign('updated', $updated)
       ->assign('inserted', $inserted)
       ->assign('available', $admin->getAvailableCronJobs())
       ->assign('tab', $tab)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_CRON))
       ->display('cron.tpl');
