<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return        = 3;
$xml           = [];
$customerCount = 0;

if (auth()) {
    $return        = 0;
    $db            = Shop::Container()->getDB();
    $customers     = $db->query(
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
    $customerCount = count($customers);
    $cryptoService = Shop::Container()->getCryptoService();
    $attributes    = [];
    foreach ($customers as &$customer) {
        $customer['cAnrede']   = Kunde::mapSalutation($customer['cAnrede'], $customer['kSprache']);
        $customer['cNachname'] = trim($cryptoService->decryptXTEA($customer['cNachname']));
        $customer['cFirma']    = trim($cryptoService->decryptXTEA($customer['cFirma']));
        $customer['cStrasse']  = trim($cryptoService->decryptXTEA($customer['cStrasse']));
        // Strasse und Hausnummer zusammenfuehren
        $customer['cStrasse'] .= ' ' . trim($customer['cHausnummer']);
        unset($customer['cHausnummer'], $customer['cPasswort']);
        $attribute  = buildAttributes($customer);
        $additional = $customer['cZusatz'];
        unset($customer['cZusatz']);
        $customer['cZusatz']         = trim($cryptoService->decryptXTEA($additional));
        $customer['tkundenattribut'] = $db->query(
            'SELECT * 
                FROM tkundenattribut 
                WHERE kKunde = ' . (int)$attribute['kKunde'],
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $attributeCount              = count($customer['tkundenattribut']);
        for ($o = 0; $o < $attributeCount; $o++) {
            $customer['tkundenattribut'][$o . ' attr'] = buildAttributes($customer['tkundenattribut'][$o]);
        }
        $attributes[] = $attribute;
    }
    unset($customer);
    foreach ($attributes as $i => $attribute) {
        $customers[$i . ' attr'] = $attribute;
    }
    $xml['kunden']['tkunde']      = $customers;
    $xml['kunden attr']['anzahl'] = $customerCount;
}

if ($customerCount > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
