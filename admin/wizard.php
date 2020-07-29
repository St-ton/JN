<?php

use JTL\Backend\Wizard\DefaultFactory;
use JTL\Backend\Wizard\Controller;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$factory    = new DefaultFactory(
    Shop::Container()->getDB(),
    Shop::Container()->getGetText(),
    Shop::Container()->getAlertService(),
    Shop::Container()->getAdminAccount()
);
$controller = new Controller($factory);
$conf       = Shop::getSettings([CONF_GLOBAL]);

$smarty->assign('steps', $controller->getSteps())
    ->display('wizard.tpl');
