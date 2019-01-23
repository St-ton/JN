<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Product;
use Helpers\Date;
use Helpers\Request;
use Helpers\Tax;

/**
 * @return int
 */
function bestellungKomplett(): int
{
    $oCheckBox               = new CheckBox();
    $_SESSION['cPlausi_arr'] = $oCheckBox->validateCheckBox(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        \Session\Frontend::getCustomerGroup()->getID(),
        $_POST,
        true
    );
    $_SESSION['cPost_arr']   = $_POST;

    return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
        && $_SESSION['Kunde']
        && $_SESSION['Lieferadresse']
        && (int)$_SESSION['Versandart']->kVersandart > 0
        && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
        && Request::verifyGPCDataInt('abschluss') === 1
        && count($_SESSION['cPlausi_arr']) === 0
    ) ? 1 : 0;
}

/**
 * @return int
 */
function gibFehlendeEingabe(): int
{
    if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
        return 1;
    }
    if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
        return 2;
    }
    if (!isset($_SESSION['Versandart'])
        || !$_SESSION['Versandart']
        || (int)$_SESSION['Versandart']->kVersandart === 0
    ) {
        return 3;
    }
    if (!isset($_SESSION['Zahlungsart'])
        || !$_SESSION['Zahlungsart']
        || (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
    ) {
        return 4;
    }
    if (count($_SESSION['cPlausi_arr']) > 0) {
        return 6;
    }

    return -1;
}

/**
 * @param int    $nBezahlt
 * @param string $orderNo
 */
