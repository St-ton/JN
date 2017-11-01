<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $extended
 * @return array
 */
function getDBStruct($extended = false)
{
    static $dbStruct = [
        'normal' => null,
        'extended' => null,
    ];

    $dbLocked = [];
    $database = Shop::DB()->getConfig()['database'];

    if ($extended) {
        $cDBStruct_arr =& $dbStruct['extended'];
        $dbStatus      = Shop::DB()->queryPrepared(
            "SHOW OPEN TABLES
                WHERE `Database` LIKE :schema", [
                'schema' => $database,
            ], 2
        );
        if ($dbStatus) {
            foreach ($dbStatus as $oStatus) {
                if ((int)$oStatus->In_use > 0) {
                    $dbLocked[$oStatus->Table] = 1;
                }
            }
        }
    } else {
        $cDBStruct_arr =& $dbStruct['normal'];
    }

    if ($cDBStruct_arr === null) {
        $oData_arr = Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION, TABLE_ROWS, DATA_LENGTH + INDEX_LENGTH AS DATA_SIZE
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME NOT LIKE 'xplugin_%'
                ORDER BY `TABLE_NAME`", [
                'schema' => $database
            ], 2
        );
        foreach ($oData_arr as $oData) {
            $cTable = $oData->TABLE_NAME;

            if ($extended) {
                $cDBStruct_arr[$cTable]          = $oData;
                $cDBStruct_arr[$cTable]->Locked  = isset($dbLocked[$cTable]) ? $dbLocked[$cTable] : 0;
                $cDBStruct_arr[$cTable]->Columns = [];
            } else {
                $cDBStruct_arr[$cTable] = [];
            }

            $oCol_arr = Shop::DB()->queryPrepared(
                "SELECT `COLUMN_NAME`, DATA_TYPE, COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME
                    FROM information_schema.columns
                    WHERE TABLE_SCHEMA = :schema
                        AND TABLE_NAME = :table
                    ORDER BY ORDINAL_POSITION", [
                    'schema' => $database,
                    'table'  => $cTable,
                ], 2
            );
            if ($oCol_arr !== false) {
                foreach ($oCol_arr as $oCol) {
                    if ($extended) {
                        $cDBStruct_arr[$cTable]->Columns[$oCol->COLUMN_NAME] = $oCol;
                    } else {
                        $cDBStruct_arr[$cTable][] = $oCol->COLUMN_NAME;
                    }
                }
            }
        }
    }

    return $cDBStruct_arr;
}

/**
 * @return array|bool|mixed
 */
function getDBFileStruct()
{
    $cDateiPfad  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5;
    $cDateiListe = $cDateiPfad . 'dbstruct_' . JTL_VERSION . '.json';
    if (!file_exists($cDateiListe)) {
        return false;
    }
    $cJSON         = file_get_contents($cDateiListe);
    $oDBFileStruct = json_decode($cJSON);
    if (is_object($oDBFileStruct)) {
        $oDBFileStruct = get_object_vars($oDBFileStruct);
    }

    return $oDBFileStruct;
}

/**
 * @param array $cDBFileStruct_arr
 * @param array $cDBStruct_arr
 * @return array
 */
function compareDBStruct($cDBFileStruct_arr, $cDBStruct_arr)
{
    $cDBError_arr = [];
    foreach ($cDBFileStruct_arr as $cTable => $cColumn_arr) {
        if (!array_key_exists($cTable, $cDBStruct_arr)) {
            $cDBError_arr[$cTable] = 'Tabelle nicht vorhanden';
        } else {
            if (isset($cDBStruct_arr[$cTable]->ENGINE) && strcasecmp($cDBStruct_arr[$cTable]->ENGINE, 'InnoDB') !== 0) {
                $cDBError_arr[$cTable] = "Tabelle $cTable ist keine InnoDB-Tabelle";
            } elseif (isset($cDBStruct_arr[$cTable]->TABLE_COLLATION) && strpos($cDBStruct_arr[$cTable]->TABLE_COLLATION, 'utf8') !== 0) {
                $cDBError_arr[$cTable] = "Tabelle $cTable hat die falsche Kollation";
            } else {
                foreach ($cColumn_arr as $cColumn) {
                    if (!in_array($cColumn, isset($cDBStruct_arr[$cTable]->Columns) ? array_keys($cDBStruct_arr[$cTable]->Columns) : $cDBStruct_arr[$cTable], true)) {
                        $cDBError_arr[$cTable] = "Spalte $cColumn in $cTable nicht vorhanden";
                        break;
                    }
                    if (isset($cDBStruct_arr[$cTable]->Columns)
                        && $cDBStruct_arr[$cTable]->Columns[$cColumn]->COLLATION_NAME !== null
                        && $cDBStruct_arr[$cTable]->Columns[$cColumn]->COLLATION_NAME !== $cDBStruct_arr[$cTable]->TABLE_COLLATION) {
                            $cDBError_arr[$cTable] = "Inkonsistente Kollation in Spalte $cColumn";
                            break;
                    }
                }
            }
        }
    }

    return $cDBError_arr;
}

