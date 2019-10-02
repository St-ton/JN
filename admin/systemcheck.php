<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Systemcheck\Environment;
use Systemcheck\Platform\Hosting;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();

$phpInfo = '';
/** @global \JTL\Smarty\JTLSmarty $smarty */
if (isset($_GET['phpinfo'])) {
    if (in_array('phpinfo', explode(',', ini_get('disable_functions')), true)) {
        return;
    }
    ob_start();
    phpinfo();
    $content = ob_get_contents();
    ob_end_clean();
    require_once PFAD_ROOT . PFAD_PHPQUERY . 'phpquery.class.php';

    $doc     = phpQuery::newDocumentHTML($content, JTL_CHARSET);
    $phpInfo = pq('body', $doc)->html();
}

$systemcheck = new Environment();
$platform    = new Hosting();

$smarty->assign('tests', $systemcheck->executeTestGroup('Shop5'))
       ->assign('platform', $platform)
       ->assign('passed', $systemcheck->getIsPassed())
       ->assign('phpinfo', $phpInfo)
       ->display('systemcheck.tpl');
