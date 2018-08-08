<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return int
 */
function bestellungKomplett()
{
    $oCheckBox               = new CheckBox();
    $_SESSION['cPlausi_arr'] = $oCheckBox->validateCheckBox(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        Session::CustomerGroup()->getID(),
        $_POST,
        true
    );
    $_SESSION['cPost_arr']   = $_POST;

    return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
        && $_SESSION['Kunde']
        && $_SESSION['Lieferadresse']
        && $_SESSION['Versandart']
        && $_SESSION['Zahlungsart']
        && (int)$_SESSION['Versandart']->kVersandart > 0
        && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
        && RequestHelper::verifyGPCDataInt('abschluss') === 1
        && count($_SESSION['cPlausi_arr']) === 0
    ) ? 1 : 0;
}

/**
 * @return int
 */
function gibFehlendeEingabe()
{
    if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
        return 1;
    }
    if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
        return 2;
    }
    if (!isset($_SESSION['Versandart']) ||
        !$_SESSION['Versandart'] ||
        (int)$_SESSION['Versandart']->kVersandart === 0
    ) {
        return 3;
    }
    if (!isset($_SESSION['Zahlungsart']) ||
        !$_SESSION['Zahlungsart'] ||
        (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
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
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    /** @var array('Kunde' => Kunde) $_SESSION */

    //für saubere DB Einträge
    unhtmlSession();
    //erstelle neue Bestellung
    $order = new Bestellung();
    //setze InetBestellNummer
    $order->cBestellNr   = empty($orderNo) ? baueBestellnummer() : $orderNo;
    $oWarenkorbpositionen_arr = [];
    //füge Kunden ein, falls er nicht schon existiert ( loginkunde)
    if (!$_SESSION['Kunde']->kKunde) {
        // Kundenattribute sichern
        $cKundenattribut_arr = $_SESSION['Kunde']->cKundenattribut_arr;

        $_SESSION['Kunde']->kKundengruppe = Session::CustomerGroup()->getID();
        $_SESSION['Kunde']->kSprache      = Shop::getLanguage();
        $_SESSION['Kunde']->cAbgeholt     = 'N';
        $_SESSION['Kunde']->cAktiv        = 'Y';
        $_SESSION['Kunde']->cSperre       = 'N';
        $_SESSION['Kunde']->dErstellt     = 'now()';
        $cPasswortKlartext                = '';
        $_SESSION['Kunde']->nRegistriert  = 0;
        if ($_SESSION['Kunde']->cPasswort) {
            $_SESSION['Kunde']->nRegistriert = 1;
            $cPasswortKlartext               = $_SESSION['Kunde']->cPasswort;
            $_SESSION['Kunde']->cPasswort    = md5($_SESSION['Kunde']->cPasswort);
        }
        $_SESSION['Warenkorb']->kKunde = $_SESSION['Kunde']->insertInDB();
        $_SESSION['Kunde']->kKunde     = $_SESSION['Warenkorb']->kKunde;
        //Land: Deutschland -> DE
        $_SESSION['Kunde']->cLand = $_SESSION['Kunde']->pruefeLandISO($_SESSION['Kunde']->cLand);
        // Kundenattribute in DB setzen
        if (is_array($cKundenattribut_arr)) {
            $nKundenattributKey_arr = array_keys($cKundenattribut_arr);

            if (is_array($nKundenattributKey_arr) && count($nKundenattributKey_arr) > 0) {
                foreach ($nKundenattributKey_arr as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = $_SESSION['Warenkorb']->kKunde;
                    $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    Shop::Container()->getDB()->insert('tkundenattribut', $oKundenattribut);
                }
            }
        }

        if (isset($_SESSION['Kunde']->cPasswort) && $_SESSION['Kunde']->cPasswort) {
            $_SESSION['Kunde']->cPasswortKlartext = $cPasswortKlartext;

            $obj         = new stdClass();
            $obj->tkunde = $_SESSION['Kunde'];

            executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_NEUKUNDENREGISTRIERUNG);

            sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);
        }
    } else {
        $_SESSION['Warenkorb']->kKunde = $_SESSION['Kunde']->kKunde;
        Shop::Container()->getDB()->update(
            'tkunde',
            'kKunde',
            (int)$_SESSION['Kunde']->kKunde,
            (object)['cAbgeholt' => 'N']
        );
    }
    //Lieferadresse
    $_SESSION['Warenkorb']->kLieferadresse = 0; //=rechnungsadresse
    if (isset($_SESSION['Bestellung']->kLieferadresse)
        && $_SESSION['Bestellung']->kLieferadresse == -1
        && !$_SESSION['Lieferadresse']->kLieferadresse
    ) {
        //neue Lieferadresse
        $_SESSION['Lieferadresse']->kKunde     = $_SESSION['Warenkorb']->kKunde;
        $_SESSION['Warenkorb']->kLieferadresse = $_SESSION['Lieferadresse']->insertInDB();
    } elseif (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse > 0) {
        $_SESSION['Warenkorb']->kLieferadresse = $_SESSION['Bestellung']->kLieferadresse;
    }
    $conf = Shop::getSettings([CONF_GLOBAL, CONF_TRUSTEDSHOPS]);
    //füge Warenkorb ein
    executeHook(HOOK_BESTELLABSCHLUSS_INC_WARENKORBINDB, ['oWarenkorb' => &$_SESSION['Warenkorb']]);
    $_SESSION['Warenkorb']->kWarenkorb = $_SESSION['Warenkorb']->insertInDB();
    //füge alle Warenkorbpositionen ein
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        $productFilter = (int)$conf['global']['artikel_artikelanzeigefilter'];
        /** @var WarenkorbPos $Position */
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $Position) {
            if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $Position->fLagerbestandVorAbschluss = $Position->Artikel->fLagerbestand !== null
                    ? (double)$Position->Artikel->fLagerbestand
                    : 0;
            }
            $Position->cName         = StringHandler::unhtmlentities(is_array($Position->cName)
                ? $Position->cName[$_SESSION['cISOSprache']]
                : $Position->cName);
            $Position->cLieferstatus = isset($Position->cLieferstatus[$_SESSION['cISOSprache']])
                ? StringHandler::unhtmlentities($Position->cLieferstatus[$_SESSION['cISOSprache']])
                : '';
            $Position->kWarenkorb    = $_SESSION['Warenkorb']->kWarenkorb;
            $Position->fMwSt         = TaxHelper::getSalesTax($Position->kSteuerklasse);
            $Position->kWarenkorbPos = $Position->insertInDB();
            if (is_array($Position->WarenkorbPosEigenschaftArr) && count($Position->WarenkorbPosEigenschaftArr) > 0) {
                // Bei einem Varkombikind dürfen nur FREIFELD oder PFLICHT-FREIFELD gespeichert werden,
                // da sonst eventuelle Aufpreise in der Wawi doppelt berechnet werden
                if (isset($Position->Artikel->kVaterArtikel) && $Position->Artikel->kVaterArtikel > 0) {
                    foreach ($Position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->kWarenkorbPos        = $Position->kWarenkorbPos;
                            $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$_SESSION['cISOSprache']];
                            $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                            $WKPosEigenschaft->cFreifeldWert        = $WKPosEigenschaft->cEigenschaftWertName;
                            $WKPosEigenschaft->insertInDB();
                        }
                    }
                } else {
                    foreach ($Position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        $WKPosEigenschaft->kWarenkorbPos        = $Position->kWarenkorbPos;
                        $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$_SESSION['cISOSprache']];
                        $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->cFreifeldWert = $WKPosEigenschaft->cEigenschaftWertName;
                        }
                        $WKPosEigenschaft->insertInDB();
                    }
                }
            }
            //bestseller tabelle füllen
            if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                //Lagerbestand verringern
                aktualisiereLagerbestand(
                    $Position->Artikel,
                    $Position->nAnzahl,
                    $Position->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                aktualisiereBestseller($Position->kArtikel, $Position->nAnzahl);
                //xsellkauf füllen
                foreach ($_SESSION['Warenkorb']->PositionenArr as $pos) {
                    if ($pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $pos->kArtikel != $Position->kArtikel) {
                        aktualisiereXselling($Position->kArtikel, $pos->kArtikel);
                    }
                }
                $oWarenkorbpositionen_arr[] = $Position;
                // Clear Cache
                Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $Position->kArtikel]);
            } elseif ($Position->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                aktualisiereLagerbestand(
                    $Position->Artikel,
                    $Position->nAnzahl,
                    $Position->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                $oWarenkorbpositionen_arr[] = $Position;
                // Clear Cache
                Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $Position->kArtikel]);
            }

            $order->Positionen[] = $Position;
        }
        // Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
        // Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
        if (isset($_SESSION['Wunschliste']->kWunschliste) && $_SESSION['Wunschliste']->kWunschliste > 0) {
            Wunschliste::pruefeArtikelnachBestellungLoeschen($_SESSION['Wunschliste']->kWunschliste, $oWarenkorbpositionen_arr);
        }
    }
    $oRechnungsadresse = new Rechnungsadresse();

    $oRechnungsadresse->kKunde        = $_SESSION['Kunde']->kKunde;
    $oRechnungsadresse->cAnrede       = $_SESSION['Kunde']->cAnrede;
    $oRechnungsadresse->cTitel        = $_SESSION['Kunde']->cTitel;
    $oRechnungsadresse->cVorname      = $_SESSION['Kunde']->cVorname;
    $oRechnungsadresse->cNachname     = $_SESSION['Kunde']->cNachname;
    $oRechnungsadresse->cFirma        = $_SESSION['Kunde']->cFirma;
    $oRechnungsadresse->cStrasse      = $_SESSION['Kunde']->cStrasse;
    $oRechnungsadresse->cHausnummer   = $_SESSION['Kunde']->cHausnummer;
    $oRechnungsadresse->cAdressZusatz = $_SESSION['Kunde']->cAdressZusatz;
    $oRechnungsadresse->cPLZ          = $_SESSION['Kunde']->cPLZ;
    $oRechnungsadresse->cOrt          = $_SESSION['Kunde']->cOrt;
    $oRechnungsadresse->cBundesland   = $_SESSION['Kunde']->cBundesland;
    $oRechnungsadresse->cLand         = $_SESSION['Kunde']->cLand;
    $oRechnungsadresse->cTel          = $_SESSION['Kunde']->cTel;
    $oRechnungsadresse->cMobil        = $_SESSION['Kunde']->cMobil;
    $oRechnungsadresse->cFax          = $_SESSION['Kunde']->cFax;
    $oRechnungsadresse->cUSTID        = $_SESSION['Kunde']->cUSTID;
    $oRechnungsadresse->cWWW          = $_SESSION['Kunde']->cWWW;
    $oRechnungsadresse->cMail         = $_SESSION['Kunde']->cMail;

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE);

    $kRechnungsadresse = $oRechnungsadresse->insertInDB();

    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags($_POST['kommentar']), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }

    $order->kKunde            = $_SESSION['Warenkorb']->kKunde;
    $order->kWarenkorb        = $_SESSION['Warenkorb']->kWarenkorb;
    $order->kLieferadresse    = $_SESSION['Warenkorb']->kLieferadresse;
    $order->kRechnungsadresse = $kRechnungsadresse;
    $order->kZahlungsart      = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart       = $_SESSION['Versandart']->kVersandart;
    $order->kSprache          = Shop::getLanguage();
    $order->kWaehrung         = Session::Currency()->getID();
    $order->fGesamtsumme      = Session::Cart()->gibGesamtsummeWaren(true);
    $order->cVersandartName   = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName  = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession          = session_id();
    $order->cKommentar        = $_SESSION['kommentar'];
    $order->cAbgeholt         = 'N';
    $order->cStatus           = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt         = 'now()';
    $order->berechneEstimatedDelivery();
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben - :cred
                WHERE kKunde = :cid',
            [
                'cred' => (float)$_SESSION['Bestellung']->fGuthabenGenutzt, 
                'cid'  => (int)$order->kKunde
            ],
            \DB\ReturnType::DEFAULT
        );
        $_SESSION['Kunde']->fGuthaben -= $_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    // Gesamtsumme entspricht 0
    if ($order->fGesamtsumme == 0) {
        $order->cStatus          = BESTELLUNG_STATUS_BEZAHLT;
        $order->dBezahltDatum    = 'now()';
        $order->cZahlungsartName = Shop::Lang()->get('paymentNotNecessary', 'checkout');
    }
    $order->cIP = $_SESSION['IP']->cIP ?? RequestHelper::getIP(true);
    //#8544
    $order->fWaehrungsFaktor = Session::Currency()->getConversionFactor();

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
        $ts->amount            = Session::Currency()->getConversionFactor() * Session::Cart()->gibGesamtsummeWaren(true);
        $ts->currency          = Session::Currency()->getCode();
        $ts->paymentType       = $_SESSION['Zahlungsart']->cTSCode;
        $ts->buyerEmail        = $_SESSION['Kunde']->cMail;
        $ts->shopCustomerID    = $_SESSION['Kunde']->kKunde;
        $ts->shopOrderID       = $order->cBestellNr;
        $ts->orderDate         = date('Y-m-d') . 'T' . date('H:i:s');
        $ts->shopSystemVersion = 'JTL-Shop ' . JTL_VERSION;

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
    $bestellid->dDatum      = 'now()';
    Shop::Container()->getDB()->insert('tbestellid', $bestellid);
    //bestellstatus füllen
    $bestellstatus              = new stdClass();
    $bestellstatus->kBestellung = $order->kBestellung;
    $bestellstatus->dDatum      = 'now()';
    $bestellstatus->cUID        = uniqid('', true);
    Shop::Container()->getDB()->insert('tbestellstatus', $bestellstatus);
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
 *
 * @return bool
 */