function bestellungInDB($nBezahlt = 0, $orderNo = '')
{
    unhtmlSession();
    $order             = new Bestellung();
    $customer          = \Session\Frontend::getCustomer();
    $deliveryAddress   = \Session\Frontend::getDeliveryAddress();
    $order->cBestellNr = empty($orderNo) ? baueBestellnummer() : $orderNo;
    $cartPositions     = [];
    $db                = Shop::Container()->getDB();
    $cart              = \Session\Frontend::getCart();
    if (\Session\Frontend::getCustomer()->getID() <= 0) {
        $customerAttributes      = $customer->cKundenattribut_arr;
        $customer->kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        $customer->kSprache      = Shop::getLanguageID();
        $customer->cAbgeholt     = 'N';
        $customer->cAktiv        = 'Y';
        $customer->cSperre       = 'N';
        $customer->dErstellt     = 'NOW()';
        $customer->nRegistriert  = 0;
        $cPasswortKlartext       = '';
        if ($customer->cPasswort) {
            $customer->nRegistriert = 1;
            $cPasswortKlartext      = $customer->cPasswort;
            $customer->cPasswort    = md5($customer->cPasswort);
        }
        $cart->kKunde = $customer->insertInDB();

        $customer->kKunde = $cart->kKunde;
        $customer->cLand  = $customer->pruefeLandISO($customer->cLand);
        if (is_array($customerAttributes)) {
            foreach (array_keys($customerAttributes) as $kKundenfeld) {
                $oKundenattribut              = new stdClass();
                $oKundenattribut->kKunde      = $cart->kKunde;
                $oKundenattribut->kKundenfeld = $customerAttributes[$kKundenfeld]->kKundenfeld;
                $oKundenattribut->cName       = $customerAttributes[$kKundenfeld]->cWawi;
                $oKundenattribut->cWert       = $customerAttributes[$kKundenfeld]->cWert;

                $db->insert('tkundenattribut', $oKundenattribut);
            }
        }

        if (!empty($customer->cPasswort)) {
            $customer->cPasswortKlartext = $cPasswortKlartext;

            $obj         = new stdClass();
            $obj->tkunde = $customer;

            executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_NEUKUNDENREGISTRIERUNG);

            sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);
        }
    } else {
        $cart->kKunde = $customer->kKunde;
        $db->update(
            'tkunde',
            'kKunde',
            $customer->kKunde,
            (object)['cAbgeholt' => 'N']
        );
    }
    $cart->kLieferadresse = 0; //=rechnungsadresse
    if (isset($_SESSION['Bestellung']->kLieferadresse)
        && $_SESSION['Bestellung']->kLieferadresse == -1
        && !$deliveryAddress->kLieferadresse
    ) {
        $deliveryAddress->kKunde = $cart->kKunde;
        $cart->kLieferadresse    = $deliveryAddress->insertInDB();
    } elseif (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse > 0) {
        $cart->kLieferadresse = $_SESSION['Bestellung']->kLieferadresse;
    }
    $conf = Shop::getSettings([CONF_GLOBAL, CONF_TRUSTEDSHOPS]);
    //füge Warenkorb ein
    executeHook(HOOK_BESTELLABSCHLUSS_INC_WARENKORBINDB, ['oWarenkorb' => &$cart]);
    $cart->kWarenkorb = $cart->insertInDB();
    //füge alle Warenkorbpositionen ein
    if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
        $productFilter = (int)$conf['global']['artikel_artikelanzeigefilter'];
        /** @var WarenkorbPos $position */
        foreach ($cart->PositionenArr as $position) {
            if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $position->fLagerbestandVorAbschluss = $position->Artikel->fLagerbestand !== null
                    ? (double)$position->Artikel->fLagerbestand
                    : 0;
            }
            $position->cName         = StringHandler::unhtmlentities(is_array($position->cName)
                ? $position->cName[$_SESSION['cISOSprache']]
                : $position->cName);
            $position->cLieferstatus = isset($position->cLieferstatus[$_SESSION['cISOSprache']])
                ? StringHandler::unhtmlentities($position->cLieferstatus[$_SESSION['cISOSprache']])
                : '';
            $position->kWarenkorb    = $cart->kWarenkorb;
            $position->fMwSt         = Tax::getSalesTax($position->kSteuerklasse);
            $position->kWarenkorbPos = $position->insertInDB();
            if (is_array($position->WarenkorbPosEigenschaftArr) && count($position->WarenkorbPosEigenschaftArr) > 0) {
                $idx = Shop::getLanguageCode();
                // Bei einem Varkombikind dürfen nur FREIFELD oder PFLICHT-FREIFELD gespeichert werden,
                // da sonst eventuelle Aufpreise in der Wawi doppelt berechnet werden
                if (isset($position->Artikel->kVaterArtikel) && $position->Artikel->kVaterArtikel > 0) {
                    foreach ($position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->kWarenkorbPos        = $position->kWarenkorbPos;
                            $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$idx];
                            $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$idx];
                            $WKPosEigenschaft->cFreifeldWert        = $WKPosEigenschaft->cEigenschaftWertName;
                            $WKPosEigenschaft->insertInDB();
                        }
                    }
                } else {
                    foreach ($position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        $WKPosEigenschaft->kWarenkorbPos        = $position->kWarenkorbPos;
                        $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$idx];
                        $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$idx];
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->cFreifeldWert = $WKPosEigenschaft->cEigenschaftWertName;
                        }
                        $WKPosEigenschaft->insertInDB();
                    }
                }
            }
            //bestseller tabelle füllen
            if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && is_object($position->Artikel)) {
                //Lagerbestand verringern
                aktualisiereLagerbestand(
                    $position->Artikel,
                    $position->nAnzahl,
                    $position->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                aktualisiereBestseller($position->kArtikel, $position->nAnzahl);
                //xsellkauf füllen
                foreach ($cart->PositionenArr as $pos) {
                    if ($pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $pos->kArtikel != $position->kArtikel) {
                        aktualisiereXselling($position->kArtikel, $pos->kArtikel);
                    }
                }
                $cartPositions[] = $position;
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $position->kArtikel]);
            } elseif ($position->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                aktualisiereLagerbestand(
                    $position->Artikel,
                    $position->nAnzahl,
                    $position->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                $cartPositions[] = $position;
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $position->kArtikel]);
            }

            $order->Positionen[] = $position;
        }
        // Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
        // Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
        if (isset($_SESSION['Wunschliste']->kWunschliste) && $_SESSION['Wunschliste']->kWunschliste > 0) {
            Wunschliste::pruefeArtikelnachBestellungLoeschen(
                $_SESSION['Wunschliste']->kWunschliste,
                $cartPositions
            );
        }
    }
    $oRechnungsadresse = new Rechnungsadresse();

    $oRechnungsadresse->kKunde        = $customer->kKunde;
    $oRechnungsadresse->cAnrede       = $customer->cAnrede;
    $oRechnungsadresse->cTitel        = $customer->cTitel;
    $oRechnungsadresse->cVorname      = $customer->cVorname;
    $oRechnungsadresse->cNachname     = $customer->cNachname;
    $oRechnungsadresse->cFirma        = $customer->cFirma;
    $oRechnungsadresse->cStrasse      = $customer->cStrasse;
    $oRechnungsadresse->cHausnummer   = $customer->cHausnummer;
    $oRechnungsadresse->cAdressZusatz = $customer->cAdressZusatz;
    $oRechnungsadresse->cPLZ          = $customer->cPLZ;
    $oRechnungsadresse->cOrt          = $customer->cOrt;
    $oRechnungsadresse->cBundesland   = $customer->cBundesland;
    $oRechnungsadresse->cLand         = $customer->cLand;
    $oRechnungsadresse->cTel          = $customer->cTel;
    $oRechnungsadresse->cMobil        = $customer->cMobil;
    $oRechnungsadresse->cFax          = $customer->cFax;
    $oRechnungsadresse->cUSTID        = $customer->cUSTID;
    $oRechnungsadresse->cWWW          = $customer->cWWW;
    $oRechnungsadresse->cMail         = $customer->cMail;

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE);

    $kRechnungsadresse = $oRechnungsadresse->insertInDB();

    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags($_POST['kommentar']), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }

    $order->kKunde            = $cart->kKunde;
    $order->kWarenkorb        = $cart->kWarenkorb;
    $order->kLieferadresse    = $cart->kLieferadresse;
    $order->kRechnungsadresse = $kRechnungsadresse;
    $order->kZahlungsart      = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart       = $_SESSION['Versandart']->kVersandart;
    $order->kSprache          = Shop::getLanguage();
    $order->kWaehrung         = \Session\Frontend::getCurrency()->getID();
    $order->fGesamtsumme      = \Session\Frontend::getCart()->gibGesamtsummeWaren(true);
    $order->cVersandartName   = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName  = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession          = session_id();
    $order->cKommentar        = $_SESSION['kommentar'];
    $order->cAbgeholt         = 'N';
    $order->cStatus           = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt         = 'NOW()';
    $order->berechneEstimatedDelivery();
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
        $db->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben - :cred
                WHERE kKunde = :cid',
            [
                'cred' => (float)$_SESSION['Bestellung']->fGuthabenGenutzt,
                'cid'  => (int)$order->kKunde
            ],
            \DB\ReturnType::DEFAULT
        );
        $customer->fGuthaben -= $_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    // Gesamtsumme entspricht 0
    if ($order->fGesamtsumme == 0) {
        $order->cStatus          = BESTELLUNG_STATUS_BEZAHLT;
        $order->dBezahltDatum    = 'NOW()';
        $order->cZahlungsartName = Shop::Lang()->get('paymentNotNecessary', 'checkout');
    }
    // no anonymization is done here anymore, cause we got a contract
    $order->cIP = $_SESSION['IP']->cIP ?? Request::getRealIP();
    //#8544
    $order->fWaehrungsFaktor = \Session\Frontend::getCurrency()->getConversionFactor();

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, ['oBestellung' => &$order]);

    $kBestellung = $order->insertInDB();

    $logger = Shop::Container()->getLogService();
    if ($logger->isHandling(JTLLOG_LEVEL_NOTICE)) {
        $logger->withName('kBestellung')->notice('Bestellung gespeichert: ' . print_r($order, true), [$kBestellung]);
    }
    // TrustedShops buchen
    if (isset($_SESSION['TrustedShops']->cKaeuferschutzProdukt)
        && $_SESSION['Zahlungsart']->nWaehrendBestellung == 0
        && $conf['trustedshops']['trustedshops_nutzen'] === 'Y'
        && strlen($_SESSION['TrustedShops']->cKaeuferschutzProdukt) > 0
    ) {
        $ts                    = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
        $ts->tsProductId       = $_SESSION['TrustedShops']->cKaeuferschutzProdukt;
        $ts->amount            = \Session\Frontend::getCurrency()->getConversionFactor() *
            \Session\Frontend::getCart()->gibGesamtsummeWaren(true);
        $ts->currency          = \Session\Frontend::getCurrency()->getCode();
        $ts->paymentType       = $_SESSION['Zahlungsart']->cTSCode;
        $ts->buyerEmail        = $customer->cMail;
        $ts->shopCustomerID    = $customer->kKunde;
        $ts->shopOrderID       = $order->cBestellNr;
        $ts->orderDate         = date('Y-m-d') . 'T' . date('H:i:s');
        $ts->shopSystemVersion = 'JTL-Shop ' . APPLICATION_VERSION;

        if (strlen($ts->tsProductId) > 0
            && strlen($ts->amount) > 0
            && strlen($ts->currency) > 0
            && strlen($ts->paymentType) > 0
            && strlen($ts->buyerEmail) > 0
            && strlen($ts->shopCustomerID) > 0
            && strlen($ts->shopOrderID) > 0
        ) {
            $ts->sendeBuchung();
        }
    }
    //BestellID füllen
    $bestellid              = new stdClass();
    $bestellid->cId         = uniqid('', true);
    $bestellid->kBestellung = $order->kBestellung;
    $bestellid->dDatum      = 'NOW()';
    $db->insert('tbestellid', $bestellid);
    //bestellstatus füllen
    $bestellstatus              = new stdClass();
    $bestellstatus->kBestellung = $order->kBestellung;
    $bestellstatus->dDatum      = 'NOW()';
    $bestellstatus->cUID        = uniqid('', true);
    $db->insert('tbestellstatus', $bestellstatus);
    //füge ZahlungsInfo ein, falls es die Versandart erfordert
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo) && $_SESSION['Zahlungsart']->ZahlungsInfo) {
        saveZahlungsInfo($order->kKunde, $order->kBestellung);
    }

    $_SESSION['BestellNr']   = $order->cBestellNr;
    $_SESSION['kBestellung'] = $order->kBestellung;
    //evtl. Kupon  Verwendungen hochzählen
    KuponVerwendungen($order);
    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        Kampagne::setCampaignAction(KAMPAGNE_DEF_VERKAUF, $order->kBestellung, 1.0);
        Kampagne::setCampaignAction(KAMPAGNE_DEF_VERKAUFSSUMME, $order->kBestellung, $order->fGesamtsumme);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, [
        'oBestellung'   => &$order,
        'bestellID'     => &$bestellid,
        'bestellstatus' => &$bestellstatus,
    ]);
}

