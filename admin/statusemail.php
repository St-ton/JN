<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$cHinweis = '';
$cFehler  = '';
$step     = 'statusemail_uebersicht';
$statusMail = new Statusmail(Shop::Container()->getDB());

if (FormHelper::validateToken()) {
    if (isset($_POST['action']) && $_POST['action'] === 'sendnow') {
        $statusMail->sendAllActiveStatusMails();
    }
    elseif (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if ($statusMail->updateConfig()) {
            $cHinweis .= 'Ihre Einstellungen wurden übernommen.<br>';
        } else {
            $cFehler .= 'Fehler: Ihre Einstellungen konnte nicht gespeichert werden. Bitte prüfen Sie Ihre Eingaben.<br>';
        }
        $step = 'statusemail_uebersicht';
    }
}
if ($step === 'statusemail_uebersicht') {
    $smarty->assign('oStatusemailEinstellungen', $statusMail->loadConfig());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('statusemail.tpl');
