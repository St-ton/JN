<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
/** @global AdminAccount $oAccount */

require_once __DIR__ . '/includes/admininclude.php';

if (!$oAccount->getIsAuthenticated()) {
    AdminIO::getInstance()->respondAndExit(new IOError('Not authenticated as admin.', 401));
} elseif (!validateToken()) {
    AdminIO::getInstance()->respondAndExit(new IOError('CSRF validation failed.', 403));
}

$jsonApi = JSONAPI::getInstance();
$io      = AdminIO::getInstance()->setAccount($oAccount);

Shop::Container()->getOPC()->registerAdminIOFunctions($io);

$dashboardInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
$accountInc          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
$bannerInc           = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$sucheInc            = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';
$bilderverwaltungInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bilderverwaltung_inc.php';
$sucheinstellungInc  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sucheinstellungen_inc.php';
$plzimportInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plz_ort_import_inc.php';
$redirectInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'redirect_inc.php';
$dbupdaterInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbupdater_inc.php';
$dbcheckInc          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$io
    ->register('getPages', [$jsonApi, 'getPages'])
    ->register('getCategories', [$jsonApi, 'getCategories'])
    ->register('getProducts', [$jsonApi, 'getProducts'])
    ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
    ->register('getCustomers', [$jsonApi, 'getCustomers'])
    ->register('getSeos', [$jsonApi, 'getSeos'])
    ->register('getTags', [$jsonApi, 'getTags'])
    ->register('getAttributes', [$jsonApi, 'getAttributes'])
    ->register('getCurrencyConversion', 'getCurrencyConversionIO')
    ->register('setCurrencyConversionTooltip', 'setCurrencyConversionTooltipIO')
    ->register('getNotifyDropIO')
    ->register('getNewTwoFA', ['TwoFA', 'getNewTwoFA'])
    ->register('genTwoFAEmergencyCodes', ['TwoFA', 'genTwoFAEmergencyCodes'])
    ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getAvailableWidgets', 'getAvailableWidgetsIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getRemoteData', 'getRemoteDataIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getShopInfo', 'getShopInfoIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('truncateJtllog', ['Jtllog', 'truncateLog'], null, 'DASHBOARD_VIEW')
    ->register('addFav')
    ->register('reloadFavs')
    ->register('loadStats', 'loadStats', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('cleanupStorage', 'cleanupStorage', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('clearImageCache', 'clearImageCache', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('generateImageCache', 'generateImageCache', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
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
    ->register('redirectCheckAvailability', ['Redirect', 'checkAvailability'])
    ->register('updateRedirectState', null, $redirectInc, 'REDIRECT_VIEW')

    /*
    // OnPage Composer
    ->register('opcGetPage', [$opc, 'getPage'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcGetPageRevisions', [$opc, 'getPageRevisions'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcSavePage', [$opc, 'savePage'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcLockPage', [$opc, 'lockPage'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcUnlockPage', [$opc, 'unlockPage'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcGetPortletInstance', [$opc, 'getPortletInstance'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcGetBlueprint', [$opc, 'getBlueprint'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcGetBlueprintList', [$opc, 'getBlueprintList'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcSaveBlueprint', [$opc, 'saveBlueprint'], null, 'CONTENT_PAGE_VIEW')
    ->register('opcDeleteBlueprint', [$opc, 'deleteBlueprint'], null, 'CONTENT_PAGE_VIEW')

//    ->register('getPortletPreviewHtml', [$cms, 'getPortletPreviewHtml'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getPortletConfigPanelHtml', [$cms, 'getPortletConfigPanelHtml'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getPortletFullPreviewHtml', [$cms, 'getPortletFullPreviewHtml'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getPortletDefaultProps', [$cms, 'getPortletDefaultProps'], null, 'CONTENT_PAGE_VIEW')
//    ->register('saveCmsPage', [$cms, 'savePage'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getCmsPage', [$cms, 'getPage'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getCmsPageRevision', [$cms, 'getPageRevision'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getCmsPageRevisions', [$cms, 'getPageRevisions'], null, 'CONTENT_PAGE_VIEW')
//    ->register('lockCmsPage', [$cms, 'lockPage'], null, 'CONTENT_PAGE_VIEW')
//    ->register('unlockCmsPage', [$cms, 'unlockPage'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getCmsTemplates', [$cms, 'getTemplates'], null, 'CONTENT_PAGE_VIEW')
//    ->register('storeCmsTemplate', [$cms, 'storeTemplate'], null, 'CONTENT_PAGE_VIEW')
//    ->register('deleteCmsTemplate', [$cms, 'deleteTemplate'], null, 'CONTENT_PAGE_VIEW')
//    ->register('getProductFilterOptions', [$cms, 'getFilterOptions'], null, 'CONTENT_PAGE_VIEW')
*/

    // Other
    ->register('getRandomPassword', 'getRandomPasswordIO', $accountInc, 'ACCOUNT_VIEW')
    ->register('saveBannerAreas', 'saveBannerAreasIO', $bannerInc, 'DISPLAY_BANNER_VIEW')
    ->register('createSearchIndex', 'createSearchIndex', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
    ->register('clearSearchCache', 'clearSearchCache', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
    ->register('adminSearch', 'adminSearch', $sucheInc, 'SETTINGS_SEARCH_VIEW')
;

$data = $io->handleRequest($_REQUEST['io']);
$io->respondAndExit($data);