/**
 * @param int  $kKunde
 * @param int  $kBestellung
 * @param bool $bZahlungAgain
 * @return bool
 */
function saveZahlungsInfo(int $kKunde, int $kBestellung, bool $bZahlungAgain = false): bool
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */

    if (!$kKunde || !$kBestellung) {
        return false;
    }
    $_SESSION['ZahlungsInfo']               = new ZahlungsInfo();
    $_SESSION['ZahlungsInfo']->kBestellung  = $kBestellung;
    $_SESSION['ZahlungsInfo']->kKunde       = $kKunde;
    $_SESSION['ZahlungsInfo']->cKartenTyp   = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        : null;
    $_SESSION['ZahlungsInfo']->cGueltigkeit = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        : null;
    $_SESSION['ZahlungsInfo']->cBankName    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        : null;
    $_SESSION['ZahlungsInfo']->cKartenNr    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        : null;
    $_SESSION['ZahlungsInfo']->cCVV         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        : null;
    $_SESSION['ZahlungsInfo']->cKontoNr     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        : null;
    $_SESSION['ZahlungsInfo']->cBLZ         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        : null;
    $_SESSION['ZahlungsInfo']->cIBAN        = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        : null;
    $_SESSION['ZahlungsInfo']->cBIC         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        : null;
    $_SESSION['ZahlungsInfo']->cInhaber     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        : null;

    if (!$bZahlungAgain) {
        $cart                = \Session\Frontend::getCart();
        $cart->kZahlungsInfo = $_SESSION['ZahlungsInfo']->insertInDB();
        $cart->updateInDB();
    } else {
        $_SESSION['ZahlungsInfo']->insertInDB();
    }
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        || isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
    ) {
        Shop::Container()->getDB()->delete('tkundenkontodaten', 'kKunde', $kKunde);
        speicherKundenKontodaten($_SESSION['Zahlungsart']->ZahlungsInfo);
    }

    return true;
}

/**
 * @param object $oZahlungsinfo
 */
function speicherKundenKontodaten($oZahlungsinfo): void
{
    $cryptoService   = Shop::Container()->getCryptoService();
    $data            = new stdClass();
    $data->kKunde    = \Session\Frontend::getCart()->kKunde;
    $data->cBLZ      = $cryptoService->encryptXTEA($oZahlungsinfo->cBLZ ?? '');
    $data->nKonto    = $cryptoService->encryptXTEA($oZahlungsinfo->cKontoNr ?? '');
    $data->cInhaber  = $cryptoService->encryptXTEA($oZahlungsinfo->cInhaber ?? '');
    $data->cBankName = $cryptoService->encryptXTEA($oZahlungsinfo->cBankName ?? '');
    $data->cIBAN     = $cryptoService->encryptXTEA($oZahlungsinfo->cIBAN ?? '');
    $data->cBIC      = $cryptoService->encryptXTEA($oZahlungsinfo->cBIC ?? '');

    Shop::Container()->getDB()->insert('tkundenkontodaten', $data);
}

/**
 *
 */
