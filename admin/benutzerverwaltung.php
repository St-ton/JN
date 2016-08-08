<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cAction  = 'account_view';
$messages = array(
    'notice' => '',
    'error'  => '',
);

if (isset($_REQUEST['action']) && validateToken()) {
    $cAction = StringHandler::filterXSS($_REQUEST['action']);
}

switch ($cAction) {
    case 'account_lock':
        $cAction = benutzerverwaltungActionAccountLock($GLOBALS['smarty'], $messages);
        break;
    case 'account_unlock':
        $cAction = benutzerverwaltungActionAccountUnLock($GLOBALS['smarty'], $messages);
        break;
    case 'account_edit':
        $cAction = benutzerverwaltungActionAccountEdit($GLOBALS['smarty'], $messages);
        break;
    case 'account_delete':
        $cAction = benutzerverwaltungActionAccountDelete($GLOBALS['smarty'], $messages);
        break;
    case 'group_edit':
        $cAction = benutzerverwaltungActionGroupEdit($GLOBALS['smarty'], $messages);
        break;
    case 'group_delete':
        $cAction = benutzerverwaltungActionGroupDelete($GLOBALS['smarty'], $messages);
        break;
}

benutzerverwaltungFinalize($cAction, $GLOBALS['smarty'], $messages);
