<?php

use JTL\Backend\Wizard\DefaultFactory;
use JTL\Backend\Wizard\Controller;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$factory    = new DefaultFactory(Shop::Container()->getDB());
$controller = new Controller($factory);
$conf       = Shop::getSettings([CONF_GLOBAL]);
$controller->answerQuestions($_POST);

$smarty->assign('steps', $controller->getSteps())
    ->assign('wizardFirstTime', $conf['global']['global_wizard_done'] === 'N')
    ->display('wizard.tpl');
