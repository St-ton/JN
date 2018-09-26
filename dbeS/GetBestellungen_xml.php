<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$xml_obj = [];
if (auth()) {
    $return          = 0;
    $oBestellung_arr = Shop::Container()->getDB()->query(
        "SELECT tbestellung.kBestellung, tbestellung.kWarenkorb, tbestellung.kKunde, tbestellung.kLieferadresse,
            tbestellung.kRechnungsadresse,  tbestellung.kZahlungsart, tbestellung.kVersandart, tbestellung.kSprache, 
            tbestellung.kWaehrung, '0' AS nZahlungsTyp, tbestellung.fGuthaben,  tbestellung.cSession, 
            tbestellung.cZahlungsartName, tbestellung.cBestellNr, tbestellung.cVersandInfo, tbestellung.dVersandDatum, 
            tbestellung.cTracking, tbestellung.cKommentar, tbestellung.cAbgeholt, tbestellung.cStatus, 
            date_format(tbestellung.dErstellt, \"%d.%m.%Y\") AS dErstellt_formatted,  tbestellung.dErstellt, 
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

    foreach ($oBestellung_arr as $i => $oBestellung) {
        if (strlen($oBestellung['cPUIZahlungsdaten']) > 0
            && preg_match('/^kPlugin_(\d+)_paypalexpress$/', $oBestellung['cModulId'], $matches)
        ) {
            $oBestellung_arr[$i]['cModulId'] = 'za_paypal_pui_jtl';
        }

        // workaround; ACHTUNG: NUR BIS AUSSCHLIESSLICH WAWI 1.0.9.2
        /*if ($oBestellung['cModulId'] === 'za_billpay_invoice_jtl') {
            $oBestellung_arr[$i]['cModulId'] = 'za_billpay_jtl';
        }*/
    }

    $xml_obj['bestellungen']['tbestellung'] = $oBestellung_arr;

    if (is_array($xml_obj['bestellungen']['tbestellung'])) {
        $cryptoService                          = Shop::Container()->getCryptoService();
        $xml_obj['bestellungen attr']['anzahl'] = count($xml_obj['bestellungen']['tbestellung']);
        for ($i = 0; $i < $xml_obj['bestellungen attr']['anzahl']; $i++) {
            $xml_obj['bestellungen']['tbestellung'][$i . ' attr'] = buildAttributes($xml_obj['bestellungen']['tbestellung'][$i]);

            $xml_obj['bestellungen']['tbestellung'][$i]['tkampagne'] = Shop::Container()->getDB()->query(
                "SELECT tkampagne.cName,
                        tkampagne.cParameter cIdentifier,
                        COALESCE(tkampagnevorgang.cParamWert, '') cWert
                    FROM tkampagnevorgang
                    INNER JOIN tkampagne ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                    INNER JOIN tkampagnedef ON tkampagnedef.kKampagneDef = tkampagnevorgang.kKampagneDef
                    WHERE tkampagnedef.cKey = 'kBestellung'
                        AND tkampagnevorgang.kKey = " . (int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . "
                    ORDER BY tkampagnevorgang.kKampagneDef DESC LIMIT 1",
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );

            $xml_obj['bestellungen']['tbestellung'][$i]['ttrackinginfo'] = Shop::Container()->getDB()->query(
                "SELECT cUserAgent, cReferer
                    FROM tbesucher
                    WHERE kBestellung = " . (int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . "
                    LIMIT 1",
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );

            $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'] = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM twarenkorbpos
                    WHERE kWarenkorb = " . (int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kWarenkorb'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            $warenkorbpos_anz                                            = count($xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos']);
            for ($o = 0; $o < $warenkorbpos_anz; $o++) {
                $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']                   = buildAttributes(
                    $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o],
                    ['cUnique', 'kKonfigitem', 'kBestellpos']
                );
                $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kBestellung']    = $xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'];
                $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'] = Shop::Container()->getDB()->query(
                    "SELECT *
                        FROM twarenkorbposeigenschaft
                        WHERE kWarenkorbPos = " . (int)$xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kWarenkorbPos'],
                    \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
                );
                unset($xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kWarenkorb']);
                $warenkorbposeigenschaft_anz = count($xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft']);
                for ($j = 0; $j < $warenkorbposeigenschaft_anz; $j++) {
                    $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'][$j . ' attr'] = buildAttributes(
                        $xml_obj['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'][$j]
                    );
                }
            }
            $oLieferadresse        = new Lieferadresse((int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kLieferadresse']);
            $land                  = Shop::Container()->getDB()->select(
                'tland',
                'cISO', $oLieferadresse->cLand,
                null, null,
                null, null,
                false,
                'cDeutsch'
            );
            $cISO                  = $oLieferadresse->cLand;
            $oLieferadresse->cLand = isset($land) ? $land->cDeutsch : $oLieferadresse->angezeigtesLand;
            unset($oLieferadresse->angezeigtesLand);
            $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse'] = $oLieferadresse->gibLieferadresseAssoc();

            if (count($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cAnrede'] =
                    $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cISO'] = $cISO;
            }
            $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse attr'] = buildAttributes($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']);
            //Strasse und Hausnummer zusammenführen
            if (isset($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer'])) {
                $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] .= ' ' . trim($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer']);
            }
            //Trim Konkatenation
            $xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] = trim($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] ?? '');
            unset($xml_obj['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer']);

            $oRechnungsadresse        = new Rechnungsadresse($xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kRechnungsadresse']);
            $land                     = Shop::Container()->getDB()->select(
                'tland',
                'cISO', $oRechnungsadresse->cLand,
                null, null,
                null, null,
                false,
                'cDeutsch'
            );
            $cISO                     = $oRechnungsadresse->cLand;
            $oRechnungsadresse->cLand = isset($land) ? $land->cDeutsch : $oRechnungsadresse->angezeigtesLand;
            unset($oRechnungsadresse->angezeigtesLand);
            $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse'] = $oRechnungsadresse->gibRechnungsadresseAssoc();

            if (count($xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cAnrede'] =
                    $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cISO'] = $cISO;
            }
            $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse attr'] = buildAttributes($xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']);
            //Strasse und Hausnummer zusammenführen
            $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse'] .= ' ' . trim($xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cHausnummer']);
            //Trim Konkatenation
            $xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse'] = trim($xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse']);
            unset($xml_obj['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cHausnummer']);

            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo'] = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tzahlungsinfo
                    WHERE kBestellung = " . (int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . "
                        AND cAbgeholt = 'N'
                    ORDER BY kZahlungsInfo DESC LIMIT 1",
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );
            // Entschlüsseln
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'] = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ']      = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber']  = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr']  = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN']     = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC']      = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'] = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'])
                ? $cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'])
                : null;
            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']      = isset($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'])
                ? trim($cryptoService->decryptXTEA($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']))
                : null;
            if (strlen($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']) > 4) {
                $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'] =
                    substr($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'], 0, 4);
            }

            $xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo attr'] = buildAttributes($xml_obj['bestellungen']['tbestellung'][$i]['tzahlungsinfo']);
            unset($xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kVersandArt'], $xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kWarenkorb']);

            $xml_obj['bestellungen']['tbestellung'][$i]['tbestellattribut'] = Shop::Container()->getDB()->query(
                "SELECT cName AS 'key', cValue AS 'value'
                    FROM tbestellattribut
                    WHERE kBestellung = " . (int)$xml_obj['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            if (count($xml_obj['bestellungen']['tbestellung'][$i]['tbestellattribut']) === 0) {
                unset($xml_obj['bestellungen']['tbestellung'][$i]['tbestellattribut']);
            }
        }
    }
}

if ($xml_obj['bestellungen attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml_obj);
}
echo $return;
