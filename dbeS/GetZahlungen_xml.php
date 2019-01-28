<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return = 3;
$xml    = [];
if (auth()) {
    $return                                       = 0;
    $xml['zahlungseingaenge']['tzahlungseingang'] = Shop::Container()->getDB()->query(
        "SELECT *, date_format(dZeit, '%d.%m.%Y') AS dZeit_formatted
            FROM tzahlungseingang
            WHERE cAbgeholt = 'N'
            ORDER BY kZahlungseingang",
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    $xml['zahlungseingaenge attr']['anzahl']      = count($xml['zahlungseingaenge']['tzahlungseingang']);
    for ($i = 0; $i < $xml['zahlungseingaenge attr']['anzahl']; $i++) {
        $xml['zahlungseingaenge']['tzahlungseingang'][$i . ' attr'] =
            buildAttributes($xml['zahlungseingaenge']['tzahlungseingang'][$i]);
    }
}

if ($xml['zahlungseingaenge attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
