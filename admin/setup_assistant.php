<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\Wizard\DefaultFactory;
use JTL\Backend\Wizard\Controller;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$factory    = new DefaultFactory(Shop::Container()->getDB());
$controller = new Controller($factory);
$controller->answerQuestions($_POST);

//foreach ($controller->getSteps() as $step) {
//    Shop::dbg($step->getQuestions());
//}
//die();
$smarty->assign('steps', $controller->getSteps())
    ->display('wizard.tpl');
