<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once dirname(__FILE__) . '/includes/plz_ort_import_inc.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

/** @global JTLSmarty $smarty */

$cAction  = 'index';
$messages = array(
    'notice' => '',
    'error'  => '',
);

if (isset($_REQUEST['action']) && validateToken()) {
    $cAction = StringHandler::filterXSS($_REQUEST['action']);
}

switch ($cAction) {
    case 'callStatus':
        plzimportActionCallStatus();
        break;
    case 'checkStatus':
        plzimportActionCheckStatus();
        break;
    case 'delTempImport':
        plzimportActionDelTempImport();
        break;
    case 'doImport':
        plzimportActionDoImport(
            isset($_REQUEST['target']) ? StringHandler::filterXSS($_REQUEST['target']) : '',
            isset($_REQUEST['part']) ? StringHandler::filterXSS($_REQUEST['part']) : '',
            isset($_REQUEST['step']) ? (int)$_REQUEST['step'] : 0
        );
        break;
    case 'doLocalImport':
        plzimportActionDoImport(
            isset($_REQUEST['target']) ? StringHandler::filterXSS($_REQUEST['target']) : '',
            'import',
            isset($_REQUEST['step']) ? (int)$_REQUEST['step'] : 0
        );
        break;
    case 'loadAvailableDownloads':
        plzimportActionLoadAvailableDownloads($smarty);
        break;
    case 'loadBackup':
        plzimportActionRestoreBackup(isset($_REQUEST['target']) ? StringHandler::filterXSS($_REQUEST['target']) : '');
        break;
    case 'updateIndex':
        plzimportActionUpdateIndex($smarty);
        break;
    case 'index':
    default:
        plzimportActionIndex($smarty, $messages);
        break;
}

plzimportFinalize($cAction, $smarty, $messages);
