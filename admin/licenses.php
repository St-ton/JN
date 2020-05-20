<?php declare(strict_types=1);

use GuzzleHttp\Exception\RequestException;
use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\License\Admin;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
$db    = Shop::Container()->getDB();
$admin = new Admin(new Manager($db), $db, Shop::Container()->getCache());
$admin->handle($smarty);
$smarty->display('licenses.tpl');
