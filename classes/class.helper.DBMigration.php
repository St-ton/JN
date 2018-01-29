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
     * @return stdClass
     */
    public static function getMySQLVersion()
    {
        static $versionInfo = null;

        if ($versionInfo === null) {
            $versionInfo = new stdClass();

            $innodbSupport = Shop::DB()->query(
                "SELECT `SUPPORT`
                    FROM information_schema.ENGINES
                    WHERE `ENGINE` = 'InnoDB'",
                NiceDB::RET_SINGLE_OBJECT
            );
            $utf8Support   = Shop::DB()->query(
                "SELECT `IS_COMPILED` FROM information_schema.COLLATIONS
                    WHERE `COLLATION_NAME` = 'utf8_unicode_ci'",
                NiceDB::RET_SINGLE_OBJECT
            );
            $innodbPath    = Shop::DB()->query('SELECT @@innodb_data_file_path AS path', NiceDB::RET_SINGLE_OBJECT);
            $innodbSize    = 'auto';

            if ($innodbPath && strpos(strtolower($innodbPath->path), 'autoextend') === false) {
                $innodbSize = 0;
                $paths      = explode(';', $innodbPath->path);
                foreach ($paths as $path) {
                    if (preg_match('/:([0-9]+)([MGTKmgtk]+)/', $path, $hits)) {
                        switch (strtoupper($hits[2])) {
                            case 'T':
                                $innodbSize += $hits[1] * 1024 * 1024 * 1024 * 1024;
                                break;
                            case 'G':
                                $innodbSize += $hits[1] * 1024 * 1024 * 1024;
                                break;
                            case 'M':
                                $innodbSize += $hits[1] * 1024 * 1024;
                                break;
                            case 'K':
                                $innodbSize += $hits[1] * 1024;
                                break;
                            default:
                                $innodbSize += $hits[1];
                        }
                    }
                }
            }

            $versionInfo->server = Shop::DB()->info();
            $versionInfo->innodb = new stdClass();

            $versionInfo->innodb->support = $innodbSupport && in_array($innodbSupport->SUPPORT, ['YES', 'DEFAULT']);
            $versionInfo->innodb->version = Shop::DB()->query("SHOW VARIABLES LIKE 'innodb_version'", NiceDB::RET_SINGLE_OBJECT)->Value;
            $versionInfo->innodb->size    = $innodbSize;
            $versionInfo->collation_utf8  = $utf8Support && strtolower($utf8Support->IS_COMPILED) === 'yes';
        }

        return $versionInfo;
    }

    /**
     * @return stdClass[]
     */
    public static function getTablesNeedMigration()
    {
        $database = Shop::DB()->getConfig()['database'];

        return Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, `TABLE_COLLATION`, `TABLE_COMMENT`
                FROM information_schema.TABLES
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` NOT LIKE 'xplugin_%'
                    AND (`ENGINE` != 'InnoDB' OR `TABLE_COLLATION` != 'utf8_unicode_ci')
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
            "SELECT `TABLE_NAME`, `ENGINE`, `TABLE_COLLATION`
                FROM information_schema.TABLES
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` NOT LIKE 'xplugin_%'
                    " . (!empty($excludeStr) ? "AND TABLE_NAME NOT IN ('" . $excludeStr . "')" : '') . "
                    AND (`ENGINE` != 'InnoDB' OR `TABLE_COLLATION` != 'utf8_unicode_ci')
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
            "SELECT `TABLE_NAME`, `ENGINE`, `TABLE_COLLATION`, `TABLE_COMMENT`
                FROM information_schema.TABLES
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` = :table
                ORDER BY `TABLE_NAME` LIMIT 1", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 1
        );
    }

    /**
     * @param string $cTable
     * @return stdClass[]
     */
    public static function getFulltextIndizes($cTable = null)
    {
        $params = ['schema' => Shop::DB()->getConfig()['database']];
        $filter = "AND `INDEX_NAME` NOT IN ('idx_tartikel_fulltext', 'idx_tartikelsprache_fulltext')";

        if (!empty($cTable)) {
            $params['table'] = $cTable;
            $filter          = "AND `TABLE_NAME` = :table";
        }

        return Shop::DB()->queryPrepared(
            "SELECT DISTINCT `TABLE_NAME`, `INDEX_NAME`
                FROM information_schema.STATISTICS
                WHERE `TABLE_SCHEMA` = :schema
                    {$filter}
                    AND `INDEX_TYPE` = 'FULLTEXT'
                    ", $params, 2
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
        $mysqlVersion = self::getMySQLVersion();
        $database     = Shop::DB()->getConfig()['database'];

        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
            $oTable = self::getTable($cTable);

            return strpos($oTable->TABLE_COMMENT, ':Migrating') !== false;
        }

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
            "SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `COLUMN_DEFAULT`, `IS_NULLABLE`
                FROM information_schema.COLUMNS
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` = :table
                    AND `CHARACTER_SET_NAME` IS NOT NULL
                    AND (`CHARACTER_SET_NAME` != 'utf8' OR `COLLATION_NAME` != 'utf8_unicode_ci')
                ORDER BY `ORDINAL_POSITION`", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 2
        );
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlAddLockInfo($oTable)
    {
        $mysqlVersion = self::getMySQLVersion();

        return version_compare($mysqlVersion->innodb->version, '5.6', '<') ? "ALTER TABLE `{$oTable->TABLE_NAME}` COMMENT = '{$oTable->TABLE_COMMENT}:Migrating'" : '';
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlClearLockInfo($oTable)
    {
        $mysqlVersion = self::getMySQLVersion();

        return version_compare($mysqlVersion->innodb->version, '5.6', '<') ? "ALTER TABLE `{$oTable->TABLE_NAME}` COMMENT = '{$oTable->TABLE_COMMENT}'" : '';
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlMoveToInnoDB($oTable)
    {
        $mysqlVersion = self::getMySQLVersion();

        if ($oTable->ENGINE !== 'InnoDB' && $oTable->TABLE_COLLATION !== 'utf8_unicode_ci') {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci' ENGINE='InnoDB'";
        } elseif ($oTable->ENGINE !== 'InnoDB') {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` ENGINE='InnoDB'";
        } else {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'";
        }

        return version_compare($mysqlVersion->innodb->version, '5.6', '<') ? $sql : $sql . ', LOCK EXCLUSIVE';
    }

    /**
     * @param stdClass $oTable
     * @param string $lineBreak
     * @return string
     */
    public static function sqlConvertUTF8($oTable, $lineBreak = '')
    {
        $mysqlVersion = self::getMySQLVersion();
        $oColumn_arr  = self::getColumnsNeedMigration($oTable->TABLE_NAME);
        $sql          = '';

        if ($oColumn_arr !== false && count($oColumn_arr) > 0) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}`$lineBreak";

            $columnChange = [];
            foreach ($oColumn_arr as $key => $oColumn) {
                $columnChange[] = "    CHANGE COLUMN `{$oColumn->COLUMN_NAME}` `{$oColumn->COLUMN_NAME}` {$oColumn->COLUMN_TYPE} CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
                    . ($oColumn->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                    . ($oColumn->IS_NULLABLE === 'NO' && $oColumn->COLUMN_DEFAULT === null ? '' : " DEFAULT " . ($oColumn->COLUMN_DEFAULT === null ? 'NULL' : "'{$oColumn->COLUMN_DEFAULT}'"));
            }

            $sql .= implode(", $lineBreak", $columnChange);

            if (version_compare($mysqlVersion->innodb->version, '5.6', '>=')) {
                $sql .= ', LOCK EXCLUSIVE';
            }
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
