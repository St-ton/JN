<?php

use JTL\Backend\AuthToken;
use JTL\Backend\Wizard\DefaultFactory;
use JTL\Backend\Wizard\Controller;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Session\Backend;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$db      = Shop::Container()->getDB();
$cache   = Shop::Container()->getCache();
$checker = new Checker(Shop::Container()->getLogService(), $db, $cache);
$manager = new Manager($db, $cache);
$admin   = new Admin($manager, $db, $cache, $checker);

$factory    = new DefaultFactory(
    $db,
    Shop::Container()->getGetText(),
    Shop::Container()->getAlertService(),
    Shop::Container()->getAdminAccount()
);
$controller = new Controller($factory);
$conf       = Shop::getSettings([CONF_GLOBAL]);

if (Request::postVar('action') === 'code') {
//    $admin->handleAuth();
} elseif (Request::getVar('action') === 'auth') {
//    $token = AuthToken::getInstance(Shop::Container()->getDB());
//    $token->requestToken(
//        Backend::get('jtl_token'),
//        Shop::getAdminURL() . '/wizard.php?action=code'
//    );
}

$smarty->assign('steps', $controller->getSteps())
    ->display('wizard.tpl');
