<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$smarty->assign('status', Status::getInstance())
       ->assign('sub', Shop::Container()->get(\Network\JTLApi::class)->getSubscription())
       ->display('status.tpl');
