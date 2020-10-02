<?php

use JTL\Alert\Alert;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager as LicenseManager;
use JTL\Recommendation\Manager;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$recommendationID = Request::verifyGPDataString('id');
$alertHelper      = Shop::Container()->getAlertService();
$db               = Shop::Container()->getDB();
$cache            = Shop::Container()->getCache();
$getText          = Shop::Container()->getGetText();
$checker          = new Checker(Shop::Container()->getLogService(), $db, $cache);
$manager          = new LicenseManager($db, $cache);
$admin            = new Admin($manager, $db, $cache, $checker);
$recommendations  = new Manager($alertHelper, Request::verifyGPDataString('scope'));
$hasLicense       = $manager->getLicenseByExsID($recommendationID) !== null;

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

$smarty->assign('recommendation', $recommendations->getRecommendationById($recommendationID))
       ->assign('hasLicense', $hasLicense)
       ->display('premiumplugin.tpl');
