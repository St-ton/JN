<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class DBManager
 */
class DBManager
{
    /**
     * @return array
     */
    public static function getTables()
    {
        $tables = array();
        $rows   = Shop::DB()->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'", 2);

        foreach ($rows as $row) {
            $tables[] = current($row);
        }

        return $tables;
    }

    /**
     * @param string $table
     * @return array
     */
    public static function getColumns($table)
    {
        $table   = Shop::DB()->escape($table);
        $list    = array();
        $columns = Shop::DB()->query("SHOW FULL COLUMNS FROM `{$table}`", 2);

        foreach ($columns as $column) {
            $list[$column->Field] = $column;
        }

        return $list;
    }

    /**
     * @param string $table
     * @return array
     */
    public static function getIndexes($table)
    {
        $table = Shop::DB()->escape($table);

        $list    = array();
        $indexes = Shop::DB()->query("SHOW INDEX FROM `{$table}`", 2);

        foreach ($indexes as $index) {
            $list[$index->Key_name][] = $index;
        }

        return $list;
    }

    /**
     * @param string      $database
     * @param string|null $table
     * @return array|mixed
     */
    public static function getStatus($database, $table = null)
    {
        $database = Shop::DB()->escape($database);

        if ($table !== null) {
            $table = Shop::DB()->escape($table);

            return Shop::DB()->query("SHOW TABLE STATUS FROM `{$database}` WHERE name='{$table}'", 1);
        }

        $list   = array();
        $status = Shop::DB()->query("SHOW TABLE STATUS FROM `{$database}`", 2);
        foreach ($status as $s) {
            $list[$s->Name] = $s;
        }

        return $list;
    }

    /**
     * @param string $type
     * @return array
     */
    public static function parseType($type)
    {
        $result = [
            'type'     => null,
            'size'     => null,
            'unsigned' => false
        ];

        $type = explode(' ', $type);

        if (isset($type[1]) && $type[1] === 'unsigned') {
            $result['unsigned'] = true;
        }

        $type           = explode('(', $type[0]);
        $result['type'] = $type[0];

        if (isset($type[1])) {
            $result['size'] = (int) $type[1];
        }

        return $result;
    }
}
