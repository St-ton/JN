<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class DBMigration
 */
class DBMigrationHelper
{
    const IN_USE  = 'in_use';
    const SUCCESS = 'success';
    const FAILURE = 'failure';

    /**
     * @return stdClass[]
     */
    public static function getTablesNeedMigration()
    {
        $database = Shop::DB()->getConfig()['database'];

        return Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME NOT LIKE 'xplugin_%'
                    AND (`ENGINE` != 'InnoDB' OR TABLE_COLLATION != 'utf8_unicode_ci')
                ORDER BY `TABLE_NAME`", [
                    'schema' => $database
                ], 2
        );
    }

    /**
     * @param string[] $excludeTables
     * @return stdClass
     */
    public static function getNextTableNeedMigration($excludeTables = [])
    {
        $database   = Shop::DB()->getConfig()['database'];
        $excludeStr = implode("','", StringHandler::filterXSS($excludeTables));

        return Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME NOT LIKE 'xplugin_%'
                    " . (!empty($excludeStr) ? "AND TABLE_NAME NOT IN ('" . $excludeStr . "')" : '') . "
                    AND (`ENGINE` != 'InnoDB' OR TABLE_COLLATION != 'utf8_unicode_ci')
                ORDER BY `TABLE_NAME` LIMIT 1", [
                    'schema' => $database
                ], 1
        );
    }

    /**
     * @param string $cTable
     * @return stdClass
     */
    public static function getTable($cTable)
    {
        $database = Shop::DB()->getConfig()['database'];

        return Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME = :table
                ORDER BY `TABLE_NAME` LIMIT 1", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 1
        );
    }

    /**
     * @param string|stdClass $oTable
     * @return bool
     */
    public static function isTableNeedMigration($oTable)
    {
        if (is_string($oTable)) {
            $oTable = self::getTable($oTable);
        }

        return (is_object($oTable) && ($oTable->ENGINE != 'InnoDB' || $oTable->TABLE_COLLATION != 'utf8_unicode_ci'));
    }

    /**
     * @param string $cTable
     * @return bool
     */
    public static function isTableInUse($cTable)
    {
        $database    = Shop::DB()->getConfig()['database'];
        $tableStatus = Shop::DB()->queryPrepared(
            "SHOW OPEN TABLES
                WHERE `Database` LIKE :schema
                    AND `Table` LIKE :table", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 1
        );

        return is_object($tableStatus) && (int)$tableStatus->In_use > 0;
    }

    /**
     * @param string $cTable
     * @return stdClass[]
     */
    public static function getColumnsNeedMigration($cTable)
    {
        $database = Shop::DB()->getConfig()['database'];

        return Shop::DB()->queryPrepared(
            "SELECT `COLUMN_NAME`, DATA_TYPE, COLUMN_TYPE, `COLUMN_DEFAULT`, IS_NULLABLE
                FROM information_schema.columns
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME = :table
                    AND CHARACTER_SET_NAME IS NOT NULL
                    AND (CHARACTER_SET_NAME != 'utf8' OR COLLATION_NAME != 'utf8_unicode_ci')
                ORDER BY ORDINAL_POSITION", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 2
        );
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlMoveToInnoDB($oTable)
    {
        if ($oTable->ENGINE !== 'InnoDB' && $oTable->TABLE_COLLATION !== 'utf8_unicode_ci') {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci' ENGINE= 'InnoDB'";
        } elseif ($oTable->ENGINE !== 'InnoDB') {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` ENGINE= 'InnoDB'";
        } else {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'";
        }

        return $sql . ', LOCK EXCLUSIVE';
    }

    /**
     * @param stdClass $oTable
     * @param string $lineBreak
     * @return string
     */
    public static function sqlConvertUTF8($oTable, $lineBreak = '')
    {
        $oColumn_arr = self::getColumnsNeedMigration($oTable->TABLE_NAME);
        $sql         = '';

        if ($oColumn_arr !== false && count($oColumn_arr) > 0) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}`$lineBreak";

            $columChange = [];
            foreach ($oColumn_arr as $key => $oColumn) {
                $columChange[] = "    CHANGE COLUMN `{$oColumn->COLUMN_NAME}` `{$oColumn->COLUMN_NAME}` {$oColumn->COLUMN_TYPE} CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
                    . ($oColumn->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                    . ($oColumn->IS_NULLABLE === 'NO' && $oColumn->COLUMN_DEFAULT === null ? '' : " DEFAULT " . ($oColumn->COLUMN_DEFAULT === null ? 'NULL' : "'{$oColumn->COLUMN_DEFAULT}'"));
            }
            $sql .= implode(", $lineBreak", $columChange) . ', LOCK EXCLUSIVE';
        }

        return $sql;
    }

    /**
     * @param string $cTable
     * @return string - SUCCESS, FAILURE or IN_USE
     */
    public static function migrateToInnoDButf8($cTable)
    {
        $oTable = self::getTable($cTable);
        if (self::isTableInUse($oTable->TABLE_NAME)) {
            return self::IN_USE;
        }

        if (self::isTableNeedMigration($oTable)) {
            $sql = self::sqlMoveToInnoDB($oTable);
            if (Shop::DB()->executeQuery($sql, 10)) {
                $sql = self::sqlConvertUTF8($oTable);
                if (empty($sql) || Shop::DB()->executeQuery($sql, 10)) {
                    return self::SUCCESS;
                }
            }
        } else {
            $sql = self::sqlConvertUTF8($oTable);
            if (empty($sql) || Shop::DB()->executeQuery($sql, 10)) {
                return self::SUCCESS;
            }
        }

        return self::FAILURE;
    }
}
