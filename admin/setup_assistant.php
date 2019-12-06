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

$smarty->assign('step', $controller->getActiveStep())
    ->assign('nextStep', $controller->getNextStep())
    ->assign('previousStep', $controller->getPreviousStep())
    ->display('setup_assistant.tpl');
