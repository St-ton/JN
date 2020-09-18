<?php

use JTL\Helpers\Request;
use JTL\Recommendation\Manager;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$recommendation = (new Manager(Shop::Container()->getAlertService(), Request::verifyGPDataString('scope')))
    ->getRecommendationById(Request::verifyGPDataString('id'));

$smarty->assign('recommendation', $recommendation)
       ->display('premiumplugin.tpl');
