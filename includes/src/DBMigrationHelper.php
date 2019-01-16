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
    public const IN_USE  = 'in_use';
    public const SUCCESS = 'success';
    public const FAILURE = 'failure';

    public const MIGRATE_NONE   = 0x0000;
    public const MIGRATE_INNODB = 0x0001;
    public const MIGRATE_UTF8   = 0x0002;
    public const MIGRATE_TEXT   = 0x0004;
    public const MIGRATE_C_UTF8 = 0x00A0;
    public const MIGRATE_TABLE  = self::MIGRATE_INNODB | self::MIGRATE_UTF8;
    public const MIGRATE_COLUMN = self::MIGRATE_C_UTF8 | self::MIGRATE_TEXT;

    /**
     * @return stdClass
     */
    public static function getMySQLVersion()
    {
        static $versionInfo = null;

        if ($versionInfo === null) {
            $db          = Shop::Container()->getDB();
            $versionInfo = new stdClass();

            $innodbSupport = $db->query(
                "SELECT `SUPPORT`
                    FROM information_schema.ENGINES
                    WHERE `ENGINE` = 'InnoDB'",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $utf8Support   = $db->query(
                "SELECT `IS_COMPILED` FROM information_schema.COLLATIONS
                    WHERE `COLLATION_NAME` = 'utf8_unicode_ci'",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $innodbPath    = $db->query(
                'SELECT @@innodb_data_file_path AS path',
                \DB\ReturnType::SINGLE_OBJECT
            );
            $innodbSize    = 'auto';

            if ($innodbPath && stripos($innodbPath->path, 'autoextend') === false) {
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

            $versionInfo->server = $db->info();
            $versionInfo->innodb = new stdClass();

            $versionInfo->innodb->support = $innodbSupport
                && in_array($innodbSupport->SUPPORT, ['YES', 'DEFAULT'], true);
            $versionInfo->innodb->version = $db->query(
                "SHOW VARIABLES LIKE 'innodb_version'",
                \DB\ReturnType::SINGLE_OBJECT
            )->Value;
            $versionInfo->innodb->size    = $innodbSize;
            $versionInfo->collation_utf8  = $utf8Support && strtolower($utf8Support->IS_COMPILED) === 'yes';
        }

        return $versionInfo;
    }

    /**
     * @return stdClass[]
     */
    public static function getTablesNeedMigration(): array
    {
        $database = Shop::Container()->getDB()->getConfig()['database'];

        return Shop::Container()->getDB()->queryPrepared(
            "SELECT t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`, t.`TABLE_COMMENT`
                , COUNT(c.COLUMN_NAME) TEXT_FIELDS
                , COUNT(IF(c.COLLATION_NAME = 'utf8_unicode_ci', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c 
                    ON c.TABLE_NAME = t.TABLE_NAME
                    AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                    AND (c.COLUMN_TYPE = 'text' OR c.COLLATION_NAME != 'utf8_unicode_ci')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` NOT LIKE 'xplugin_%'
                    AND (t.`ENGINE` != 'InnoDB' 
                           OR t.`TABLE_COLLATION` != 'utf8_unicode_ci' 
                           OR c.COLLATION_NAME != 'utf8_unicode_ci' 
                           OR c.COLUMN_TYPE = 'text')
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`, t.`TABLE_COMMENT`
                ORDER BY t.`TABLE_NAME`;",
            ['schema' => $database],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string[] $excludeTables
     * @return stdClass
     */
    public static function getNextTableNeedMigration($excludeTables = [])
    {
        $database   = Shop::Container()->getDB()->getConfig()['database'];
        $excludeStr = implode("','", StringHandler::filterXSS($excludeTables));

        return Shop::Container()->getDB()->queryPrepared(
            "SELECT t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`, t.`TABLE_COMMENT`
                , COUNT(c.COLUMN_NAME) TEXT_FIELDS
                , COUNT(IF(c.COLLATION_NAME = 'utf8_unicode_ci', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c 
                    ON c.TABLE_NAME = t.TABLE_NAME
                    AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                    AND (c.COLUMN_TYPE = 'text' OR c.COLLATION_NAME != 'utf8_unicode_ci')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` NOT LIKE 'xplugin_%'
                    " . (!empty($excludeStr) ? "AND t.`TABLE_NAME` NOT IN ('" . $excludeStr . "')" : '') . "
                    AND (t.`ENGINE` != 'InnoDB' 
                        OR t.`TABLE_COLLATION` != 'utf8_unicode_ci' 
                        OR c.COLLATION_NAME != 'utf8_unicode_ci' 
                        OR c.COLUMN_TYPE = 'text')
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`
                ORDER BY t.`TABLE_NAME` LIMIT 1",
            ['schema' => $database],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @param string $cTable
     * @return stdClass
     */
    public static function getTable($cTable)
    {
        $database = Shop::Container()->getDB()->getConfig()['database'];

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`, t.`TABLE_COMMENT`
                , COUNT(c.COLUMN_NAME) TEXT_FIELDS
                , COUNT(IF(c.COLLATION_NAME = \'utf8_unicode_ci\', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c 
                    ON c.TABLE_NAME = t.TABLE_NAME
                    AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                    AND (c.COLUMN_TYPE = \'text\' OR c.COLLATION_NAME != \'utf8_unicode_ci\')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` = :table
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, t.`TABLE_COLLATION`, t.`TABLE_COMMENT`
                ORDER BY t.`TABLE_NAME` LIMIT 1',
            ['schema' => $database, 'table'  => $cTable,],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @param string $cTable
     * @return stdClass[]
     */
    public static function getFulltextIndizes($cTable = null): array
    {
        $params = ['schema' => Shop::Container()->getDB()->getConfig()['database']];
        $filter = "AND `INDEX_NAME` NOT IN ('idx_tartikel_fulltext', 'idx_tartikelsprache_fulltext')";

        if (!empty($cTable)) {
            $params['table'] = $cTable;
            $filter          = 'AND `TABLE_NAME` = :table';
        }

        return Shop::Container()->getDB()->queryPrepared(
            "SELECT DISTINCT `TABLE_NAME`, `INDEX_NAME`
                FROM information_schema.STATISTICS
                WHERE `TABLE_SCHEMA` = :schema
                    {$filter}
                    AND `INDEX_TYPE` = 'FULLTEXT'",
            $params,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string|stdClass $oTable
     * @return int
     */
    public static function isTableNeedMigration($oTable): int
    {
        $result = self::MIGRATE_NONE;

        if (is_string($oTable)) {
            $oTable = self::getTable($oTable);
        }

        if (is_object($oTable)) {
            if ($oTable->ENGINE !== 'InnoDB') {
                $result |= self::MIGRATE_INNODB;
            }
            if ($oTable->TABLE_COLLATION !== 'utf8_unicode_ci') {
                $result |= self::MIGRATE_UTF8;
            }
            if (isset($oTable->TEXT_FIELDS) && (int)$oTable->TEXT_FIELDS > 0) {
                $result |= self::MIGRATE_TEXT;
            }
            if (isset($oTable->FIELD_COLLATIONS) && (int)$oTable->FIELD_COLLATIONS > 0) {
                $result |= self::MIGRATE_C_UTF8;
            }
        }

        return $result;
    }

    /**
     * @param string $cTable
     * @return bool
     */
    public static function isTableInUse($cTable): bool
    {
        $mysqlVersion = self::getMySQLVersion();
        $database     = Shop::Container()->getDB()->getConfig()['database'];

        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
            $oTable = self::getTable($cTable);

            return strpos($oTable->TABLE_COMMENT, ':Migrating') !== false;
        }

        $tableStatus = Shop::Container()->getDB()->queryPrepared(
            'SHOW OPEN TABLES
                WHERE `Database` LIKE :schema
                    AND `Table` LIKE :table',
            ['schema' => $database, 'table'  => $cTable,],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return is_object($tableStatus) && (int)$tableStatus->In_use > 0;
    }

    /**
     * @param string $cTable
     * @return stdClass[]
     */
    public static function getColumnsNeedMigration(string $cTable): array
    {
        $database = Shop::Container()->getDB()->getConfig()['database'];

        return Shop::Container()->getDB()->queryPrepared(
            "SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `COLUMN_DEFAULT`, `IS_NULLABLE`
                FROM information_schema.COLUMNS
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` = :table
                    AND `CHARACTER_SET_NAME` IS NOT NULL
                    AND (`CHARACTER_SET_NAME` != 'utf8' 
                       OR `COLLATION_NAME` != 'utf8_unicode_ci' 
                       OR COLUMN_TYPE = 'text')
                ORDER BY `ORDINAL_POSITION`",
            ['schema' => $database, 'table'  => $cTable],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlAddLockInfo($oTable): string
    {
        $mysqlVersion = self::getMySQLVersion();

        return version_compare($mysqlVersion->innodb->version, '5.6', '<')
            ? "ALTER TABLE `{$oTable->TABLE_NAME}` COMMENT = '{$oTable->TABLE_COMMENT}:Migrating'"
            : '';
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlClearLockInfo($oTable): string
    {
        $mysqlVersion = self::getMySQLVersion();

        return version_compare($mysqlVersion->innodb->version, '5.6', '<')
            ? "ALTER TABLE `{$oTable->TABLE_NAME}` COMMENT = '{$oTable->TABLE_COMMENT}'"
            : '';
    }

    /**
     * @param stdClass $oTable
     * @return string
     */
    public static function sqlMoveToInnoDB($oTable): string
    {
        $mysqlVersion = self::getMySQLVersion();

        if (!isset($oTable->Migration)) {
            $oTable->Migration = self::isTableNeedMigration($oTable);
        }

        if (($oTable->Migration & self::MIGRATE_TABLE) === self::MIGRATE_TABLE) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci' ENGINE='InnoDB'";
        } elseif (($oTable->Migration & self::MIGRATE_INNODB) === self::MIGRATE_INNODB) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` ENGINE='InnoDB'";
        } elseif (($oTable->Migration & self::MIGRATE_UTF8) === self::MIGRATE_UTF8) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'";
        } else {
            return '';
        }

        return version_compare($mysqlVersion->innodb->version, '5.6', '<')
            ? $sql
            : $sql . ', LOCK EXCLUSIVE';
    }

    /**
     * @param stdClass $oTable
     * @param string $lineBreak
     * @return string
     */
    public static function sqlConvertUTF8($oTable, $lineBreak = ''): string
    {
        $mysqlVersion = self::getMySQLVersion();
        $columns      = self::getColumnsNeedMigration($oTable->TABLE_NAME);
        $sql          = '';
        if ($columns !== false && count($columns) > 0) {
            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}`$lineBreak";

            $columnChange = [];
            foreach ($columns as $key => $col) {
                /* Workaround for quoted values in MariaDB >= 10.2.7 Fix: SHOP-2593 */
                if ($col->COLUMN_DEFAULT === 'NULL' || $col->COLUMN_DEFAULT === "'NULL'") {
                    $col->COLUMN_DEFAULT = null;
                }
                if ($col->COLUMN_DEFAULT !== null) {
                    $col->COLUMN_DEFAULT = trim($col->COLUMN_DEFAULT, '\'');
                }

                if ($col->COLUMN_TYPE === 'text') {
                    $col->COLUMN_TYPE = 'MEDIUMTEXT';
                }

                $columnChange[] = "    CHANGE COLUMN `{$col->COLUMN_NAME}` `{$col->COLUMN_NAME}` "
                    ."{$col->COLUMN_TYPE} CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
                    . ($col->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                    . ($col->IS_NULLABLE === 'NO' && $col->COLUMN_DEFAULT === null ? '' : ' DEFAULT '
                        . ($col->COLUMN_DEFAULT === null ? 'NULL' : "'{$col->COLUMN_DEFAULT}'"));
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
    public static function migrateToInnoDButf8(string $cTable): string
    {
        $oTable = self::getTable($cTable);
        if (self::isTableInUse($oTable->TABLE_NAME)) {
            return self::IN_USE;
        }

        $migration = self::isTableNeedMigration($oTable);
        if (($migration & self::MIGRATE_TEXT) !== self::MIGRATE_NONE) {
            $sql = self::sqlMoveToInnoDB($oTable);
            if (!empty($sql) && !Shop::Container()->getDB()->executeQuery($sql, \DB\ReturnType::QUERYSINGLE)) {
                return self::FAILURE;
            }
        }
        if (($migration & self::MIGRATE_COLUMN) !== self::MIGRATE_NONE) {
            $sql = self::sqlConvertUTF8($oTable);
            if (!empty($sql) && !Shop::Container()->getDB()->executeQuery($sql, \DB\ReturnType::QUERYSINGLE)) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