function saveZahlungsInfo(int $kKunde, int $kBestellung, bool $bZahlungAgain = false)
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
        $_SESSION['Warenkorb']->kZahlungsInfo = $_SESSION['ZahlungsInfo']->insertInDB();
        $_SESSION['Warenkorb']->updateInDB();
    } else {
        $_SESSION['ZahlungsInfo']->insertInDB();
    }
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        || isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
    ) {
        Shop::Container()->getDB()->delete('tkundenkontodaten', 'kKunde', (int)$kKunde);
        speicherKundenKontodaten($_SESSION['Zahlungsart']->ZahlungsInfo);
    }

    return true;
}

/**
 * @param object $oZahlungsinfo
 */
function speicherKundenKontodaten($oZahlungsinfo)
{
    $cryptoService                = Shop::Container()->getCryptoService();
    $data            = new stdClass();
    $data->kKunde    = $_SESSION['Warenkorb']->kKunde;
    $data->cBLZ      = $cryptoService->encryptXTEA($oZahlungsinfo->cBLZ);
    $data->nKonto    = $cryptoService->encryptXTEA($oZahlungsinfo->cKontoNr);
    $data->cInhaber  = $cryptoService->encryptXTEA($oZahlungsinfo->cInhaber);
    $data->cBankName = $cryptoService->encryptXTEA($oZahlungsinfo->cBankName);
    $data->cIBAN     = $cryptoService->encryptXTEA($oZahlungsinfo->cIBAN);
    $data->cBIC      = $cryptoService->encryptXTEA($oZahlungsinfo->cBIC);

    Shop::Container()->getDB()->insert('tkundenkontodaten', $data);
}

