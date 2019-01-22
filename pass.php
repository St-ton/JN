<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_PASSWORTVERGESSEN);
$linkHelper = Shop::Container()->getLinkService();
$kLink      = $linkHelper->getSpecialPageLinkKey(LINKTYP_PASSWORD_VERGESSEN);
$step       = 'formular';
$hinweis    = '';
$cFehler    = '';
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
        $hinweis = Shop::Lang()->get('accountLocked');
    } else {
        $hinweis = Shop::Lang()->get('incorrectEmail');
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
                $cFehler = Shop::Lang()->get('invalidCustomer', 'account data');
            } else {
                $cFehler = Shop::Lang()->get('invalidHash', 'account data');
            }
        } else {
            $cFehler = Shop::Lang()->get('invalidHash', 'account data');
        }
    } else {
        $cFehler = Shop::Lang()->get('passwordsMustBeEqual', 'account data');
    }
    $step = 'confirm';
    Shop::Smarty()->assign('fpwh', StringHandler::filterXSS($_POST['fpwh']));
} elseif (isset($_GET['fpwh'])) {
    $resetItem = Shop::Container()->getDB()->select('tpasswordreset', 'cKey', $_GET['fpwh']);
    if ($resetItem) {
        $dateExpires = new DateTime($resetItem->dExpires);
        if ($dateExpires >= new DateTime()) {
            Shop::Smarty()->assign('fpwh', StringHandler::filterXSS($_GET['fpwh']));
        } else {
            $cFehler = Shop::Lang()->get('invalidHash', 'account data');
        }
    } else {
        $cFehler = Shop::Lang()->get('invalidHash', 'account data');
    }
    $step = 'confirm';
}
$cCanonicalURL    = $linkHelper->getStaticRoute('pass.php');
$metaData         = $linkHelper->buildSpecialPageMeta(LINKTYP_PASSWORD_VERGESSEN);
$link             = $linkHelper->getPageLink($kLink);
$cMetaTitle       = $metaData->cTitle;
$cMetaDescription = $metaData->cDesc;
$cMetaKeywords    = $metaData->cKeywords;
Shop::Smarty()->assign('step', $step)
    ->assign('hinweis', $hinweis)
    ->assign('Link', $link)
    ->assign('cFehler', $cFehler);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('account/password.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
