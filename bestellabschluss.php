<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'trustedshops_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$Einstellungen = Shopsetting::getInstance()->getAll();
Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$linkHelper    = Shop::Container()->getLinkService();
$AktuelleSeite = 'BESTELLVORGANG';
$kLink         = $linkHelper->getSpecialPageLinkKey(LINKTYP_BESTELLABSCHLUSS);
$link          = $linkHelper->getPageLink($kLink);
$cart          = Session::Cart();
$smarty        = Shop::Smarty();
$bestellung    = null;
if (isset($_GET['i'])) {
    $bestellid = Shop::Container()->getDB()->select('tbestellid', 'cId', $_GET['i']);
    if (isset($bestellid->kBestellung) && $bestellid->kBestellung > 0) {
        $bestellung = new Bestellung($bestellid->kBestellung);
        $bestellung->fuelleBestellung(false);
        speicherUploads($bestellung);
        Shop::Container()->getDB()->delete('tbestellid', 'kBestellung', (int)$bestellid->kBestellung);
    }
    Shop::Container()->getDB()->query(
        'DELETE FROM tbestellid WHERE dDatum < DATE_SUB(NOW(),INTERVAL 30 DAY)',
        \DB\ReturnType::DEFAULT
    );
    $smarty->assign('abschlussseite', 1);
} else {
    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags(Shop::Container()->getDB()->escape($_POST['kommentar'])), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }
    if (SimpleMail::checkBlacklist($_SESSION['Kunde']->cMail)) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
            '?mailBlocked=1', true, 303);
        exit;
    }
    if (!bestellungKomplett()) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
            '?fillOut=' . gibFehlendeEingabe(), true, 303);
        exit;
    }
    //pruefen, ob von jedem Artikel im WK genug auf Lager sind. Wenn nicht, WK verkleinern und Redirect zum WK
    $cart->pruefeLagerbestaende();

    if ($cart->checkIfCouponIsStillValid() === false) {
        $_SESSION['checkCouponResult']['ungueltig'] = 3;
        header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
        exit;
    }

    if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung)) {
        $cart->loescheDeaktiviertePositionen();
        $wkChecksum = Warenkorb::getChecksum($cart);
        if (!empty($cart->cChecksumme)
            && $wkChecksum !== $cart->cChecksumme
        ) {
            if (!$cart->posTypEnthalten(C_WARENKORBPOS_TYP_ARTIKEL)) {
                WarenkorbHelper::deleteAllSpecialPositions();
            }
            $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('yourbasketismutating', 'checkout');
            header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
            exit;
        }
        $bestellung = finalisiereBestellung();
        $bestellid  = $bestellung->kBestellung > 0
            ? Shop::Container()->getDB()->select('tbestellid', 'kBestellung', $bestellung->kBestellung)
            : false;
        if ($bestellung->Lieferadresse === null && !empty($_SESSION['Lieferadresse']->cVorname)) {
            $bestellung->Lieferadresse = gibLieferadresseAusSession();
        }
        $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php');
        $successPaymentURL = !empty($bestellid->cId)
            ? ($orderCompleteURL . '?i=' . $bestellid->cId)
            : Shop::getURL();
        $smarty->assign('Bestellung', $bestellung);
    } else {
        $bestellung = fakeBestellung();
    }
    setzeSmartyWeiterleitung($bestellung);
}
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
// Trusted Shops Kaeuferschutz Classic
if ($Einstellungen['trustedshops']['trustedshops_nutzen'] === 'Y') {
    $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
    if ((int)$oTrustedShops->nAktiv === 1 && strlen($oTrustedShops->tsId) > 0) {
        $smarty->assign('oTrustedShops', $oTrustedShops);
    }
}

$smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
       ->assign('Bestellung', $bestellung)
       ->assign('Link', $link)
       ->assign('Kunde', $_SESSION['Kunde'] ?? null)
       ->assign('bOrderConf', true)
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);

// Plugin Zahlungsmethode beachten
$kPlugin = isset($bestellung->Zahlungsart->cModulId)
    ? Plugin::getIDByModuleID($bestellung->Zahlungsart->cModulId)
    : 0;
if ($kPlugin > 0) {
    $oPlugin = new Plugin($kPlugin);
    $smarty->assign('oPlugin', $oPlugin);
}
if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung) || isset($_GET['i'])) {
    if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
        $smarty->assign('oTrustedShopsBewertenButton',
            TrustedShops::getRatingButton($bestellung->oRechnungsadresse->cMail, $bestellung->cBestellNr)
        );
    }
    $session->cleanUp();
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    executeHook(HOOK_BESTELLABSCHLUSS_PAGE, ['oBestellung' => $bestellung]);
    $smarty->display('checkout/order_completed.tpl');
} else {
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    executeHook(HOOK_BESTELLABSCHLUSS_PAGE_ZAHLUNGSVORGANG, ['oBestellung' => $bestellung]);
    $smarty->display('checkout/step6_init_payment.tpl');
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
