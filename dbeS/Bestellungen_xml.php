<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

$archive = null;
$return  = 3;
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;
    $zipFile = $_FILES['data']['tmp_name'];
    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'Bestellungen_xml');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $xmlFile . ' size: ' .
                    filesize($xmlFile), JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
            }
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (strpos($xmlFile, 'ack_bestellung.xml') !== false) {
                bearbeiteAck($xml);
            } elseif (strpos($xmlFile, 'del_bestellung.xml') !== false) {
                bearbeiteDel($xml);
            } elseif (strpos($xmlFile, 'delonly_bestellung.xml') !== false) {
                bearbeiteDelOnly($xml);
            } elseif (strpos($xmlFile, 'storno_bestellung.xml') !== false) {
                bearbeiteStorno($xml);
            } elseif (strpos($xmlFile, 'reaktiviere_bestellung.xml') !== false) {
                bearbeiteRestorno($xml);
            } elseif (strpos($xmlFile, 'ack_zahlungseingang.xml') !== false) {
                bearbeiteAckZahlung($xml);
            } elseif (strpos($xmlFile, 'set_bestellung.xml') !== false) {
                bearbeiteSet($xml);
            } elseif (strpos($xmlFile, 'upd_bestellung.xml') !== false) {
                bearbeiteUpdate($xml);
            }
            removeTemporaryFiles($xmlFile);
        }
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteAck($xml)
{
    if (!is_array($xml['ack_bestellungen']['kBestellung']) && (int)$xml['ack_bestellungen']['kBestellung'] > 0) {
        $xml['ack_bestellungen']['kBestellung'] = [$xml['ack_bestellungen']['kBestellung']];
    }
    if (!is_array($xml['ack_bestellungen']['kBestellung'])) {
        return;
    }
    foreach ($xml['ack_bestellungen']['kBestellung'] as $orderID) {
        $orderID = (int)$orderID;
        if ($orderID > 0) {
            Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
            Shop::Container()->getDB()->update(
                'tbestellung',
                ['kBestellung', 'cStatus'],
                [$orderID, BESTELLUNG_STATUS_OFFEN],
                (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]
            );
            Shop::Container()->getDB()->update('tzahlungsinfo', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
        }
    }
}

/**
 * @param int $orderID
 * @return bool|PaymentMethod
 */
function gibZahlungsmodul(int $orderID)
{
    $oBestellung = Shop::Container()->getDB()->queryPrepared(
        'SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart 
                ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = :oid
            LIMIT 1',
        ['oid' => $orderID],
        \DB\ReturnType::SINGLE_OBJECT
    );

    return $oBestellung ? PaymentMethod::create($oBestellung->cModulId) : false;
}

/**
 * @param array $xml
 */
function bearbeiteDel($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID > 0) {
                $oModule = gibZahlungsmodul($orderID);
                if ($oModule) {
                    $oModule->cancelOrder($orderID, true);
                }
                deleteOrder($orderID);
                //uploads (bestellungen)
                Shop::Container()->getDB()->delete('tuploadschema', ['kCustomID', 'nTyp'], [$orderID, 2]);
                Shop::Container()->getDB()->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, 2]);
                //uploads (artikel der bestellung)
                //todo...
                //wenn unreg kunde, dann kunden auch löschen
                $b = Shop::Container()->getDB()->query(
                    'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $orderID,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($b->kKunde) && $b->kKunde > 0) {
                    checkGuestAccount($b->kKunde);
                }
            }
        }
    } else {
        $orderID = (int)$xml['del_bestellungen']['kBestellung'];
        if ($orderID > 0) {
            $oModule = gibZahlungsmodul($orderID);
            if ($oModule) {
                $oModule->cancelOrder($orderID, true);
            }
            deleteOrder($orderID);
            //wenn unreg kunde, dann kunden auch löschen
            $b = Shop::Container()->getDB()->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $orderID,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($b->kKunde) && $b->kKunde > 0) {
                checkGuestAccount($b->kKunde);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDelOnly($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID > 0) {
                $oModule = gibZahlungsmodul($orderID);
                if ($oModule) {
                    $oModule->cancelOrder($orderID, true);
                }
                deleteOrder($orderID);
            }
        }
    } else {
        $orderID = (int)$xml['del_bestellungen']['kBestellung'];
        if ($orderID > 0) {
            $oModule = gibZahlungsmodul($orderID);
            if ($oModule) {
                $oModule->cancelOrder($orderID, true);
            }
            deleteOrder($orderID);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteStorno($xml)
{
    if (!is_array($xml['storno_bestellungen']['kBestellung'])) {
        $xml['storno_bestellungen']['kBestellung'] = [$xml['storno_bestellungen']['kBestellung']];
    }
    foreach ($xml['storno_bestellungen']['kBestellung'] as $orderID) {
        $orderID       = (int)$orderID;
        $bestellungTmp = null;
        $kunde         = null;
        $oModule       = gibZahlungsmodul($orderID);
        $bestellungTmp = new Bestellung($orderID);
        $kunde         = new Kunde($bestellungTmp->kKunde);
        $bestellungTmp->fuelleBestellung();
        if ($oModule) {
            $oModule->cancelOrder($orderID);
        } else {
            if (!empty($kunde->cMail) && ($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                $oMail              = new stdClass();
                $oMail->tkunde      = $kunde;
                $oMail->tbestellung = $bestellungTmp;
                sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
            }
            Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $orderID,
                (object)['cStatus' => BESTELLUNG_STATUS_STORNO]);
        }
        checkGuestAccount($kunde->kKunde);
        executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
            'oBestellung' => &$bestellungTmp,
            'oKunde'      => &$kunde,
            'oModule'     => $oModule
        ]);
    }
}

