<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return = 3;
$xml    = [];
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

    $xml['bestellungen']['tbestellung'] = $oBestellung_arr;
    if (is_array($xml['bestellungen']['tbestellung'])) {
        $cryptoService                      = Shop::Container()->getCryptoService();
        $db                                 = Shop::Container()->getDB();
        $xml['bestellungen attr']['anzahl'] = count($xml['bestellungen']['tbestellung']);
        for ($i = 0; $i < $xml['bestellungen attr']['anzahl']; $i++) {
            $xml['bestellungen']['tbestellung'][$i . ' attr'] =
                buildAttributes($xml['bestellungen']['tbestellung'][$i]);

            $xml['bestellungen']['tbestellung'][$i]['tkampagne'] = $db->query(
                "SELECT tkampagne.cName,
                        tkampagne.cParameter cIdentifier,
                        COALESCE(tkampagnevorgang.cParamWert, '') cWert
                    FROM tkampagnevorgang
                    INNER JOIN tkampagne 
                        ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                    INNER JOIN tkampagnedef 
                        ON tkampagnedef.kKampagneDef = tkampagnevorgang.kKampagneDef
                    WHERE tkampagnedef.cKey = 'kBestellung'
                        AND tkampagnevorgang.kKey = " .
                        (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . '
                    ORDER BY tkampagnevorgang.kKampagneDef DESC LIMIT 1',
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );

            $xml['bestellungen']['tbestellung'][$i]['ttrackinginfo'] = $db->query(
                'SELECT cUserAgent, cReferer
                    FROM tbesucher
                    WHERE kBestellung = ' .
                    (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . '
                    LIMIT 1',
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );

            $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'] = $db->query(
                'SELECT *
                    FROM twarenkorbpos
                    WHERE kWarenkorb = ' . (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kWarenkorb'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );

            $warenkorbpos_anz = count($xml['bestellungen']['tbestellung'][$i]['twarenkorbpos']);
            for ($o = 0; $o < $warenkorbpos_anz; $o++) {
                $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']                   =
                    buildAttributes(
                        $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o],
                        ['cUnique', 'kKonfigitem', 'kBestellpos']
                    );
                $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kBestellung']    =
                    $xml['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'];
                $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'] =
                    $db->query(
                        'SELECT *
                            FROM twarenkorbposeigenschaft
                            WHERE kWarenkorbPos = ' .
                        (int)$xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kWarenkorbPos'],
                        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
                    );
                unset($xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o . ' attr']['kWarenkorb']);
                $warenkorbposeigenschaft_anz = count(
                    $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft']
                );
                for ($j = 0; $j < $warenkorbposeigenschaft_anz; $j++) {
                    $idx = $j . ' attr';
                    $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'][$idx] =
                        buildAttributes(
                            $xml['bestellungen']['tbestellung'][$i]['twarenkorbpos'][$o]['twarenkorbposeigenschaft'][$j]
                        );
                }
            }
            $oLieferadresse        = new Lieferadresse(
                (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kLieferadresse']
            );
            $land                  = $db->select(
                'tland',
                'cISO',
                $oLieferadresse->cLand,
                null,
                null,
                null,
                null,
                false,
                'cDeutsch'
            );
            $cISO                  = $oLieferadresse->cLand;
            $oLieferadresse->cLand = isset($land) ? $land->cDeutsch : $oLieferadresse->angezeigtesLand;
            unset($oLieferadresse->angezeigtesLand);
            $xml['bestellungen']['tbestellung'][$i]['tlieferadresse'] = $oLieferadresse->gibLieferadresseAssoc();

            if (count($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cAnrede'] =
                    $xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cISO'] = $cISO;
            }
            $xml['bestellungen']['tbestellung'][$i]['tlieferadresse attr'] =
                buildAttributes($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']);
            // Strasse und Hausnummer zusammenführen
            if (isset($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer'])) {
                $xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] .= ' ' .
                    trim($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer']);
            }
            $xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] =
                trim($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cStrasse'] ?? '');
            unset($xml['bestellungen']['tbestellung'][$i]['tlieferadresse']['cHausnummer']);

            $oRechnungsadresse        = new Rechnungsadresse(
                $xml['bestellungen']['tbestellung'][$i . ' attr']['kRechnungsadresse']
            );
            $land                     = $db->select(
                'tland',
                'cISO',
                $oRechnungsadresse->cLand,
                null,
                null,
                null,
                null,
                false,
                'cDeutsch'
            );
            $cISO                     = $oRechnungsadresse->cLand;
            $oRechnungsadresse->cLand = isset($land) ? $land->cDeutsch : $oRechnungsadresse->angezeigtesLand;
            unset($oRechnungsadresse->angezeigtesLand);
            $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse'] =
                $oRechnungsadresse->gibRechnungsadresseAssoc();

            if (count($xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']) > 0) {
                // Work Around um der Wawi die ausgeschriebene Anrede mitzugeben
                $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cAnrede'] =
                    $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cAnredeLocalized'] ?? null;
                // Am Ende zusätzlich Ländercode cISO mitgeben
                $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cISO'] = $cISO;
            }
            $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse attr'] =
                buildAttributes($xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']);
            //Strasse und Hausnummer zusammenführen
            $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse'] .= ' ' .
                trim($xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cHausnummer']);
            //Trim Konkatenation
            $xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse'] =
                trim($xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cStrasse']);
            unset($xml['bestellungen']['tbestellung'][$i]['trechnungsadresse']['cHausnummer']);

            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo'] = $db->query(
                'SELECT *
                    FROM tzahlungsinfo
                    WHERE kBestellung = ' .
                        (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'] . "
                        AND cAbgeholt = 'N'
                    ORDER BY kZahlungsInfo DESC LIMIT 1",
                \DB\ReturnType::SINGLE_ASSOC_ARRAY
            );
            // Entschlüsseln
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'] =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBankName'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ']      =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBLZ'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber']  =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cInhaber'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr']  =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKontoNr'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN']     =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cIBAN'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC']      =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cBIC'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'] =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'])
                ? $cryptoService->decryptXTEA($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cKartenNr'])
                : null;
            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']      =
                isset($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'])
                ? trim($cryptoService->decryptXTEA(
                    $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']
                ))
                : null;
            if (strlen($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV']) > 4) {
                $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'] =
                    substr($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']['cCVV'], 0, 4);
            }

            $xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo attr'] =
                buildAttributes($xml['bestellungen']['tbestellung'][$i]['tzahlungsinfo']);
            unset(
                $xml['bestellungen']['tbestellung'][$i . ' attr']['kVersandArt'],
                $xml['bestellungen']['tbestellung'][$i . ' attr']['kWarenkorb']
            );

            $xml['bestellungen']['tbestellung'][$i]['tbestellattribut'] = $db->query(
                "SELECT cName AS `key`, cValue AS `value`
                    FROM tbestellattribut
                    WHERE kBestellung = " . (int)$xml['bestellungen']['tbestellung'][$i . ' attr']['kBestellung'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            if (count($xml['bestellungen']['tbestellung'][$i]['tbestellattribut']) === 0) {
                unset($xml['bestellungen']['tbestellung'][$i]['tbestellattribut']);
            }
        }
    }
}

if ($xml['bestellungen attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
