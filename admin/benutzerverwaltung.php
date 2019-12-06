<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
        $action = $adminAccountManager->benutzerverwaltungActionAccountLock();
        break;
    case 'account_unlock':
        $action = $adminAccountManager->benutzerverwaltungActionAccountUnLock();
        break;
    case 'account_edit':
        $action = $adminAccountManager->benutzerverwaltungActionAccountEdit();
        break;
    case 'account_delete':
        $action = $adminAccountManager->benutzerverwaltungActionAccountDelete();
        break;
    case 'group_edit':
        $action = $adminAccountManager->benutzerverwaltungActionGroupEdit();
        break;
    case 'group_delete':
        $action = $adminAccountManager->benutzerverwaltungActionGroupDelete();
        break;
    case 'quick_change_language':
        $adminAccountManager->benutzerverwaltungActionQuickChangeLanguage();
        break;
}

$adminAccountManager->benutzerverwaltungFinalize($action);