function unhtmlSession(): void
{
    $customer        = new Kunde();
    $sessionCustomer = \Session\Frontend::getCustomer();
    if ($sessionCustomer->kKunde > 0) {
        $customer->kKunde = $sessionCustomer->kKunde;
    }
    $customer->kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
    if ($sessionCustomer->kKundengruppe > 0) {
        $customer->kKundengruppe = $sessionCustomer->kKundengruppe;
    }
    $customer->kSprache = Shop::getLanguage();
    if ($sessionCustomer->kSprache > 0) {
        $customer->kSprache = $sessionCustomer->kSprache;
    }
    if ($sessionCustomer->cKundenNr) {
        $customer->cKundenNr = $sessionCustomer->cKundenNr;
    }
    if ($sessionCustomer->cPasswort) {
        $customer->cPasswort = $sessionCustomer->cPasswort;
    }
    if ($sessionCustomer->fGuthaben) {
        $customer->fGuthaben = $sessionCustomer->fGuthaben;
    }
    if ($sessionCustomer->fRabatt) {
        $customer->fRabatt = $sessionCustomer->fRabatt;
    }
    if ($sessionCustomer->dErstellt) {
        $customer->dErstellt = $sessionCustomer->dErstellt;
    }
    if ($sessionCustomer->cAktiv) {
        $customer->cAktiv = $sessionCustomer->cAktiv;
    }
    if ($sessionCustomer->cAbgeholt) {
        $customer->cAbgeholt = $sessionCustomer->cAbgeholt;
    }
    if (isset($sessionCustomer->nRegistriert)) {
        $customer->nRegistriert = $sessionCustomer->nRegistriert;
    }
    $customer->cAnrede       = StringHandler::unhtmlentities($sessionCustomer->cAnrede);
    $customer->cVorname      = StringHandler::unhtmlentities($sessionCustomer->cVorname);
    $customer->cNachname     = StringHandler::unhtmlentities($sessionCustomer->cNachname);
    $customer->cStrasse      = StringHandler::unhtmlentities($sessionCustomer->cStrasse);
    $customer->cHausnummer   = StringHandler::unhtmlentities($sessionCustomer->cHausnummer);
    $customer->cPLZ          = StringHandler::unhtmlentities($sessionCustomer->cPLZ);
    $customer->cOrt          = StringHandler::unhtmlentities($sessionCustomer->cOrt);
    $customer->cLand         = StringHandler::unhtmlentities($sessionCustomer->cLand);
    $customer->cMail         = StringHandler::unhtmlentities($sessionCustomer->cMail);
    $customer->cTel          = StringHandler::unhtmlentities($sessionCustomer->cTel);
    $customer->cFax          = StringHandler::unhtmlentities($sessionCustomer->cFax);
    $customer->cFirma        = StringHandler::unhtmlentities($sessionCustomer->cFirma);
    $customer->cZusatz       = StringHandler::unhtmlentities($sessionCustomer->cZusatz);
    $customer->cTitel        = StringHandler::unhtmlentities($sessionCustomer->cTitel);
    $customer->cAdressZusatz = StringHandler::unhtmlentities($sessionCustomer->cAdressZusatz);
    $customer->cMobil        = StringHandler::unhtmlentities($sessionCustomer->cMobil);
    $customer->cWWW          = StringHandler::unhtmlentities($sessionCustomer->cWWW);
    $customer->cUSTID        = StringHandler::unhtmlentities($sessionCustomer->cUSTID);
    $customer->dGeburtstag   = StringHandler::unhtmlentities($sessionCustomer->dGeburtstag);
    $customer->cBundesland   = StringHandler::unhtmlentities($sessionCustomer->cBundesland);

    $customer->cKundenattribut_arr = $sessionCustomer->cKundenattribut_arr;

    $_SESSION['Kunde'] = $customer;

    $shippingAddress = new Lieferadresse();
    $deliveryAddress = \Session\Frontend::getDeliveryAddress();
    if (($cid = $deliveryAddress->kKunde) > 0) {
        $shippingAddress->kKunde = $cid;
    }
    if (($did = $deliveryAddress->kLieferadresse) > 0) {
        $shippingAddress->kLieferadresse = $did;
    }
    $shippingAddress->cVorname      = StringHandler::unhtmlentities($deliveryAddress->cVorname);
    $shippingAddress->cNachname     = StringHandler::unhtmlentities($deliveryAddress->cNachname);
    $shippingAddress->cFirma        = StringHandler::unhtmlentities($deliveryAddress->cFirma);
    $shippingAddress->cZusatz       = StringHandler::unhtmlentities($deliveryAddress->cZusatz);
    $shippingAddress->cStrasse      = StringHandler::unhtmlentities($deliveryAddress->cStrasse);
    $shippingAddress->cHausnummer   = StringHandler::unhtmlentities($deliveryAddress->cHausnummer);
    $shippingAddress->cPLZ          = StringHandler::unhtmlentities($deliveryAddress->cPLZ);
    $shippingAddress->cOrt          = StringHandler::unhtmlentities($deliveryAddress->cOrt);
    $shippingAddress->cLand         = StringHandler::unhtmlentities($deliveryAddress->cLand);
    $shippingAddress->cAnrede       = StringHandler::unhtmlentities($deliveryAddress->cAnrede);
    $shippingAddress->cMail         = StringHandler::unhtmlentities($deliveryAddress->cMail);
    $shippingAddress->cBundesland   = StringHandler::unhtmlentities($deliveryAddress->cBundesland);
    $shippingAddress->cTel          = StringHandler::unhtmlentities($deliveryAddress->cTel);
    $shippingAddress->cFax          = StringHandler::unhtmlentities($deliveryAddress->cFax);
    $shippingAddress->cTitel        = StringHandler::unhtmlentities($deliveryAddress->cTitel);
    $shippingAddress->cAdressZusatz = StringHandler::unhtmlentities($deliveryAddress->cAdressZusatz);
    $shippingAddress->cMobil        = StringHandler::unhtmlentities($deliveryAddress->cMobil);

    $shippingAddress->angezeigtesLand = Sprache::getCountryCodeByCountryName($shippingAddress->cLand);

    $deliveryAddress = $shippingAddress;
}

/**
 * @param int       $kArtikel
 * @param int|float $amount
 */
function aktualisiereBestseller(int $kArtikel, $amount): void
{
    if (!$kArtikel || !$amount) {
        return;
    }
    $best_obj = Shop::Container()->getDB()->select('tbestseller', 'kArtikel', $kArtikel);
    if (isset($best_obj->kArtikel) && $best_obj->kArtikel > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tbestseller SET fAnzahl = fAnzahl + :mnt WHERE kArtikel = :aid',
            ['mnt' => $amount, 'aid' => $kArtikel],
            \DB\ReturnType::DEFAULT
        );
    } else {
        $Bestseller           = new stdClass();
        $Bestseller->kArtikel = $kArtikel;
        $Bestseller->fAnzahl  = $amount;
        Shop::Container()->getDB()->insert('tbestseller', $Bestseller);
    }
    if (Product::isVariCombiChild($kArtikel)) {
        aktualisiereBestseller(Product::getParent($kArtikel), $amount);
    }
}

