<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global Smarty\JTLSmarty $smarty */
$oAccount->permission('WAWI_SYNC_VIEW', true, true);

$cFehler  = '';
$cHinweis = '';

if (isset($_POST['wawi-pass'], $_POST['wawi-user']) && FormHelper::validateToken()) {
    $passwordService = Shop::Container()->getPasswordService();
    $passInfo        = $passwordService->getInfo($_POST['wawi-pass']);
    $upd             = new stdClass();
    $upd->cName      = $_POST['wawi-user'];
    $upd->cPass      = $passInfo['algo'] > 0
        ? $_POST['wawi-pass'] // hashed password was not changed
        : $passwordService->hash($_POST['wawi-pass']); // new clear text password was given

    Shop::Container()->getDB()->queryPrepared(
        'INSERT INTO `tsynclogin` (kSynclogin, cName, cPass)
            VALUES (1, :cName, :cPass)
            ON DUPLICATE KEY UPDATE
            cName = :cName,
            cPass = :cPass',
        ['cName' => $upd->cName, 'cPass' => $upd->cPass],
        \DB\ReturnType::AFFECTED_ROWS
    );

    $cHinweis = 'Erfolgreich gespeichert.';
}

$user = Shop::Container()->getDB()->select('tsynclogin', 'kSynclogin', 1);
$smarty->assign('wawiuser', $user->cName)
       ->assign('cHinweis', $cHinweis)
       ->assign('wawipass', $user->cPass)
       ->display('wawisync.tpl');
