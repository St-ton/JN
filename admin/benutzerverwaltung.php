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
$cAction  = 'account_view';
$messages = [
    'notice' => '',
    'error'  => ''
];

if (isset($_REQUEST['action']) && Form::validateToken()) {
    $cAction = Text::filterXSS($_REQUEST['action']);
}

switch ($cAction) {
    case 'account_lock':
        $cAction = benutzerverwaltungActionAccountLock($messages);
        break;
    case 'account_unlock':
        $cAction = benutzerverwaltungActionAccountUnLock($messages);
        break;
    case 'account_edit':
        $cAction = benutzerverwaltungActionAccountEdit($smarty, $messages);
        break;
    case 'account_delete':
        $cAction = benutzerverwaltungActionAccountDelete($messages);
        break;
    case 'group_edit':
        $cAction = benutzerverwaltungActionGroupEdit($smarty, $messages);
        break;
    case 'group_delete':
        $cAction = benutzerverwaltungActionGroupDelete($messages);
        break;
    case 'quick_change_language':
        benutzerverwaltungActionQuickChangeLanguage();
        break;
}

benutzerverwaltungFinalize($cAction, $smarty, $messages);
