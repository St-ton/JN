<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\AdminIO;
use JTL\Backend\JSONAPI;
use JTL\Backend\TwoFA;
use JTL\Helpers\Form;
use JTL\IO\IOError;
use JTL\Jtllog;
use JTL\Link\Admin\LinkAdmin;
use JTL\Media\Manager;
use JTL\Shop;

/** @global \JTL\Backend\AdminAccount $oAccount */

require_once __DIR__ . '/includes/admininclude.php';

if (!$oAccount->getIsAuthenticated()) {
    AdminIO::getInstance()->respondAndExit(new IOError('Not authenticated as admin.', 401));
}
if (!Form::validateToken()) {
    AdminIO::getInstance()->respondAndExit(new IOError('CSRF validation failed.', 403));
}

$jsonApi = JSONAPI::getInstance();
$io      = AdminIO::getInstance()->setAccount($oAccount);
$images  = new Manager();

Shop::Container()->getOPC()->registerAdminIOFunctions($io);
Shop::Container()->getOPCPageService()->registerAdminIOFunctions($io);

$dashboardInc       = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
$accountInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
$bannerInc          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$sucheInc           = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';
$sucheinstellungInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sucheinstellungen_inc.php';
$plzimportInc       = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plz_ort_import_inc.php';
$redirectInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'redirect_inc.php';
$dbupdaterInc       = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbupdater_inc.php';
$dbcheckInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
$versandartenInc    = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';

$io->register('getPages', [$jsonApi, 'getPages'])
   ->register('getCategories', [$jsonApi, 'getCategories'])
   ->register('getProducts', [$jsonApi, 'getProducts'])
   ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
   ->register('getCustomers', [$jsonApi, 'getCustomers'])
   ->register('getSeos', [$jsonApi, 'getSeos'])
   ->register('getAttributes', [$jsonApi, 'getAttributes'])
   ->register('isDuplicateSpecialLink', [LinkAdmin::class, 'isDuplicateSpecialLink'])
   ->register('getCurrencyConversion', 'getCurrencyConversionIO')
   ->register('setCurrencyConversionTooltip', 'setCurrencyConversionTooltipIO')
   ->register('getNotifyDropIO')
   ->register('getNewTwoFA', [TwoFA::class, 'getNewTwoFA'])
   ->register('genTwoFAEmergencyCodes', [TwoFA::class, 'genTwoFAEmergencyCodes'])
   ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('getAvailableWidgets', 'getAvailableWidgetsIO', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('getRemoteData', 'getRemoteDataIO', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('getShopInfo', 'getShopInfoIO', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('truncateJtllog', [Jtllog::class, 'truncateLog'], null, 'DASHBOARD_VIEW')
   ->register('addFav')
   ->register('reloadFavs')
   ->register('loadStats', [$images, 'loadStats'], null, 'DISPLAY_IMAGES_VIEW')
   ->register('cleanupStorage', [$images, 'cleanupStorage'], null, 'DISPLAY_IMAGES_VIEW')
   ->register('clearImageCache', [$images, 'clearImageCache'], null, 'DISPLAY_IMAGES_VIEW')
   ->register('generateImageCache', [$images, 'generateImageCache'], null, 'DISPLAY_IMAGES_VIEW')
   ->register('plzimportActionLoadAvailableDownloads', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionDoImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionResetImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionCallStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionUpdateIndex', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionRestoreBackup', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionCheckStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('plzimportActionDelTempImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
   ->register('dbUpdateIO', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
   ->register('dbupdaterBackup', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
   ->register('dbupdaterDownload', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
   ->register('dbupdaterStatusTpl', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
   ->register('dbupdaterMigration', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
   ->register('migrateToInnoDB_utf8', 'doMigrateToInnoDB_utf8', $dbcheckInc, 'DBCHECK_VIEW')
   ->register('redirectCheckAvailability', [JTL\Redirect::class, 'checkAvailability'])
   ->register('updateRedirectState', null, $redirectInc, 'REDIRECT_VIEW')
   ->register('getRandomPassword', 'getRandomPasswordIO', $accountInc, 'ACCOUNT_VIEW')
   ->register('saveBannerAreas', 'saveBannerAreasIO', $bannerInc, 'DISPLAY_BANNER_VIEW')
   ->register('createSearchIndex', 'createSearchIndex', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
   ->register('clearSearchCache', 'clearSearchCache', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
   ->register('adminSearch', 'adminSearch', $sucheInc, 'SETTINGS_SEARCH_VIEW')
   ->register('saveShippingSurcharge', 'saveShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
   ->register('deleteShippingSurcharge', 'deleteShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
   ->register('deleteShippingSurchargeZIP', 'deleteShippingSurchargeZIP', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
   ->register('createShippingSurchargeZIP', 'createShippingSurchargeZIP', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
   ->register('getShippingSurcharge', 'getShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW');

$io->respondAndExit($io->handleRequest($_REQUEST['io']));