/**
 * @param int $kArtikel
 * @param int $kZielArtikel
 */
function aktualisiereXselling(int $kArtikel, int $kZielArtikel): void
{
    if (!$kArtikel || !$kZielArtikel) {
        return;
    }
    $obj = Shop::Container()->getDB()->select('txsellkauf', 'kArtikel', $kArtikel, 'kXSellArtikel', $kZielArtikel);
    if (isset($obj->nAnzahl) && $obj->nAnzahl > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE txsellkauf
              SET nAnzahl = nAnzahl + 1
              WHERE kArtikel = :pid
                AND kXSellArtikel = :xs',
            [
                'pid' => $kArtikel,
                'xs'  => $kZielArtikel
            ],
            \DB\ReturnType::DEFAULT
        );
    } else {
        $xs                = new stdClass();
        $xs->kArtikel      = $kArtikel;
        $xs->kXSellArtikel = $kZielArtikel;
        $xs->nAnzahl       = 1;
        Shop::Container()->getDB()->insert('txsellkauf', $xs);
    }
}

/**
 * @param Artikel   $product
 * @param int|float $amount
 * @param array     $attributeValues
 * @param int       $productFilter
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereLagerbestand(Artikel $product, $amount, $attributeValues, int $productFilter = 1)
{
    $artikelBestand = (float)$product->fLagerbestand;
    $db             = Shop::Container()->getDB();
    if ($amount <= 0 || $product->cLagerBeachten !== 'Y') {
        return $artikelBestand;
    }
    if ($product->cLagerVariation === 'Y'
        && is_array($attributeValues)
        && count($attributeValues) > 0
    ) {
        foreach ($attributeValues as $eWert) {
            $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
            if ($EigenschaftWert->fPackeinheit == 0) {
                $EigenschaftWert->fPackeinheit = 1;
            }
            $db->queryPrepared(
                'UPDATE teigenschaftwert
                    SET fLagerbestand = fLagerbestand - :inv
                    WHERE kEigenschaftWert = :aid',
                [
                    'aid' => (int)$eWert->kEigenschaftWert,
                    'inv' => $amount * $EigenschaftWert->fPackeinheit
                ],
                \DB\ReturnType::DEFAULT
            );
        }
    } elseif ($product->fPackeinheit > 0) {
        if ($product->kStueckliste > 0) {
            $artikelBestand = aktualisiereStuecklistenLagerbestand($product, $amount);
        } else {
            $db->query(
                'UPDATE tartikel
                    SET fLagerbestand = IF (fLagerbestand >= ' . ($amount * $product->fPackeinheit) . ',
                    (fLagerbestand - ' . ($amount * $product->fPackeinheit) . '), fLagerbestand)
                    WHERE kArtikel = ' . (int)$product->kArtikel,
                \DB\ReturnType::DEFAULT
            );
            $tmpArtikel = $db->select(
                'tartikel',
                'kArtikel',
                (int)$product->kArtikel,
                null,
                null,
                null,
                null,
                false,
                'fLagerbestand'
            );
            if ($tmpArtikel !== null) {
                $artikelBestand = (float)$tmpArtikel->fLagerbestand;
            }
            // Stücklisten Komponente
            if (Product::isStuecklisteKomponente($product->kArtikel)) {
                aktualisiereKomponenteLagerbestand(
                    $product->kArtikel,
                    $artikelBestand,
                    $product->cLagerKleinerNull === 'Y'
                );
            }
        }
        // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
        if ($product->kVaterArtikel > 0) {
            Artikel::beachteVarikombiMerkmalLagerbestand($product->kVaterArtikel, $productFilter);
        }
    }

    return $artikelBestand;
}

/**
 * @param Artikel   $partListProduct
 * @param int|float $amount
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereStuecklistenLagerbestand($partListProduct, $amount)
{
    $amount              = (float)$amount;
    $kStueckListe        = (int)$partListProduct->kStueckliste;
    $bestandAlt          = (float)$partListProduct->fLagerbestand;
    $bestandNeu          = $bestandAlt;
    $bestandUeberverkauf = $bestandAlt;

    if ($amount <= 0) {
        return $bestandNeu;
    }
    // Gibt es lagerrelevante Komponenten in der Stückliste?
    $oKomponente_arr = Shop::Container()->getDB()->queryPrepared(
        "SELECT tstueckliste.kArtikel, tstueckliste.fAnzahl
            FROM tstueckliste
            JOIN tartikel
              ON tartikel.kArtikel = tstueckliste.kArtikel
            WHERE tstueckliste.kStueckliste = :slid
                AND tartikel.cLagerBeachten = 'Y'",
        ['slid' => $kStueckListe],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    if (is_array($oKomponente_arr) && count($oKomponente_arr) > 0) {
        // wenn ja, dann wird für diese auch der Bestand aktualisiert
        $options = Artikel::getDefaultOptions();

        $options->nKeineSichtbarkeitBeachten = 1;
        foreach ($oKomponente_arr as $oKomponente) {
            $tmpArtikel = new Artikel();
            $tmpArtikel->fuelleArtikel($oKomponente->kArtikel, $options);

            $komponenteBestand = floor(
                aktualisiereLagerbestand(
                    $tmpArtikel,
                    $amount * $oKomponente->fAnzahl,
                    null
                ) / $oKomponente->fAnzahl
            );

            if ($komponenteBestand < $bestandNeu && $tmpArtikel->cLagerKleinerNull !== 'Y') {
                // Neuer Bestand ist der Kleinste Komponententbestand aller Artikel ohne Überverkauf
                $bestandNeu = $komponenteBestand;
            } elseif ($komponenteBestand < $bestandUeberverkauf) {
                // Für Komponenten mit Überverkauf wird der kleinste Bestand ermittelt.
                $bestandUeberverkauf = $komponenteBestand;
            }
        }
    }

    // Ist der alte gleich dem neuen Bestand?
    if ($bestandAlt === $bestandNeu) {
        // Es sind keine lagerrelevanten Komponenten vorhanden, die den Bestand der Stückliste herabsetzen.
        if ($bestandUeberverkauf === $bestandNeu) {
            // Es gibt auch keine Komponenten mit Überverkäufen, die den Bestand verringern, deshalb wird
            // der Bestand des Stücklistenartikels anhand des Verkaufs verringert
            $bestandNeu -= $amount * $partListProduct->fPackeinheit;
        } else {
            // Da keine lagerrelevanten Komponenten vorhanden sind, wird der kleinste Bestand der
            // Komponentent mit Überverkauf verwendet.
            $bestandNeu = $bestandUeberverkauf;
        }

        Shop::Container()->getDB()->update(
            'tartikel',
            'kArtikel',
            (int)$partListProduct->kArtikel,
            (object)['fLagerbestand' => $bestandNeu]
        );
    }
    // Kein Lagerbestands-Update für die Stückliste notwendig! Dies erfolgte bereits über die Komponentenabfrage und
    // die dortige Lagerbestandsaktualisierung!

    return $bestandNeu;
}

/**
 * @param int       $kKomponenteArtikel
 * @param int|float $fLagerbestand
 * @param bool      $bLagerKleinerNull
 */
