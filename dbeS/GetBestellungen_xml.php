<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return     = 3;
$xml        = [];
$orderCount = 0;
if (auth()) {
    $return = 0;
    $orders = Shop::Container()->getDB()->query(
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
            LIMIT " . LIMIT_BESTELLUNGEN,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    foreach ($orders as $i => $order) {
        if (strlen($order['cPUIZahlungsdaten']) > 0
            && preg_match('/^kPlugin_(\d+)_paypalexpress$/', $order['cModulId'], $matches)
        ) {
            $orders[$i]['cModulId'] = 'za_paypal_pui_jtl';
        }

        // workaround; ACHTUNG: NUR BIS AUSSCHLIESSLICH WAWI 1.0.9.2
        /*if ($oBestellung['cModulId'] === 'za_billpay_invoice_jtl') {
            $oBestellung_arr[$i]['cModulId'] = 'za_billpay_jtl';
        }*/
    }

    $cryptoService = Shop::Container()->getCryptoService();
    $db            = Shop::Container()->getDB();
    $orderAttributes = [];

    foreach ($orders as &$order) {
        $orderAttribute     = buildAttributes($order);
        $orderID            = (int)$orderAttribute['kBestellung'];
        $order['tkampagne'] = $db->query(
            "SELECT tkampagne.cName, tkampagne.cParameter cIdentifier, COALESCE(tkampagnevorgang.cParamWert, '') cWert
                FROM tkampagnevorgang
                INNER JOIN tkampagne 
                    ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                INNER JOIN tkampagnedef 
                    ON tkampagnedef.kKampagneDef = tkampagnevorgang.kKampagneDef
                WHERE tkampagnedef.cKey = 'kBestellung'
                    AND tkampagnevorgang.kKey = " . $orderID . '
                ORDER BY tkampagnevorgang.kKampagneDef DESC LIMIT 1',
            \DB\ReturnType::SINGLE_ASSOC_ARRAY
        );

        $order['ttrackinginfo'] = $db->query(
            'SELECT cUserAgent, cReferer
                FROM tbesucher
                WHERE kBestellung = ' . $orderID . '
                LIMIT 1',
            \DB\ReturnType::SINGLE_ASSOC_ARRAY
        );

        $cartPositions      = $db->query(
            'SELECT *
                FROM twarenkorbpos
                WHERE kWarenkorb = ' . (int)$orderAttribute['kWarenkorb'],
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $positionAttributes = [];
        foreach ($cartPositions as &$position) {
            $posAttribute = buildAttributes($position, ['cUnique', 'kKonfigitem', 'kBestellpos']);

            $posAttribute['kBestellung']          = $orderAttribute['kBestellung'];
            $position['twarenkorbposeigenschaft'] = $db->query(
                'SELECT *
                    FROM twarenkorbposeigenschaft
                    WHERE kWarenkorbPos = ' .
                (int)$posAttribute['kWarenkorbPos'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            unset($posAttribute['kWarenkorb']);
            $positionAttributes[] = $posAttribute;

            $confCount = count($position['twarenkorbposeigenschaft']);
            for ($j = 0; $j < $confCount; $j++) {
                $idx                                        = $j . ' attr';
                $position['twarenkorbposeigenschaft'][$idx] = buildAttributes(
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
        $country                = $db->select(
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
        if (count($address) > 0) {
            // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
            $address['cAnrede'] = $address['cAnredeLocalized'] ?? null;
            // Am Ende zusätzlich Ländercode cISO mitgeben
            $address['cISO'] = $iso;
        }
        $attr = buildAttributes($address);
        // Strasse und Hausnummer zusammenführen
        if (isset($address['cHausnummer'])) {
            $address['cStrasse'] .= ' ' . trim($address['cHausnummer']);
        }
        $address['cStrasse'] = trim($address['cStrasse'] ?? '');
        unset($address['cHausnummer']);
        $order['tlieferadresse']      = $address;
        $order['tlieferadresse attr'] = $attr;

        $billingAddress        = new Rechnungsadresse($orderAttribute['kRechnungsadresse']);
        $country               = $db->select(
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

        if (count($address) > 0) {
            // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
            $address['cAnrede'] = $address['cAnredeLocalized'] ?? null;
            // Am Ende zusätzlich Ländercode cISO mitgeben
            $address['cISO'] = $iso;
        }
        $attr = buildAttributes($address);
        // Strasse und Hausnummer zusammenführen
        $address['cStrasse'] .= ' ' . trim($address['cHausnummer'] ?? '');
        $address['cStrasse'] = trim($address['cStrasse'] ?? '');
        unset($address['cHausnummer']);
        $order['trechnungsadresse']      = $address;
        $order['trechnungsadresse attr'] = $attr;

        $item = $db->query(
            'SELECT *
                FROM tzahlungsinfo
                WHERE kBestellung = ' . $orderID . " AND cAbgeholt = 'N'
                ORDER BY kZahlungsInfo DESC LIMIT 1",
            \DB\ReturnType::SINGLE_ASSOC_ARRAY
        );
        $item['cBankName'] = isset($item['cBankName']) ? $cryptoService->decryptXTEA($item['cBankName']) : null;
        $item['cBLZ']      = isset($item['cBLZ']) ? $cryptoService->decryptXTEA($item['cBLZ']) : null;
        $item['cInhaber']  = isset($item['cInhaber']) ? $cryptoService->decryptXTEA($item['cInhaber']) : null;
        $item['cKontoNr']  = isset($item['cKontoNr']) ? $cryptoService->decryptXTEA($item['cKontoNr']) : null;
        $item['cIBAN']     = isset($item['cIBAN']) ? $cryptoService->decryptXTEA($item['cIBAN']) : null;
        $item['cBIC']      = isset($item['cBIC']) ? $cryptoService->decryptXTEA($item['cBIC']) : null;
        $item['cKartenNr'] = isset($item['cKartenNr']) ? $cryptoService->decryptXTEA($item['cKartenNr']) : null;
        $item['cCVV']      = isset($item['cCVV']) ? trim($cryptoService->decryptXTEA($item['cCVV'])) : null;
        if (strlen($item['cCVV']) > 4) {
            $item['cCVV'] = substr($item['cCVV'], 0, 4);
        }
        $attr = buildAttributes($item);
        $order['tzahlungsinfo']      = $item;
        $order['tzahlungsinfo attr'] = $attr;
        unset($orderAttribute['kVersandArt'], $orderAttribute['kWarenkorb']);

        $order['tbestellattribut'] = $db->query(
            "SELECT cName AS `key`, cValue AS `value`
                FROM tbestellattribut
                WHERE kBestellung = " . $orderID,
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (count($order['tbestellattribut']) === 0) {
            unset($order['tbestellattribut']);
        }
        $orderAttributes[] = $orderAttribute;
    }
    unset($order);
    $xml['bestellungen']['tbestellung'] = $orders;
    foreach ($orderAttributes as $i => $attribute) {
        $xml['bestellungen']['tbestellung'][$i . ' attr'] = $attribute;
    }
    $orderCount = count($orders);
    $xml['bestellungen attr']['anzahl'] = $orderCount;
}

if ($orderCount > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
