<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\RateLimit\ForgotPassword;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::setPageType(PAGE_PASSWORTVERGESSEN);
$linkHelper  = Shop::Container()->getLinkService();
$step        = 'formular';
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();
$smarty      = Shop::Smarty();
$valid       = Form::validateToken();
$missing     = ['captcha' => false];
if ($valid && isset($_POST['passwort_vergessen'], $_POST['email']) && (int)$_POST['passwort_vergessen'] === 1) {
    $kunde   = $db->getSingleObject(
        'SELECT kKunde, cSperre
        FROM tkunde
            WHERE cMail = :mail
            AND nRegistriert = 1',
        ['mail' => $_POST['email']]
    );
    $limiter = new ForgotPassword($db);
    $limiter->init(Request::getRealIP(), (int)($kunde->kKunde ?? 0));
    if ($limiter->check() === true) {
        $limiter->persist();
        $limiter->cleanup();
        $validRecaptcha = true;
        if (Shop::getSettingValue(CONF_KUNDEN, 'forgot_password_captcha') === 'Y' && !Form::validateCaptcha($_POST)) {
            $validRecaptcha     = false;
            $missing['captcha'] = true;
        }
        if ($validRecaptcha === false) {
            $alertHelper->addError(Shop::Lang()->get('fillOut'), 'accountLocked');
        } elseif (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre !== 'Y') {
            $step     = 'passwort versenden';
            $customer = new Customer((int)$kunde->kKunde);
            $customer->prepareResetPassword();

            $smarty->assign('Kunde', $customer);
        } elseif (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre === 'Y') {
            $alertHelper->addError(Shop::Lang()->get('accountLocked'), 'accountLocked');
        } else {
            $alertHelper->addError(Shop::Lang()->get('incorrectEmail'), 'incorrectEmail');
        }
    } else {
        $missing['limit'] = true;
        $alertHelper->addError(Shop::Lang()->get('formToFast', 'account data'), 'accountLocked');
    }
} elseif ($valid && isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpwh'])) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $resetItem = $db->select('tpasswordreset', 'cKey', $_POST['fpwh']);
        if ($resetItem !== null && ($dateExpires = new DateTime($resetItem->dExpires)) >= new DateTime()) {
            $customer = new Customer((int)$resetItem->kKunde);
            if ($customer->kKunde > 0 && $customer->cSperre !== 'Y') {
                $customer->updatePassword($_POST['pw_new']);
                $db->delete('tpasswordreset', 'kKunde', $customer->kKunde);
                header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?updated_pw=true');
                exit();
            }
            $alertHelper->addError(Shop::Lang()->get('invalidCustomer', 'account data'), 'invalidCustomer');
        } else {
            $alertHelper->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
        }
    } else {
        $alertHelper->addError(Shop::Lang()->get('passwordsMustBeEqual', 'account data'), 'passwordsMustBeEqual');
    }
    $step = 'confirm';
    $smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']));
} elseif (isset($_GET['fpwh'])) {
    $resetItem = $db->select('tpasswordreset', 'cKey', $_GET['fpwh']);
    if ($resetItem) {
        $dateExpires = new DateTime($resetItem->dExpires);
        if ($dateExpires >= new DateTime()) {
            $smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']));
        } else {
            $alertHelper->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
        }
    } else {
        $alertHelper->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
    }
    $step = 'confirm';
}
$cCanonicalURL = $linkHelper->getStaticRoute('pass.php');
$link          = $linkHelper->getSpecialPage(LINKTYP_PASSWORD_VERGESSEN);
if (!$alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
    $alertHelper->addInfo(
        Shop::Lang()->get('forgotPasswordDesc', 'forgot password'),
        'forgotPasswordDesc',
        ['showInAlertListTemplate' => false]
    );
}

$smarty->assign('step', $step)
    ->assign('fehlendeAngaben', $missing)
    ->assign('presetEmail', Text::filterXSS(Request::verifyGPDataString('email')))
    ->assign('Link', $link);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$smarty->display('account/password.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
