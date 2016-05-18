<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
set_time_limit(0);
define('JTL_CHARSET', 'utf-8');

require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';

$response = new AjaxResponse();
$hasPermission = $oAccount->permission('SETTINGS_SEARCH_VIEW', false, false);

if (!$hasPermission) {
    $result = $response->buildError('Unauthorized', 401);
    $response->makeResponse($result, $action);
    exit;
}

Shop::DB()->executeQuery('SET NAMES ' . str_replace('-', '', JTL_CHARSET), 3);

$query = isset($_GET['query']) ? $_GET['query'] : null;
$data = isset($_GET['data']) ? (bool)(int)$_GET['data'] : false;

$settings = bearbeiteEinstellungsSuche(Shop::DB()->escape($query));

$groupedSettings = [];
$currentGroup = null;

foreach ($settings->oEinstellung_arr as $setting) {
    if ($setting->cConf === 'N') {
        $currentGroup = $setting;
        $currentGroup->oEinstellung_arr = [];
        $groupedSettings[] = $currentGroup;
    }
    elseif ($currentGroup !== null) {
        $currentGroup->oEinstellung_arr[] = $setting;
    }
}

if ($data === true) {
    if (count($groupedSettings) === 0) {
        $result = $response->buildError('No search results');
    }
    else {
        $result = $response->buildResponse($groupedSettings);
    }
}
else {
    $smarty->assign('settings', $groupedSettings);
    $template = $smarty->fetch('suche.tpl');
    $result = $response->buildResponse([ 'tpl' => $template ]);
}

$response->makeResponse($result, 'search');
Shop::DB()->close();