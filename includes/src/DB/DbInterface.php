<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace DB;

/**
 * Class NiceDB
 * Class for handling mysql DB
 */
interface DbInterface extends \Serializable
{

    /**
     * Database configuration
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * avoid destructer races with object cache
     *
     * @return $this
     */
    public function reInit(): DbInterface;

    /**
     * close db connection
     *
     * @return bool
     */
    public function close(): bool;

    /**
     * check if connected
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * get server version information
     *
     * @return string
     */
    public function getServerInfo(): string;

    /**
     * @return string
     */
    public function info(): string;

    /**
     * get server stats
     *
     * @return string
     */
    public function getServerStats(): string;

    /**
     * @return string
     */
    public function stats(): string;

    /**
     * @return \PDO
     */
    public function getPDO(): \PDO;

    /**
     * insert row into db
     *
     * @param string   $tableName - table name
     * @param object   $object - object to insert
     * @param int|bool $echo - true -> print statement
     * @param bool     $bExecuteHook - true -> execute corresponding hook
     * @return int - 0 if fails, PrimaryKeyValue if successful
     */
    public function insertRow($tableName, $object, $echo = false, $bExecuteHook = false): int;

    /**
     * @param string   $tableName
     * @param object   $object
     * @param int|bool $echo
     * @param bool     $bExecuteHook
     * @return int
     */
    public function insert($tableName, $object, $echo = false, $bExecuteHook = false): int;

    /**
     * update table row
     *
     * @param string           $tableName - table name
     * @param string|array     $keyname - Name of Key which should be compared
     * @param int|string|array $keyvalue - Value of Key which should be compared
     * @param object           $object - object to update with
     * @param int|bool         $echo - true -> print statement
     * @return int - -1 if fails, number of affected rows if successful
     */
    public function updateRow($tableName, $keyname, $keyvalue, $object, $echo = false): int;

    /**
     * @param string           $tableName
     * @param string|array     $keyname
     * @param string|int|array $keyvalue
     * @param object           $object
     * @param bool|int         $echo
     * @return int
     */
    public function update($tableName, $keyname, $keyvalue, $object, $echo = false): int;

    /**
     * selects all (*) values in a single row from a table - gives just one row back!
     *
     * @param string           $tableName - Tabellenname
     * @param string|array     $keyname - Name of Key which should be compared
     * @param string|int|array $keyvalue - Value of Key which should be compared
     * @param string|null      $keyname1 - Name of Key which should be compared
     * @param string|int       $keyvalue1 - Value of Key which should be compared
     * @param string|null      $keyname2 - Name of Key which should be compared
     * @param string|int       $keyvalue2 - Value of Key which should be compared
     * @param bool             $echo - true -> print statement
     * @param string           $select - the key to select
     * @return null|object - null if fails, resultObject if successful
     */
    public function selectSingleRow(
        $tableName,
        $keyname,
        $keyvalue,
        $keyname1 = null,
        $keyvalue1 = null,
        $keyname2 = null,
        $keyvalue2 = null,
        $echo = false,
        $select = '*'
    );

    /**
     * @param string            $tableName
     * @param string|array      $keyname
     * @param string|int|array  $keyvalue
     * @param string|null       $keyname1
     * @param string|int|null   $keyvalue1
     * @param string|array|null $keyname2
     * @param string|int|null   $keyvalue2
     * @param bool              $echo
     * @param string            $select
     * @return mixed
     */
    public function select(
        $tableName,
        $keyname,
        $keyvalue,
        $keyname1 = null,
        $keyvalue1 = null,
        $keyname2 = null,
        $keyvalue2 = null,
        $echo = false,
        $select = '*'
    );

    /**
     * @param string       $tableName
     * @param string|array $keys
     * @param string|array $values
     * @param string       $select
     * @param string       $orderBy
     * @param string       $limit
     * @return array
     * @throws \InvalidArgumentException
     */
    public function selectArray($tableName, $keys, $values, $select = '*', $orderBy = '', $limit = '');

