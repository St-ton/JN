<?php

use JTL\Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

if (Form::validateToken()) {
    $oAccount->logout();
}
$oAccount->redirectOnFailure();
