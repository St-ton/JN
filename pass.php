<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Text;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::setPageType(PAGE_PASSWORTVERGESSEN);
$linkHelper  = Shop::Container()->getLinkService();
$kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_PASSWORD_VERGESSEN);
$step        = 'formular';
$alertHelper = Shop::Container()->getAlertService();
$smarty      = Shop::Smarty();
if (isset($_POST['passwort_vergessen'], $_POST['email']) && (int)$_POST['passwort_vergessen'] === 1) {
    $kunde = Shop::Container()->getDB()->select(
        'tkunde',
        'cMail',
        $_POST['email'],
        'nRegistriert',
        1,
        null,
        null,
        false,
        'kKunde, cSperre'
    );
    if (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre !== 'Y') {
        $step     = 'passwort versenden';
        $customer = new Customer($kunde->kKunde);
        $customer->prepareResetPassword();

        $smarty->assign('Kunde', $customer);
    } elseif (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre === 'Y') {
        $alertHelper->addAlert(Alert::TYPE_ERROR, Shop::Lang()->get('accountLocked'), 'accountLocked');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, Shop::Lang()->get('incorrectEmail'), 'incorrectEmail');
    }
} elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpwh'])) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $resetItem = Shop::Container()->getDB()->select('tpasswordreset', 'cKey', $_POST['fpwh']);
        if ($resetItem) {
            $dateExpires = new DateTime($resetItem->dExpires);
            if ($dateExpires >= new DateTime()) {
                $customer = new Customer($resetItem->kKunde);
                if ($customer && $customer->cSperre !== 'Y') {
                    $customer->updatePassword($_POST['pw_new']);
                    Shop::Container()->getDB()->delete('tpasswordreset', 'kKunde', $customer->kKunde);
                    header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?updated_pw=true');
                    exit();
                }
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('invalidCustomer', 'account data'),
                    'invalidCustomer'
                );
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('invalidHash', 'account data'),
                    'invalidHash'
                );
            }
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('invalidHash', 'account data'),
                'invalidHash'
            );
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('passwordsMustBeEqual', 'account data'),
            'passwordsMustBeEqual'
        );
    }
    $step = 'confirm';
    $smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']));
} elseif (isset($_GET['fpwh'])) {
    $resetItem = Shop::Container()->getDB()->select('tpasswordreset', 'cKey', $_GET['fpwh']);
    if ($resetItem) {
        $dateExpires = new DateTime($resetItem->dExpires);
        if ($dateExpires >= new DateTime()) {
            $smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']));
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('invalidHash', 'account data'),
                'invalidHash'
            );
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('invalidHash', 'account data'),
            'invalidHash'
        );
    }
    $step = 'confirm';
}
$cCanonicalURL = $linkHelper->getStaticRoute('pass.php');
$link          = $linkHelper->getPageLink($kLink);
if (!$alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
    $alertHelper->addAlert(
        Alert::TYPE_INFO,
        Shop::Lang()->get('forgotPasswordDesc', 'forgot password'),
        'forgotPasswordDesc',
        ['showInAlertListTemplate' => false]
    );
}

$smarty->assign('step', $step)
       ->assign('Link', $link);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$smarty->display('account/password.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
