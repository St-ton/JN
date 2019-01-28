<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return = 3;
$xml    = [];

if (auth()) {
    $return                  = 0;
    $db                      = Shop::Container()->getDB();
    $xml['kunden']['tkunde'] = $db->query(
        "SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
            cNachname, cFirma, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, cLand, cTel,
            cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
            cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
            date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert, cZusatz
            FROM tkunde
                WHERE cAbgeholt = 'N'
                ORDER BY kKunde LIMIT " . LIMIT_KUNDEN,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    if (is_array($xml['kunden']['tkunde']) && count($xml['kunden']['tkunde']) > 0) {
        $cryptoService                = Shop::Container()->getCryptoService();
        $xml['kunden attr']['anzahl'] = count($xml['kunden']['tkunde']);
        for ($i = 0; $i < $xml['kunden attr']['anzahl']; ++$i) {
            $xml['kunden']['tkunde'][$i]['cAnrede']   = Kunde::mapSalutation(
                $xml['kunden']['tkunde'][$i]['cAnrede'],
                $xml['kunden']['tkunde'][$i]['kSprache']
            );
            $xml['kunden']['tkunde'][$i]['cNachname'] = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][$i]['cNachname'])
            );
            $xml['kunden']['tkunde'][$i]['cFirma']    = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][$i]['cFirma'])
            );
            $xml['kunden']['tkunde'][$i]['cStrasse']  = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][$i]['cStrasse'])
            );
            // Strasse und Hausnummer zusammenfuehren
            $xml['kunden']['tkunde'][$i]['cStrasse'] .= ' ' . trim($xml['kunden']['tkunde'][$i]['cHausnummer']);
            unset($xml['kunden']['tkunde'][$i]['cHausnummer'], $xml['kunden']['tkunde'][$i]['cPasswort']);
            $xml['kunden']['tkunde'][$i . ' attr'] = buildAttributes($xml['kunden']['tkunde'][$i]);
            $cZusatz                               = $xml['kunden']['tkunde'][$i]['cZusatz'];
            unset($xml['kunden']['tkunde'][$i]['cZusatz']);
            $xml['kunden']['tkunde'][$i]['cZusatz']         = trim($cryptoService->decryptXTEA($cZusatz));
            $xml['kunden']['tkunde'][$i]['tkundenattribut'] = $db->query(
                'SELECT * 
                    FROM tkundenattribut 
                    WHERE kKunde = ' . (int)$xml['kunden']['tkunde'][$i . ' attr']['kKunde'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );

            $attributeCount = count($xml['kunden']['tkunde'][$i]['tkundenattribut']);
            for ($o = 0; $o < $attributeCount; $o++) {
                $xml['kunden']['tkunde'][$i]['tkundenattribut'][$o . ' attr'] =
                    buildAttributes($xml['kunden']['tkunde'][$i]['tkundenattribut'][$o]);
            }
        }
    }
}

if (isset($xml['kunden attr']['anzahl']) && $xml['kunden attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
