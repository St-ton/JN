<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$oAccount->permission('DISPLAY_MARKETPLACE_VIEW', true, true);

$action      = 'overview';
$error       = '';
$currentPage = 1;
$api         = new Marketplace();
// Overview
$query = new MarketplaceQuery();
$query->setPage($currentPage);
// Filters
mpInjectFilters($smarty, $query, $currentPage);

try {
    $result = $api->fetch($query);
    $smarty->assign('data', $result->result);
} catch (Exception $exc) {
    $error = sprintf("Exception '%s' with message '%s' in %s:%s", get_class($exc), $exc->getMessage(), $exc->getFile(), $exc->getFile());
}
// New extensions
$queryNew = new MarketplaceQuery();
$queryNew->setEntitiesPerPage(10)
         ->setSort('dErstellt')
         ->setOrder('DESC');

try {
    $result = $api->fetch($queryNew);
    $smarty->assign('dataNew', $result->result);
} catch (Exception $exc) {
    $error .= sprintf("Exception '%s' with message '%s' in %s:%s", get_class($exc), $exc->getMessage(), $exc->getFile(), $exc->getFile());
}
// Popular extensions
$queryPopular = new MarketplaceQuery();
$queryPopular->setEntitiesPerPage(10)
             ->setSort('nWeiterleitungen')
             ->setOrder('DESC');

try {
    $result = $api->fetch($queryPopular);
    $smarty->assign('dataPopular', $result->result);
} catch (Exception $exc) {
    $error .= sprintf("Exception '%s' with message '%s' in %s:%s", get_class($exc), $exc->getMessage(), $exc->getFile(), $exc->getFile());
}

$smarty->assign('error', $error)
       ->assign('action', $action)
       ->assign('currentPage', $currentPage)
       ->display('marktplatz.tpl');

/**
 * @param JTLSmarty        $smarty
 * @param MarketplaceQuery $query
 * @param int              $currentPage
 */
function mpInjectFilters(&$smarty, &$query, &$currentPage)
{
    // Search
    if (isset($_REQUEST['search']) && strlen(trim($_REQUEST['search'])) > 0) {
        $query->setSearchTerm($_REQUEST['search']);
        $smarty->assign('search', $_REQUEST['search']);
    }
    // Category
    if (isset($_REQUEST['cat']) && (int)$_REQUEST['cat'] > 0) {
        $query->setCategoryId($_REQUEST['cat']);
        $smarty->assign('cat', $_REQUEST['cat']);
    }
    // Order
    if (isset($_REQUEST['order']) && strlen(trim($_REQUEST['order'])) > 0) {
        $query->setOrder($_REQUEST['order']);
        $smarty->assign('order', $_REQUEST['order']);
    }
    // Sort
    if (isset($_REQUEST['sort']) && strlen(trim($_REQUEST['sort'])) > 0) {
        $query->setSort($_REQUEST['sort']);
        $smarty->assign('sort', $_REQUEST['sort']);
    }
    // Page
    if (isset($_REQUEST['page']) && (int)$_REQUEST['page'] > 0) {
        $query->setPage($_REQUEST['page']);
        $currentPage = (int)$_REQUEST['page'];
    }
}
