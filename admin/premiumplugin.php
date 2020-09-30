<?php

use JTL\Alert\Alert;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Helpers\Request;
use JTL\Recommendation\Manager;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$recommendationID = Request::verifyGPDataString('id');
$recommendations  = new Manager(Shop::Container()->getAlertService(), Request::verifyGPDataString('scope'));
$recommendation   = $recommendations->getRecommendationById($recommendationID);
$alertHelper      = Shop::Container()->getAlertService();

if (Request::verifyGPDataString('action') === 'install') {
    Shop::Container()->getGetText()->loadAdminLocale('pages/pluginverwaltung');

    $installer = new ExtensionInstaller(Shop::Container()->getDB());
    $installer->setRecommendations($recommendations->getRecommendations());
    $errorMsg = $installer->onSaveStep([$recommendationID]);
    if ($errorMsg === '') {
        $successMsg = Manager::SCOPE_BACKEND_PAYMENT_PROVIDER
            ? __('successInstallPaymentPlugin')
            : __('successInstallLegalPlugin');
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            $successMsg,
            'successInstall',
            ['fadeOut' => Alert::FADE_NEVER, 'saveInSession' => true]
        );
        header('Refresh:0');
        exit;
    }
    $alertHelper->addAlert(Alert::TYPE_WARNING, $errorMsg, 'errorInstall');
}

$smarty->assign('recommendation', $recommendation)
       ->display('premiumplugin.tpl');
