<?php declare(strict_types=1);

use JTL\Helpers\Request;
use JTL\Router\BackendRouter;

require_once __DIR__ . '/includes/admininclude.php';
$route    = BackendRouter::ROUTE_PLUGIN;
$pluginID = Request::getInt('kPlugin');
if ($pluginID > 0) {
    $query = count($_GET) > 0 ? ('?' . http_build_query($_GET)) : '';
    routeRedirect($route . '/' . $pluginID . $query);
}