    /**
     * @param string           $tableName
     * @param string|array     $keys
     * @param string|int|array $values
     * @param string           $select
     * @param string           $orderBy
     * @param string           $limit
     * @return array
     */
    public function selectAll($tableName, $keys, $values, $select = '*', $orderBy = '', $limit = '');

    /**
     * executes query and returns misc data
     *
     * @param string   $stmt - Statement to be executed
     * @param int      $return - what should be returned.
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 7  - last inserted id
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * 11 - fetch both arrays
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * @param callable $fnInfo statistic callback
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws \InvalidArgumentException
     */
    public function executeQuery($stmt, $return, $echo = false, $bExecuteHook = false, $fnInfo = null);

    /**
     * @param string   $stmt
     * @param int      $return
     * @param int|bool $echo
     * @param bool     $bExecuteHook
     * @return int|object|array
     */
    public function query($stmt, $return, $echo = false, $bExecuteHook = false);

    /**
     * executes query and returns misc data
     *
     * @param string   $stmt - Statement to be executed
     * @param array    $params - An array of values with as many elements as there are bound parameters in the SQL statement being executed
     * @param int      $return - what should be returned.
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 7  - last inserted id
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * 11 - fetch both arrays
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * @param callable $fnInfo statistic callback
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws \InvalidArgumentException
     */
    public function executeQueryPrepared(
        $stmt,
        array $params,
        $return,
        $echo = false,
        $bExecuteHook = false,
        $fnInfo = null
    );

    /**
     * @param string   $stmt
     * @param array    $params
     * @param int      $return
     * @param int|bool $echo
     * @param bool     $bExecuteHook
     * @return int|object|array
     */
    public function queryPrepared($stmt, $params, $return, $echo = false, $bExecuteHook = false, $fnINfo = null);

    /**
     * @param string $stmt
     * @param array  $params
     * @return \Generator|int
     */
    public function executeYield($stmt, array $params = []);

    /**
     * delete row from table
     *
     * @param string           $tableName - table name
     * @param string|array     $keyname - Name of Key which should be compared
     * @param string|int|array $keyvalue - Value of Key which should be compared
     * @param bool|int         $echo - true -> print statement
     * @return int - -1 if fails, #affectedRows if successful
     */
    public function deleteRow($tableName, $keyname, $keyvalue, $echo = false): int;

    /**
     * @param string           $tableName
     * @param string|array     $keyname
     * @param string|int|array $keyvalue
     * @param bool|int         $echo
     * @return int
     */
    public function delete($tableName, $keyname, $keyvalue, $echo = false): int;

    /**
     * executes a query and gives back the result
     *
     * @param string $stmt - Statement to be executed
     * @return \PDOStatement|int
     */
    public function executeExQuery($stmt);

    /**
     * @param string $stmt
     * @return \PDOStatement|int
     */
    public function exQuery($stmt);

    /**
     * Quotes a string with outer quotes for use in a query.
     *
     * @param string|bool $string
     * @return string
     */
    public function quote($string);

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string
     * @return string
     */
    public function escape($string);

    /**
     * @param string $string
     * @return string mixed
     */
    public function pdoEscape($string);

    /**
     * @param string $string
     * @return string
     */
    public function realEscape($string);

    /**
     * logger
     *
     * @param string $entry - entry to log
     * @return $this
     */
    public function writeLog($entry);

    /**
     * @return mixed
     */
    public function _getErrorCode();

    /**
     * @return mixed
     */
    public function getErrorCode();

    /**
     * @return mixed
     */
    public function _getError();

    /**
     * @return mixed
     */
    public function getError();

    /**
     * @return string
     */
    public function _getErrorMessage();

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * @return bool
     */
    public function commit(): bool;

    /**
     * @return bool
     */
    public function rollback(): bool;

    /**
     * @param string $query
     * @param array  $params
     * @return string
     */
    public function readableQuery($query, $params);
}
