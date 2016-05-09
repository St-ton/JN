<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('WAWI_SYNC_VIEW', true, true);

$cFehler  = '';
$cHinweis = '';

if (isset($_POST['wawi-pass']) && isset($_POST['wawi-user']) && validateToken()) {
    Shop::DB()->query("UPDATE tsynclogin SET cName = '" . $_POST['wawi-user'] . "'", 3);
    Shop::DB()->query("UPDATE tsynclogin SET cPass = '" . $_POST['wawi-pass'] . "'", 3);
    $cHinweis = 'Erfolgreich gespeichert.';
}

$user = Shop::DB()->query("SELECT cName, cPass FROM tsynclogin", 1);
$smarty->assign('wawiuser', $user->cName)
    ->assign('cHinweis', $cHinweis)
    ->assign('wawipass', $user->cPass)
    ->display('wawisync.tpl');
