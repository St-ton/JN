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

$jsonApi      = JSONAPI::getInstance();
$io           = IO::getInstance();
$dashboardInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';

$io->register('getPages', [$jsonApi, 'getPages'])
   ->register('getCategories', [$jsonApi, 'getCategories'])
   ->register('getProducts', [$jsonApi, 'getProducts'])
   ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
   ->register('getCustomers', [$jsonApi, 'getCustomers'])
   ->register('getNewTwoFA', ['TwoFA', 'getNewTwoFA'])
   ->register('genTwoFAEmergencyCodes', ['TwoFA', 'genTwoFAEmergencyCodes'])
   ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
   ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW');

$data = $io->handleRequest($_REQUEST['io']);
$io->respondAndExit($data);
