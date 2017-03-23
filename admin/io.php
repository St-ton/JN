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

$jsonApi = JSONAPI::getInstance();
$io      = IO::getInstance();

$io->register('getPages', [$jsonApi, 'getPages'])
   ->register('getCategories', [$jsonApi, 'getCategories'])
   ->register('getProducts', [$jsonApi, 'getProducts'])
   ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
   ->register('getCustomers', [$jsonApi, 'getCustomers']);

$data = $io->handleRequest($_REQUEST['io']);
$io->respondAndExit($data);
