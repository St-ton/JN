<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

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
        Shop::Container()->getLogService()->error(
            'Error: Cannot extract zip file ' . $zipFile . ' to ' . PFAD_SYNC_TMP
        );
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
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
    $db = Shop::Container()->getDB();
    foreach ($xml['ack_bestellungen']['kBestellung'] as $orderID) {
        $orderID = (int)$orderID;
        if ($orderID > 0) {
            $db->update('tbestellung', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
            $db->update(
                'tbestellung',
                ['kBestellung', 'cStatus'],
                [$orderID, BESTELLUNG_STATUS_OFFEN],
                (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]
            );
            $db->update('tzahlungsinfo', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
        }
    }
}

/**
 * @param int $orderID
 * @return bool|PaymentMethod
 */
function gibZahlungsmodul(int $orderID)
{
    $order = Shop::Container()->getDB()->queryPrepared(
        'SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart 
                ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = :oid
            LIMIT 1',
        ['oid' => $orderID],
        \DB\ReturnType::SINGLE_OBJECT
    );

    return $order ? PaymentMethod::create($order->cModulId) : false;
}

/**
 * @param array $xml
 */
function bearbeiteDel($xml)
{
    $db = Shop::Container()->getDB();
    if (!is_array($xml['del_bestellungen']['kBestellung'])) {
        $xml['del_bestellungen']['kBestellung'] = [$xml['del_bestellungen']['kBestellung']];
    }
    foreach ($xml['del_bestellungen']['kBestellung'] as $orderID) {
        $orderID = (int)$orderID;
        if ($orderID > 0) {
            $oModule = gibZahlungsmodul($orderID);
            if ($oModule) {
                $oModule->cancelOrder($orderID, true);
            }
            deleteOrder($orderID);
            //uploads (bestellungen)
            $db->delete('tuploadschema', ['kCustomID', 'nTyp'], [$orderID, 2]);
            $db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, 2]);
            //uploads (artikel der bestellung)
            //todo...
            //wenn unreg kunde, dann kunden auch löschen
            $db = $db->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $orderID,
                \DB\ReturnType::SINGLE_OBJECT
            );
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDelOnly($xml)
{
    $orderIDs = is_array($xml['del_bestellungen']['kBestellung'])
        ? $xml['del_bestellungen']['kBestellung']
        : [$xml['del_bestellungen']['kBestellung']];
    foreach ($orderIDs as $orderID) {
        $orderID = (int)$orderID;
        if ($orderID > 0) {
            $module = gibZahlungsmodul($orderID);
            if ($module) {
                $module->cancelOrder($orderID, true);
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
        $orderID  = (int)$orderID;
        $module   = gibZahlungsmodul($orderID);
        $tmpOrder = new Bestellung($orderID);
        $customer = new Kunde($tmpOrder->kKunde);
        $tmpOrder->fuelleBestellung();
        if ($module) {
            $module->cancelOrder($orderID);
        } else {
            if (!empty($customer->cMail) && ($tmpOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                $mail              = new stdClass();
                $mail->tkunde      = $customer;
                $mail->tbestellung = $tmpOrder;
                sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $mail);
            }
            Shop::Container()->getDB()->update(
                'tbestellung',
                'kBestellung',
                $orderID,
                (object)['cStatus' => BESTELLUNG_STATUS_STORNO]
            );
        }
        executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
            'oBestellung' => &$tmpOrder,
            'oKunde'      => &$customer,
            'oModule'     => $module
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
        $module = gibZahlungsmodul($orderID);
        if ($module) {
            $module->reactivateOrder($orderID);
        } else {
            $tmpOrder = new Bestellung($orderID);
            $customer = new Kunde($tmpOrder->kKunde);
            $tmpOrder->fuelleBestellung();
            if (($tmpOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($customer->cMail) > 0) {
                $oMail              = new stdClass();
                $oMail->tkunde      = $customer;
                $oMail->tbestellung = $tmpOrder;
                sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
            }
            Shop::Container()->getDB()->update(
                'tbestellung',
                'kBestellung',
                $orderID,
                (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]
            );
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
    $customer = null;
    $order    = new stdClass();
    $orders   = mapArray($xml, 'tbestellung', Mapper::getMapping('mBestellung'));
    $db       = Shop::Container()->getDB();
    if (count($orders) === 1) {
        $order = $orders[0];
    }
    if (!$order->kBestellung) {
        unhandledError('Error Bestellung Update. Keine kBestellung in tbestellung! XML:' . print_r($xml, true));
    }
    $oldOrder       = $db->select(
        'tbestellung',
        'kBestellung',
        (int)$order->kBestellung
    );
    $billingAddress = new Rechnungsadresse($oldOrder->kRechnungsadresse);
    mappe($billingAddress, $xml['tbestellung']['trechnungsadresse'], Mapper::getMapping('mRechnungsadresse'));
    if (!empty($billingAddress->cAnrede)) {
        $billingAddress->cAnrede = mappeWawiAnrede2ShopAnrede($billingAddress->cAnrede);
    }
    extractStreet($billingAddress);
    if (!$billingAddress->cNachname && !$billingAddress->cFirma && !$billingAddress->cStrasse) {
        unhandledError(
            'Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' .
            print_r($xml, true)
        );
    }
    if (!$oldOrder->kBestellung || trim($order->cBestellNr) !== trim($oldOrder->cBestellNr)) {
        unhandledError('Fehler: Zur Bestellung ' . $order->cBestellNr .
            ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!');
    }
    $paymentMethod = new stdClass();
    if (isset($xml['tbestellung']['cZahlungsartName']) && strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
        // Von Wawi kommt in $xml['tbestellung']['cZahlungsartName'] nur der deutsche Wert,
        // deshalb immer Abfrage auf tzahlungsart.cName
        $paymentMethodName = $xml['tbestellung']['cZahlungsartName'];
        $paymentMethod     = $db->executeQueryPrepared(
            'SELECT tzahlungsart.kZahlungsart, IFNULL(tzahlungsartsprache.cName, tzahlungsart.cName) AS cName
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tzahlungsartsprache.cISOSprache = :iso
                WHERE tzahlungsart.cName LIKE :search
                ORDER BY CASE
                    WHEN tzahlungsart.cName = :name1 THEN 1
                    WHEN tzahlungsart.cName LIKE :name2 THEN 2
                    WHEN tzahlungsart.cName LIKE :name3 THEN 3
                    END, kZahlungsart',
            [
                'iso'    => Sprache::getLanguageDataByType('', (int)$order->kSprache),
                'search' => '%' . $paymentMethodName. '%',
                'name1'  => $paymentMethodName,
                'name2'  => $paymentMethodName . '%',
                'name3'  => '%' . $paymentMethodName . '%',
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }
    $cZAUpdateSQL = '';
    if (isset($paymentMethod->kZahlungsart) && $paymentMethod->kZahlungsart > 0) {
        $cZAUpdateSQL = ' , kZahlungsart = ' . (int)$paymentMethod->kZahlungsart .
            ", cZahlungsartName = '" . $paymentMethod->cName . "' ";
    }
    $correctionFactor = 1.0;
    if (isset($order->kWaehrung)) {
        $currentCurrency = $db->select('twaehrung', 'kWaehrung', $order->kWaehrung);
        $defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
        if (isset($currentCurrency->kWaehrung, $defaultCurrency->kWaehrung)) {
            $correctionFactor     = (float)$currentCurrency->fFaktor;
            $order->fGesamtsumme /= $correctionFactor;
            $order->fGuthaben    /= $correctionFactor;
        }
    }
    // Die Wawi schickt in fGesamtsumme die Rechnungssumme (Summe aller Positionen),
    // der Shop erwartet hier aber tatsächlich eine Gesamtsumme oder auch den Zahlungsbetrag
    // (Rechnungssumme abzgl. evtl. Guthaben)
    $order->fGesamtsumme -= $order->fGuthaben;
    $db->queryPrepared(
        'UPDATE tbestellung SET
            fGuthaben = :fg,
            fGesamtsumme = :total,
            cKommentar = :cmt ' . $cZAUpdateSQL . '
            WHERE kBestellung = :oid',
        [
            'fg'    => $order->fGuthaben,
            'total' => $order->fGesamtsumme,
            'cmt'   => $order->cKommentar,
            'oid'   => (int)$oldOrder->kBestellung
        ],
        \DB\ReturnType::DEFAULT
    );
    $deliveryAddress = new Lieferadresse($oldOrder->kLieferadresse);
    mappe($deliveryAddress, $xml['tbestellung']['tlieferadresse'], Mapper::getMapping('mLieferadresse'));
    if (isset($deliveryAddress->cAnrede)) {
        $deliveryAddress->cAnrede = mappeWawiAnrede2ShopAnrede($deliveryAddress->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($deliveryAddress);
    // lieferadresse ungleich rechungsadresse?
    if ($deliveryAddress->cVorname !== $billingAddress->cVorname
        || $deliveryAddress->cNachname !== $billingAddress->cNachname
        || $deliveryAddress->cStrasse !== $billingAddress->cStrasse
        || $deliveryAddress->cHausnummer !== $billingAddress->cHausnummer
        || $deliveryAddress->cPLZ !== $billingAddress->cPLZ
        || $deliveryAddress->cOrt !== $billingAddress->cOrt
        || $deliveryAddress->cLand !== $billingAddress->cLand
    ) {
        if ($deliveryAddress->kLieferadresse > 0) {
            $deliveryAddress->updateInDB();
        } else {
            $deliveryAddress->kKunde         = $oldOrder->kKunde;
            $deliveryAddress->kLieferadresse = $deliveryAddress->insertInDB();
            $db->query(
                'UPDATE tbestellung
                    SET kLieferadresse = ' . (int)$deliveryAddress->kLieferadresse . '
                    WHERE kBestellung = ' . (int)$oldOrder->kBestellung,
                \DB\ReturnType::DEFAULT
            );
        }
    } elseif ($oldOrder->kLieferadresse > 0) {
        $db->update(
            'tbestellung',
            'kBestellung',
            (int)$oldOrder->kBestellung,
            (object)['kLieferadresse' => 0]
        );
    }
    $billingAddress->updateInDB();
    $oldPositions = $db->selectAll(
        'twarenkorbpos',
        'kWarenkorb',
        (int)$oldOrder->kWarenkorb
    );
    $map          = [];
    foreach ($oldPositions as $key => $oldPosition) {
        $db->delete(
            'twarenkorbposeigenschaft',
            'kWarenkorbPos',
            (int)$oldPosition->kWarenkorbPos
        );
        if ($oldPosition->kArtikel > 0) {
            $map[$oldPosition->kArtikel] = $key;
        }
    }
    $db->delete('twarenkorbpos', 'kWarenkorb', (int)$oldOrder->kWarenkorb);
    $cartPositions = mapArray($xml['tbestellung'], 'twarenkorbpos', Mapper::getMapping('mWarenkorbpos'));
    $positionCount = count($cartPositions);
    for ($i = 0; $i < $positionCount; $i++) {
        $oldPosition = array_key_exists($cartPositions[$i]->kArtikel, $map)
            ? $oldPositions[$map[$cartPositions[$i]->kArtikel]]
            : null;
        unset($cartPositions[$i]->kWarenkorbPos);
        $cartPositions[$i]->kWarenkorb         = $oldOrder->kWarenkorb;
        $cartPositions[$i]->fPreis            /= $correctionFactor;
        $cartPositions[$i]->fPreisEinzelNetto /= $correctionFactor;
        // persistiere nLongestMin/MaxDelivery wenn nicht von Wawi übetragen
        if (!isset($cartPositions[$i]->nLongestMinDelivery)) {
            $cartPositions[$i]->nLongestMinDelivery = $oldPosition->nLongestMinDelivery ?? 0;
        }
        if (!isset($cartPositions[$i]->nLongestMaxDelivery)) {
            $cartPositions[$i]->nLongestMaxDelivery = $oldPosition->nLongestMaxDelivery ?? 0;
        }
        $cartPositions[$i]->kWarenkorbPos = $db->insert(
            'twarenkorbpos',
            $cartPositions[$i]
        );

        if (count($cartPositions) < 2) {
            $cartPosAttributes = mapArray(
                $xml['tbestellung']['twarenkorbpos'],
                'twarenkorbposeigenschaft',
                Mapper::getMapping('mWarenkorbposeigenschaft')
            );
        } else {
            $cartPosAttributes = mapArray(
                $xml['tbestellung']['twarenkorbpos'][$i],
                'twarenkorbposeigenschaft',
                Mapper::getMapping('mWarenkorbposeigenschaft')
            );
        }
        foreach ($cartPosAttributes as $posAttribute) {
            unset($posAttribute->kWarenkorbPosEigenschaft);
            $posAttribute->kWarenkorbPos = $cartPositions[$i]->kWarenkorbPos;
            $db->insert('twarenkorbposeigenschaft', $posAttribute);
        }
    }

    if (isset($xml['tbestellung']['tbestellattribut'])) {
        bearbeiteBestellattribute(
            $order->kBestellung,
            is_assoc($xml['tbestellung']['tbestellattribut'])
                ? [$xml['tbestellung']['tbestellattribut']]
                : $xml['tbestellung']['tbestellattribut']
        );
    }
    $module = gibZahlungsmodul($oldOrder->kBestellung);
    // neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird,
    // wenn es auch wirklich für den kunden interessant ist
    // ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
    // bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
    $mailTPL  = Emailvorlage::load(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
    $customer = new Kunde((int)$oldOrder->kKunde);

    if ($mailTPL !== null
        && $mailTPL->getAktiv() === 'Y'
        && ($order->cSendeEMail === 'Y' || !isset($order->cSendeEMail))
    ) {
        if ($module) {
            $module->sendMail($oldOrder->kBestellung, MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        } else {
            $mail              = new stdClass();
            $mail->tkunde      = $customer;
            $mail->tbestellung = new Bestellung((int)$oldOrder->kBestellung, true);
            sendeMail(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $mail);
        }
    }
    executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, [
        'oBestellung'    => &$order,
        'oBestellungAlt' => &$oldOrder,
        'oKunde'         => &$customer
    ]);
}

/**
 * @param array $xml
 */
function bearbeiteSet($xml)
{
    $orders = mapArray($xml['tbestellungen'], 'tbestellung', Mapper::getMapping('mBestellung'));
    $db     = Shop::Container()->getDB();
    foreach ($orders as $order) {
        $shopOrder = $db->select('tbestellung', 'kBestellung', (int)$order->kBestellung);
        if (!isset($shopOrder->kBestellung) || $shopOrder->kBestellung <= 0) {
            continue;
        }
        $trackingURL = '';
        if (strlen($order->cIdentCode) > 0) {
            $trackingURL = $order->cLogistikURL;
            if ($shopOrder->kLieferadresse > 0) {
                $Lieferadresse = $db->query(
                    'SELECT cPLZ
                        FROM tlieferadresse 
                        WHERE kLieferadresse = ' . (int)$shopOrder->kLieferadresse,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($Lieferadresse->cPLZ) {
                    $trackingURL = str_replace('#PLZ#', $Lieferadresse->cPLZ, $trackingURL);
                }
            } else {
                $customer    = new Kunde($shopOrder->kKunde);
                $trackingURL = str_replace('#PLZ#', $customer->cPLZ, $trackingURL);
            }
            $trackingURL = str_replace('#IdentCode#', $order->cIdentCode, $trackingURL);
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
            $updatedOrder = new Bestellung((int)$shopOrder->kBestellung, true);
            if ((is_array($updatedOrder->oLieferschein_arr)
                    && count($updatedOrder->oLieferschein_arr) > 0)
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
        $cZahlungsartName = $db->escape($order->cZahlungsartName);
        $dBezahltDatum    = $db->escape($order->dBezahltDatum);
        $dVersandDatum    = $db->escape($order->dVersandt);
        if ($dVersandDatum === null || $dVersandDatum === '') {
            $dVersandDatum = '_DBNULL_';
        }
        $upd                = new stdClass();
        $upd->dVersandDatum = $dVersandDatum;
        $upd->cTracking     = $db->escape($order->cIdentCode);
        $upd->cLogistiker   = $db->escape($order->cLogistik);
        $upd->cTrackingURL  = $db->escape($trackingURL);
        $upd->cStatus       = $status;
        $upd->cVersandInfo  = $db->escape($order->cVersandInfo);
        if (strlen($cZahlungsartName) > 0) {
            $upd->cZahlungsartName = $cZahlungsartName;
        }
        $upd->dBezahltDatum = empty($dBezahltDatum)
            ? '_DBNULL_'
            : $dBezahltDatum;
        $db->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $upd);
        $updatedOrder = new Bestellung($shopOrder->kBestellung, true);
        $customer     = null;
        if ((!$shopOrder->dVersandDatum && $order->dVersandt)
            || (!$shopOrder->dBezahltDatum && $order->dBezahltDatum)
        ) {
            $tmp      = $db->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . (int)$order->kBestellung,
                \DB\ReturnType::SINGLE_OBJECT
            );
            $customer = new Kunde((int)$tmp->kKunde);
        }

        $bLieferschein = false;
        foreach ($updatedOrder->oLieferschein_arr as $oLieferschein) {
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
            $module   = gibZahlungsmodul($order->kBestellung);
            if (!isset($updatedOrder->oVersandart->cSendConfirmationMail)
                || $updatedOrder->oVersandart->cSendConfirmationMail !== 'N'
            ) {
                if ($module) {
                    $module->sendMail((int)$order->kBestellung, $cMailType);
                } else {
                    if ($customer === null) {
                        $customer = new Kunde((int)$shopOrder->kKunde);
                    }
                    $mail              = new stdClass();
                    $mail->tkunde      = $customer;
                    $mail->tbestellung = $updatedOrder;
                    sendeMail($cMailType, $mail);
                }
            }
            /** @var Lieferschein $oLieferschein */
            foreach ($updatedOrder->oLieferschein_arr as $oLieferschein) {
                $oLieferschein->setEmailVerschickt(true)->update();
            }
            // Guthaben an Bestandskunden verbuchen, Email rausschicken:
            if ($customer === null) {
                $customer = new Kunde($shopOrder->kKunde);
            }
            $oKwK = new KundenwerbenKunden();
            $oKwK->verbucheBestandskundenBoni($customer->cMail);
        }

        if (!$shopOrder->dBezahltDatum && $order->dBezahltDatum && $customer->kKunde > 0) {
            // sende Zahlungseingangmail
            $module = gibZahlungsmodul($order->kBestellung);
            if ($module) {
                $module->sendMail((int)$order->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);
            } else {
                $customer     = $customer ?? new Kunde((int)$shopOrder->kKunde);
                $updatedOrder = new Bestellung((int)$shopOrder->kBestellung, true);
                if (($updatedOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG)
                    && strlen($customer->cMail) > 0
                ) {
                    $mail              = new stdClass();
                    $mail->tkunde      = $customer;
                    $mail->tbestellung = $updatedOrder;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $mail);
                }
            }
        }
        executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESET, [
            'oBestellung'     => &$shopOrder,
            'oKunde'          => &$customer,
            'oBestellungWawi' => &$order
        ]);
    }
}

/**
 * @param $orderID
 */
function deleteOrder(int $orderID)
{
    $db     = Shop::Container()->getDB();
    $cartID = $db->select(
        'tbestellung',
        'kBestellung',
        $orderID,
        null,
        null,
        null,
        null,
        false,
        'kWarenkorb'
    );
    $db->delete('tbestellung', 'kBestellung', $orderID);
    $db->delete('tbestellid', 'kBestellung', $orderID);
    $db->delete('tbestellstatus', 'kBestellung', $orderID);
    $db->delete('tkuponbestellung', 'kBestellung', $orderID);
    $db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, UPLOAD_TYP_BESTELLUNG]);
    $db->delete('tuploadqueue', 'kBestellung', $orderID);
    if ((int)$cartID->kWarenkorb > 0) {
        $db->delete('twarenkorb', 'kWarenkorb', (int)$cartID->kWarenkorb);
        $positions = $db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$cartID->kWarenkorb,
            'kWarenkorbPos'
        );
        $db->delete('twarenkorbpos', 'kWarenkorb', (int)$cartID->kWarenkorb);
        foreach ($positions as $position) {
            $db->delete(
                'twarenkorbposeigenschaft',
                'kWarenkorbPos',
                (int)$position->kWarenkorbPos
            );
        }
    }
}

/**
 * @param int        $orderID
 * @param stdClass[] $orderAttributes
 */
function bearbeiteBestellattribute(int $orderID, $orderAttributes)
{
    $updated = [];
    $db      = Shop::Container()->getDB();
    if (is_array($orderAttributes)) {
        foreach ($orderAttributes as $orderAttributeData) {
            $orderAttribute    = (object)$orderAttributeData;
            $orderAttributeOld = $db->select(
                'tbestellattribut',
                ['kBestellung', 'cName'],
                [$orderID, $orderAttribute->key]
            );
            if (isset($orderAttributeOld->kBestellattribut)) {
                $db->update(
                    'tbestellattribut',
                    'kBestellattribut',
                    $orderAttributeOld->kBestellattribut,
                    (object)['cValue' => $orderAttribute->value]
                );
                $updated[] = $orderAttributeOld->kBestellattribut;
            } else {
                $updated[] = $db->insert('tbestellattribut', (object)[
                    'kBestellung' => $orderID,
                    'cName'       => $orderAttribute->key,
                    'cValue'      => $orderAttribute->value,
                ]);
            }
        }
    }

    if (count($updated) > 0) {
        $db->query(
            'DELETE FROM tbestellattribut
                WHERE kBestellung = ' . $orderID . '
                    AND kBestellattribut NOT IN (' . implode(', ', $updated) . ')',
            \DB\ReturnType::DEFAULT
        );
    } else {
        $db->delete('tbestellattribut', 'kBestellung', $orderID);
    }
}
