<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->redirectOnFailure();

if (isset($_GET['phpinfo'])) {
    if (in_array('phpinfo', explode(',', ini_get('disable_functions')))) {
        return;
    }

    ob_start();
    phpinfo();
    $content = ob_get_contents();
    ob_end_clean();

    $doc     = phpQuery::newDocumentHTML($content, JTL_CHARSET);
    $content = pq('body', $doc)->html();

    $smarty->assign('phpinfo', $content);
}

$systemcheck = new Systemcheck_Environment();
$platform    = new Systemcheck_Platform_Hosting();

$tests  = $systemcheck->executeTestGroup('Shop4');
$passed = $systemcheck->getIsPassed();

$smarty->assign('tests', $tests)
    ->assign('platform', $platform)
    ->assign('passed', $passed)
    ->display('systemcheck.tpl');
