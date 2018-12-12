<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\RequestHelper;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_ZAHLUNGSARTEN
]);
$kBestellung   = (int)$_REQUEST['kBestellung'];
$linkHelper    = Shop::Container()->getLinkService();
$bestellung    = new Bestellung($kBestellung, true);
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
if (RequestHelper::verifyGPCDataInt('zusatzschritt') === 1) {
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
                    StringHandler::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit =
                    StringHandler::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cCVV         =
                    StringHandler::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp   =
                    StringHandler::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber     =
                    StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
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
                    StringHandler::htmlentities(stripslashes($_POST['bankname']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                    StringHandler::htmlentities(stripslashes($_POST['kontonr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                    StringHandler::htmlentities(stripslashes($_POST['blz']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                    StringHandler::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                    StringHandler::htmlentities(stripslashes($_POST['bic']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                    StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
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
$kPlugin = \Plugin\Helper::getIDByModuleID($moduleID);
if ($kPlugin > 0) {
    $loader  = \Plugin\Helper::getLoaderByPluginID($kPlugin);
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
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

Shop::Smarty()->assign('WarensummeLocalized', \Session\Session::getCart()->gibGesamtsummeWarenLocalized())
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
