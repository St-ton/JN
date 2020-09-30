<?php

use JTL\Alert\Alert;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Helpers\Request;
use JTL\Recommendation\Manager;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$recommendations = new Manager(Shop::Container()->getAlertService(), Request::verifyGPDataString('scope'));
$recommendation  = $recommendations->getRecommendationById(Request::verifyGPDataString('id'));
$alertHelper     = Shop::Container()->getAlertService();

if (Request::verifyGPDataString('action') === 'install') {
    Shop::Container()->getGetText()->loadAdminLocale('pages/pluginverwaltung');

    $installer = new ExtensionInstaller(Shop::Container()->getDB());
    $installer->setRecommendations($recommendations->getRecommendations());
    $errorMsg = $installer->onSaveStep([Request::verifyGPDataString('id')]);
    if ($errorMsg === '') {
        $successMsg = Manager::SCOPE_BACKEND_PAYMENT_PROVIDER
            ? __('successInstallPaymentPlugin')
            : __('successInstallLegalPlugin');
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, $successMsg, 'successInstall');
    } else {
        $alertHelper->addAlert(Alert::TYPE_WARNING, $errorMsg, 'errorInstall');
    }
}

$smarty->assign('recommendation', $recommendation)
       ->display('premiumplugin.tpl');
