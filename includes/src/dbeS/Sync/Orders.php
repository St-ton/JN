<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Emailvorlage;
use JTL\Customer\Kunde;
use JTL\Customer\KundenwerbenKunden;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Lieferschein;
use JTL\Checkout\Rechnungsadresse;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\Sprache;
use stdClass;

/**
 * Class Orders
 * @package JTL\dbeS\Sync
 */
final class Orders extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'ack_bestellung.xml') !== false) {
                $this->handleACK($xml);
            } elseif (\strpos($file, 'del_bestellung.xml') !== false) {
                $this->handleDeletes($xml);
            } elseif (\strpos($file, 'delonly_bestellung.xml') !== false) {
                $this->handleDeleteOnly($xml);
            } elseif (\strpos($file, 'storno_bestellung.xml') !== false) {
                $this->handleCancelation($xml);
            } elseif (\strpos($file, 'reaktiviere_bestellung.xml') !== false) {
                $this->handleReactivated($xml);
            } elseif (\strpos($file, 'ack_zahlungseingang.xml') !== false) {
                $this->handlePaymentACK($xml);
            } elseif (\strpos($file, 'set_bestellung.xml') !== false) {
                $this->handleSet($xml);
            } elseif (\strpos($file, 'upd_bestellung.xml') !== false) {
                $this->handleUpdate($xml);
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleACK($xml): void
    {
        if (!\is_array($xml['ack_bestellungen']['kBestellung']) && (int)$xml['ack_bestellungen']['kBestellung'] > 0) {
            $xml['ack_bestellungen']['kBestellung'] = [$xml['ack_bestellungen']['kBestellung']];
        }
        if (!\is_array($xml['ack_bestellungen']['kBestellung'])) {
            return;
        }
        foreach ($xml['ack_bestellungen']['kBestellung'] as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID > 0) {
                $this->db->update('tbestellung', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
                $this->db->update(
                    'tbestellung',
                    ['kBestellung', 'cStatus'],
                    [$orderID, \BESTELLUNG_STATUS_OFFEN],
                    (object)['cStatus' => \BESTELLUNG_STATUS_IN_BEARBEITUNG]
                );
                $this->db->update('tzahlungsinfo', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
            }
        }
    }

    /**
     * @param int $orderID
     * @return bool|\PaymentMethod
     */
    private function getPaymentMethod(int $orderID)
    {
        $order = $this->db->queryPrepared(
            'SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart 
                ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = :oid
            LIMIT 1',
            ['oid' => $orderID],
            ReturnType::SINGLE_OBJECT
        );

        return $order ? \PaymentMethod::create($order->cModulId) : false;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        if (!\is_array($xml['del_bestellungen']['kBestellung'])) {
            $xml['del_bestellungen']['kBestellung'] = [$xml['del_bestellungen']['kBestellung']];
        }
        foreach ($xml['del_bestellungen']['kBestellung'] as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID > 0) {
                $module = $this->getPaymentMethod($orderID);
                if ($module) {
                    $module->cancelOrder($orderID, true);
                }
                $this->deleteOrder($orderID);
                // uploads (bestellungen)
                $this->db->delete('tuploadschema', ['kCustomID', 'nTyp'], [$orderID, 2]);
                $this->db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, 2]);
                // uploads (artikel der bestellung)
                // todo...
                // wenn unreg kunde, dann kunden auch löschen
                $this->db->query(
                    'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $orderID,
                    ReturnType::SINGLE_OBJECT
                );
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleDeleteOnly(array $xml): void
    {
        $orderIDs = \is_array($xml['del_bestellungen']['kBestellung'])
            ? $xml['del_bestellungen']['kBestellung']
            : [$xml['del_bestellungen']['kBestellung']];
        foreach ($orderIDs as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID > 0) {
                $module = $this->getPaymentMethod($orderID);
                if ($module) {
                    $module->cancelOrder($orderID, true);
                }
                $this->deleteOrder($orderID);
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleCancelation(array $xml): void
    {
        if (!\is_array($xml['storno_bestellungen']['kBestellung'])) {
            $xml['storno_bestellungen']['kBestellung'] = [$xml['storno_bestellungen']['kBestellung']];
        }
        foreach ($xml['storno_bestellungen']['kBestellung'] as $orderID) {
            $orderID  = (int)$orderID;
            $module   = $this->getPaymentMethod($orderID);
            $tmpOrder = new Bestellung($orderID);
            $customer = new Kunde($tmpOrder->kKunde);
            $tmpOrder->fuelleBestellung();
            if ($module) {
                $module->cancelOrder($orderID);
            } else {
                if (!empty($customer->cMail) && ($tmpOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_STORNO)) {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $tmpOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_STORNO, $data));
                }
                $this->db->update(
                    'tbestellung',
                    'kBestellung',
                    $orderID,
                    (object)['cStatus' => \BESTELLUNG_STATUS_STORNO]
                );
            }
            \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
                'oBestellung' => &$tmpOrder,
                'oKunde'      => &$customer,
                'oModule'     => $module
            ]);
        }
    }

    /**
     * @param array $xml
     */
    private function handleReactivated(array $xml): void
    {
        if (!\is_array($xml['reaktiviere_bestellungen']['kBestellung'])) {
            $xml['reaktiviere_bestellungen']['kBestellung'] = [$xml['reaktiviere_bestellungen']['kBestellung']];
        }
        foreach ($xml['reaktiviere_bestellungen']['kBestellung'] as $orderID) {
            $module = $this->getPaymentMethod($orderID);
            if ($module) {
                $module->reactivateOrder($orderID);
            } else {
                $tmpOrder = new Bestellung($orderID);
                $customer = new Kunde($tmpOrder->kKunde);
                $tmpOrder->fuelleBestellung();
                if (($tmpOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_STORNO) && \strlen($customer->cMail) > 0) {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $tmpOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_RESTORNO, $data));
                }
                $this->db->update(
                    'tbestellung',
                    'kBestellung',
                    $orderID,
                    (object)['cStatus' => \BESTELLUNG_STATUS_IN_BEARBEITUNG]
                );
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handlePaymentACK(array $xml): void
    {
        if (!\is_array($xml['ack_zahlungseingang']['kZahlungseingang'])
            && (int)$xml['ack_zahlungseingang']['kZahlungseingang'] > 0
        ) {
            $xml['ack_zahlungseingang']['kZahlungseingang'] = [$xml['ack_zahlungseingang']['kZahlungseingang']];
        }
        if (!\is_array($xml['ack_zahlungseingang']['kZahlungseingang'])) {
            return;
        }
        foreach ($xml['ack_zahlungseingang']['kZahlungseingang'] as $kZahlungseingang) {
            if ((int)$kZahlungseingang > 0) {
                $this->db->update(
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
    private function handleUpdate(array $xml): void
    {
        $customer = null;
        $order    = new stdClass;
        $orders   = $this->mapper->mapArray($xml, 'tbestellung', 'mBestellung');
        if (\count($orders) === 1) {
            $order = $orders[0];
        }
        if (!$order->kBestellung) {
            \syncException(
                'Keine kBestellung in tbestellung! XML:' . \print_r($xml, true),
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $oldOrder       = $this->db->select(
            'tbestellung',
            'kBestellung',
            (int)$order->kBestellung
        );
        $billingAddress = new Rechnungsadresse($oldOrder->kRechnungsadresse);
        $this->mapper->mapObject($billingAddress, $xml['tbestellung']['trechnungsadresse'], 'mRechnungsadresse');
        if (!empty($billingAddress->cAnrede)) {
            $billingAddress->cAnrede = $this->mapSalutation($billingAddress->cAnrede);
        }
        $this->extractStreet($billingAddress);
        if (!$billingAddress->cNachname && !$billingAddress->cFirma && !$billingAddress->cStrasse) {
            \syncException(
                'Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' .
                \print_r($xml, true),
                \FREIDEFINIERBARER_FEHLER
            );
        }
        if (!$oldOrder->kBestellung || \trim($order->cBestellNr) !== \trim($oldOrder->cBestellNr)) {
            \syncException(
                'Fehler: Zur Bestellung ' . $order->cBestellNr .
                ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!',
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $paymentMethod = new stdClass;
        if (isset($xml['tbestellung']['cZahlungsartName']) && \strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
            // Von Wawi kommt in $xml['tbestellung']['cZahlungsartName'] nur der deutsche Wert,
            // deshalb immer Abfrage auf tzahlungsart.cName
            $paymentMethodName = $xml['tbestellung']['cZahlungsartName'];
            $paymentMethod     = $this->db->executeQueryPrepared(
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
                    'search' => '%' . $paymentMethodName . '%',
                    'name1'  => $paymentMethodName,
                    'name2'  => $paymentMethodName . '%',
                    'name3'  => '%' . $paymentMethodName . '%',
                ],
                ReturnType::SINGLE_OBJECT
            );
        }
        $cZAUpdateSQL = '';
        if (isset($paymentMethod->kZahlungsart) && $paymentMethod->kZahlungsart > 0) {
            $cZAUpdateSQL = ' , kZahlungsart = ' . (int)$paymentMethod->kZahlungsart .
                ", cZahlungsartName = '" . $paymentMethod->cName . "' ";
        }
        $correctionFactor = 1.0;
        if (isset($order->kWaehrung)) {
            $currentCurrency = $this->db->select('twaehrung', 'kWaehrung', $order->kWaehrung);
            $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
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
        $this->db->queryPrepared(
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
            ReturnType::DEFAULT
        );
        $deliveryAddress = new Lieferadresse($oldOrder->kLieferadresse);
        $this->mapper->mapObject($deliveryAddress, $xml['tbestellung']['tlieferadresse'], 'mLieferadresse');
        if (isset($deliveryAddress->cAnrede)) {
            $deliveryAddress->cAnrede = $this->mapSalutation($deliveryAddress->cAnrede);
        }
        // Hausnummer extrahieren
        $this->extractStreet($deliveryAddress);
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
                $this->db->query(
                    'UPDATE tbestellung
                    SET kLieferadresse = ' . (int)$deliveryAddress->kLieferadresse . '
                    WHERE kBestellung = ' . (int)$oldOrder->kBestellung,
                    ReturnType::DEFAULT
                );
            }
        } elseif ($oldOrder->kLieferadresse > 0) {
            $this->db->update(
                'tbestellung',
                'kBestellung',
                (int)$oldOrder->kBestellung,
                (object)['kLieferadresse' => 0]
            );
        }
        $billingAddress->updateInDB();
        $oldPositions = $this->db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$oldOrder->kWarenkorb
        );
        $map          = [];
        foreach ($oldPositions as $key => $oldPosition) {
            $this->db->delete(
                'twarenkorbposeigenschaft',
                'kWarenkorbPos',
                (int)$oldPosition->kWarenkorbPos
            );
            if ($oldPosition->kArtikel > 0) {
                $map[$oldPosition->kArtikel] = $key;
            }
        }
        $this->db->delete('twarenkorbpos', 'kWarenkorb', (int)$oldOrder->kWarenkorb);
        $cartPositions = $this->mapper->mapArray($xml['tbestellung'], 'twarenkorbpos', 'mWarenkorbpos');
        $positionCount = \count($cartPositions);
        for ($i = 0; $i < $positionCount; $i++) {
            $oldPosition = \array_key_exists($cartPositions[$i]->kArtikel, $map)
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
            $cartPositions[$i]->kWarenkorbPos = $this->db->insert(
                'twarenkorbpos',
                $cartPositions[$i]
            );

            if (\count($cartPositions) < 2) {
                $cartPosAttributes = $this->mapper->mapArray(
                    $xml['tbestellung']['twarenkorbpos'],
                    'twarenkorbposeigenschaft',
                    'mWarenkorbposeigenschaft'
                );
            } else {
                $cartPosAttributes = $this->mapper->mapArray(
                    $xml['tbestellung']['twarenkorbpos'][$i],
                    'twarenkorbposeigenschaft',
                    'mWarenkorbposeigenschaft'
                );
            }
            foreach ($cartPosAttributes as $posAttribute) {
                unset($posAttribute->kWarenkorbPosEigenschaft);
                $posAttribute->kWarenkorbPos = $cartPositions[$i]->kWarenkorbPos;
                $this->db->insert('twarenkorbposeigenschaft', $posAttribute);
            }
        }

        if (isset($xml['tbestellung']['tbestellattribut'])) {
            $this->editAttributes(
                $order->kBestellung,
                $this->mapper->isAssoc($xml['tbestellung']['tbestellattribut'])
                    ? [$xml['tbestellung']['tbestellattribut']]
                    : $xml['tbestellung']['tbestellattribut']
            );
        }
        $module = $this->getPaymentMethod($oldOrder->kBestellung);
        // neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird,
        // wenn es auch wirklich für den kunden interessant ist
        // ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
        // bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
        $mailTPL  = Emailvorlage::load(\MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        $customer = new Kunde((int)$oldOrder->kKunde);

        if ($mailTPL !== null
            && $mailTPL->getAktiv() === 'Y'
            && ($order->cSendeEMail === 'Y' || !isset($order->cSendeEMail))
        ) {
            if ($module) {
                $module->sendMail($oldOrder->kBestellung, \MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
            } else {
                $data              = new stdClass;
                $data->tkunde      = $customer;
                $data->tbestellung = new Bestellung((int)$oldOrder->kBestellung, true);

                $mailer = Shop::Container()->get(Mailer::class);
                $mail   = new Mail();
                $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $data));
            }
        }
        \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, [
            'oBestellung'    => &$order,
            'oBestellungAlt' => &$oldOrder,
            'oKunde'         => &$customer
        ]);
    }

    /**
     * @param array $xml
     */
    private function handleSet(array $xml): void
    {
        $orders = $this->mapper->mapArray($xml['tbestellungen'], 'tbestellung', 'mBestellung');
        foreach ($orders as $order) {
            $shopOrder = $this->db->select('tbestellung', 'kBestellung', (int)$order->kBestellung);
            if (!isset($shopOrder->kBestellung) || $shopOrder->kBestellung <= 0) {
                continue;
            }
            $trackingURL = '';
            if (\strlen($order->cIdentCode) > 0) {
                $trackingURL = $order->cLogistikURL;
                if ($shopOrder->kLieferadresse > 0) {
                    $Lieferadresse = $this->db->query(
                        'SELECT cPLZ
                        FROM tlieferadresse 
                        WHERE kLieferadresse = ' . (int)$shopOrder->kLieferadresse,
                        ReturnType::SINGLE_OBJECT
                    );
                    if ($Lieferadresse->cPLZ) {
                        $trackingURL = \str_replace('#PLZ#', $Lieferadresse->cPLZ, $trackingURL);
                    }
                } else {
                    $customer    = new Kunde($shopOrder->kKunde);
                    $trackingURL = \str_replace('#PLZ#', $customer->cPLZ, $trackingURL);
                }
                $trackingURL = \str_replace('#IdentCode#', $order->cIdentCode, $trackingURL);
            }
            if ($shopOrder->cStatus === \BESTELLUNG_STATUS_STORNO) {
                $status = \BESTELLUNG_STATUS_STORNO;
            } else {
                $status = \BESTELLUNG_STATUS_IN_BEARBEITUNG;
                if (isset($order->cBezahlt) && $order->cBezahlt === 'Y') {
                    $status = \BESTELLUNG_STATUS_BEZAHLT;
                }
                if (isset($order->dVersandt) && \strlen($order->dVersandt) > 0) {
                    $status = \BESTELLUNG_STATUS_VERSANDT;
                }
                $updatedOrder = new Bestellung((int)$shopOrder->kBestellung, true);
                if ((\is_array($updatedOrder->oLieferschein_arr)
                        && \count($updatedOrder->oLieferschein_arr) > 0)
                    && (isset($order->nKomplettAusgeliefert)
                        && (int)$order->nKomplettAusgeliefert === 0)
                ) {
                    $status = \BESTELLUNG_STATUS_TEILVERSANDT;
                }
            }
            \executeHook(\HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, [
                'status'      => &$status,
                'oBestellung' => &$shopOrder
            ]);
            $cZahlungsartName = $this->db->escape($order->cZahlungsartName);
            $dBezahltDatum    = $this->db->escape($order->dBezahltDatum);
            $dVersandDatum    = $this->db->escape($order->dVersandt);
            if ($dVersandDatum === null || $dVersandDatum === '') {
                $dVersandDatum = '_DBNULL_';
            }
            $upd                = new stdClass;
            $upd->dVersandDatum = $dVersandDatum;
            $upd->cTracking     = $this->db->escape($order->cIdentCode);
            $upd->cLogistiker   = $this->db->escape($order->cLogistik);
            $upd->cTrackingURL  = $this->db->escape($trackingURL);
            $upd->cStatus       = $status;
            $upd->cVersandInfo  = $this->db->escape($order->cVersandInfo);
            if (\strlen($cZahlungsartName) > 0) {
                $upd->cZahlungsartName = $cZahlungsartName;
            }
            $upd->dBezahltDatum = empty($dBezahltDatum)
                ? '_DBNULL_'
                : $dBezahltDatum;
            $this->db->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $upd);
            $updatedOrder = new Bestellung($shopOrder->kBestellung, true);
            $customer     = null;
            if ((!$shopOrder->dVersandDatum && $order->dVersandt)
                || (!$shopOrder->dBezahltDatum && $order->dBezahltDatum)
            ) {
                $tmp      = $this->db->query(
                    'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . (int)$order->kBestellung,
                    ReturnType::SINGLE_OBJECT
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
            if (($status === \BESTELLUNG_STATUS_VERSANDT && (int)$shopOrder->cStatus !== \BESTELLUNG_STATUS_VERSANDT)
                || ($status === \BESTELLUNG_STATUS_TEILVERSANDT && $bLieferschein === true)
            ) {
                $mailType = $status === \BESTELLUNG_STATUS_VERSANDT
                    ? \MAILTEMPLATE_BESTELLUNG_VERSANDT
                    : \MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
                $module   = $this->getPaymentMethod($order->kBestellung);
                if (!isset($updatedOrder->oVersandart->cSendConfirmationMail)
                    || $updatedOrder->oVersandart->cSendConfirmationMail !== 'N'
                ) {
                    if ($module) {
                        $module->sendMail((int)$order->kBestellung, $mailType);
                    } else {
                        if ($customer === null) {
                            $customer = new Kunde((int)$shopOrder->kKunde);
                        }
                        $data              = new stdClass;
                        $data->tkunde      = $customer;
                        $data->tbestellung = $updatedOrder;

                        $mailer = Shop::Container()->get(Mailer::class);
                        $mail   = new Mail();
                        $mailer->send($mail->createFromTemplateID($mailType, $data));
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
                $module = $this->getPaymentMethod($order->kBestellung);
                if ($module) {
                    $module->sendMail((int)$order->kBestellung, \MAILTEMPLATE_BESTELLUNG_BEZAHLT);
                } else {
                    $customer     = $customer ?? new Kunde((int)$shopOrder->kKunde);
                    $updatedOrder = new Bestellung((int)$shopOrder->kBestellung, true);
                    if (($updatedOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_EINGANG)
                        && \strlen($customer->cMail) > 0
                    ) {
                        $data              = new stdClass;
                        $data->tkunde      = $customer;
                        $data->tbestellung = $updatedOrder;

                        $mailer = Shop::Container()->get(Mailer::class);
                        $mail   = new Mail();
                        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_BEZAHLT, $data));
                    }
                }
            }
            \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITESET, [
                'oBestellung'     => &$shopOrder,
                'oKunde'          => &$customer,
                'oBestellungWawi' => &$order
            ]);
        }
    }

    /**
     * @param int $orderID
     */
    private function deleteOrder(int $orderID): void
    {
        $cartID = $this->db->select(
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
        $this->db->delete('tbestellung', 'kBestellung', $orderID);
        $this->db->delete('tbestellid', 'kBestellung', $orderID);
        $this->db->delete('tbestellstatus', 'kBestellung', $orderID);
        $this->db->delete('tkuponbestellung', 'kBestellung', $orderID);
        $this->db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, \UPLOAD_TYP_BESTELLUNG]);
        $this->db->delete('tuploadqueue', 'kBestellung', $orderID);
        if ((int)$cartID->kWarenkorb > 0) {
            $this->db->delete('twarenkorb', 'kWarenkorb', (int)$cartID->kWarenkorb);
            $positions = $this->db->selectAll(
                'twarenkorbpos',
                'kWarenkorb',
                (int)$cartID->kWarenkorb,
                'kWarenkorbPos'
            );
            $this->db->delete('twarenkorbpos', 'kWarenkorb', (int)$cartID->kWarenkorb);
            foreach ($positions as $position) {
                $this->db->delete(
                    'twarenkorbposeigenschaft',
                    'kWarenkorbPos',
                    (int)$position->kWarenkorbPos
                );
            }
        }
    }

    /**
     * @param int         $orderID
     * @param stdClass[] $orderAttributes
     */
    private function editAttributes(int $orderID, $orderAttributes): void
    {
        $updated = [];
        if (\is_array($orderAttributes)) {
            foreach ($orderAttributes as $orderAttributeData) {
                $orderAttribute    = (object)$orderAttributeData;
                $orderAttributeOld = $this->db->select(
                    'tbestellattribut',
                    ['kBestellung', 'cName'],
                    [$orderID, $orderAttribute->key]
                );
                if (isset($orderAttributeOld->kBestellattribut)) {
                    $this->db->update(
                        'tbestellattribut',
                        'kBestellattribut',
                        $orderAttributeOld->kBestellattribut,
                        (object)['cValue' => $orderAttribute->value]
                    );
                    $updated[] = $orderAttributeOld->kBestellattribut;
                } else {
                    $updated[] = $this->db->insert('tbestellattribut', (object)[
                        'kBestellung' => $orderID,
                        'cName'       => $orderAttribute->key,
                        'cValue'      => $orderAttribute->value,
                    ]);
                }
            }
        }

        if (\count($updated) > 0) {
            $this->db->query(
                'DELETE FROM tbestellattribut
                WHERE kBestellung = ' . $orderID . '
                    AND kBestellattribut NOT IN (' . \implode(', ', $updated) . ')',
                ReturnType::DEFAULT
            );
        } else {
            $this->db->delete('tbestellattribut', 'kBestellung', $orderID);
        }
    }
}
