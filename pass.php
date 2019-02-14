<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\Helpers\Text;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_PASSWORTVERGESSEN);
$linkHelper  = Shop::Container()->getLinkService();
$kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_PASSWORD_VERGESSEN);
$step        = 'formular';
$alertHelper = Shop::Container()->getAlertService();
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
        $step   = 'passwort versenden';
        $oKunde = new Kunde($kunde->kKunde);
        $oKunde->prepareResetPassword();

        Shop::Smarty()->assign('Kunde', $oKunde);
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
                $customer = new Kunde($resetItem->kKunde);
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
    Shop::Smarty()->assign('fpwh', Text::filterXSS($_POST['fpwh']));
} elseif (isset($_GET['fpwh'])) {
    $resetItem = Shop::Container()->getDB()->select('tpasswordreset', 'cKey', $_GET['fpwh']);
    if ($resetItem) {
        $dateExpires = new DateTime($resetItem->dExpires);
        if ($dateExpires >= new DateTime()) {
            Shop::Smarty()->assign('fpwh', Text::filterXSS($_GET['fpwh']));
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

Shop::Smarty()->assign('step', $step)
    ->assign('Link', $link);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('account/password.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
