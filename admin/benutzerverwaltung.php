<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Text;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$action = 'account_view';

if (isset($_REQUEST['action']) && Form::validateToken()) {
    $action = Text::filterXSS($_REQUEST['action']);
}

switch ($action) {
    case 'account_lock':
        $action = benutzerverwaltungActionAccountLock($messages);
        break;
    case 'account_unlock':
        $action = benutzerverwaltungActionAccountUnLock($messages);
        break;
    case 'account_edit':
        $action = benutzerverwaltungActionAccountEdit($smarty, $messages);
        break;
    case 'account_delete':
        $action = benutzerverwaltungActionAccountDelete($messages);
        break;
    case 'group_edit':
        $action = benutzerverwaltungActionGroupEdit($smarty, $messages);
        break;
    case 'group_delete':
        $action = benutzerverwaltungActionGroupDelete($messages);
        break;
    case 'quick_change_language':
        benutzerverwaltungActionQuickChangeLanguage();
        break;
}

benutzerverwaltungFinalize($action, $smarty, $messages);
