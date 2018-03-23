<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace DB;

/**
 * Class ReturnType
 *
 * Defines which result DbService returns
 *
 * @package DB
 */
abstract class ReturnType
{
    /**
     *
     */
    const DEFAULT = 4;

    /**
     * Return a single instance of \stdClass
     */
    const SINGLE_OBJECT = 1;

    /**
     * Return an array of instances of \stdClass
     */
    const ARRAY_OF_OBJECTS = 2;

    /**
     * Return the amount of affected rows as integer
     */
    const AFFECTED_ROWS = 3;

    /**
     * Return the last inserted id (note: you should only use this, if you insert one row)
     */
    const LAST_INSERTED_ID = 7;

    /**
     * Returns one result row as an assoc array
     */
    const SINGLE_ASSOC_ARRAY = 8;

    /**
     * Return the result set as an array of assoc arrays
     */
    const ARRAY_OF_ASSOC_ARRAYS = 9;

    /**
     * Returns the PDOStatement after the query was executed
     */
    const QUERYSINGLE = 10;

    /**
     * Equivalent to PDO's $stmt->fetchAll(PDO::FETCH_BOTH);
     */
    const ARRAY_OF_BOTH_ARRAYS = 11;
}
