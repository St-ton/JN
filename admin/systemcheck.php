<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->redirectOnFailure();

$systemcheck = new Systemcheck_Environment();
$platform = new Systemcheck_Platform_Hosting();

$tests = $systemcheck->executeTestGroup('Shop4');
$passed = $systemcheck->getIsPassed();

$smarty->assign('tests', $tests)
    ->assign('platform', $platform)
    ->assign('passed', $passed)
    ->display('systemcheck.tpl');