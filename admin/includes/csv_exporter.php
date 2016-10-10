<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function handleCsvExportAction ($exporterId, $arr, $fields)
{
    if (validateToken() && verifyGPDataString('exportcsv') === $exporterId) {
        $csvFilename = verifyGPDataString('csvFilename') ?: 'export.csv';
        header('Content-Disposition: attachment; filename=' . $csvFilename);
        header('Content-Type: text/csv');
        $fs = fopen('php://output', 'w');
        fputcsv($fs, $fields);

        foreach ($arr as $elem) {
            $csvRow = [];
            foreach ($fields as $field) {
                $csvRow[] = (string)$elem->$field;
            }
            fputcsv($fs, $csvRow);
        }
        exit();
    }
}
