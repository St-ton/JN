<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
/** @global AdminAccount $oAccount */

require_once __DIR__ . '/includes/admininclude.php';

if (!$oAccount->getIsAuthenticated()) {
    http_response_code(403);
    exit();
}

$jsonApi       = JSONAPI::getInstance();
$io            = AdminIO::getInstance()->setAccount($oAccount);
$dashboardInc  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
$widgetBaseInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
$accountInc    = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
$bannerInc     = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$sucheInc      = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';

$io->register('getPages', [$jsonApi, 'getPages'])
   ->register('getCategories', [$jsonApi, 'getCategories'])
   ->register('getProducts', [$jsonApi, 'getProducts'])
   ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
   ->register('getCustomers', [$jsonApi, 'getCustomers'])
   ->register('getSeos', [$jsonApi, 'getSeos'])
   // Allround-IO-calls
   ->register('getCurrencyConversion', 'getCurrencyConversionIO')
   ->register('setCurrencyConversionTooltip', 'setCurrencyConversionTooltipIO')
   // Two-FA-related functions
   ->register('getNewTwoFA', ['TwoFA', 'getNewTwoFA'])
   ->register('genTwoFAEmergencyCodes', ['TwoFA', 'genTwoFAEmergencyCodes'])
   // Dashboard-related functions
   ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('getAvailableWidgets', 'getAvailableWidgetsIO', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('getRemoteData', ['WidgetBase', 'getRemoteDataIO'], $widgetBaseInc, 'DASHBOARD_VIEW')
   ->register('truncateJtllog', ['Jtllog', 'truncateLog'], null, 'DASHBOARD_VIEW')
   // Benutzerverwaltung
   ->register('getRandomPassword', 'getRandomPasswordIO', $accountInc, 'ACCOUNT_VIEW')
   // Bannerverwaltung
   ->register('saveBannerAreas', 'saveBannerAreasIO', $bannerInc, 'DISPLAY_BANNER_VIEW')
   // Backend-Suche
   ->register('adminSearch', 'adminSearch', $sucheInc, 'SETTINGS_SEARCH_VIEW');

$data = $io->handleRequest($_REQUEST['io']);
$io->respondAndExit($data);
