<?php declare(strict_types=1);

use JTL\Router\BackendRouter;
use JTL\Shop;

if (isset($_SERVER['HTTP_TESTDB']) === true) {
    define('TESTDB', true);
}

require_once __DIR__ . '/includes/admininclude.php';

$router = new BackendRouter(
    Shop::Container()->getDB(),
    Shop::Container()->getCache(),
    Shop::Container()->getAdminAccount(),
    Shop::Container()->getAlertService(),
    Shop::Container()->getGetText(),
    Shop::Smarty()
);
$router->dispatch();