/**
 * @param string $action
 * @param array  $tables
 * @return array|bool
 */
function doDBMaintenance($action, array $tables)
{
    $tableString = implode(', ', $tables);

    switch ($action) {
        case 'optimize':
            $cmd = 'OPTIMIZE TABLE ';
            break;
        case 'analyze':
            $cmd = 'ANALYZE TABLE ';
            break;
        case 'repair':
            $cmd = 'REPAIR TABLE ';
            break;
        case 'check':
            $cmd = 'CHECK TABLE ';
            break;
        default:
            return false;
    }

    return (count($tables) > 0)
        ? Shop::DB()->query($cmd . $tableString, 2)
        : false;
}

/**
 * @param array $cDBStruct_arr
 * @return stdClass
 */
function determineEngineUpdate($cDBStruct_arr)
{
    $result = new stdClass();

    $result->tableCount = 0;
    $result->dataSize   = 0;
    $result->estimated  = [];

    foreach ($cDBStruct_arr as $cTable => $oMeta) {
        if (isset($cDBStruct_arr[$cTable]->ENGINE) && strcasecmp($cDBStruct_arr[$cTable]->ENGINE, 'InnoDB') !== 0) {
            $result->tableCount++;
            $result->dataSize += $cDBStruct_arr[$cTable]->DATA_SIZE;
        }
    }

    $result->estimated = [
        $result->tableCount * 1.60 + $result->dataSize / 1048576 * 1.15,
        $result->tableCount * 2.40 + $result->dataSize / 1048576 * 2.50,
    ];

    return $result;
}

/**
 * @param string $fileName
 * @param string[] $shopTables
 * @return string
 */
