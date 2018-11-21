<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global Smarty\JTLSmarty $smarty
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$cHinweis      = '';
$cFehler       = '';
$shopSettings  = Shopsetting::getInstance();

if (!empty($_POST) && FormHelper::validateToken()) {
    $cHinweis = saveAdminSectionSettings(CONF_FTP, $_POST);
    $shopSettings->reset();
    
    if (isset($_POST['test'])) {
        unset($cHinweis);

        try {
            $fs = new Filesystem\FtpFilesystem([
                'hostname' => $_POST['ftp_hostname'],
                'port' => (int)$_POST['ftp_port'],
                'username' => $_POST['ftp_user'],
                'password' => $_POST['ftp_pass'],
                'ssl' => (int)$_POST['ftp_ssl'],
                'root' => $_POST['ftp_path'],
                'timeout' => 60
            ]);

            $isShopRoot = $fs->exists('includes/config.JTL-Shop.ini.php');
            $smarty->assign('isShopRoot', $isShopRoot);
        } catch (\Exception $e) {
            $cFehler = $e->getMessage();
        }
    }
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_FTP))
       ->assign('oConfig', Shop::getSettings([CONF_FTP])['ftp'])
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('ftp.tpl');
