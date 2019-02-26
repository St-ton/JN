<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Helpers\Text;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step         = 'prepare';
$aslertHelper = Shop::Container()->getAlertService();
if (isset($_POST['mail']) && Form::validateToken()) {
    $account = Shop::Container()->getAdminAccount();
    $account->prepareResetPassword(Text::filterXSS($_POST['mail']));
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successEmailSend'), 'successEmailSend');
} elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpm'], $_POST['fpwh']) && Form::validateToken()) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $account  = Shop::Container()->getAdminAccount();
        $verified = $account->verifyResetPasswordHash($_POST['fpwh'], $_POST['fpm']);
        if ($verified === true) {
            $upd        = new stdClass();
            $upd->cPass = Shop::Container()->getPasswordService()->hash($_POST['pw_new']);
            $update     = Shop::Container()->getDB()->update('tadminlogin', 'cMail', $_POST['fpm'], $upd);
            if ($update > 0) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    __('successPasswordChange'),
                    'successPasswordChange',
                    ['saveInSession' => true]
                );
                header('Location: index.php?pw_updated=true');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPasswordChange'), 'errorPasswordChange');
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorHashInvalid'), 'errorHashInvalid');
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPasswordMismatch'), 'errorPasswordMismatch');
    }
    $smarty->assign('fpwh', $_POST['fpwh'])
           ->assign('fpm', $_POST['fpm']);
    $step = 'confirm';
} elseif (isset($_GET['fpwh'], $_GET['mail'])) {
    $smarty->assign('fpwh', $_GET['fpwh'])
           ->assign('fpm', $_GET['mail']);
    $step = 'confirm';
}

$smarty->assign('step', $step)
       ->display('pass.tpl');
