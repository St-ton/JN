<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Statusmail;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);

$alertHelper = Shop::Container()->getAlertService();
$step        = 'statusemail_uebersicht';
$statusMail  = new Statusmail(Shop::Container()->getDB());

if (Form::validateToken()) {
    if (Request::postVar('action') === 'sendnow') {
        $statusMail->sendAllActiveStatusMails();
    } elseif (Request::postInt('einstellungen') === 1) {
        if ($statusMail->updateConfig()) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successChangesSave'), 'successChangesSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorConfigSave'), 'errorConfigSave');
        }
    }
}

$smarty->assign('step', $step)
    ->assign('oStatusemailEinstellungen', $statusMail->loadConfig())
    ->display('statusemail.tpl');
