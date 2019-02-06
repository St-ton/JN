<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Backend\AdminFavorite;
use Helpers\Form;
use Helpers\Request;

/**
 * @global \Smarty\JTLSmarty     $smarty
 * @global \Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->redirectOnFailure();

$kAdminlogin = (int)$_SESSION['AdminAccount']->kAdminlogin;
if (isset($_POST['title'], $_POST['url'])
    && Form::validateToken()
    && Request::verifyGPDataString('action') === 'save'
) {
    $titles = $_POST['title'];
    $urls   = $_POST['url'];
    if (is_array($titles) && is_array($urls) && count($titles) === count($urls)) {
        AdminFavorite::remove($kAdminlogin);
        foreach ($titles as $i => $title) {
            AdminFavorite::add($kAdminlogin, $title, $urls[$i], $i);
        }
    }
}

$smarty->assign('favorites', $oAccount->favorites())
       ->display('favs.tpl');
