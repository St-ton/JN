<?php

use JTL\Backend\AdminAccountManager;
use JTL\Helpers\Form;
use JTL\Helpers\Text;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$action              = 'account_view';
$adminAccountManager = new AdminAccountManager($smarty, Shop::Container()->getDB());

if (isset($_REQUEST['action']) && Form::validateToken()) {
    $action = Text::filterXSS($_REQUEST['action']);
}

switch ($action) {
    case 'account_lock':
        $action = $adminAccountManager->actionAccountLock();
        break;
    case 'account_unlock':
        $action = $adminAccountManager->actionAccountUnLock();
        break;
    case 'account_edit':
        $action = $adminAccountManager->actionAccountEdit();
        break;
    case 'account_delete':
        $action = $adminAccountManager->actionAccountDelete();
        break;
    case 'group_edit':
        $action = $adminAccountManager->actionGroupEdit();
        break;
    case 'group_delete':
        $action = $adminAccountManager->actionGroupDelete();
        break;
    case 'quick_change_language':
        $adminAccountManager->actionQuickChangeLanguage();
        break;
}

$adminAccountManager->finalize($action);
