<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/** @global \JTL\Smarty\JTLSmarty $smarty */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

use JTL\Alert\Alert;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Shopsetting;
use League\Flysystem\Adapter\Ftp;

$shopSettings = Shopsetting::getInstance();
$alertHelper  = Shop::Container()->getAlertService();

Shop::Container()->getGetText()->loadConfigLocales(true, true);

if (!empty($_POST) && Form::validateToken()) {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, saveAdminSectionSettings(CONF_FTP, $_POST), 'saveSettings');
    $shopSettings->reset();

    if (isset($_POST['test'])) {
        try {
            $fs = new Filesystem(new Ftp([
                'host'                 => $_POST['ftp_hostname'],
                'port'                 => (int)($_POST['ftp_port'] ?? 21),
                'username'             => $_POST['ftp_user'],
                'password'             => $_POST['ftp_pass'],
                'ssl'                  => (int)$_POST['ftp_ssl'] === 1,
                'root'                 => $_POST['ftp_path'],
                'timeout'              => 60,
                'passive'              => true,
                'ignorePassiveAddress' => false
            ]));

            $isShopRoot = $fs->has('includes/config.JTL-Shop.ini.php');
            if ($isShopRoot) {
                $alertHelper->addAlert(Alert::TYPE_INFO, __('ftpValidConnection'), 'ftpValidConnection');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('ftpInvalidShopRoot'), 'ftpInvalidShopRoot');
            }
        } catch (Exception $e) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, $e->getMessage(), 'errorFTP');
        }
    }
}

$config = getAdminSectionSettings(CONF_FTP);
Shop::Container()->getGetText()->localizeConfigs($config);

$smarty->assign('oConfig_arr', $config)
    ->assign('oConfig', Shop::getSettings([CONF_FTP])['ftp'])
    ->display('ftp.tpl');
