<?php

/**
 * @global Smarty\JTLSmarty $smarty
 */

require_once __DIR__ . '/includes/admininclude.php';

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Shop;

$action = $_POST['action'] ?? null;

if ($action !== 'code') {
    $oAccount->redirectOnFailure();
}

$db = Shop::Container()->getDB();

switch ($action) {
    case 'revoke':
        if (Form::validateToken()) {
            $db->executeQuery('TRUNCATE TABLE tstoreauth', 3);
        }
        break;

    case 'redirect':
        if (Form::validateToken()) {
            $db->executeQuery('TRUNCATE TABLE tstoreauth', 3);

            $code = $_SESSION['jtl_token'];
            $url  = Shop::getURL(true).$_SERVER['SCRIPT_NAME'].'?action=code';

            $db->insertRow(
                'tstoreauth',
                (object)[
                    'auth_code' => $code,
                    'created_at' => gmdate('Y-m-d H:i:s')
                ]
            );

            $query = [
                'url' => $url,
                'code' => $code
            ];

            $redirectUrl = sprintf(
                '%s?%s',
                'https://auth.jtl-test.de/link',
                http_build_query($query, '', '&')
            );

            header('location: ' . $redirectUrl);
            exit;
        }
        break;

    case 'code':
        $code  = $_POST['code'] ?? null;
        $token = $_POST['token'] ?? null;
        $res   = null;

        if ($code) {
            $res = $db->selectSingleRow('tstoreauth', 'auth_code', $code);
            if ($token) {
                $res->access_token = $token;
                $res               = $db->updateRow('tstoreauth', 'auth_code', $code, $res);
            }
        }

        http_response_code($res ? 200 : 404);
        exit;
}

$hasAuth = !!$db->query(
    'SELECT access_token FROM tstoreauth WHERE access_token IS NOT NULL',
    ReturnType::AFFECTED_ROWS
);

$smarty->assign('hasAuth', $hasAuth)
       ->display('store.tpl');