/**
 *
 */
function unhtmlSession()
{
    $customer = new Kunde();
    if ($_SESSION['Kunde']->kKunde > 0) {
        $customer->kKunde = $_SESSION['Kunde']->kKunde;
    }
    $customer->kKundengruppe = Session::CustomerGroup()->getID();
    if ($_SESSION['Kunde']->kKundengruppe > 0) {
        $customer->kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
    }
    $customer->kSprache = Shop::getLanguage();
    if ($_SESSION['Kunde']->kSprache > 0) {
        $customer->kSprache = $_SESSION['Kunde']->kSprache;
    }
    if ($_SESSION['Kunde']->cKundenNr) {
        $customer->cKundenNr = $_SESSION['Kunde']->cKundenNr;
    }
    if ($_SESSION['Kunde']->cPasswort) {
        $customer->cPasswort = $_SESSION['Kunde']->cPasswort;
    }
    if ($_SESSION['Kunde']->fGuthaben) {
        $customer->fGuthaben = $_SESSION['Kunde']->fGuthaben;
    }
    if ($_SESSION['Kunde']->fRabatt) {
        $customer->fRabatt = $_SESSION['Kunde']->fRabatt;
    }
    if ($_SESSION['Kunde']->dErstellt) {
        $customer->dErstellt = $_SESSION['Kunde']->dErstellt;
    }
    if ($_SESSION['Kunde']->cAktiv) {
        $customer->cAktiv = $_SESSION['Kunde']->cAktiv;
    }
    if ($_SESSION['Kunde']->cAbgeholt) {
        $customer->cAbgeholt = $_SESSION['Kunde']->cAbgeholt;
    }
    if (isset($_SESSION['Kunde']->nRegistriert)) {
        $customer->nRegistriert = $_SESSION['Kunde']->nRegistriert;
    }
    $customer->cAnrede       = StringHandler::unhtmlentities($_SESSION['Kunde']->cAnrede);
    $customer->cVorname      = StringHandler::unhtmlentities($_SESSION['Kunde']->cVorname);
    $customer->cNachname     = StringHandler::unhtmlentities($_SESSION['Kunde']->cNachname);
    $customer->cStrasse      = StringHandler::unhtmlentities($_SESSION['Kunde']->cStrasse);
    $customer->cHausnummer   = StringHandler::unhtmlentities($_SESSION['Kunde']->cHausnummer);
    $customer->cPLZ          = StringHandler::unhtmlentities($_SESSION['Kunde']->cPLZ);
    $customer->cOrt          = StringHandler::unhtmlentities($_SESSION['Kunde']->cOrt);
    $customer->cLand         = StringHandler::unhtmlentities($_SESSION['Kunde']->cLand);
    $customer->cMail         = StringHandler::unhtmlentities($_SESSION['Kunde']->cMail);
    $customer->cTel          = StringHandler::unhtmlentities($_SESSION['Kunde']->cTel);
    $customer->cFax          = StringHandler::unhtmlentities($_SESSION['Kunde']->cFax);
    $customer->cFirma        = StringHandler::unhtmlentities($_SESSION['Kunde']->cFirma);
    $customer->cZusatz       = StringHandler::unhtmlentities($_SESSION['Kunde']->cZusatz);
    $customer->cTitel        = StringHandler::unhtmlentities($_SESSION['Kunde']->cTitel);
    $customer->cAdressZusatz = StringHandler::unhtmlentities($_SESSION['Kunde']->cAdressZusatz);
    $customer->cMobil        = StringHandler::unhtmlentities($_SESSION['Kunde']->cMobil);
    $customer->cWWW          = StringHandler::unhtmlentities($_SESSION['Kunde']->cWWW);
    $customer->cUSTID        = StringHandler::unhtmlentities($_SESSION['Kunde']->cUSTID);
    $customer->dGeburtstag   = StringHandler::unhtmlentities($_SESSION['Kunde']->dGeburtstag);
    $customer->cBundesland   = StringHandler::unhtmlentities($_SESSION['Kunde']->cBundesland);

    $customer->cKundenattribut_arr = $_SESSION['Kunde']->cKundenattribut_arr;

    $_SESSION['Kunde'] = $customer;

    $shippingAddress = new Lieferadresse();
    if ($_SESSION['Lieferadresse']->kKunde > 0) {
        $shippingAddress->kKunde = $_SESSION['Lieferadresse']->kKunde;
    }
    if ($_SESSION['Lieferadresse']->kLieferadresse > 0) {
        $shippingAddress->kLieferadresse = $_SESSION['Lieferadresse']->kLieferadresse;
    }
    $shippingAddress->cVorname      = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cVorname);
    $shippingAddress->cNachname     = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cNachname);
    $shippingAddress->cFirma        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cFirma);
    $shippingAddress->cZusatz       = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cZusatz);
    $shippingAddress->cStrasse      = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cStrasse);
    $shippingAddress->cHausnummer   = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cHausnummer);
    $shippingAddress->cPLZ          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cPLZ);
    $shippingAddress->cOrt          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cOrt);
    $shippingAddress->cLand         = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cLand);
    $shippingAddress->cAnrede       = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cAnrede);
    $shippingAddress->cMail         = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cMail);
    $shippingAddress->cBundesland   = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cBundesland);
    $shippingAddress->cTel          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cTel);
    $shippingAddress->cFax          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cFax);
    $shippingAddress->cTitel        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cTitel);
    $shippingAddress->cAdressZusatz = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cAdressZusatz);
    $shippingAddress->cMobil        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cMobil);

    $shippingAddress->angezeigtesLand = Sprache::getCountryCodeByCountryName($shippingAddress->cLand);

    $_SESSION['Lieferadresse'] = $shippingAddress;
}

