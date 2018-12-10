<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

/** @global \Smarty\JTLSmarty $smarty */
$cAction  = 'account_view';
$messages = [
    'notice' => '',
    'error'  => '',
];

if (isset($_REQUEST['action']) && FormHelper::validateToken()) {
    $cAction = StringHandler::filterXSS($_REQUEST['action']);
}

switch ($cAction) {
    case 'account_lock':
        $cAction = benutzerverwaltungActionAccountLock($smarty, $messages);
        break;
    case 'account_unlock':
        $cAction = benutzerverwaltungActionAccountUnLock($smarty, $messages);
        break;
    case 'account_edit':
        $cAction = benutzerverwaltungActionAccountEdit($smarty, $messages);
        break;
    case 'account_delete':
        $cAction = benutzerverwaltungActionAccountDelete($smarty, $messages);
        break;
    case 'group_edit':
        $cAction = benutzerverwaltungActionGroupEdit($smarty, $messages);
        break;
    case 'group_delete':
        $cAction = benutzerverwaltungActionGroupDelete($smarty, $messages);
        break;
    case 'quick_change_language':
        benutzerverwaltungActionQuickChangeLanguage($smarty, $messages);
        break;
}

benutzerverwaltungFinalize($cAction, $smarty, $messages);
