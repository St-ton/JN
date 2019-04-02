<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Checkout\Bestellung;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Session\Frontend;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$kBestellung = (int)$_REQUEST['kBestellung'];
$linkHelper  = Shop::Container()->getLinkService();
$bestellung  = new Bestellung($kBestellung, true);
//abfragen, ob diese Bestellung dem Kunden auch gehoert
//bei Gastbestellungen ist ggf das Kundenobjekt bereits entfernt bzw nRegistriert = 0
if ($bestellung->oKunde !== null
    && (int)$bestellung->oKunde->nRegistriert === 1
    && (int)$bestellung->kKunde !== (int)$_SESSION['Kunde']->kKunde
) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
    exit;
}

$bestellid         = Shop::Container()->getDB()->select('tbestellid', 'kBestellung', $bestellung->kBestellung);
$successPaymentURL = Shop::getURL();
if ($bestellid->cId) {
    $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php');
    $successPaymentURL = $orderCompleteURL . '?i=' . $bestellid->cId;
}

$obj              = new stdClass();
$obj->tkunde      = $_SESSION['Kunde'];
$obj->tbestellung = $bestellung;
Shop::Smarty()->assign('Bestellung', $bestellung);

$oZahlungsInfo = new stdClass();
if (Request::verifyGPCDataInt('zusatzschritt') === 1) {
    $bZusatzangabenDa = false;
    $moduleID         = $bestellung->Zahlungsart->cModulId;
    switch ($moduleID) {
        case 'za_kreditkarte_jtl':
            if ($_POST['kreditkartennr']
                && $_POST['gueltigkeit']
                && $_POST['cvv']
                && $_POST['kartentyp']
                && $_POST['inhaber']
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr    =
                    Text::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit =
                    Text::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cCVV         =
                    Text::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp   =
                    Text::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber     =
                    Text::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $bZusatzangabenDa                                    = true;
            }
            break;
        case 'za_lastschrift_jtl':
            if (($_POST['bankname']
                    && $_POST['blz']
                    && $_POST['kontonr']
                    && $_POST['inhaber'])
                || ($_POST['bankname']
                    && $_POST['iban']
                    && $_POST['bic']
                    && $_POST['inhaber'])
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBankName =
                    Text::htmlentities(stripslashes($_POST['bankname']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                    Text::htmlentities(stripslashes($_POST['kontonr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                    Text::htmlentities(stripslashes($_POST['blz']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                    Text::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                    Text::htmlentities(stripslashes($_POST['bic']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                    Text::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $bZusatzangabenDa                                 = true;
            }
            break;
    }

    if ($bZusatzangabenDa) {
        if (saveZahlungsInfo($bestellung->kKunde, $bestellung->kBestellung)) {
            Shop::Container()->getDB()->update(
                'tbestellung',
                'kBestellung',
                (int)$bestellung->kBestellung,
                (object)['cAbgeholt' => 'N']
            );
            unset($_SESSION['Zahlungsart']);
            header('Location: ' . $successPaymentURL, true, 303);
            exit();
        }
    } else {
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
}
// Zahlungsart als Plugin
$kPlugin = Helper::getIDByModuleID($moduleID);
if ($kPlugin > 0) {
    $loader  = Helper::getLoaderByPluginID($kPlugin);
    $oPlugin = $loader->init($kPlugin);
    if ($oPlugin !== null) {
        require_once $oPlugin->getPaths()->getVersionedPath() . PFAD_PLUGIN_PAYMENTMETHOD .
            $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleID]->cClassPfad;
        /** @var PaymentMethod $paymentMethod */
        $pluginName              = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleID]->cClassName;
        $paymentMethod           = new $pluginName($moduleID);
        $paymentMethod->cModulId = $moduleID;
        $paymentMethod->preparePaymentProcess($bestellung);
        Shop::Smarty()->assign('oPlugin', $oPlugin);
    }
} elseif ($moduleID === 'za_lastschrift_jtl') {
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if ($oKundenKontodaten->kKunde > 0) {
        Shop::Smarty()->assign('oKundenKontodaten', $oKundenKontodaten);
    }
} elseif ($moduleID === 'za_sofortueberweisung_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
    $paymentMethod           = new SofortUeberweisung($moduleID);
    $paymentMethod->cModulId = $moduleID;
    $paymentMethod->preparePaymentProcess($bestellung);
}

Shop::Smarty()->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
    ->assign('Bestellung', $bestellung);

unset(
    $_SESSION['Zahlungsart'],
    $_SESSION['Versandart'],
    $_SESSION['Lieferadresse'],
    $_SESSION['VersandKupon'],
    $_SESSION['NeukundenKupon'],
    $_SESSION['Kupon']
);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('checkout/order_completed.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
