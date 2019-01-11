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
            Shop::Container()->getDB()->update(
                'tbestellung',
                'kBestellung',
                $orderID,
                (object)['cStatus' => BESTELLUNG_STATUS_STORNO]
            );
        }
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
    $db          = Shop::Container()->getDB();
    if (count($orders) === 1) {
        $oBestellung = $orders[0];
    }
    if (!$oBestellung->kBestellung) {
        unhandledError('Error Bestellung Update. Keine kBestellung in tbestellung! XML:' . print_r($xml, true));
    }
    $oBestellungAlt    = $db->select(
        'tbestellung',
        'kBestellung',
        (int)$oBestellung->kBestellung
    );
    $oRechnungsadresse = new Rechnungsadresse($oBestellungAlt->kRechnungsadresse);
    mappe($oRechnungsadresse, $xml['tbestellung']['trechnungsadresse'], $GLOBALS['mRechnungsadresse']);
    if (!empty($oRechnungsadresse->cAnrede)) {
        $oRechnungsadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oRechnungsadresse->cAnrede);
    }
    extractStreet($oRechnungsadresse);
    if (!$oRechnungsadresse->cNachname && !$oRechnungsadresse->cFirma && !$oRechnungsadresse->cStrasse) {
        unhandledError(
            'Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' .
            print_r($xml, true)
        );
    }
    if (!$oBestellungAlt->kBestellung || trim($oBestellung->cBestellNr) !== trim($oBestellungAlt->cBestellNr)) {
        unhandledError('Fehler: Zur Bestellung ' . $oBestellung->cBestellNr .
            ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!');
    }
    $oZahlungsart = new stdClass();
    if (isset($xml['tbestellung']['cZahlungsartName']) && strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
        // Von Wawi kommt in $xml['tbestellung']['cZahlungsartName'] nur der deutsche Wert,
        // deshalb immer Abfrage auf tzahlungsart.cName
        $cZahlungsartName = $xml['tbestellung']['cZahlungsartName'];
        $oZahlungsart     = $db->executeQueryPrepared(
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
                'iso'    => Sprache::getLanguageDataByType('', (int)$oBestellung->kSprache),
                'search' => '%' . $cZahlungsartName. '%',
                'name1'  => $cZahlungsartName,
                'name2'  => $cZahlungsartName . '%',
                'name3'  => '%' . $cZahlungsartName . '%',
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }
    $cZAUpdateSQL = '';
    if (isset($oZahlungsart->kZahlungsart) && $oZahlungsart->kZahlungsart > 0) {
        $cZAUpdateSQL = ' , kZahlungsart = ' . (int)$oZahlungsart->kZahlungsart .
            ", cZahlungsartName = '" . $oZahlungsart->cName . "' ";
    }
    $correctionFactor = 1.0;
    if (isset($oBestellung->kWaehrung)) {
        $currentCurrency = $db->select('twaehrung', 'kWaehrung', $oBestellung->kWaehrung);
        $defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
        if (isset($currentCurrency->kWaehrung, $defaultCurrency->kWaehrung)) {
            $correctionFactor           = (float)$currentCurrency->fFaktor;
            $oBestellung->fGesamtsumme /= $correctionFactor;
            $oBestellung->fGuthaben    /= $correctionFactor;
        }
    }
    // Die Wawi schickt in fGesamtsumme die Rechnungssumme (Summe aller Positionen),
    // der Shop erwartet hier aber tatsächlich eine Gesamtsumme oder auch den Zahlungsbetrag
    // (Rechnungssumme abzgl. evtl. Guthaben)
    $oBestellung->fGesamtsumme -= $oBestellung->fGuthaben;
    $db->queryPrepared(
        'UPDATE tbestellung SET
            fGuthaben = :fg,
            fGesamtsumme = :total,
            cKommentar = :cmt ' . $cZAUpdateSQL . '
            WHERE kBestellung = :oid',
        [
            'fg'    => $oBestellung->fGuthaben,
            'total' => $oBestellung->fGesamtsumme,
            'cmt'   => $oBestellung->cKommentar,
            'oid'   => (int)$oBestellungAlt->kBestellung
        ],
        \DB\ReturnType::DEFAULT
    );
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
            $oLieferadresse->updateInDB();
        } else {
            $oLieferadresse->kKunde         = $oBestellungAlt->kKunde;
            $oLieferadresse->kLieferadresse = $oLieferadresse->insertInDB();
            $db->query(
                'UPDATE tbestellung
                    SET kLieferadresse = ' . (int)$oLieferadresse->kLieferadresse . '
                    WHERE kBestellung = ' . (int)$oBestellungAlt->kBestellung,
                \DB\ReturnType::DEFAULT
            );
        }
    } elseif ($oBestellungAlt->kLieferadresse > 0) {
        $db->update(
            'tbestellung',
            'kBestellung',
            (int)$oBestellungAlt->kBestellung,
            (object)['kLieferadresse' => 0]
        );
    }
    $oRechnungsadresse->updateInDB();
    $WarenkorbposAlt_arr = $db->selectAll(
        'twarenkorbpos',
        'kWarenkorb',
        (int)$oBestellungAlt->kWarenkorb
    );
    $WarenkorbposAlt_map = [];
    foreach ($WarenkorbposAlt_arr as $key => $WarenkorbposAlt) {
        $db->delete(
            'twarenkorbposeigenschaft',
            'kWarenkorbPos',
            (int)$WarenkorbposAlt->kWarenkorbPos
        );
        if ($WarenkorbposAlt->kArtikel > 0) {
            $WarenkorbposAlt_map[$WarenkorbposAlt->kArtikel] = $key;
        }
    }
    $db->delete('twarenkorbpos', 'kWarenkorb', (int)$oBestellungAlt->kWarenkorb);
    $Warenkorbpos_arr = mapArray($xml['tbestellung'], 'twarenkorbpos', $GLOBALS['mWarenkorbpos']);
    $positionCount    = count($Warenkorbpos_arr);
    for ($i = 0; $i < $positionCount; $i++) {
        $oWarenkorbposAlt = array_key_exists($Warenkorbpos_arr[$i]->kArtikel, $WarenkorbposAlt_map)
            ? $WarenkorbposAlt_arr[$WarenkorbposAlt_map[$Warenkorbpos_arr[$i]->kArtikel]]
            : null;
        unset($Warenkorbpos_arr[$i]->kWarenkorbPos);
        $Warenkorbpos_arr[$i]->kWarenkorb         = $oBestellungAlt->kWarenkorb;
        $Warenkorbpos_arr[$i]->fPreis            /= $correctionFactor;
        $Warenkorbpos_arr[$i]->fPreisEinzelNetto /= $correctionFactor;
        // persistiere nLongestMin/MaxDelivery wenn nicht von Wawi übetragen
        if (!isset($Warenkorbpos_arr[$i]->nLongestMinDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMinDelivery = $oWarenkorbposAlt->nLongestMinDelivery ?? 0;
        }
        if (!isset($Warenkorbpos_arr[$i]->nLongestMaxDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMaxDelivery = $oWarenkorbposAlt->nLongestMaxDelivery ?? 0;
        }
        $Warenkorbpos_arr[$i]->kWarenkorbPos = $db->insert(
            'twarenkorbpos',
            $Warenkorbpos_arr[$i]
        );

        if (count($Warenkorbpos_arr) < 2) {
            $Warenkorbposeigenschaft_arr = mapArray(
                $xml['tbestellung']['twarenkorbpos'],
                'twarenkorbposeigenschaft',
                $GLOBALS['mWarenkorbposeigenschaft']
            );
        } else {
            $Warenkorbposeigenschaft_arr = mapArray(
                $xml['tbestellung']['twarenkorbpos'][$i],
                'twarenkorbposeigenschaft',
                $GLOBALS['mWarenkorbposeigenschaft']
            );
        }
        foreach ($Warenkorbposeigenschaft_arr as $Warenkorbposeigenschaft) {
            unset($Warenkorbposeigenschaft->kWarenkorbPosEigenschaft);
            $Warenkorbposeigenschaft->kWarenkorbPos = $Warenkorbpos_arr[$i]->kWarenkorbPos;
            $db->insert('twarenkorbposeigenschaft', $Warenkorbposeigenschaft);
        }
    }

    if (isset($xml['tbestellung']['tbestellattribut'])) {
        bearbeiteBestellattribute(
            $oBestellung->kBestellung,
            is_assoc($xml['tbestellung']['tbestellattribut'])
                ? [$xml['tbestellung']['tbestellattribut']]
                : $xml['tbestellung']['tbestellattribut']
        );
    }
    $oModule = gibZahlungsmodul($oBestellungAlt->kBestellung);
    // neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird,
    // wenn es auch wirklich für den kunden interessant ist
    // ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
    // bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
    $emailvorlage = Emailvorlage::load(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
    $kunde        = new Kunde((int)$oBestellungAlt->kKunde);

    if ($emailvorlage !== null
        && $emailvorlage->getAktiv() === 'Y'
        && ($oBestellung->cSendeEMail === 'Y' || !isset($oBestellung->cSendeEMail))
    ) {
        if ($oModule) {
            $oModule->sendMail($oBestellungAlt->kBestellung, MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        } else {
            $bestellungTmp = new Bestellung((int)$oBestellungAlt->kBestellung, true);

            $oMail              = new stdClass();
            $oMail->tkunde      = $kunde;
            $oMail->tbestellung = $bestellungTmp;
            sendeMail(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $oMail);
        }
    }
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
    $db     = Shop::Container()->getDB();
    foreach ($orders as $order) {
        $shopOrder = $db->select('tbestellung', 'kBestellung', (int)$order->kBestellung);
        if (!isset($shopOrder->kBestellung) || $shopOrder->kBestellung <= 0) {
            continue;
        }
        $cTrackingURL = '';
        if (strlen($order->cIdentCode) > 0) {
            $cTrackingURL = $order->cLogistikURL;
            if ($shopOrder->kLieferadresse > 0) {
                $Lieferadresse = $db->query(
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
            $oBestellungUpdated = new Bestellung((int)$shopOrder->kBestellung, true);
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
        $upd->cTrackingURL  = $db->escape($cTrackingURL);
        $upd->cStatus       = $status;
        $upd->cVersandInfo  = $db->escape($order->cVersandInfo);
        if (strlen($cZahlungsartName) > 0) {
            $upd->cZahlungsartName = $cZahlungsartName;
        }
        $upd->dBezahltDatum = empty($dBezahltDatum)
            ? '_DBNULL_'
            : $dBezahltDatum;
        $db->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $upd);
        $oBestellungUpdated = new Bestellung($shopOrder->kBestellung, true);
        $kunde              = null;
        if ((!$shopOrder->dVersandDatum && $order->dVersandt)
            || (!$shopOrder->dBezahltDatum && $order->dBezahltDatum)
        ) {
            $tmp   = $db->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . (int)$order->kBestellung,
                \DB\ReturnType::SINGLE_OBJECT
            );
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

        if (!$shopOrder->dBezahltDatum && $order->dBezahltDatum && $kunde->kKunde > 0) {
            //sende Zahlungseingangmail
            $oModule = gibZahlungsmodul($order->kBestellung);
            if ($oModule) {
                $oModule->sendMail((int)$order->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);
            } else {
                $kunde              = $kunde ?? new Kunde((int)$shopOrder->kKunde);
                $oBestellungUpdated = new Bestellung((int)$shopOrder->kBestellung, true);
                if (($oBestellungUpdated->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG)
                    && strlen($kunde->cMail) > 0
                ) {
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
    $db         = Shop::Container()->getDB();
    $kWarenkorb = $db->select(
        'tbestellung',
        'kBestellung',
        $kBestellung,
        null,
        null,
        null,
        null,
        false,
        'kWarenkorb'
    );
    $db->delete('tbestellung', 'kBestellung', $kBestellung);
    $db->delete('tbestellid', 'kBestellung', $kBestellung);
    $db->delete('tbestellstatus', 'kBestellung', $kBestellung);
    $db->delete('tkuponbestellung', 'kBestellung', $kBestellung);
    $db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$kBestellung, UPLOAD_TYP_BESTELLUNG]);
    $db->delete('tuploadqueue', 'kBestellung', $kBestellung);
    if ((int)$kWarenkorb->kWarenkorb > 0) {
        $db->delete('twarenkorb', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
        $positions = $db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$kWarenkorb->kWarenkorb,
            'kWarenkorbPos'
        );
        $db->delete('twarenkorbpos', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
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
 * @param int        $kBestellung
 * @param stdClass[] $orderAttributes
 */
function bearbeiteBestellattribute(int $kBestellung, $orderAttributes)
{
    $updated = [];
    $db      = Shop::Container()->getDB();
    if (is_array($orderAttributes)) {
        foreach ($orderAttributes as $orderAttributeData) {
            $orderAttribute    = (object)$orderAttributeData;
            $orderAttributeOld = $db->select(
                'tbestellattribut',
                ['kBestellung', 'cName'],
                [$kBestellung, $orderAttribute->key]
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
                    'kBestellung' => $kBestellung,
                    'cName'       => $orderAttribute->key,
                    'cValue'      => $orderAttribute->value,
                ]);
            }
        }
    }

    if (count($updated) > 0) {
        $db->query(
            'DELETE FROM tbestellattribut
                WHERE kBestellung = ' . $kBestellung . '
                    AND kBestellattribut NOT IN (' . implode(', ', $updated) . ')',
            \DB\ReturnType::DEFAULT
        );
    } else {
        $db->delete('tbestellattribut', 'kBestellung', $kBestellung);
    }
}
