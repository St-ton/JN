<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * If the "Import CSV" button was clicked with the id $importerId, try to insert entries from the CSV file uploaded
 * into to the table $target or call a function for each row to be imported. Call this function before you read the
 * data from the table again! Make sure, the CSV contains all important fields to form a valid row in your DB-table!
 * Missing fields in the CSV will be set to the DB-tables default value if your DB is configured so.
 *
 * @param string $importerId
 * @param string|callable $target - either target table name or callback function that takes an object to be imported
 * @param string[] $fields - array of names of the fields in the order they appear in one data row. If and only if this
 *      array is empty, a header line of field names is expected, otherwise not.
 * @param string|null $delim - delimiter character or null to guess it from the first row
 * @param int $importType -
 *      0 = clear table, then import (careful!!! again: this will clear the table denoted by $target)
 *      1 = insert new, overwrite existing
 *      2 = insert only non-existing
 * @return int - -1 if importer-id-mismatch / 0 on success / >1 import error count
 */
function handleCsvImportAction($importerId, $target, $fields = [], $delim = null, $importType = 2)
{
    if (Form::validateToken() && Request::verifyGPDataString('importcsv') === $importerId) {
        if (isset($_FILES['csvfile']['type'])
            && (
                $_FILES['csvfile']['type'] === 'application/vnd.ms-excel'
                || $_FILES['csvfile']['type'] === 'text/csv'
                || $_FILES['csvfile']['type'] === 'application/csv'
                || $_FILES['csvfile']['type'] === 'application/vnd.msexcel'
            )
        ) {
            $csvFilename = $_FILES['csvfile']['tmp_name'];
            $fs          = fopen($_FILES['csvfile']['tmp_name'], 'rb');
            $nErrors     = 0;

            if ($delim === null) {
                $delim = getCsvDelimiter($csvFilename);
            }

            if (count($fields) === 0) {
                $fields = fgetcsv($fs, 0, $delim);
            }

            if (isset($_REQUEST['importType'])) {
                $importType = Request::verifyGPCDataInt('importType');
            }

            if ($importType === 0 && is_string($target)) {
                Shop::Container()->getDB()->query('TRUNCATE ' . $target, ReturnType::AFFECTED_ROWS);
            }
            $importDeleteDone = false;
            while (($row = fgetcsv($fs, 0, $delim)) !== false) {
                $obj = new stdClass();

                foreach ($fields as $i => $field) {
                    $row[$i]     = Shop::Container()->getDB()->escape($row[$i]);
                    $obj->$field = $row[$i];
                }

                if (is_callable($target)) {
                    $res = $target($obj, $importDeleteDone, $importType);

                    if ($res === false) {
                        ++$nErrors;
                    }
                } elseif (is_string($target)) {
                    $table = $target;

                    if ($importType === 0 || $importerId === 2) {
                        $res = Shop::Container()->getDB()->insert($table, $obj);

                        if ($res === 0) {
                            ++$nErrors;
                        }
                    } elseif ($importType === 1) {
                        Shop::Container()->getDB()->delete($target, $fields, $row);
                        $res = Shop::Container()->getDB()->insert($table, $obj);

                        if ($res === 0) {
                            ++$nErrors;
                        }
                    }
                }
            }

            return $nErrors;
        }

        return 1;
    }

    return -1;
}
