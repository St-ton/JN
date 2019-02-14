<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Rechnungsadresse;
use JTL\Shop;

/**
 * Class Orders
 * @package JTL\dbeS\Push
 */
final class Orders extends AbstractPush
{
    /**
     * @return array|string
     */
    public function getData()
    {
        $xml    = [];
        $orders = $this->db->query(
            "SELECT tbestellung.kBestellung, tbestellung.kWarenkorb, tbestellung.kKunde, tbestellung.kLieferadresse,
            tbestellung.kRechnungsadresse, tbestellung.kZahlungsart, tbestellung.kVersandart, tbestellung.kSprache, 
            tbestellung.kWaehrung, '0' AS nZahlungsTyp, tbestellung.fGuthaben, tbestellung.cSession, 
            tbestellung.cZahlungsartName, tbestellung.cBestellNr, tbestellung.cVersandInfo, tbestellung.dVersandDatum, 
            tbestellung.cTracking, tbestellung.cKommentar, tbestellung.cAbgeholt, tbestellung.cStatus, 
            date_format(tbestellung.dErstellt, \"%d.%m.%Y\") AS dErstellt_formatted, tbestellung.dErstellt, 
            tzahlungsart.cModulId, tbestellung.cPUIZahlungsdaten, tbestellung.nLongestMinDelivery, 
            tbestellung.nLongestMaxDelivery, tbestellung.fWaehrungsFaktor
            FROM tbestellung
            LEFT JOIN tzahlungsart
                ON tzahlungsart.kZahlungsart = tbestellung.kZahlungsart
            WHERE cAbgeholt = 'N'
            ORDER BY tbestellung.kBestellung
            LIMIT " . \LIMIT_BESTELLUNGEN,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (\count($orders) === 0) {
            return $xml;
        }
        foreach ($orders as $i => $order) {
            if (\strlen($order['cPUIZahlungsdaten']) > 0
                && \preg_match('/^kPlugin_(\d+)_paypalexpress$/', $order['cModulId'], $matches)
            ) {
                $orders[$i]['cModulId'] = 'za_paypal_pui_jtl';
            }

            // workaround; ACHTUNG: NUR BIS AUSSCHLIESSLICH WAWI 1.0.9.2
            /*if ($oBestellung['cModulId'] === 'za_billpay_invoice_jtl') {
                $oBestellung_arr[$i]['cModulId'] = 'za_billpay_jtl';
            }*/
        }

        $crypto          = Shop::Container()->getCryptoService();
        $orderAttributes = [];

        foreach ($orders as &$order) {
            $orderAttribute     = $this->buildAttributes($order);
            $orderID            = (int)$orderAttribute['kBestellung'];
            $order['tkampagne'] = $this->db->query(
                "SELECT tkampagne.cName, tkampagne.cParameter cIdentifier,
                COALESCE(tkampagnevorgang.cParamWert, '') cWert
                FROM tkampagnevorgang
                INNER JOIN tkampagne 
                    ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                INNER JOIN tkampagnedef 
                    ON tkampagnedef.kKampagneDef = tkampagnevorgang.kKampagneDef
                WHERE tkampagnedef.cKey = 'kBestellung'
                    AND tkampagnevorgang.kKey = " . $orderID . '
                ORDER BY tkampagnevorgang.kKampagneDef DESC LIMIT 1',
                ReturnType::SINGLE_ASSOC_ARRAY
            );

            $order['ttrackinginfo'] = $this->db->query(
                'SELECT cUserAgent, cReferer
                FROM tbesucher
                WHERE kBestellung = ' . $orderID . '
                LIMIT 1',
                ReturnType::SINGLE_ASSOC_ARRAY
            );

            $cartPositions      = $this->db->query(
                'SELECT *
                FROM twarenkorbpos
                WHERE kWarenkorb = ' . (int)$orderAttribute['kWarenkorb'],
                ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            $positionAttributes = [];
            foreach ($cartPositions as &$position) {
                $posAttribute = $this->buildAttributes($position, ['cUnique', 'kKonfigitem', 'kBestellpos']);

                $posAttribute['kBestellung']          = $orderAttribute['kBestellung'];
                $position['twarenkorbposeigenschaft'] = $this->db->query(
                    'SELECT *
                    FROM twarenkorbposeigenschaft
                    WHERE kWarenkorbPos = ' .
                    (int)$posAttribute['kWarenkorbPos'],
                    ReturnType::ARRAY_OF_ASSOC_ARRAYS
                );
                unset($posAttribute['kWarenkorb']);
                $positionAttributes[] = $posAttribute;

                $confCount = \count($position['twarenkorbposeigenschaft']);
                for ($j = 0; $j < $confCount; $j++) {
                    $idx                                        = $j . ' attr';
                    $position['twarenkorbposeigenschaft'][$idx] = $this->buildAttributes(
                        $position['twarenkorbposeigenschaft'][$j]
                    );
                }
            }
            unset($position);
            $order['twarenkorbpos'] = $cartPositions;
            foreach ($positionAttributes as $i => $attribute) {
                $order['twarenkorbpos'][$i . ' attr'] = $attribute;
            }

            $deliveryAddress        = new Lieferadresse((int)$orderAttribute['kLieferadresse']);
            $country                = $this->db->select(
                'tland',
                'cISO',
                $deliveryAddress->cLand,
                null,
                null,
                null,
                null,
                false,
                'cDeutsch'
            );
            $iso                    = $deliveryAddress->cLand;
            $deliveryAddress->cLand = isset($country) ? $country->cDeutsch : $deliveryAddress->angezeigtesLand;
            unset($deliveryAddress->angezeigtesLand);
            $address = $deliveryAddress->gibLieferadresseAssoc();
            if (\count($address) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $address['cAnrede'] = $address['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $address['cISO'] = $iso;
            }
            $attr = $this->buildAttributes($address);
            // Strasse und Hausnummer zusammenführen
            if (isset($address['cHausnummer'])) {
                $address['cStrasse'] .= ' ' . \trim($address['cHausnummer']);
            }
            $address['cStrasse'] = \trim($address['cStrasse'] ?? '');
            unset($address['cHausnummer']);
            $order['tlieferadresse']      = $address;
            $order['tlieferadresse attr'] = $attr;

            $billingAddress        = new Rechnungsadresse($orderAttribute['kRechnungsadresse']);
            $country               = $this->db->select(
                'tland',
                'cISO',
                $billingAddress->cLand,
                null,
                null,
                null,
                null,
                false,
                'cDeutsch'
            );
            $iso                   = $billingAddress->cLand;
            $billingAddress->cLand = isset($country) ? $country->cDeutsch : $billingAddress->angezeigtesLand;
            unset($billingAddress->angezeigtesLand);
            $address = $billingAddress->gibRechnungsadresseAssoc();

            if (\count($address) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $address['cAnrede'] = $address['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $address['cISO'] = $iso;
            }
            $attr = $this->buildAttributes($address);
            // Strasse und Hausnummer zusammenführen
            $address['cStrasse'] .= ' ' . \trim($address['cHausnummer'] ?? '');
            $address['cStrasse']  = \trim($address['cStrasse'] ?? '');
            unset($address['cHausnummer']);
            $order['trechnungsadresse']      = $address;
            $order['trechnungsadresse attr'] = $attr;

            $item              = $this->db->query(
                'SELECT *
                FROM tzahlungsinfo
                WHERE kBestellung = ' . $orderID . " AND cAbgeholt = 'N'
                ORDER BY kZahlungsInfo DESC LIMIT 1",
                ReturnType::SINGLE_ASSOC_ARRAY
            );
            $item['cBankName'] = isset($item['cBankName']) ? $crypto->decryptXTEA($item['cBankName']) : null;
            $item['cBLZ']      = isset($item['cBLZ']) ? $crypto->decryptXTEA($item['cBLZ']) : null;
            $item['cInhaber']  = isset($item['cInhaber']) ? $crypto->decryptXTEA($item['cInhaber']) : null;
            $item['cKontoNr']  = isset($item['cKontoNr']) ? $crypto->decryptXTEA($item['cKontoNr']) : null;
            $item['cIBAN']     = isset($item['cIBAN']) ? $crypto->decryptXTEA($item['cIBAN']) : null;
            $item['cBIC']      = isset($item['cBIC']) ? $crypto->decryptXTEA($item['cBIC']) : null;
            $item['cKartenNr'] = isset($item['cKartenNr']) ? $crypto->decryptXTEA($item['cKartenNr']) : null;
            $item['cCVV']      = isset($item['cCVV']) ? \trim($crypto->decryptXTEA($item['cCVV'])) : null;
            if (\strlen($item['cCVV']) > 4) {
                $item['cCVV'] = \substr($item['cCVV'], 0, 4);
            }
            $attr                        = $this->buildAttributes($item);
            $order['tzahlungsinfo']      = $item;
            $order['tzahlungsinfo attr'] = $attr;
            unset($orderAttribute['kVersandArt'], $orderAttribute['kWarenkorb']);

            $order['tbestellattribut'] = $this->db->query(
                'SELECT cName AS `key`, cValue AS `value`
                FROM tbestellattribut
                WHERE kBestellung = ' . $orderID,
                ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            if (\count($order['tbestellattribut']) === 0) {
                unset($order['tbestellattribut']);
            }
            $orderAttributes[] = $orderAttribute;
        }
        unset($order);
        $xml['bestellungen']['tbestellung'] = $orders;
        foreach ($orderAttributes as $i => $attribute) {
            $xml['bestellungen']['tbestellung'][$i . ' attr'] = $attribute;
        }
        $orderCount                         = \count($orders);
        $xml['bestellungen attr']['anzahl'] = $orderCount;

        return $xml;
    }
}