function aktualisiereKomponenteLagerbestand(int $kKomponenteArtikel, $fLagerbestand, $bLagerKleinerNull): void
{
    $db            = Shop::Container()->getDB();
    $fLagerbestand = (float)$fLagerbestand;
    $partLists     = $db->queryPrepared(
        "SELECT tstueckliste.kStueckliste, tstueckliste.fAnzahl,
                tartikel.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull
            FROM tstueckliste
            JOIN tartikel
                ON tartikel.kStueckliste = tstueckliste.kStueckliste
            WHERE tstueckliste.kArtikel = :cid
                AND tartikel.cLagerBeachten = 'Y'",
        ['cid' => $kKomponenteArtikel],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($partLists as $partList) {
        // Ist der aktuelle Bestand der Stückliste größer als dies mit dem Bestand der Komponente möglich wäre?
        $max = floor($fLagerbestand / $partList->fAnzahl);
        if ($max < (float)$partList->fLagerbestand && (!$bLagerKleinerNull || $partList->cLagerKleinerNull === 'Y')) {
            // wenn ja, dann den Bestand der Stückliste entsprechend verringern, aber nur wenn die Komponente nicht
            // überberkaufbar ist oder die gesamte Stückliste Überverkäufe zulässt
            $db->update(
                'tartikel',
                'kArtikel',
                (int)$partList->kArtikel,
                (object)['fLagerbestand' => $max]
            );
        }
    }
}

/**
 * @param int       $productID
 * @param int|float $amount
 * @param null|int  $kStueckliste
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereAndereStuecklisten(int $productID, $amount, $kStueckliste = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($productID > 0) {
        $tmpArtikel = new Artikel();
        $tmpArtikel->fuelleArtikel($productID, Artikel::getDefaultOptions());
        aktualisiereKomponenteLagerbestand($productID, $tmpArtikel->fLagerbestand, $tmpArtikel->cLagerKleinerNull);
    }
}

/**
 * @param int       $kStueckliste
 * @param float     $fPackeinheitSt
 * @param float     $fLagerbestandSt
 * @param int|float $amount
 * @deprecated since 4.06 - dont use anymore
 */
function AktualisiereStueckliste(int $kStueckliste, $fPackeinheitSt, $fLagerbestandSt, $amount): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $fLagerbestand = (float)$fLagerbestandSt;
    Shop::Container()->getDB()->update(
        'tartikel',
        'kStueckliste',
        $kStueckliste,
        (object)['fLagerbestand' => $fLagerbestand]
    );
}

/**
 * @param Artikel        $product
 * @param null|int|float $amount
 * @param bool           $bStueckliste
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereLagerStuecklisten($product, $amount = null, $bStueckliste = false): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (isset($product->kArtikel) && $product->kArtikel > 0) {
        if ($bStueckliste) {
            aktualisiereStuecklistenLagerbestand($product, $amount);
        } else {
            aktualisiereKomponenteLagerbestand(
                $product->kArtikel,
                $product->fLagerbestand,
                $product->cLagerKleinerNull
            );
        }
    }
}

/**
 * @param $oBestellung
 */
function KuponVerwendungen($oBestellung): void
{
    $db               = Shop::Container()->getDB();
    $cart             = \Session\Frontend::getCart();
    $kKupon           = 0;
    $cKuponTyp        = '';
    $fKuponwertBrutto = 0;
    if (isset($_SESSION['VersandKupon']->kKupon) && $_SESSION['VersandKupon']->kKupon > 0) {
        $kKupon           = (int)$_SESSION['VersandKupon']->kKupon;
        $cKuponTyp        = Kupon::TYPE_SHIPPING;
        $fKuponwertBrutto = $_SESSION['Versandart']->fPreis;
    }
    if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
        $kKupon    = (int)$_SESSION['NeukundenKupon']->kKupon;
        $cKuponTyp = Kupon::TYPE_NEWCUSTOMER;
    }
    if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0) {
        $kKupon    = (int)$_SESSION['Kupon']->kKupon;
        $cKuponTyp = Kupon::TYPE_STANDARD;
    }
    foreach ($cart->PositionenArr as $Position) {
        $Position->nPosTyp = (int)$Position->nPosTyp;
        if (!isset($_SESSION['VersandKupon'])
            && ($Position->nPosTyp === C_WARENKORBPOS_TYP_KUPON
                || $Position->nPosTyp === C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
        ) {
            $fKuponwertBrutto = Tax::getGross(
                $Position->fPreisEinzelNetto,
                Tax::getSalesTax($Position->kSteuerklasse)
            ) * (-1);
        }
    }
    if ($kKupon > 0) {
        $db->queryPrepared(
            'UPDATE tkupon
              SET nVerwendungenBisher = nVerwendungenBisher + 1
              WHERE kKupon = :couponID',
            ['couponID' => $kKupon],
            \DB\ReturnType::DEFAULT
        );

        $db->queryPrepared(
            'INSERT INTO `tkuponkunde` (kKupon, cMail, dErstellt, nVerwendungen)
                VALUES (:couponID, :email, NOW(), :used)
                ON DUPLICATE KEY UPDATE
                  nVerwendungen = nVerwendungen + 1',
            [
                'couponID'   => $kKupon,
                'email' => Kupon::hash(\Session\Frontend::getCustomer()->cMail),
                'used' => 1
            ],
            \DB\ReturnType::DEFAULT
        );

        $db->insert('tkuponflag', (object)[
            'cKuponTyp'  => $cKuponTyp,
            'cEmailHash' => Kupon::hash(\Session\Frontend::getCustomer()->cMail),
            'dErstellt'  => 'NOW()'
        ]);

        $oKuponBestellung                     = new KuponBestellung();
        $oKuponBestellung->kKupon             = $kKupon;
        $oKuponBestellung->kBestellung        = $oBestellung->kBestellung;
        $oKuponBestellung->kKunde             = $cart->kKunde;
        $oKuponBestellung->cBestellNr         = $oBestellung->cBestellNr;
        $oKuponBestellung->fGesamtsummeBrutto = $oBestellung->fGesamtsumme;
        $oKuponBestellung->fKuponwertBrutto   = $fKuponwertBrutto;
        $oKuponBestellung->cKuponTyp          = $cKuponTyp;
        $oKuponBestellung->dErstellt          = 'NOW()';

        $oKuponBestellung->save();
    }
}