/**
 * @param int       $kArtikel
 * @param int|float $amount
 */
function aktualisiereBestseller(int $kArtikel, $amount)
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
    if (ArtikelHelper::isVariCombiChild($kArtikel)) {
        aktualisiereBestseller(ArtikelHelper::getParent($kArtikel), $amount);
    }
}

/**
 * @param int $kArtikel
 * @param int $kZielArtikel
 */
function aktualisiereXselling(int $kArtikel, int $kZielArtikel)
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
            Shop::Container()->getDB()->queryPrepared(
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
            Shop::Container()->getDB()->query(
                'UPDATE tartikel
                    SET fLagerbestand = IF (fLagerbestand >= ' . ($amount * $product->fPackeinheit) . ',
                    (fLagerbestand - ' . ($amount * $product->fPackeinheit) . '), fLagerbestand)
                    WHERE kArtikel = ' . (int)$product->kArtikel,
                \DB\ReturnType::DEFAULT
            );
            $tmpArtikel = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', (int)$product->kArtikel,
                null, null,
                null, null,
                false,
                'fLagerbestand'
            );
            if ($tmpArtikel !== null) {
                $artikelBestand = (float)$tmpArtikel->fLagerbestand;
            }
            // Stücklisten Komponente
            if (ArtikelHelper::isStuecklisteKomponente($product->kArtikel)) {
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
 * @param Artikel $partListProduct
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
    $oKomponente_arr = Shop::Container()->getDB()->query(
        "SELECT tstueckliste.kArtikel, tstueckliste.fAnzahl
            FROM tstueckliste
            JOIN tartikel
              ON tartikel.kArtikel = tstueckliste.kArtikel
            WHERE tstueckliste.kStueckliste = {$kStueckListe}
                AND tartikel.cLagerBeachten = 'Y'",
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
            (int)$partListProduct->kArtikel, (object)['fLagerbestand' => $bestandNeu,]
        );
    }
    // Kein Lagerbestands-Update für die Stückliste notwendig! Dies erfolgte bereits über die Komponentenabfrage und
    // die dortige Lagerbestandsaktualisierung!

    return $bestandNeu;
}