function doEngineUpdateScript($fileName, $shopTables)
{
    $nl = "\r\n";

    $database = Shop::DB()->getConfig()['database'];
    $host     = Shop::DB()->getConfig()['host'];

    $result  = '-- ' . $fileName . $nl;
    $result .= '-- ' . $nl;
    $result .= '-- @host: ' . $host . $nl;
    $result .= '-- @database: ' . $database . $nl;
    $result .= '-- @created: ' . date(DATE_RFC822) . $nl;
    $result .= '-- ' . $nl;
    $result .= '-- @important: !!! PLEASE MAKE AN BACKUP OF STRUCTURE AND DATA FOR `' . $database . '` !!!' . $nl;
    $result .= '-- ' . $nl;
    $result .= $nl;
    $result .= '-- ----------------------------------------------------------------------------------------------------' . $nl;
    $result .= '-- ' . $nl;
    $result .= 'use `' . $database . '`;' . $nl;

    $oTable_arr = Shop::DB()->queryPrepared(
        "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = :schema
                AND TABLE_NAME NOT LIKE 'xplugin_%'
                AND (`ENGINE` != 'InnoDB' OR TABLE_COLLATION != 'utf8_general_ci')
            ORDER BY `TABLE_NAME`", [
            'schema' => $database
        ], 2
    );
    foreach ($oTable_arr as $oTable) {
        if (!in_array($oTable->TABLE_NAME, $shopTables)) {
            continue;
        }

        $result .= $nl;
        $result .= '-- ' . $nl;

        if ($oTable->ENGINE !== 'InnoDB' && $oTable->TABLE_COLLATION !== 'utf8_general_ci') {
            $result .= '-- ' . 'update engine and collation for ' . $oTable->TABLE_NAME . $nl;
            $result .= '-- ' . $nl;
            $result .= "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_general_ci' ENGINE='InnoDB', LOCK EXCLUSIVE;$nl";
        } elseif ($oTable->ENGINE !== 'InnoDB') {
            $result .= '-- ' . 'update engine for ' . $oTable->TABLE_NAME . $nl;
            $result .= '-- ' . $nl;
            $result .= "ALTER TABLE `{$oTable->TABLE_NAME}` ENGINE= 'InnoDB', LOCK EXCLUSIVE;$nl";
        } else {
            $result .= '-- ' . 'update collation for ' . $oTable->TABLE_NAME . $nl;
            $result .= '-- ' . $nl;
            $result .= "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_general_ci', LOCK EXCLUSIVE;$nl";
        }

        $oColumn_arr = Shop::DB()->queryPrepared(
            "SELECT `COLUMN_NAME`, DATA_TYPE, COLUMN_TYPE, `COLUMN_DEFAULT`, IS_NULLABLE
                FROM information_schema.columns
                WHERE TABLE_SCHEMA = :schema
                    AND TABLE_NAME = :table
                    AND CHARACTER_SET_NAME IS NOT NULL
                    AND (CHARACTER_SET_NAME != 'utf8' OR COLLATION_NAME != 'utf8_general_ci')
                ORDER BY ORDINAL_POSITION", [
                'schema' => $database,
                'table'  => $oTable->TABLE_NAME,
            ], 2
        );
        if ($oColumn_arr !== false && count($oColumn_arr) > 0) {
            $result .= '-- ' . $nl;
            $result .= '-- ' . 'update character set and collation for columns in ' . $oTable->TABLE_NAME . $nl;
            $result .= '-- ' . $nl;
            $result .= "ALTER TABLE `{$oTable->TABLE_NAME}`" .$nl;

            $columChange = [];
            foreach ($oColumn_arr as $key => $oColumn) {
                $columChange[] = "    CHANGE COLUMN `{$oColumn->COLUMN_NAME}` `{$oColumn->COLUMN_NAME}` {$oColumn->COLUMN_TYPE} CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'"
                    . ($oColumn->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                    . ($oColumn->IS_NULLABLE === 'NO' && $oColumn->COLUMN_DEFAULT === null ? '' : " DEFAULT " . ($oColumn->COLUMN_DEFAULT === null ? 'NULL' : "'{$oColumn->COLUMN_DEFAULT}'"));
            }
            $result .= implode(",$nl", $columChange) . ", LOCK EXCLUSIVE;$nl";
        }
    }

    return $result;
}

/**
 * @param string $status
 * @param string $table
 * @param int $step
 * @param array $exclude
 * @return stdClass
 */
