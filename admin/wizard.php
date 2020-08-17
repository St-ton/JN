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

$db           = Shop::Container()->getDB();
$cache        = Shop::Container()->getCache();
$checker      = new Checker(Shop::Container()->getLogService(), $db, $cache);
$manager      = new Manager($db, $cache);
$admin        = new Admin($manager, $db, $cache, $checker);
$factory      = new DefaultFactory(
    $db,
    Shop::Container()->getGetText(),
    Shop::Container()->getAlertService(),
    Shop::Container()->getAdminAccount()
);
$controller   = new Controller($factory);
$conf         = Shop::getSettings([CONF_GLOBAL]);
$token        = AuthToken::getInstance(Shop::Container()->getDB());
$authRedirect = Backend::get('wizard-authenticated') ?? false;

if (Request::postVar('action') === 'code') {
    $admin->handleAuth();
} elseif (Request::getVar('action') === 'auth') {
    Backend::set('wizard-authenticated', true);
    $token->requestToken(
        Backend::get('jtl_token'),
        Shop::getAdminURL() . '/wizard.php?action=code'
    );
}
if (Request::postVar('action') !== 'code') {
    unset($_SESSION['wizard-authenticated']);
    $oAccount->redirectOnFailure();
    $smarty->assign('steps', $controller->getSteps())
        ->assign('authRedirect', $authRedirect)
        ->assign('hasAuth', $token->isValid())
        ->display('wizard.tpl');
}