/**
 * @param int $kKomponenteArtikel
 * @param int|float $fLagerbestand
 * @param bool $bLagerKleinerNull
 * @return void
 */
function aktualisiereKomponenteLagerbestand(int $kKomponenteArtikel, $fLagerbestand, $bLagerKleinerNull)
{
    $fLagerbestand      = (float)$fLagerbestand;
    $partLists          = Shop::Container()->getDB()->query(
        "SELECT tstueckliste.kStueckliste, tstueckliste.fAnzahl,
                tartikel.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull
            FROM tstueckliste
            JOIN tartikel
                ON tartikel.kStueckliste = tstueckliste.kStueckliste
            WHERE tstueckliste.kArtikel = {$kKomponenteArtikel}
                AND tartikel.cLagerBeachten = 'Y'",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($partLists as $partList) {
        // Ist der aktuelle Bestand der Stückliste größer als dies mit dem Bestand der Komponente möglich wäre?
        $max = floor($fLagerbestand / $partList->fAnzahl);
        if ($max < (float)$partList->fLagerbestand && (!$bLagerKleinerNull || $partList->cLagerKleinerNull === 'Y')) {
            // wenn ja, dann den Bestand der Stückliste entsprechend verringern, aber nur wenn die Komponente nicht
            // überberkaufbar ist oder die gesamte Stückliste Überverkäufe zulässt
            Shop::Container()->getDB()->update(
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
function AktualisiereAndereStuecklisten(int $productID, $amount, $kStueckliste = null)
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
function AktualisiereStueckliste(int $kStueckliste, $fPackeinheitSt, $fLagerbestandSt, $amount)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $fLagerbestand = (float)$fLagerbestandSt;
    Shop::Container()->getDB()->update('tartikel', 'kStueckliste', $kStueckliste, (object)['fLagerbestand' => $fLagerbestand]);
}

/**
 * @param Artikel        $product
 * @param null|int|float $amount
 * @param bool           $bStueckliste
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereLagerStuecklisten($product, $amount = null, $bStueckliste = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (isset($product->kArtikel) && $product->kArtikel > 0) {
        if ($bStueckliste) {
            aktualisiereStuecklistenLagerbestand($product, $amount);
        } else {
            aktualisiereKomponenteLagerbestand($product->kArtikel, $product->fLagerbestand, $product->cLagerKleinerNull);
        }
    }
}

/**
 * @param $oBestellung
 */
function KuponVerwendungen($oBestellung)
{
    $kKupon           = 0;
    $cKuponTyp        = '';
    $fKuponwertBrutto = 0;
    if (isset($_SESSION['VersandKupon']->kKupon) && $_SESSION['VersandKupon']->kKupon > 0) {
        $kKupon           = $_SESSION['VersandKupon']->kKupon;
        $cKuponTyp        = 'versand';
        $fKuponwertBrutto = $_SESSION['Versandart']->fPreis;
    }
    if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
        $kKupon    = $_SESSION['NeukundenKupon']->kKupon;
        $cKuponTyp = 'neukunden';
    }
    if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0) {
        $kKupon = $_SESSION['Kupon']->kKupon;
        if (isset($_SESSION['Kupon']->cWertTyp)
            && ($_SESSION['Kupon']->cWertTyp === 'prozent' || $_SESSION['Kupon']->cWertTyp === 'festpreis')
        ) {
            $cKuponTyp = $_SESSION['Kupon']->cWertTyp;
        }
    }
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $Position) {
            $Position->nPosTyp = (int)$Position->nPosTyp;
            if (!isset($_SESSION['VersandKupon'])
                && ($Position->nPosTyp === C_WARENKORBPOS_TYP_KUPON
                    || $Position->nPosTyp === C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            ) {
                $fKuponwertBrutto = TaxHelper::getGross(
                    $Position->fPreisEinzelNetto,
                    TaxHelper::getSalesTax($Position->kSteuerklasse)
                ) * (-1);
            }
        }
    }
    $kKupon = (int)$kKupon;
    if ($kKupon > 0) {
        Shop::Container()->getDB()->query(
            'UPDATE tkupon SET nVerwendungenBisher = nVerwendungenBisher + 1 WHERE kKupon = ' . $kKupon,
            \DB\ReturnType::DEFAULT
        );
        $KuponKunde                = new stdClass();
        $KuponKunde->kKupon        = $kKupon;
        $KuponKunde->kKunde        = $_SESSION['Warenkorb']->kKunde;
        $KuponKunde->cMail         = StringHandler::filterXSS($_SESSION['Kunde']->cMail);
        $KuponKunde->dErstellt     = 'now()';
        $KuponKunde->nVerwendungen = 1;
        $KuponKundeBisher          = Shop::Container()->getDB()->query(
            "SELECT SUM(nVerwendungen) AS nVerwendungen
                FROM tkuponkunde
                WHERE cMail = '{$KuponKunde->cMail}'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($KuponKundeBisher->nVerwendungen) && $KuponKundeBisher->nVerwendungen > 0) {
            $KuponKunde->nVerwendungen += $KuponKundeBisher->nVerwendungen;
        }
        Shop::Container()->getDB()->delete('tkuponkunde', ['kKunde', 'kKupon'], [(int)$KuponKunde->kKunde, $kKupon]);
        Shop::Container()->getDB()->insert('tkuponkunde', $KuponKunde);

        if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
            Shop::Container()->getDB()->delete('tkuponneukunde', ['kKupon', 'cEmail'], [$kKupon, $_SESSION['Kunde']->cMail]);
        }

        $oKuponBestellung                     = new KuponBestellung();
        $oKuponBestellung->kKupon             = $kKupon;
        $oKuponBestellung->kBestellung        = $oBestellung->kBestellung;
        $oKuponBestellung->kKunde             = $_SESSION['Warenkorb']->kKunde;
        $oKuponBestellung->cBestellNr         = $oBestellung->cBestellNr;
        $oKuponBestellung->fGesamtsummeBrutto = $oBestellung->fGesamtsumme;
        $oKuponBestellung->fKuponwertBrutto   = $fKuponwertBrutto;
        $oKuponBestellung->cKuponTyp          = $cKuponTyp;
        $oKuponBestellung->dErstellt          = 'now()';
        $oKuponBestellung->save();
    }
}