/**
 * @param array $xml
 */
function bearbeiteRestorno($xml)
{
    if (!is_array($xml['reaktiviere_bestellungen']['kBestellung'])) {
        $xml['reaktiviere_bestellungen']['kBestellung'] = [$xml['reaktiviere_bestellungen']['kBestellung']];
    }
    foreach ($xml['reaktiviere_bestellungen']['kBestellung'] as $orderID) {
        $oModule = gibZahlungsmodul($orderID);
        if ($oModule) {
            $oModule->reactivateOrder($orderID);
        } else {
            $bestellungTmp = new Bestellung($orderID);
            $kunde         = new Kunde($bestellungTmp->kKunde);
            $bestellungTmp->fuelleBestellung();

            if (($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($kunde->cMail) > 0) {
                $oMail              = new stdClass();
                $oMail->tkunde      = $kunde;
                $oMail->tbestellung = $bestellungTmp;
                sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
            }
            Shop::Container()->getDB()->update(
                'tbestellung',
                'kBestellung',
                $orderID,
                (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]
            );
            checkGuestAccount($kunde->kKunde);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAckZahlung($xml)
{
    if (!is_array($xml['ack_zahlungseingang']['kZahlungseingang'])
        && (int)$xml['ack_zahlungseingang']['kZahlungseingang'] > 0
    ) {
        $xml['ack_zahlungseingang']['kZahlungseingang'] = [$xml['ack_zahlungseingang']['kZahlungseingang']];
    }
    if (!is_array($xml['ack_zahlungseingang']['kZahlungseingang'])) {
        return;
    }
    foreach ($xml['ack_zahlungseingang']['kZahlungseingang'] as $kZahlungseingang) {
        if ((int)$kZahlungseingang > 0) {
            Shop::Container()->getDB()->update(
                'tzahlungseingang',
                'kZahlungseingang',
                (int)$kZahlungseingang,
                (object)['cAbgeholt' => 'Y']
            );
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteUpdate($xml)
{
    $kunde       = null;
    $oBestellung = new stdClass();
    $orders      = mapArray($xml, 'tbestellung', $GLOBALS['mBestellung']);
    if (is_array($orders) && count($orders) === 1) {
        $oBestellung = $orders[0];
    }
    //kommt überhaupt eine kbestellung?
    if (!$oBestellung->kBestellung) {
        unhandledError('Error Bestellung Update. Keine kBestellung in tbestellung! XML:' . print_r($xml, true));
    }
    //hole bestellung
    $oBestellungAlt = Shop::Container()->getDB()->select('tbestellung', 'kBestellung', (int)$oBestellung->kBestellung);
    //mappe rechnungsadresse
    $oRechnungsadresse = new Rechnungsadresse($oBestellungAlt->kRechnungsadresse);
    mappe($oRechnungsadresse, $xml['tbestellung']['trechnungsadresse'], $GLOBALS['mRechnungsadresse']);
    if (!empty($oRechnungsadresse->cAnrede)) {
        $oRechnungsadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oRechnungsadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oRechnungsadresse);
    //rechnungsadresse gefüllt?
    if (!$oRechnungsadresse->cNachname && !$oRechnungsadresse->cFirma && !$oRechnungsadresse->cStrasse) {
        unhandledError('Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' .
            print_r($xml, true)
        );
    }
    //existiert eine alte bestellung mit dieser kBestellung?
    if (!$oBestellungAlt->kBestellung || trim($oBestellung->cBestellNr) !== trim($oBestellungAlt->cBestellNr)) {
        unhandledError('Fehler: Zur Bestellung ' . $oBestellung->cBestellNr .
            ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!');
    }
    // Zahlungsart vorhanden?
    $oZahlungsart = new stdClass();
    if (isset($xml['tbestellung']['cZahlungsartName']) && strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
        // Von Wawi kommt in $xml['tbestellung']['cZahlungsartName'] nur der deutsche Wert,
        // deshalb immer Abfrage auf tzahlungsart.cName
        $cZahlungsartName = $xml['tbestellung']['cZahlungsartName'];
        $oZahlungsart     = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT tzahlungsart.kZahlungsart, IFNULL(tzahlungsartsprache.cName, tzahlungsart.cName) AS cName
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tzahlungsartsprache.cISOSprache = :iso
                WHERE tzahlungsart.cName LIKE :search
                ORDER BY CASE
                    WHEN tzahlungsart.cName = :name1 THEN 1
                    WHEN tzahlungsart.cName LIKE :name2 THEN 2
                    WHEN tzahlungsart.cName LIKE :name3 THEN 3
                    END, kZahlungsart",
            [
                'iso'    => Sprache::getLanguageDataByType('', (int)$oBestellung->kSprache),
                'search' => "%{$cZahlungsartName}%",
                'name1'  => $cZahlungsartName,
                'name2'  => "{$cZahlungsartName}%",
                'name3'  => "%{$cZahlungsartName}%",
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog(
                'Zahlungsart Matching (' . Sprache::getLanguageDataByType('', (int)$oBestellung->kSprache) . '): ' .
                $xml['tbestellung']['cZahlungsartName'] . ' matched: ' . ($oZahlungsart->cName ?? ''),
                JTLLOG_LEVEL_DEBUG,
                false,
                'Bestellungen_xml'
            );
        }
    }
    $cZAUpdateSQL = '';
    if (isset($oZahlungsart->kZahlungsart) && $oZahlungsart->kZahlungsart > 0) {
        $cZAUpdateSQL = " , kZahlungsart = " . (int)$oZahlungsart->kZahlungsart .
            ", cZahlungsartName = '" . $oZahlungsart->cName . "' ";
    }
    //#8544
    $correctionFactor = 1.0;
    if (isset($oBestellung->kWaehrung)) {
        $currentCurrency = Shop::Container()->getDB()->select('twaehrung', 'kWaehrung', $oBestellung->kWaehrung);
        $defaultCurrency = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
        if (isset($currentCurrency->kWaehrung, $defaultCurrency->kWaehrung)) {
            $correctionFactor          = (float)$currentCurrency->fFaktor;
            $oBestellung->fGesamtsumme /= $correctionFactor;
            $oBestellung->fGuthaben    /= $correctionFactor;
        }
    }
    // Die Wawi schickt in fGesamtsumme die Rechnungssumme (Summe aller Positionen), der Shop erwartet hier aber tatsächlich
    // eine Gesamtsumme oder auch den Zahlungsbetrag (Rechnungssumme abzgl. evtl. Guthaben)
    $oBestellung->fGesamtsumme -= $oBestellung->fGuthaben;

    //aktualisiere bestellung
    Shop::Container()->getDB()->query(
        "UPDATE tbestellung SET
            fGuthaben = '" . Shop::Container()->getDB()->escape($oBestellung->fGuthaben) . "',
            fGesamtsumme = '" . Shop::Container()->getDB()->escape($oBestellung->fGesamtsumme) . "',
            cKommentar = '" . Shop::Container()->getDB()->escape($oBestellung->cKommentar) . "'
            {$cZAUpdateSQL}
            WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung,
        \DB\ReturnType::DEFAULT
    );
    //aktualisliere lieferadresse
    $oLieferadresse = new Lieferadresse($oBestellungAlt->kLieferadresse);
    mappe($oLieferadresse, $xml['tbestellung']['tlieferadresse'], $GLOBALS['mLieferadresse']);
    if (isset($oLieferadresse->cAnrede)) {
        $oLieferadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oLieferadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oLieferadresse);
    //lieferadresse ungleich rechungsadresse?
    if ($oLieferadresse->cVorname !== $oRechnungsadresse->cVorname
        || $oLieferadresse->cNachname !== $oRechnungsadresse->cNachname
        || $oLieferadresse->cStrasse !== $oRechnungsadresse->cStrasse
        || $oLieferadresse->cHausnummer !== $oRechnungsadresse->cHausnummer
        || $oLieferadresse->cPLZ !== $oRechnungsadresse->cPLZ
        || $oLieferadresse->cOrt !== $oRechnungsadresse->cOrt
        || $oLieferadresse->cLand !== $oRechnungsadresse->cLand
    ) {
        if ($oLieferadresse->kLieferadresse > 0) {
            //lieferadresse aktualisieren
            $oLieferadresse->updateInDB();
        } else {
            //lieferadresse erstellen
            $oLieferadresse->kKunde         = $oBestellungAlt->kKunde;
            $oLieferadresse->kLieferadresse = $oLieferadresse->insertInDB();

            Shop::Container()->getDB()->query(
                "UPDATE tbestellung
                    SET kLieferadresse = " . (int)$oLieferadresse->kLieferadresse . "
                    WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung,
                \DB\ReturnType::DEFAULT
            );
        }
    } elseif ($oBestellungAlt->kLieferadresse > 0) { //falls lieferadresse vorhanden zurücksetzen
        Shop::Container()->getDB()->update(
            'tbestellung',
            'kBestellung',
            (int)$oBestellungAlt->kBestellung,
            (object)['kLieferadresse' => 0]
        );
    }

    $oRechnungsadresse->updateInDB();
    //loesche alte positionen
    $WarenkorbposAlt_arr = Shop::Container()->getDB()->selectAll('twarenkorbpos', 'kWarenkorb',
        (int)$oBestellungAlt->kWarenkorb);
    $WarenkorbposAlt_map = [];
    //loesche poseigenschaften
    foreach ($WarenkorbposAlt_arr as $key => $WarenkorbposAlt) {
        Shop::Container()->getDB()->delete('twarenkorbposeigenschaft', 'kWarenkorbPos',
            (int)$WarenkorbposAlt->kWarenkorbPos);
        if ($WarenkorbposAlt->kArtikel > 0) {
            $WarenkorbposAlt_map[$WarenkorbposAlt->kArtikel] = $key;
        }
    }
    //loesche positionen
    Shop::Container()->getDB()->delete('twarenkorbpos', 'kWarenkorb', (int)$oBestellungAlt->kWarenkorb);
    //erstelle neue posis
    $Warenkorbpos_arr = mapArray($xml['tbestellung'], 'twarenkorbpos', $GLOBALS['mWarenkorbpos']);
    $positionCount    = count($Warenkorbpos_arr);
    for ($i = 0; $i < $positionCount; $i++) {
        //füge wkpos ein
        $oWarenkorbposAlt = array_key_exists($Warenkorbpos_arr[$i]->kArtikel, $WarenkorbposAlt_map)
            ? $WarenkorbposAlt_arr[$WarenkorbposAlt_map[$Warenkorbpos_arr[$i]->kArtikel]]
            : null;
        unset($Warenkorbpos_arr[$i]->kWarenkorbPos);
        $Warenkorbpos_arr[$i]->kWarenkorb        = $oBestellungAlt->kWarenkorb;
        $Warenkorbpos_arr[$i]->fPreis            /= $correctionFactor;
        $Warenkorbpos_arr[$i]->fPreisEinzelNetto /= $correctionFactor;
        // persistiere nLongestMin/MaxDelivery wenn nicht von Wawi übetragen
        if (!isset($Warenkorbpos_arr[$i]->nLongestMinDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMinDelivery = $oWarenkorbposAlt->nLongestMinDelivery ?? 0;
        }
        if (!isset($Warenkorbpos_arr[$i]->nLongestMaxDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMaxDelivery = $oWarenkorbposAlt->nLongestMaxDelivery ?? 0;
        }
        $Warenkorbpos_arr[$i]->kWarenkorbPos = Shop::Container()->getDB()->insert('twarenkorbpos',
            $Warenkorbpos_arr[$i]);

        if (count($Warenkorbpos_arr) < 2) { // nur eine pos
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'], 'twarenkorbposeigenschaft',
                $GLOBALS['mWarenkorbposeigenschaft']);
        } else { //mehrere posis
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'][$i],
                'twarenkorbposeigenschaft', $GLOBALS['mWarenkorbposeigenschaft']);
        }
        //füge warenkorbposeigenschaften ein
        foreach ($Warenkorbposeigenschaft_arr as $Warenkorbposeigenschaft) {
            unset($Warenkorbposeigenschaft->kWarenkorbPosEigenschaft);
            $Warenkorbposeigenschaft->kWarenkorbPos = $Warenkorbpos_arr[$i]->kWarenkorbPos;
            Shop::Container()->getDB()->insert('twarenkorbposeigenschaft', $Warenkorbposeigenschaft);
        }
    }

    if (isset($xml['tbestellung']['tbestellattribut'])) {
        bearbeiteBestellattribute($oBestellung->kBestellung,
            is_assoc($xml['tbestellung']['tbestellattribut'])
                ? [$xml['tbestellung']['tbestellattribut']]
                : $xml['tbestellung']['tbestellattribut']);
    }

    //sende Versandmail
    $oModule = gibZahlungsmodul($oBestellungAlt->kBestellung);
    //neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird wenns auch wirklich für den kunden interessant ist
    //ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
    //bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
    $emailvorlage = Emailvorlage::load(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
    $kunde        = new Kunde((int)$oBestellungAlt->kKunde);

    if ($emailvorlage !== null
        && $emailvorlage->getAktiv() === 'Y'
        && ($oBestellung->cSendeEMail === 'Y' || !isset($oBestellung->cSendeEMail))
    ) {
        if ($oModule) {
            $oModule->sendMail($oBestellungAlt->kBestellung, MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        } else {
            $bestellungTmp = new Bestellung((int)$oBestellungAlt->kBestellung);
            $bestellungTmp->fuelleBestellung();

            $oMail              = new stdClass();
            $oMail->tkunde      = $kunde;
            $oMail->tbestellung = $bestellungTmp;
            sendeMail(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $oMail);
        }
    }
    checkGuestAccount($kunde->kKunde);
    executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, [
        'oBestellung'    => &$oBestellung,
        'oBestellungAlt' => &$oBestellungAlt,
        'oKunde'         => &$kunde
    ]);
}

/**
 * @param array $xml
 */
function bearbeiteSet($xml)
{
    $orders = mapArray($xml['tbestellungen'], 'tbestellung', $GLOBALS['mBestellung']);
    foreach ($orders as $order) {
        $shopOrder = Shop::Container()->getDB()->select('tbestellung', 'kBestellung', (int)$order->kBestellung);
        if (!isset($shopOrder->kBestellung) || $shopOrder->kBestellung <= 0) {
            continue;
        }
        $cTrackingURL = '';
        if (strlen($order->cIdentCode) > 0) {
            $cTrackingURL = $order->cLogistikURL;
            if ($shopOrder->kLieferadresse > 0) {
                $Lieferadresse = Shop::Container()->getDB()->query(
                    'SELECT cPLZ
                        FROM tlieferadresse 
                        WHERE kLieferadresse = ' . (int)$shopOrder->kLieferadresse,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($Lieferadresse->cPLZ) {
                    $cTrackingURL = str_replace('#PLZ#', $Lieferadresse->cPLZ, $cTrackingURL);
                }
            } else {
                $kunde        = new Kunde($shopOrder->kKunde);
                $cTrackingURL = str_replace('#PLZ#', $kunde->cPLZ, $cTrackingURL);
            }
            $cTrackingURL = str_replace('#IdentCode#', $order->cIdentCode, $cTrackingURL);
        }

        if ($shopOrder->cStatus === BESTELLUNG_STATUS_STORNO) {
            $status = BESTELLUNG_STATUS_STORNO;
        } else {
            $status = BESTELLUNG_STATUS_IN_BEARBEITUNG;
            if (isset($order->cBezahlt) && $order->cBezahlt === 'Y') {
                $status = BESTELLUNG_STATUS_BEZAHLT;
            }

            if (isset($order->dVersandt) && strlen($order->dVersandt) > 0) {
                $status = BESTELLUNG_STATUS_VERSANDT;
            }
            $oBestellungUpdated = new Bestellung((int)$shopOrder->kBestellung);
            $oBestellungUpdated->fuelleBestellung();

            if ((is_array($oBestellungUpdated->oLieferschein_arr)
                    && count($oBestellungUpdated->oLieferschein_arr) > 0)
                && (isset($order->nKomplettAusgeliefert)
                    && (int)$order->nKomplettAusgeliefert === 0)
            ) {
                $status = BESTELLUNG_STATUS_TEILVERSANDT;
            }
        }
        executeHook(HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, [
            'status'      => &$status,
            'oBestellung' => &$shopOrder
        ]);
        $cZahlungsartName = Shop::Container()->getDB()->escape($order->cZahlungsartName);
        $dBezahltDatum    = Shop::Container()->getDB()->escape($order->dBezahltDatum);
        $dVersandDatum    = Shop::Container()->getDB()->escape($order->dVersandt);
        if ($dVersandDatum === null || $dVersandDatum === '') {
            $dVersandDatum = '0000-00-00';
        }
        $upd                = new stdClass();
        $upd->dVersandDatum = $dVersandDatum;
        $upd->cTracking     = Shop::Container()->getDB()->escape($order->cIdentCode);
        $upd->cLogistiker   = Shop::Container()->getDB()->escape($order->cLogistik);
        $upd->cTrackingURL  = Shop::Container()->getDB()->escape($cTrackingURL);
        $upd->cStatus       = $status;
        $upd->cVersandInfo  = Shop::Container()->getDB()->escape($order->cVersandInfo);
        if (strlen($cZahlungsartName) > 0) {
            $upd->cZahlungsartName = $cZahlungsartName;
        }
        $upd->dBezahltDatum = empty($dBezahltDatum)
            ? '0000-00-00'
            : $dBezahltDatum;
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $upd);
        $oBestellungUpdated = new Bestellung($shopOrder->kBestellung, true);

        $kunde = null;
        if (((!$shopOrder->dVersandDatum || $shopOrder->dVersandDatum === '0000-00-00') && $order->dVersandt) ||
            ((!$shopOrder->dBezahltDatum || $shopOrder->dBezahltDatum === '0000-00-00') && $order->dBezahltDatum)
        ) {
            $tmp   = Shop::Container()->getDB()->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . (int)$order->kBestellung,
                \DB\ReturnType::SINGLE_OBJECT);
            $kunde = new Kunde((int)$tmp->kKunde);
        }

        $bLieferschein = false;
        foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
            /** @var Lieferschein $oLieferschein */
            if ($oLieferschein->getEmailVerschickt() === false) {
                $bLieferschein = true;
                break;
            }
        }
        $status = (int)$status;
        if (($status === BESTELLUNG_STATUS_VERSANDT && (int)$shopOrder->cStatus !== BESTELLUNG_STATUS_VERSANDT)
            || ($status === BESTELLUNG_STATUS_TEILVERSANDT && $bLieferschein === true)
        ) {
            $cMailType = $status === BESTELLUNG_STATUS_VERSANDT
                ? MAILTEMPLATE_BESTELLUNG_VERSANDT
                : MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
            $oModule   = gibZahlungsmodul($order->kBestellung);
            if (!isset($oBestellungUpdated->oVersandart->cSendConfirmationMail)
                || $oBestellungUpdated->oVersandart->cSendConfirmationMail !== 'N'
            ) {
                if ($oModule) {
                    $oModule->sendMail((int)$order->kBestellung, $cMailType);
                } else {
                    if ($kunde === null) {
                        $kunde = new Kunde((int)$shopOrder->kKunde);
                    }
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $oBestellungUpdated;
                    sendeMail($cMailType, $oMail);
                }
            }
            /** @var Lieferschein $oLieferschein */
            foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
                $oLieferschein->setEmailVerschickt(true)->update();
            }
            // Guthaben an Bestandskunden verbuchen, Email rausschicken:
            if ($kunde === null) {
                $kunde = new Kunde($shopOrder->kKunde);
            }

            $oKwK = new KundenwerbenKunden();
            $oKwK->verbucheBestandskundenBoni($kunde->cMail);
        }

        checkGuestAccount((int)$shopOrder->kKunde);

        if ((!$shopOrder->dBezahltDatum || $shopOrder->dBezahltDatum === '0000-00-00')
            && $order->dBezahltDatum
            && $kunde->kKunde > 0
        ) {
            //sende Zahlungseingangmail
            $oModule = gibZahlungsmodul($order->kBestellung);
            if ($oModule) {
                $oModule->sendMail((int)$order->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);
            } else {
                $kunde              = $kunde ?? new Kunde((int)$shopOrder->kKunde);
                $oBestellungUpdated = new Bestellung((int)$shopOrder->kBestellung);
                $oBestellungUpdated->fuelleBestellung();
                if (($oBestellungUpdated->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG) && strlen($kunde->cMail) > 0) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $oBestellungUpdated;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $oMail);
                }
            }
        }
        executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESET, [
            'oBestellung'     => &$shopOrder,
            'oKunde'          => &$kunde,
            'oBestellungWawi' => &$order
        ]);
    }
}

/**
 * @param $kBestellung
 */
function deleteOrder(int $kBestellung)
{
    $kWarenkorb = Shop::Container()->getDB()->select(
        'tbestellung',
        'kBestellung', $kBestellung,
        null, null,
        null, null,
        false,
        'kWarenkorb'
    );
    Shop::Container()->getDB()->delete('tbestellung', 'kBestellung', $kBestellung);
    Shop::Container()->getDB()->delete('tbestellid', 'kBestellung', $kBestellung);
    Shop::Container()->getDB()->delete('tbestellstatus', 'kBestellung', $kBestellung);
    Shop::Container()->getDB()->delete('tkuponbestellung', 'kBestellung', $kBestellung);
    Shop::Container()->getDB()->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$kBestellung, UPLOAD_TYP_BESTELLUNG]);
    Shop::Container()->getDB()->delete('tuploadqueue', 'kBestellung', $kBestellung);
    if ((int)$kWarenkorb->kWarenkorb > 0) {
        Shop::Container()->getDB()->delete('twarenkorb', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
        $positions = Shop::Container()->getDB()->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$kWarenkorb->kWarenkorb,
            'kWarenkorbPos'
        );
        Shop::Container()->getDB()->delete('twarenkorbpos', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
        foreach ($positions as $position) {
            Shop::Container()->getDB()->delete('twarenkorbposeigenschaft', 'kWarenkorbPos',
                (int)$position->kWarenkorbPos);
        }
    }
}

/**
 * @param int $kKunde
 */
function checkGuestAccount(int $kKunde)
{
    //Bei komplett versendeten Gastbestellungen, Kundendaten aus dem Shop loeschen
    $kunde = new Kunde($kKunde);
    if ($kunde->cPasswort !== null && strlen($kunde->cPasswort) < 10) {
        // Da Gastkonten auch durch Kundenkontolöschung entstehen können, kann es auch mehrere Bestellungen geben
        $oBestellung = Shop::Container()->getDB()->query(
            "SELECT COUNT(kBestellung) AS countBestellung
                FROM tbestellung
                WHERE cStatus NOT IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                    AND kKunde = " . (int)$kunde->kKunde,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oBestellung->countBestellung) && (int)$oBestellung->countBestellung === 0) {
            Shop::Container()->getDB()->delete('tlieferadresse', 'kKunde', (int)$kunde->kKunde);
            Shop::Container()->getDB()->delete('trechnungsadresse', 'kKunde', (int)$kunde->kKunde);
            Shop::Container()->getDB()->delete('tkundenattribut', 'kKunde', (int)$kunde->kKunde);
            Shop::Container()->getDB()->delete('tkunde', 'kKunde', (int)$kunde->kKunde);
        }
    }
}

/**
 * @param int        $kBestellung
 * @param stdClass[] $orderAttributes
 */
function bearbeiteBestellattribute(int $kBestellung, $orderAttributes)
{
    $updated = [];
    if (is_array($orderAttributes)) {
        foreach ($orderAttributes as $orderAttributeData) {
            $orderAttribute    = (object)$orderAttributeData;
            $orderAttributeOld = Shop::Container()->getDB()->select(
                'tbestellattribut',
                ['kBestellung', 'cName'],
                [$kBestellung, $orderAttribute->key]
            );
            if (isset($orderAttributeOld->kBestellattribut)) {
                Shop::Container()->getDB()->update(
                    'tbestellattribut',
                    'kBestellattribut',
                    $orderAttributeOld->kBestellattribut,
                    (object)['cValue' => $orderAttribute->value]
                );
                $updated[] = $orderAttributeOld->kBestellattribut;
            } else {
                $updated[] = Shop::Container()->getDB()->insert('tbestellattribut', (object)[
                    'kBestellung' => $kBestellung,
                    'cName'       => $orderAttribute->key,
                    'cValue'      => $orderAttribute->value,
                ]);
            }
        }
    }

    if (count($updated) > 0) {
        Shop::Container()->getDB()->query(
            "DELETE FROM tbestellattribut
                WHERE kBestellung = {$kBestellung}
                    AND kBestellattribut NOT IN (" . implode(', ', $updated) . ")",
            \DB\ReturnType::QUERYSINGLE
        );
    } else {
        Shop::Container()->getDB()->delete('tbestellattribut', 'kBestellung', $kBestellung);
    }
}