/**
 * @return string
 */
function baueBestellnummer(): string
{
    $conf           = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    $oNummer        = new Nummern(JTL_GENNUMBER_ORDERNUMBER);
    $nBestellnummer = 1;
    $nIncrement     = isset($conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer'])
        ? (int)$conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer']
        : 1;
    if ($oNummer) {
        $nBestellnummer = $oNummer->getNummer() + $nIncrement;
        $oNummer->setNummer($oNummer->getNummer() + 1);
        $oNummer->update();
    }

    /*
    *   %Y = -aktuelles Jahr
    *   %m = -aktueller Monat
    *   %d = -aktueller Tag
    *   %W = -aktuelle KW
    */
    $cPraefix = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_praefix']
    );
    $cSuffix  = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_suffix']
    );

    return $cPraefix . $nBestellnummer . $cSuffix;
}

/**
 * @param Bestellung $oBestellung
 */
function speicherUploads($oBestellung): void
{
    if (!empty($oBestellung->kBestellung) && class_exists('Upload')) {
        Upload::speicherUploadDateien(\Session\Frontend::getCart(), $oBestellung->kBestellung);
    }
}

/**
 * @param Bestellung $bestellung
 */
function setzeSmartyWeiterleitung(Bestellung $bestellung): void
{
    speicherUploads($bestellung);
    $logger = Shop::Container()->getLogService();
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->withName('cModulId')->debug(
            'setzeSmartyWeiterleitung wurde mit folgender Zahlungsart ausgefuehrt: ' .
            print_r($_SESSION['Zahlungsart'], true),
            [$_SESSION['Zahlungsart']->cModulId]
        );
    }
    $kPlugin = \Plugin\Helper::getIDByModuleID($_SESSION['Zahlungsart']->cModulId);
    if ($kPlugin > 0) {
        $loader             = \Plugin\Helper::getLoaderByPluginID($kPlugin);
        $oPlugin            = $loader->init($kPlugin);
        $GLOBALS['oPlugin'] = $oPlugin;
        if ($oPlugin !== null) {
            require_once $oPlugin->getPaths()->getVersionedPath() . PFAD_PLUGIN_PAYMENTMETHOD .
                $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassPfad;
            $pluginClass = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassName;
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod           = new $pluginClass($_SESSION['Zahlungsart']->cModulId);
            $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
            $paymentMethod->preparePaymentProcess($bestellung);
            Shop::Smarty()->assign('oPlugin', $oPlugin);
        }
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_sofortueberweisung_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
        $paymentMethod           = new SofortUeberweisung($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif (strpos($_SESSION['Zahlungsart']->cModulId, 'za_billpay') === 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $paymentMethod           = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_kreditkarte_jtl'
        || $_SESSION['Zahlungsart']->cModulId === 'za_lastschrift_jtl'
    ) {
        Shop::Smarty()->assign('abschlussseite', 1);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_SMARTYWEITERLEITUNG);
}

/**
 * @return Bestellung
 */
function fakeBestellung()
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */

    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags(Shop::Container()->getDB()->escape($_POST['kommentar'])), 0, 1000);
    }
    $cart                    = \Session\Frontend::getCart();
    $customer                = \Session\Frontend::getCustomer();
    $order                   = new Bestellung();
    $order->kKunde           = $cart->kKunde;
    $order->kWarenkorb       = $cart->kWarenkorb;
    $order->kLieferadresse   = $cart->kLieferadresse;
    $order->kZahlungsart     = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart      = $_SESSION['Versandart']->kVersandart;
    $order->kSprache         = Shop::getLanguageID();
    $order->kWaehrung        = \Session\Frontend::getCurrency()->getID();
    $order->fGesamtsumme     = \Session\Frontend::getCart()->gibGesamtsummeWaren(true);
    $order->fWarensumme      = $order->fGesamtsumme;
    $order->cVersandartName  = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession         = session_id();
    $order->cKommentar       = $_SESSION['kommentar'];
    $order->cAbgeholt        = 'N';
    $order->cStatus          = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt        = 'NOW()';
    $order->Zahlungsart      = $_SESSION['Zahlungsart'];
    $order->Positionen       = [];
    $order->Waehrung         = $_SESSION['Waehrung']; // @todo - check if this matches the new Currency class
    $order->kWaehrung        = \Session\Frontend::getCurrency()->getID();
    $order->fWaehrungsFaktor = \Session\Frontend::getCurrency()->getConversionFactor();
    if ($order->oRechnungsadresse === null) {
        $order->oRechnungsadresse = new stdClass();
    }
    $order->oRechnungsadresse->cVorname    = $customer->cVorname;
    $order->oRechnungsadresse->cNachname   = $customer->cNachname;
    $order->oRechnungsadresse->cFirma      = $customer->cFirma;
    $order->oRechnungsadresse->kKunde      = $customer->kKunde;
    $order->oRechnungsadresse->cAnrede     = $customer->cAnrede;
    $order->oRechnungsadresse->cTitel      = $customer->cTitel;
    $order->oRechnungsadresse->cStrasse    = $customer->cStrasse;
    $order->oRechnungsadresse->cHausnummer = $customer->cHausnummer;
    $order->oRechnungsadresse->cPLZ        = $customer->cPLZ;
    $order->oRechnungsadresse->cOrt        = $customer->cOrt;
    $order->oRechnungsadresse->cLand       = $customer->cLand;
    $order->oRechnungsadresse->cTel        = $customer->cTel;
    $order->oRechnungsadresse->cMobil      = $customer->cMobil;
    $order->oRechnungsadresse->cFax        = $customer->cFax;
    $order->oRechnungsadresse->cUSTID      = $customer->cUSTID;
    $order->oRechnungsadresse->cWWW        = $customer->cWWW;
    $order->oRechnungsadresse->cMail       = $customer->cMail;

    if (strlen(\Session\Frontend::getDeliveryAddress()->cVorname) > 0) {
        $order->Lieferadresse = gibLieferadresseAusSession();
    }
    $order->cBestellNr = date('dmYHis') . substr($order->cSession, 0, 4);
    if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
        $order->Positionen = [];
        foreach ($cart->PositionenArr as $i => $position) {
            $order->Positionen[$i] = new WarenkorbPos();
            foreach (array_keys(get_object_vars($position)) as $member) {
                $order->Positionen[$i]->$member = $position->$member;
            }

            $order->Positionen[$i]->cName = $order->Positionen[$i]->cName[$_SESSION['cISOSprache']];
            $order->Positionen[$i]->fMwSt = Tax::getSalesTax($position->kSteuerklasse);
            $order->Positionen[$i]->setzeGesamtpreisLocalized();
        }
    }
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    if ($conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y') {
        // non-anonymized IP (! we got a contract)
        $order->cIP = Request::getRealIP();
    }

    return $order->fuelleBestellung(false, true);
}

