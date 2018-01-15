<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$oAccount->permission('WAWI_SYNC_VIEW', true, true);

$cFehler  = '';
$cHinweis = '';

if (isset($_POST['wawi-pass'], $_POST['wawi-user']) && validateToken()) {
    $passInfo   = password_get_info($_POST['wawi-pass']);
    $upd        = new stdClass();
    $upd->cName = $_POST['wawi-user'];
    $upd->cPass = $passInfo['algo'] > 0
        ? $_POST['wawi-pass'] // new clear text password was given
        : password_hash($_POST['wawi-pass'], PASSWORD_DEFAULT); // hashed password was not changed
    Shop::DB()->update('tsynclogin', 1, 1, $upd);
    $cHinweis = 'Erfolgreich gespeichert.';
}

$user = Shop::DB()->query("SELECT cName, cPass FROM tsynclogin", 1);
$smarty->assign('wawiuser', $user->cName)
       ->assign('cHinweis', $cHinweis)
       ->assign('wawipass', $user->cPass)
       ->display('wawisync.tpl');
