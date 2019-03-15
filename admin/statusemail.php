<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Statusmail;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$alertHelper = Shop::Container()->getAlertService();
$step        = 'statusemail_uebersicht';
$statusMail  = new Statusmail(Shop::Container()->getDB());

if (Form::validateToken()) {
    if (isset($_POST['action']) && $_POST['action'] === 'sendnow') {
        $statusMail->sendAllActiveStatusMails();
    } elseif (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if ($statusMail->updateConfig()) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successChangesSave'), 'successChangesSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorConfigSave'), 'errorConfigSave');
        }
        $step = 'statusemail_uebersicht';
    }
}
if ($step === 'statusemail_uebersicht') {
    $smarty->assign('oStatusemailEinstellungen', $statusMail->loadConfig());
}

$smarty->assign('step', $step)
       ->display('statusemail.tpl');
