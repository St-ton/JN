<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global Smarty\JTLSmarty $smarty
 */

require_once __DIR__ . '/includes/admininclude.php';

use JTL\Backend\AuthToken;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Session\Backend;
use JTL\Shop;

$action = Request::postVar('action', 'default');

if ($action !== 'code') {
    $oAccount->permission($_SESSION['jtl_token'], true, true);
}

$authToken = AuthToken::instance();

switch ($action) {
    case 'revoke':
        if (Form::validateToken()) {
            $authToken->revoke();
        }
        break;
    case 'redirect':
        if (Form::validateToken()) {
            $authToken->requestToken(
                Backend::get('jtl_token'),
                Shop::getURL(true).$_SERVER['SCRIPT_NAME'].'?action=code'
            );
        }
        break;
    case 'code':
        $authToken->responseToken();
        break;
}

$smarty->assign('hasAuth', $authToken->isValid())
       ->display('store.tpl');