/**
 * @return null|stdClass
 */
function gibLieferadresseAusSession()
{
    $deliveryAddress = \Session\Frontend::getDeliveryAddress();
    if (empty($deliveryAddress->cVorname)) {
        return null;
    }
    $shippingAddress              = new stdClass();
    $shippingAddress->cVorname    = $deliveryAddress->cVorname;
    $shippingAddress->cNachname   = $deliveryAddress->cNachname;
    $shippingAddress->cFirma      = $deliveryAddress->cFirma ?? null;
    $shippingAddress->kKunde      = $deliveryAddress->kKunde;
    $shippingAddress->cAnrede     = $deliveryAddress->cAnrede;
    $shippingAddress->cTitel      = $deliveryAddress->cTitel;
    $shippingAddress->cStrasse    = $deliveryAddress->cStrasse;
    $shippingAddress->cHausnummer = $deliveryAddress->cHausnummer;
    $shippingAddress->cPLZ        = $deliveryAddress->cPLZ;
    $shippingAddress->cOrt        = $deliveryAddress->cOrt;
    $shippingAddress->cLand       = $deliveryAddress->cLand;
    $shippingAddress->cTel        = $deliveryAddress->cTel;
    $shippingAddress->cMobil      = $deliveryAddress->cMobil ?? null;
    $shippingAddress->cFax        = $deliveryAddress->cFax ?? null;
    $shippingAddress->cUSTID      = $deliveryAddress->cUSTID ?? null;
    $shippingAddress->cWWW        = $deliveryAddress->cWWW ?? null;
    $shippingAddress->cMail       = $deliveryAddress->cMail;
    $shippingAddress->cAnrede     = $deliveryAddress->cAnrede;

    return $shippingAddress;
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis.
 *
 * @return array
 */
function pruefeVerfuegbarkeit(): array
{
    $res  = ['cArtikelName_arr' => []];
    $conf = Shop::getSettings([CONF_GLOBAL]);
    foreach (\Session\Frontend::getCart()->PositionenArr as $oPosition) {
        if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && isset($oPosition->Artikel->cLagerBeachten)
            && $oPosition->Artikel->cLagerBeachten === 'Y'
            && $oPosition->Artikel->cLagerKleinerNull === 'Y'
            && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
            && $oPosition->nAnzahl > $oPosition->Artikel->fLagerbestand
        ) {
            $res['cArtikelName_arr'][] = $oPosition->Artikel->cName;
        }
    }

    if (count($res['cArtikelName_arr']) > 0) {
        $cHinweis        = str_replace('%s', '', Shop::Lang()->get('orderExpandInventory', 'basket'));
        $res['cHinweis'] = $cHinweis;
    }

    return $res;
}

/**
 * @param string $orderNo
 * @param bool   $sendMail
 * @return Bestellung
 */
function finalisiereBestellung($orderNo = '', bool $sendMail = true): Bestellung
{
    $obj                      = new stdClass();
    $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

    bestellungInDB(0, $orderNo);

    $order = new Bestellung($_SESSION['kBestellung']);
    $order->fuelleBestellung(false);

    $upd              = new stdClass();
    $upd->kKunde      = \Session\Frontend::getCart()->kKunde;
    $upd->kBestellung = (int)$order->kBestellung;
    Shop::Container()->getDB()->update('tbesucher', 'kKunde', $upd->kKunde, $upd);
    $obj->tkunde      = \Session\Frontend::getCustomer();
    $obj->tbestellung = $order;

    if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
        $obj->tbestellung->cEstimatedDeliveryEx = Date::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMin
        )->format('d.m.Y') . ' - ' .
        Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y');
    }
    $oKunde = new Kunde();
    $oKunde->kopiereSession();
    if ($sendMail === true) {
        sendeMail(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj);
    }
    $_SESSION['Kunde'] = $oKunde;
    $kKundengruppe     = \Session\Frontend::getCustomerGroup()->getID();
    $oCheckBox         = new CheckBox();
    $oCheckBox->triggerSpecialFunction(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        $kKundengruppe,
        true,
        $_POST,
        ['oBestellung' => $order, 'oKunde' => $oKunde]
    );
    $oCheckBox->checkLogging(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);

    return $order;
}
