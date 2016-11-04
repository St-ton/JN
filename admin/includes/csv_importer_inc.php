<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * When the "Import CSV" button was clicked with the id $importerId, try to insert entries from the CSV file uploaded
 * into to the table $cTable. Call this function before you read the data from the table again!
 *
 * @param string $importerId
 * @param string $cTable
 */
function handleCsvImportAction ($importerId, $cTable)
{
    if (validateToken() && verifyGPDataString('importcsv') === $importerId) {
        if (isset($_FILES['csvfile']['type']) && $_FILES['csvfile']['type'] === 'text/csv') {
            $csvFilename = $_FILES['csvfile']['tmp_name'];
            $cDelim      = guessCsvDelimiter($csvFilename);
            $fs          = fopen($_FILES['csvfile']['tmp_name'], 'r');
            $row         = fgetcsv($fs, 0, $cDelim);
            $fields      = $row;
            $nErrors     = 0;

            while($row = fgetcsv($fs, 0, $cDelim)) {
                $obj = new stdClass();

                foreach ($fields as $i => $field) {
                    $obj->$field = $row[$i];
                }

                $res = Shop::DB()->insert($cTable, $obj);

                if ($res === 0) {
                    $nErrors ++;
                }
            }
        }
    }
}
