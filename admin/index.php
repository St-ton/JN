<?php declare(strict_types=1);

use JTL\Backend\Menu;
use JTL\Router\BackendRouter;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$db           = Shop::Container()->getDB();
$alertService = Shop::Container()->getAlertService();
$cache        = Shop::Container()->getCache();
$account      = Shop::Container()->getAdminAccount();
$getText      = Shop::Container()->getGetText();
$router       = new BackendRouter($db, $cache, $account, $alertService, $getText);
$menu         = new Menu($db, $account, $getText);
$data         = $menu->build();
Shop::Smarty()->assign('oLinkOberGruppe_arr', $data);
$router->dispatch();