/**
 * @return string
 */
function baueBestellnummer()
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
function speicherUploads($oBestellung)
{
    if (!empty($oBestellung->kBestellung) && class_exists('Upload')) {
        // Uploads speichern
        Upload::speicherUploadDateien($_SESSION['Warenkorb'], $oBestellung->kBestellung);
    }
}

/**
 * @param Bestellung $bestellung
 */
function setzeSmartyWeiterleitung(Bestellung $bestellung)
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
    // Zahlungsart als Plugin
    $kPlugin = Plugin::getIDByModuleID($_SESSION['Zahlungsart']->cModulId);
    if ($kPlugin > 0) {
        $oPlugin            = new Plugin($kPlugin);
        $GLOBALS['oPlugin'] = $oPlugin;
        if ($oPlugin->kPlugin > 0) {
            require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION .
                $oPlugin->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD .
                $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassPfad;
            $pluginClass = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassName;
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod           = new $pluginClass($_SESSION['Zahlungsart']->cModulId);
            $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
            $paymentMethod->preparePaymentProcess($bestellung);
            Shop::Smarty()->assign('oPlugin', $oPlugin);
        }
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_paypal_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paypal/PayPal.class.php';
        $paymentMethod           = new PayPal($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_worldpay_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'worldpay/WorldPay.class.php';
        $paymentMethod           = new WorldPay($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ipayment_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ipayment/iPayment.class.php';
        $paymentMethod           = new iPayment($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_sofortueberweisung_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
        $paymentMethod           = new SofortUeberweisung($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_stand_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_dd_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_cc_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_prepaid_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_gi_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_ebank_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_safetypay') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/confirmation.php';
        Shop::Smarty()->assign('safetypay_form', show_confirmation($bestellung));
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_wirecard_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'wirecard/Wirecard.class.php';
        $paymentMethod           = new Wirecard($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_postfinance_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'postfinance/PostFinance.class.php';
        $paymentMethod           = new PostFinance($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_paymentpartner_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paymentpartner/PaymentPartner.class.php';
        $paymentMethod           = new PaymentPartner($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif (strpos($_SESSION['Zahlungsart']->cModulId, 'za_billpay') === 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $paymentMethod           = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_kreditkarte_jtl' ||
        $_SESSION['Zahlungsart']->cModulId === 'za_lastschrift_jtl'
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
    $order                   = new Bestellung();
    $order->kKunde           = $_SESSION['Warenkorb']->kKunde;
    $order->kWarenkorb       = $_SESSION['Warenkorb']->kWarenkorb;
    $order->kLieferadresse   = $_SESSION['Warenkorb']->kLieferadresse;
    $order->kZahlungsart     = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart      = $_SESSION['Versandart']->kVersandart;
    $order->kSprache         = Shop::getLanguage();
    $order->kWaehrung        = Session::Currency()->getID();
    $order->fGesamtsumme     = Session::Cart()->gibGesamtsummeWaren(true);
    $order->fWarensumme      = $order->fGesamtsumme;
    $order->cVersandartName  = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession         = session_id();
    $order->cKommentar       = $_SESSION['kommentar'];
    $order->cAbgeholt        = 'N';
    $order->cStatus          = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt        = 'now()';
    $order->Zahlungsart      = $_SESSION['Zahlungsart'];
    $order->Positionen       = [];
    $order->Waehrung         = $_SESSION['Waehrung']; // @todo - check if this matches the new Currency class
    $order->kWaehrung        = Session::Currency()->getID();
    $order->fWaehrungsFaktor = Session::Currency()->getConversionFactor();
    if ($order->oRechnungsadresse === null) {
        $order->oRechnungsadresse = new stdClass();
    }
    $order->oRechnungsadresse->cVorname    = $_SESSION['Kunde']->cVorname;
    $order->oRechnungsadresse->cNachname   = $_SESSION['Kunde']->cNachname;
    $order->oRechnungsadresse->cFirma      = $_SESSION['Kunde']->cFirma;
    $order->oRechnungsadresse->kKunde      = $_SESSION['Kunde']->kKunde;
    $order->oRechnungsadresse->cAnrede     = $_SESSION['Kunde']->cAnrede;
    $order->oRechnungsadresse->cTitel      = $_SESSION['Kunde']->cTitel;
    $order->oRechnungsadresse->cStrasse    = $_SESSION['Kunde']->cStrasse;
    $order->oRechnungsadresse->cHausnummer = $_SESSION['Kunde']->cHausnummer;
    $order->oRechnungsadresse->cPLZ        = $_SESSION['Kunde']->cPLZ;
    $order->oRechnungsadresse->cOrt        = $_SESSION['Kunde']->cOrt;
    $order->oRechnungsadresse->cLand       = $_SESSION['Kunde']->cLand;
    $order->oRechnungsadresse->cTel        = $_SESSION['Kunde']->cTel;
    $order->oRechnungsadresse->cMobil      = $_SESSION['Kunde']->cMobil;
    $order->oRechnungsadresse->cFax        = $_SESSION['Kunde']->cFax;
    $order->oRechnungsadresse->cUSTID      = $_SESSION['Kunde']->cUSTID;
    $order->oRechnungsadresse->cWWW        = $_SESSION['Kunde']->cWWW;
    $order->oRechnungsadresse->cMail       = $_SESSION['Kunde']->cMail;

    if (isset($_SESSION['Lieferadresse']) && strlen($_SESSION['Lieferadresse']->cVorname) > 0) {
        $order->Lieferadresse = gibLieferadresseAusSession();
    }
    $order->cBestellNr = date('dmYHis') . substr($order->cSession, 0, 4);
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        $order->Positionen = [];
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPositionen) {
            $order->Positionen[$i] = new WarenkorbPos();
            $cMember_arr                = array_keys(get_object_vars($oPositionen));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $order->Positionen[$i]->$cMember = $oPositionen->$cMember;
                }
            }

            $order->Positionen[$i]->cName = $order->Positionen[$i]->cName[$_SESSION['cISOSprache']];
            $order->Positionen[$i]->fMwSt = TaxHelper::getSalesTax($oPositionen->kSteuerklasse);
            $order->Positionen[$i]->setzeGesamtpreisLocalized();
        }
    }
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    if ($conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y') {
        $order->cIP = RequestHelper::getIP();
    }

    return $order->fuelleBestellung(0, true);
}

/**
 * @return null|stdClass
 */
function gibLieferadresseAusSession()
{
    if (!isset($_SESSION['Lieferadresse']) || strlen($_SESSION['Lieferadresse']->cVorname) === 0) {
        return null;
    }
    $shippingAddress              = new stdClass();
    $shippingAddress->cVorname    = $_SESSION['Lieferadresse']->cVorname;
    $shippingAddress->cNachname   = $_SESSION['Lieferadresse']->cNachname;
    $shippingAddress->cFirma      = $_SESSION['Lieferadresse']->cFirma ?? null;
    $shippingAddress->kKunde      = $_SESSION['Lieferadresse']->kKunde;
    $shippingAddress->cAnrede     = $_SESSION['Lieferadresse']->cAnrede;
    $shippingAddress->cTitel      = $_SESSION['Lieferadresse']->cTitel;
    $shippingAddress->cStrasse    = $_SESSION['Lieferadresse']->cStrasse;
    $shippingAddress->cHausnummer = $_SESSION['Lieferadresse']->cHausnummer;
    $shippingAddress->cPLZ        = $_SESSION['Lieferadresse']->cPLZ;
    $shippingAddress->cOrt        = $_SESSION['Lieferadresse']->cOrt;
    $shippingAddress->cLand       = $_SESSION['Lieferadresse']->cLand;
    $shippingAddress->cTel        = $_SESSION['Lieferadresse']->cTel;
    $shippingAddress->cMobil      = $_SESSION['Lieferadresse']->cMobil ?? null;
    $shippingAddress->cFax        = $_SESSION['Lieferadresse']->cFax ?? null;
    $shippingAddress->cUSTID      = $_SESSION['Lieferadresse']->cUSTID ?? null;
    $shippingAddress->cWWW        = $_SESSION['Lieferadresse']->cWWW ?? null;
    $shippingAddress->cMail       = $_SESSION['Lieferadresse']->cMail;
    $shippingAddress->cAnrede     = $_SESSION['Lieferadresse']->cAnrede;

    return $shippingAddress;
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis.
 *
 * @return array
 */
function pruefeVerfuegbarkeit()
{
    $res  = ['cArtikelName_arr' => []];
    $conf = Shop::getSettings([CONF_GLOBAL]);
    foreach (Session::Cart()->PositionenArr as $i => $oPosition) {
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
function finalisiereBestellung($orderNo = '', bool $sendMail = true)
{
    $obj                      = new stdClass();
    $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

    bestellungInDB(0, $orderNo);

    $order = new Bestellung($_SESSION['kBestellung']);
    $order->fuelleBestellung(0);
    $order->machGoogleAnalyticsReady();

    if ($order->oRechnungsadresse !== null) {
        $hash = Kuponneukunde::Hash(
            null,
            trim($order->oRechnungsadresse->cNachname),
            trim($order->oRechnungsadresse->cStrasse),
            null,
            trim($order->oRechnungsadresse->cPLZ),
            trim($order->oRechnungsadresse->cOrt),
            trim($order->oRechnungsadresse->cLand)
        );
        Shop::Container()->getDB()->update('tkuponneukunde', 'cDatenHash', $hash, (object)['cVerwendet' => 'Y']);
    }

    $_upd              = new stdClass();
    $_upd->kKunde      = (int)$_SESSION['Warenkorb']->kKunde;
    $_upd->kBestellung = (int)$order->kBestellung;
    Shop::Container()->getDB()->update('tbesucher', 'kKunde', $_upd->kKunde, $_upd);
    $obj->tkunde      = $_SESSION['Kunde'];
    $obj->tbestellung = $order;

    if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
        $obj->tbestellung->cEstimatedDeliveryEx = DateHelper::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMin
        )->format('d.m.Y')
            . ' - ' .
            DateHelper::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y');
    }
    $oKunde = new Kunde();
    $oKunde->kopiereSession();
    if ($sendMail === true) {
        sendeMail(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj);
    }
    $_SESSION['Kunde'] = $oKunde;
    $kKundengruppe     = Session::CustomerGroup()->getID();
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
