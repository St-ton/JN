<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * If the "Import CSV" button was clicked with the id $importerId, try to insert entries from the CSV file uploaded
 * into to the table $target or call a function for each row to be imported. Call this function before you read the
 * data from the table again! Make sure, the CSV contains all important fields to form a valid row in your DB-table!
 * Missing fields in the CSV will be set to the DB-tables default value if your DB is configured so.
 *
 * @param string $importerId
 * @param string|callable $target - either target table name or callback function that takes an object to be
 *      imported
 * @param string[] $fields - array of names of the fields in the order they appear in one data row. If and only if this
 *      array is empty, a header line of field names is expected, otherwise not.
 * @param string|null $cDelim - delimiter character or null to guess it from the first row
 * @return int - -1 if importer-id-mismatch / 0 on success / >1 import error count
 */
function handleCsvImportAction ($importerId, $target, $fields = [], $cDelim = null)
{
    if (validateToken() && verifyGPDataString('importcsv') === $importerId) {
        if (isset($_FILES['csvfile']['type']) && $_FILES['csvfile']['type'] === 'text/csv') {
            $csvFilename = $_FILES['csvfile']['tmp_name'];
            $fs          = fopen($_FILES['csvfile']['tmp_name'], 'r');
            $nErrors     = 0;

            if ($cDelim === null) {
                $cDelim = guessCsvDelimiter($csvFilename);
            }

            if (count($fields) === 0) {
                $row    = fgetcsv($fs, 0, $cDelim);
                $fields = $row;
            }

            while($row = fgetcsv($fs, 0, $cDelim)) {
                $obj = new stdClass();

                foreach ($fields as $i => $field) {
                    $obj->$field = $row[$i];
                }

                if (is_callable($target)) {
                    $res = $target($obj);

                    if ($res === false) {
                        $nErrors ++;
                    }
                } else {
                    $cTable = $target;
                    $res    = Shop::DB()->insert($cTable, $obj);

                    if ($res === 0) {
                        $nErrors ++;
                    }
                }
            }

            return $nErrors;
        }

        return 1;
    }

    return -1;
}
