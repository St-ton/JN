<?php declare(strict_types=1);

use JTL\Cron\Admin\Controller;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('CRON_VIEW', true, true);
$admin    = Shop::Container()->get(Controller::class);
$deleted  = 0;
$updated  = 0;
$inserted = 0;
$tab      = 'overview';
if (Form::validateToken()) {
    if (isset($_POST['reset'])) {
        $updated = $admin->resetQueueEntry(Request::postInt('reset'));
    } elseif (isset($_POST['delete'])) {
        $deleted = $admin->deleteQueueEntry(Request::postInt('delete'));
    } elseif (Request::postInt('add-cron') === 1) {
        $inserted = $admin->addQueueEntry($_POST);
        $tab      = 'add-cron';
    } elseif (Request::postVar('a') === 'saveSettings') {
        $tab = 'settings';
        saveAdminSectionSettings(CONF_CRON, $_POST);
    }
}
getAdminSectionSettings(CONF_CRON);
$smarty->assign('jobs', $admin->getJobs())
    ->assign('deleted', $deleted)
    ->assign('updated', $updated)
    ->assign('inserted', $inserted)
    ->assign('available', $admin->getAvailableCronJobs())
    ->assign('tab', $tab)
    ->display('cron.tpl');
