<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
set_time_limit(0);

use JMS\Serializer\SerializerBuilder;
/** @global JTLSmarty $smarty */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Updater.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';

$hasPermission = $oAccount->permission('SHOP_UPDATE_VIEW', false, false);
$action        = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === null && !$hasPermission) {
    $oAccount->redirectOnFailure();
    exit;
}

if ($hasPermission === false) {
    $action = 'login';
}
$response  = new AjaxResponse();
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

$getData = function () {
    $host = 'andyfront.de'; // Shop::getUrl()
    $api  = new \Andyftw\SSLLabs\Api();
    $info = $api->analyze($host);

    if ($info->getStatus() === 'READY' && $endpoints = $info->getEndpoints()) {
        if (count($endpoints) > 0) {
            $endpoint = $endpoints[0];
            $details  = $api->getEndpointData($host, $endpoint->getIpAddress());
            $info->setEndpoints([$details]);
        }
    }

    return $info;
};

$rebuildData = function ($data) {
    $serializer = SerializerBuilder::create()->build();

    return json_decode($serializer->serialize($data, 'json'));
};

switch ($action) {
    default:
        $data = $getData();
        $smarty->assign('data', $data);
        $smarty->display('sslcheck.tpl');
        break;

    case 'check':
        try {
            $data = $getData();
            $smarty->assign('data', $data);
            $content = $smarty->fetch('tpl_inc/sslcheck.tpl');

            $res    = (object)['tpl' => $content, 'data' =>  $rebuildData($data)];
            $result = $response->buildResponse($res);
        } catch (\Andyftw\SSLLabs\Exception\ApiException $e) {
            $result = $response->buildError($e->getMessage());
        }

        $response->makeResponse($result, $action);
        break;

    case 'login':
        $result = $response->buildError('Unauthorized', 401);
        $response->makeResponse($result, $action);
        break;
}
