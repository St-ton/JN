<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\AdminAccount;
use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Menu;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Profiler;
use JTL\Router\BackendRouter;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty     $smarty */
/** @global AdminAccount $oAccount */
$db           = Shop::Container()->getDB();
$alertService = Shop::Container()->getAlertService();
$cache        = Shop::Container()->getCache();
$account      = Shop::Container()->getAdminAccount();
$getText      = Shop::Container()->getGetText();
$oUpdater     = new Updater($db);

$router = new BackendRouter($db, $cache, $account, $alertService, $getText);
$menu   = new Menu($router->getRouter(), $db, $account, $getText);
$data   = $menu->build();
$smarty->assign('oLinkOberGruppe_arr', $data);
$router->dispatch();
