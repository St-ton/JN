<?php declare(strict_types=1);

use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$db    = Shop::Container()->getDB();
$admin = new Admin(new Manager($db), $db, Shop::Container()->getCache());
if (Request::postVar('action') === 'code') {
    $admin->handleAuth();
} else {
    $oAccount->permission('CONTENT_PAGE_VIEW', true, true);
    $admin->handle($smarty);
    $smarty->display('licenses.tpl');
}