function doMigrateToInnoDB_utf8($status = 'start', $table = '', $step = 1, $exclude = [])
{
    $database   = Shop::DB()->getConfig()['database'];
    $table      = StringHandler::filterXSS($table);
    $step       = (int)$step;
    $excludeStr = implode("','", StringHandler::filterXSS($exclude));
    $result     = new stdClass();

    switch (strtolower($status)) {
        case 'start':
            $shopTables = array_keys(getDBFileStruct());
            $oTable     = Shop::DB()->queryPrepared(
                "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
                    FROM information_schema.tables
                    WHERE TABLE_SCHEMA = :schema
                        AND TABLE_NAME NOT LIKE 'xplugin_%'
                        AND TABLE_NAME NOT IN ('" . $excludeStr . "')
                        AND (`ENGINE` != 'InnoDB' OR TABLE_COLLATION != 'utf8_general_ci')
                    ORDER BY `TABLE_NAME` LIMIT 1", [
                        'schema' => $database
                    ], 1
            );

            if (is_object($oTable)) {
                if (!in_array($oTable->TABLE_NAME, $shopTables)) {
                    $exclude[] = $oTable->TABLE_NAME;
                    $result    = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                } else {
                    $result->nextTable = $oTable->TABLE_NAME;
                    $result->nextStep  = 1;
                    $result->status    = 'migrate';
                }
            } else {
                $result->nextTable = '';
                $result->status    = 'finished';
            }
            break;
        case 'migrate':
            if (!empty($table) && $step === 1) {
                // Migration Step 1...
                $oTable = Shop::DB()->queryPrepared(
                    "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION
                        FROM information_schema.tables
                        WHERE TABLE_SCHEMA = :schema
                            AND TABLE_NAME = :table
                            AND TABLE_NAME NOT IN ('" . $excludeStr . "')
                            AND (`ENGINE` != 'InnoDB' OR TABLE_COLLATION != 'utf8_general_ci')
                        ORDER BY `TABLE_NAME` LIMIT 1", [
                            'schema' => $database,
                            'table'  => $table,
                        ], 1
                );
                if (is_object($oTable)) {
                    $tableStatus = Shop::DB()->queryPrepared(
                        "SHOW OPEN TABLES
                            WHERE `Database` LIKE :schema
                                AND `Table` LIKE :table", [
                            'schema' => $database,
                            'table'  => $table,
                        ], 1
                    );

                    if (!$tableStatus || (int)$tableStatus->In_use === 0) {
                        if ($oTable->ENGINE !== 'InnoDB' && $oTable->TABLE_COLLATION !== 'utf8_general_ci') {
                            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_general_ci' ENGINE= 'InnoDB'";
                        } elseif ($oTable->ENGINE !== 'InnoDB') {
                            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` ENGINE= 'InnoDB'";
                        } else {
                            $sql = "ALTER TABLE `{$oTable->TABLE_NAME}` CHARACTER SET='utf8' COLLATE='utf8_general_ci'";
                        }
                        $sql .= ', LOCK EXCLUSIVE';

                        if (Shop::DB()->executeQuery($sql, 10)) {
                            $result->nextTable = $table;
                            $result->nextStep  = 2;
                            $result->status    = 'migrate';
                        } else {
                            $result->status = 'failure';
                        }
                    } else {
                        $result->status = 'in_use';
                    }
                } else {
                    $result = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                }
            } elseif (!empty($table) && $step === 2) {
                // Migration Step 2...
                $oColumn_arr = Shop::DB()->queryPrepared(
                    "SELECT `COLUMN_NAME`, DATA_TYPE, COLUMN_TYPE, `COLUMN_DEFAULT`, IS_NULLABLE
                        FROM information_schema.columns
                        WHERE TABLE_SCHEMA = :schema
                            AND TABLE_NAME = :table
                            AND TABLE_NAME NOT IN ('" . $excludeStr . "')
                            AND CHARACTER_SET_NAME IS NOT NULL
                            AND (CHARACTER_SET_NAME != 'utf8' OR COLLATION_NAME != 'utf8_general_ci')
                        ORDER BY ORDINAL_POSITION", [
                            'schema' => $database,
                            'table'  => $table,
                        ], 2
                );
                if ($oColumn_arr !== false && count($oColumn_arr) > 0) {
                    $tableStatus = Shop::DB()->queryPrepared(
                        "SHOW OPEN TABLES
                            WHERE `Database` LIKE :schema
                                AND `Table` LIKE :table", [
                            'schema' => $database,
                            'table'  => $table,
                        ], 1
                    );

                    if (!$tableStatus || (int)$tableStatus->In_use === 0) {
                        $sql = "ALTER TABLE `{$table}`";

                        $columChange = [];
                        foreach ($oColumn_arr as $key => $oColumn) {
                            $columChange[] = "    CHANGE COLUMN `{$oColumn->COLUMN_NAME}` `{$oColumn->COLUMN_NAME}` {$oColumn->COLUMN_TYPE} CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'"
                                . ($oColumn->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                                . ($oColumn->IS_NULLABLE === 'NO' && $oColumn->COLUMN_DEFAULT === null ? '' : " DEFAULT " . ($oColumn->COLUMN_DEFAULT === null ? 'NULL' : "'{$oColumn->COLUMN_DEFAULT}'"));
                        }
                        $sql .= implode(",", $columChange) . ', LOCK EXCLUSIVE';

                        if (Shop::DB()->executeQuery($sql, 10)) {
                            $result = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                        } else {
                            $result->status = 'failure';
                        }
                    } else {
                        $result->status = 'in_use';
                    }
                } else {
                    $result = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                }
            }

            break;
    }

    return $result;
}
