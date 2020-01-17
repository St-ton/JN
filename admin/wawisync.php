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
    if (!preg_match('/[^A-Za-z0-9\!"\#\$%&\'\(\)\*\+,-\.\/:;\=\>\?@\[\\\\\]\^_`\|\}~]/', $_POST['wawi-pass'])) {
        $upd = new stdClass();
        $upd->cName = $_POST['wawi-user'];
        $upd->cPass = $_POST['wawi-pass'];
        Shop::DB()->update('tsynclogin', 1, 1, $upd);
        $cHinweis = 'Erfolgreich gespeichert.';
    } else {
        $cFehler = 'Benutzername und Passwort d&uuml;rfen nur Gro&szlig;- und Kleinbuchstaben, Zahlen sowie folgende ' .
            'Sonderzeichen enthalten: !\"#$%&\'()*+,-./:;=>?@[\\]^_`|}~';
    }
}

$user = Shop::DB()->query("SELECT cName, cPass FROM tsynclogin", 1);
$smarty->assign('wawiuser', $user->cName)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('wawipass', $user->cPass)
       ->display('wawisync.tpl');
