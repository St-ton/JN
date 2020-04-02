<?php

use JTL\Backend\Status;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global \JTL\Smarty\JTLSmarty $smarty */

$status             = Status::getInstance();
$orphanedCategories = $status->getOrphanedCategories(false);

$smarty->assign('passed', count($orphanedCategories) === 0)
       ->assign('cateogries', $orphanedCategories)
       ->display('categorycheck.tpl');
