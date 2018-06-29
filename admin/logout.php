<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

if (FormHelper::validateToken()) {
    $oAccount->logout();
}
$oAccount->redirectOnFailure();
