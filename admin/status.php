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
    $result = $response->buildError('Unauthorized', 401);
    $response->makeResponse($result);
}

/*
 *  TODO: Build notification storage & allow plugins to add notification entries
 */

switch ($action) {
    case 'notify': {
        $result = $response->buildResponse([
            'tpl' => $smarty
                ->assign('notifications', Notification::buildDefault())
                ->fetch('tpl_inc/notify_drop.tpl')
        ]);

        $response->makeResponse($result, $action);
        break;
    }
    
    default: {
        $status = new Status();
        $smarty
            ->assign('objectCache', $status->getObjectCache())
            ->assign('imageCache', $status->getImageCache())
            ->assign('systemLogInfo', $status->getSystemLogInfo())
            ->assign('validDatabaseStruct', $status->validDatabateStruct())
            ->assign('validFileStruct', $status->validFileStruct())
            ->assign('validFolderPermissions', $status->validFolderPermissions())
            ->assign('pluginSharedHooks', $status->getPluginSharedHooks())
            ->display('status.tpl');
        break;
    }
}
