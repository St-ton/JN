<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return = 3;
$xml    = [];
if (auth()) {
    $db     = Shop::Container()->getDB();
    $return = 0;

    $current = $db->query(
        "SELECT *
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cAbgeholt = 'N'
            LIMIT " . LIMIT_VERFUEGBARKEITSBENACHRICHTIGUNGEN,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );

    $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] = count($current);
    for ($i = 0; $i < $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl']; $i++) {
        $current[$i . ' attr'] = buildAttributes($current[$i]);
        $db->query(
            "UPDATE tverfuegbarkeitsbenachrichtigung
                SET cAbgeholt = 'Y'
                WHERE kVerfuegbarkeitsbenachrichtigung = " .
            (int)$current[$i . ' attr']['kVerfuegbarkeitsbenachrichtigung'],
            \DB\ReturnType::DEFAULT
        );
    }
    $xml['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'] = $current;

    $xml['queueddata']['uploadqueue']['tuploadqueue'] = $db->query(
        'SELECT *
            FROM tuploadqueue
            LIMIT ' . LIMIT_UPLOADQUEUE,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );

    $xml['tuploadqueue attr']['anzahl'] = count($xml['queueddata']['uploadqueue']['tuploadqueue']);
    for ($i = 0; $i < $xml['tuploadqueue attr']['anzahl']; $i++) {
        $xml['queueddata']['uploadqueue']['tuploadqueue'][$i . ' attr'] =
            buildAttributes($xml['queueddata']['uploadqueue']['tuploadqueue'][$i]);
    }
}

if ($xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] > 0 || $xml['tuploadqueue attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml);
}

echo $return;
