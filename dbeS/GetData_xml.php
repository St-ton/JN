<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$xml_obj = [];
if (auth()) {
    $return = 0;

    $xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'] = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cAbgeholt = 'N'
            LIMIT " . LIMIT_VERFUEGBARKEITSBENACHRICHTIGUNGEN,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );

    $xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] =
        count($xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung']);
    for ($i = 0; $i < $xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl']; $i++) {
        $xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i . ' attr'] =
            buildAttributes($xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i]);
        Shop::Container()->getDB()->query(
            "UPDATE tverfuegbarkeitsbenachrichtigung
                SET cAbgeholt = 'Y'
                WHERE kVerfuegbarkeitsbenachrichtigung = " .
            (int)$xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i . ' attr']['kVerfuegbarkeitsbenachrichtigung'],
            \DB\ReturnType::DEFAULT
        );
    }
    $xml_obj['queueddata']['uploadqueue']['tuploadqueue'] = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tuploadqueue
            LIMIT ' . LIMIT_UPLOADQUEUE,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );

    $xml_obj['tuploadqueue attr']['anzahl'] = count($xml_obj['queueddata']['uploadqueue']['tuploadqueue']);
    for ($i = 0; $i < $xml_obj['tuploadqueue attr']['anzahl']; $i++) {
        $xml_obj['queueddata']['uploadqueue']['tuploadqueue'][$i . ' attr'] =
            buildAttributes($xml_obj['queueddata']['uploadqueue']['tuploadqueue'][$i]);
    }
}

if ($xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] > 0 || $xml_obj['tuploadqueue attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml_obj);
}

echo $return;
