<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Cron.php';

$cHinweis = '';
$cFehler  = '';
$step     = 'statusemail_uebersicht';

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1 && validateToken()) {
    if (speicherStatusemailEinstellungen($_POST)) {
        $cHinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.<br />';
    } else {
        $cFehler .= 'Fehler: Ihre Einstellungen konnte nicht gespeichert werden. Bitte pr&uuml;fen Sie Ihre Eingaben.<br />';
    }
    $step = 'statusemail_uebersicht';
}
if ($step === 'statusemail_uebersicht') {
    $smarty->assign('oStatusemailEinstellungen', ladeStatusemailEinstellungen());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('statusemail.tpl');
