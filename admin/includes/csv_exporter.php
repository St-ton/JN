<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function exportArrayAsCSV ($arr)
{
//    header('Content-Disposition: attachment, filename=export.csv');
//    header('Content-Type: text/csv');

    $fs     = fopen('php://output', 'w');
    $fields = array_keys($arr[0]);
    fputcsv($fs, $fields);

    foreach ($arr as $i => $assoc) {
        foreach ($assoc as $name => $val) {
        }
    }
}
