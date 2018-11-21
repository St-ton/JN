<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global Smarty\JTLSmarty $smarty
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$db = Shop::Container()->getDB();

if (isset($_GET['revoke'])) {
    $db->executeQuery("TRUNCATE TABLE tstoreauth", 3);
} elseif (isset($_GET['redirect']) || isset($_GET['code'])) {
    try {
        $provider = new Jtl\OAuth2\Client\Provider\Jtl([
            'clientId' => STORE_AUTH_ID,
            'clientSecret' => STORE_AUTH_SECRET,
            'redirectUri' => Shop::getURL(true),
        ]);

        if (!isset($_GET['code'])) {
            $authorizationUrl = $provider->getAuthorizationUrl([
                'scope' => ['profile']
            ]);
            $_SESSION['oauth2state'] = $provider->getState();

            header('Location: ' . $authorizationUrl);
            exit;
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            throw new Exception('Invalid state');
        } else {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            $storeAuth = (object)[
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'created_at' => gmdate("Y-m-d H:i:s"),
                'expires_at' => gmdate("Y-m-d H:i:s", $accessToken->getExpires()),
            ];

            $db->insertRow("tstoreauth", $storeAuth);

            header('Location: store.php');
            exit;
        }
    } catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        $body = $e->getResponseBody();
        $message = is_array($body)
            ? $body['message']
            : $e->getMessage();
        Shop::Smarty()->assign('error', $message);
    } catch (Exception $e) {
        Shop::Smarty()->assign('error', $e->getMessage());
    }
}

$hasAuth = !!$db->query("SELECT access_token FROM tstoreauth", 3);

$smarty->assign('hasAuth', $hasAuth)
    ->display('store.tpl');
