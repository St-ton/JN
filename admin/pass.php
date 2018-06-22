<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$step     = 'prepare';
$cFehler  = '';
$cHinweis = '';
if (isset($_POST['mail']) && FormHelper::validateToken()) {
    $account = new AdminAccount(false);
    $account->prepareResetPassword(StringHandler::filterXSS($_POST['mail']));
    $cHinweis = 'Eine E-Mail mit weiteren Anweisung wurde an die hinterlegte Adresse gesendet, sofern vorhanden.';
} elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpm'], $_POST['fpwh']) && FormHelper::validateToken()) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $account  = new AdminAccount(false);
        $verified = $account->verifyResetPasswordHash($_POST['fpwh'], $_POST['fpm']);
        if ($verified === true) {
            $_upd                     = new stdClass();
            $_upd->cPass              = Shop::Container()->getPasswordService()->hash($_POST['pw_new']);
            $update                   = Shop::Container()->getDB()->update('tadminlogin', 'cMail', $_POST['fpm'], $_upd);
            if ($update > 0) {
                $cHinweis = 'Passwort wurde erfolgreich geändert.';
                header('Location: index.php?pw_updated=true');
            } else {
                $cFehler = 'Passwort konnte nicht geändert werden.';
            }
        } else {
            $cFehler = 'Ungütiger Hash übergeben.';
        }
    } else {
        $cFehler = 'Passwörter stimmen nicht überein.';
    }
    $smarty->assign('fpwh', $_POST['fpwh'])
           ->assign('fpm', $_POST['fpm']);
    $step = 'confirm';
} elseif (isset($_GET['fpwh'], $_GET['mail'])) {
    $smarty->assign('fpwh', $_GET['fpwh'])
           ->assign('fpm', $_GET['mail']);
    $step = 'confirm';
}

$smarty->assign('step', $step)
       ->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis)
       ->display('pass.tpl');
