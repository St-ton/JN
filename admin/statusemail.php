<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Cron.php';

$cHinweis = '';
$cFehler  = '';
$step     = 'statusemail_uebersicht';

$dVon = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', time() - (3600 * 24)), date('d', time() - (3600 * 24)), date('Y', time() - (3600 * 24))));
$dBis = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));


if (validateToken()) {
    if (isset($_POST['action']) && $_POST['action'] === 'sendnow') {
        sendStatusMail();
    }
    elseif (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1) {
        if (speicherStatusemailEinstellungen()) {
            $cHinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.<br />';
        } else {
            $cFehler .= 'Fehler: Ihre Einstellungen konnte nicht gespeichert werden. Bitte pr&uuml;fen Sie Ihre Eingaben.<br />';
        }
        $step = 'statusemail_uebersicht';
    }
}
if ($step === 'statusemail_uebersicht') {
    $smarty->assign('oStatusemailEinstellungen', ladeStatusemailEinstellungen());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('statusemail.tpl');
