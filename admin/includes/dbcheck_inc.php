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

    $dbLocked     = [];
    $database     = Shop::DB()->getConfig()['database'];
    $mysqlVersion = DBMigrationHelper::getMySQLVersion();

    if ($extended) {
        $cDBStruct_arr =& $dbStruct['extended'];

        if (version_compare($mysqlVersion->innodb->version, '5.6', '>=')) {
            $dbStatus = Shop::DB()->queryPrepared(
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
        }
    } else {
        $cDBStruct_arr =& $dbStruct['normal'];
    }

    if ($cDBStruct_arr === null) {
        $oData_arr = Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, `TABLE_COLLATION`, `TABLE_ROWS`, `TABLE_COMMENT`,
                    `DATA_LENGTH` + `INDEX_LENGTH` AS DATA_SIZE
                FROM information_schema.TABLES
                WHERE `TABLE_SCHEMA` = :schema
                    AND `TABLE_NAME` NOT LIKE 'xplugin_%'
                ORDER BY `TABLE_NAME`", [
                    'schema' => $database
                ], 2
        );
        foreach ($oData_arr as $oData) {
            $cTable = $oData->TABLE_NAME;

            if ($extended) {
                $cDBStruct_arr[$cTable]          = $oData;
                $cDBStruct_arr[$cTable]->Columns = [];

                if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                    $cDBStruct_arr[$cTable]->Locked = strpos($oData->TABLE_COMMENT, ':Migrating') !== false ? 1 : 0;
                } else {
                    $cDBStruct_arr[$cTable]->Locked = isset($dbLocked[$cTable]) ? $dbLocked[$cTable] : 0;
                }
            } else {
                $cDBStruct_arr[$cTable] = [];
            }

            $oCol_arr = Shop::DB()->queryPrepared(
                "SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME`
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :schema
                        AND `TABLE_NAME` = :table
                    ORDER BY `ORDINAL_POSITION`", [
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
    $cDateiListe = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . 'dbstruct_' . JTL_VERSION . '.json';
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
    $mysqlVer = DBMigrationHelper::getMySQLVersion();

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

    $oTable_arr = DBMigrationHelper::getTablesNeedMigration();
    foreach ($oTable_arr as $oTable) {
        $fulltextSQL = [];

        if (!in_array($oTable->TABLE_NAME, $shopTables, true)) {
            continue;
        }

        if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
            // Fulltext indizes are not supported for innoDB on MySQL < 5.6
            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($oTable->TABLE_NAME);

            if ($fulltextIndizes) {
                foreach ($fulltextIndizes as $fulltextIndex) {
                    $fulltextSQL[] = "ALTER TABLE `{$oTable->TABLE_NAME}` DROP KEY `{$fulltextIndex->INDEX_NAME}`";
                }
            }
        }

        $result .= "$nl--$nl";

        if ($oTable->ENGINE !== 'InnoDB' && $oTable->TABLE_COLLATION !== 'utf8_unicode_ci') {
            $result .= "-- update engine and collation for {$oTable->TABLE_NAME}$nl";
        } elseif ($oTable->ENGINE !== 'InnoDB') {
            $result .= "-- update engine for {$oTable->TABLE_NAME}$nl";
        } else {
            $result .= "-- update collation for {$oTable->TABLE_NAME}$nl";
        }
        $result .= "--$nl";

        if (count($fulltextSQL) > 0) {
            $result .= implode(";$nl", $fulltextSQL) . ";$nl";
        }
        $result .= DBMigrationHelper::sqlMoveToInnoDB($oTable) . ";$nl";

        $sql = DBMigrationHelper::sqlConvertUTF8($oTable, $nl);
        if (!empty($sql)) {
            $result .= "--$nl";
            $result .= "-- update character set and collation for columns in {$oTable->TABLE_NAME}$nl";
            $result .= "--$nl";
            $result .= "$sql;$nl";
        }
    }

    $result .= $nl;

    if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
        // Fulltext search is not available on MySQL < 5.6
        $result .= "--$nl";
        $result .= "-- Fulltext search is not available on MySQL < 5.6$nl";
        $result .= "--$nl";
        $result .= "UPDATE `teinstellungen` SET `cWert` = 'N' WHERE `cName` = 'suche_fulltext';$nl";
        $result .= $nl;
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
    $mysqlVersion = DBMigrationHelper::getMySQLVersion();
    $table        = StringHandler::filterXSS($table);
    $step         = (int)$step;
    $result       = new stdClass();

    switch (strtolower($status)) {
        case 'start':
            $shopTables = array_keys(getDBFileStruct());
            $oTable     = DBMigrationHelper::getNextTableNeedMigration($exclude);

            if (is_object($oTable)) {
                if (!in_array($oTable->TABLE_NAME, $shopTables, true)) {
                    $exclude[] = $oTable->TABLE_NAME;
                    $result    = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                } else {
                    $result->nextTable = $oTable->TABLE_NAME;
                    $result->nextStep  = 1;
                    $result->status    = 'migrate';
                }
            } else {
                $result->nextTable = '';
                $result->status    = 'all done';
            }
            break;
        case 'migrate':
            if (!empty($table) && $step === 1) {
                // Migration Step 1...
                $oTable = DBMigrationHelper::getTable($table);
                if (is_object($oTable) && DBMigrationHelper::isTableNeedMigration($oTable) && !in_array($oTable->TABLE_NAME, $exclude, true)) {
                    if (!DBMigrationHelper::isTableInUse($table)) {
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            // If MySQL version is lower than 5.6 use alternative lock method and delete all fulltext indexes because these are not supported
                            Shop::DB()->executeQuery(DBMigrationHelper::sqlAddLockInfo($oTable), 10);
                            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($oTable->TABLE_NAME);

                            if ($fulltextIndizes) {
                                foreach ($fulltextIndizes as $fulltextIndex) {
                                    Shop::DB()->executeQuery("ALTER TABLE `{$oTable->TABLE_NAME}` DROP KEY `{$fulltextIndex->INDEX_NAME}`", 10);
                                }
                            }
                        }
                        if (Shop::DB()->executeQuery(DBMigrationHelper::sqlMoveToInnoDB($oTable), 10)) {
                            $result->nextTable = $table;
                            $result->nextStep  = 2;
                            $result->status    = 'migrate';
                        } else {
                            $result->status = 'failure';
                        }
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            Shop::DB()->executeQuery(DBMigrationHelper::sqlClearLockInfo($oTable), 10);
                        }
                    } else {
                        $result->status = 'in_use';
                    }
                } else {
                    // Get next table for migration...
                    $exclude[] = $table;
                    $result    = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                }
            } elseif (!empty($table) && $step === 2) {
                // Migration Step 2...
                if (!DBMigrationHelper::isTableInUse($table)) {
                    $oTable = DBMigrationHelper::getTable($table);
                    $sql    = DBMigrationHelper::sqlConvertUTF8($oTable);

                    if (!empty($sql)) {
                        if (Shop::DB()->executeQuery($sql, 10)) {
                            // Get next table for migration...
                            $result = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                        } else {
                            $result->status = 'failure';
                        }
                    } else {
                        // Get next table for migration...
                        $result = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                    }
                } else {
                    $result->status = 'in_use';
                }
            }

            break;
        case 'clear cache':
            // Objektcache leeren
            try {
                $cache = JTLCache::getInstance();

                if ($cache !== null) {
                    $cache->setJtlCacheConfig();
                    $cache->flushAll();
                }
            } catch (Exception $e) {
                Jtllog::writeLog('Leeren des Objektcache fehlgeschlagen! (' . $e->getMessage() . ')', JTLLOG_LEVEL_ERROR);
            }

            // Templatecache leeren
            $callback = function (array $pParameters) {
                if (!$pParameters['isdir']) {
                    @unlink($pParameters['path'] . $pParameters['filename']);
                } else {
                    @rmdir($pParameters['path'] . $pParameters['filename']);
                }
            };
            $template    = Template::getInstance();
            $templateDir = $template->getDir();
            $dirMan      = new DirManager();
            $dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $templateDir, $callback);
            $dirMan->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback);

            // Clear special category session array
            unset($_SESSION['oKategorie_arr_new']);

            // Reset Fulltext search if version is lower than 5.6
            if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                Shop::DB()->executeQuery("UPDATE `teinstellungen` SET `cWert` = 'N' WHERE `cName` = 'suche_fulltext'", 10);
            }

            $result->nextTable = '';
            $result->status    = 'finished';
            break;
    }

    return $result;
}
