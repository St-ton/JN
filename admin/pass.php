<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step         = 'prepare';
$alertService = Shop::Container()->getAlertService();
$alertService->addWarning(__('warningPasswordResetAuth'), 'warningPasswordResetAuth');
if (isset($_POST['mail']) && Form::validateToken()) {
    $account = Shop::Container()->getAdminAccount();
    $account->prepareResetPassword(Text::filterXSS($_POST['mail']));
} elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpm'], $_POST['fpwh']) && Form::validateToken()) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $account  = Shop::Container()->getAdminAccount();
        $verified = $account->verifyResetPasswordHash($_POST['fpwh'], $_POST['fpm']);
        if ($verified === true) {
            $upd        = new stdClass();
            $upd->cPass = Shop::Container()->getPasswordService()->hash($_POST['pw_new']);
            $update     = Shop::Container()->getDB()->update('tadminlogin', 'cMail', $_POST['fpm'], $upd);
            if ($update > 0) {
                $alertService->addSuccess(
                    __('successPasswordChange'),
                    'successPasswordChange',
                    ['saveInSession' => true]
                );
                header('Location: ' . Shop::getAdminURL() . '/index.php?pw_updated=true');
            } else {
                $alertService->addError(__('errorPasswordChange'), 'errorPasswordChange');
            }
        } else {
            $alertService->addError(__('errorHashInvalid'), 'errorHashInvalid');
        }
    } else {
        $alertService->addError(__('errorPasswordMismatch'), 'errorPasswordMismatch');
    }
    $smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']))
        ->assign('fpm', Text::filterXSS($_POST['fpm']));
    $step = 'confirm';
} elseif (isset($_GET['fpwh'], $_GET['mail'])) {
    $smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']))
        ->assign('fpm', Text::filterXSS($_GET['mail']));
    $step = 'confirm';
}

$smarty->assign('step', $step)
    ->display('pass.tpl');
