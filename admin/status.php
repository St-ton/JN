<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$smarty->assign('status', Status::getInstance())
    ->assign('phpLT55', version_compare(PHP_VERSION, '5.5') < 0)
    ->display('status.tpl');
