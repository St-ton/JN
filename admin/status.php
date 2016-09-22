<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';

$response  = new AjaxResponse();
$action    = isset($_GET['action']) ? $_GET['action'] : null;

if ($oAccount->logged() !== true) {
    $action = 'login';
}

switch ($action) {
    case 'login': {
        if ($response->isAjax()) {
            $result = $response->buildError('Unauthorized', 401);
            $response->makeResponse($result);
        } else {
            $oAccount->redirectOnFailure();
        }

        return;
    }
    case 'notify': {
        $result = $response->buildResponse([
            'tpl' => $smarty
                ->assign('notifications', $notify)
                ->fetch('tpl_inc/notify_drop.tpl')
        ]);
        $response->makeResponse($result, $action);
        break;
    }

    default: {
        $smarty->assign('status', Status::getInstance())
            ->display('status.tpl');
        break;
    }
}
