<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * When the "Export CSV" button was clicked with the id $exporterId, offer a CSV download and stop execution of current
 * script. Call this function as soon as you can provide data to be exported but before any page output has been done!
 * Call this function for each CSV exporter on a page with its unique $exporterId!
 *
 * @param string $exporterId
 * @param string $csvFilename
 * @param array $arr - array of rows to be exported as csv
 * @param array $fields - array of field/column names
 */
function handleCsvExportAction ($exporterId, $csvFilename, $arr, $fields)
{
    if (validateToken() && verifyGPDataString('exportcsv') === $exporterId) {
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
