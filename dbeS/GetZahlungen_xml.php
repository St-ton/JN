<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return = 3;
$xml    = [];
$count  = 0;
if (auth()) {
    $return   = 0;
    $payments = Shop::Container()->getDB()->query(
        "SELECT *, date_format(dZeit, '%d.%m.%Y') AS dZeit_formatted
            FROM tzahlungseingang
            WHERE cAbgeholt = 'N'
            ORDER BY kZahlungseingang",
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    $count    = count($payments);
    foreach ($payments as $i => $payment) {
        $payments[$i . ' attr'] = buildAttributes($payment);
        $payments[$i]           = $payment;
    }
    $xml['zahlungseingaenge']['tzahlungseingang'] = $payments;
    $xml['zahlungseingaenge attr']['anzahl']      = $count;
}
if ($count > 0) {
    zipRedirect(time() . '.jtl', $xml);
}
echo $return;
