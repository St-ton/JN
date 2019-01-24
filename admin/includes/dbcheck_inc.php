<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $extended
 * @param bool $clearCache
 * @return array
 */
function getDBStruct(bool $extended = false, bool $clearCache = false)
{
    static $dbStruct = [
        'normal'   => null,
        'extended' => null,
    ];

    $db           = Shop::Container()->getDB();
    $dbLocked     = [];
    $database     = $db->getConfig()['database'];
    $mysqlVersion = DBMigrationHelper::getMySQLVersion();

    if ($clearCache) {
        if (Shop::Container()->getCache()->isActive()) {
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE . '_getDBStruct']);
        } else {
            \Session\Backend::set('getDBStruct_extended', false);
            \Session\Backend::set('getDBStruct_normal', false);
        }
        $dbStruct['extended'] = null;
        $dbStruct['normal']   = null;
    }

    if ($extended) {
        $cacheID = 'getDBStruct_extended';
        if ($dbStruct['extended'] === null) {
            $dbStruct['extended'] = Shop::Container()->getCache()->isActive()
                ? Shop::Container()->getCache()->get($cacheID)
                : \Session\Backend::get($cacheID, false);
        }
        $cDBStruct_arr =& $dbStruct['extended'];

        if (version_compare($mysqlVersion->innodb->version, '5.6', '>=')) {
            $dbStatus = $db->queryPrepared(
                'SHOW OPEN TABLES
                    WHERE `Database` LIKE :schema',
                ['schema' => $database],
                \DB\ReturnType::ARRAY_OF_OBJECTS
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
        $cacheID = 'getDBStruct_normal';
        if ($dbStruct['normal'] === null) {
            $dbStruct['normal'] = Shop::Container()->getCache()->isActive()
                ? Shop::Container()->getCache()->get($cacheID)
                : \Session\Backend::get($cacheID);
        }
        $cDBStruct_arr =& $dbStruct['normal'];
    }

    if ($cDBStruct_arr === false) {
        $oData_arr = $db->queryPrepared(
            "SELECT t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH` AS DATA_SIZE,
                    COUNT(c.COLUMN_NAME) TEXT_FIELDS,
                    COUNT(IF(c.COLLATION_NAME = 'utf8_unicode_ci', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c ON c.TABLE_NAME = t.TABLE_NAME
                                                       AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                                                       AND (c.COLUMN_TYPE = 'text' 
                                                          OR c.COLLATION_NAME != 'utf8_unicode_ci')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` NOT LIKE 'xplugin_%'
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH`
                ORDER BY t.`TABLE_NAME`",
            ['schema' => $database],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($oData_arr as $oData) {
            $cTable = $oData->TABLE_NAME;

            if ($extended) {
                $cDBStruct_arr[$cTable]            = $oData;
                $cDBStruct_arr[$cTable]->Columns   = [];
                $cDBStruct_arr[$cTable]->Migration = DBMigrationHelper::MIGRATE_NONE;

                if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                    $cDBStruct_arr[$cTable]->Locked = strpos($oData->TABLE_COMMENT, ':Migrating') !== false ? 1 : 0;
                } else {
                    $cDBStruct_arr[$cTable]->Locked = $dbLocked[$cTable] ?? 0;
                }
            } else {
                $cDBStruct_arr[$cTable] = [];
            }

            $oCol_arr = $db->queryPrepared(
                'SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME`
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :schema
                        AND `TABLE_NAME` = :table
                    ORDER BY `ORDINAL_POSITION`',
                [
                    'schema' => $database,
                    'table'  => $cTable
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
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
            if ($extended) {
                $cDBStruct_arr[$cTable]->Migration = DBMigrationHelper::isTableNeedMigration($oData);
            }
        }
        if (Shop::Container()->getCache()->isActive()) {
            Shop::Container()->getCache()->set(
                $cacheID,
                $cDBStruct_arr,
                [CACHING_GROUP_CORE, CACHING_GROUP_CORE . '_getDBStruct']
            );
        } else {
            \Session\Backend::set($cacheID, $cDBStruct_arr);
        }
    } elseif ($extended) {
        foreach (array_keys($cDBStruct_arr) as $cTable) {
            $cDBStruct_arr[$cTable]->Locked = $dbLocked[$cTable] ?? 0;
        }
    }

    return $cDBStruct_arr;
}

/**
 * @return array
 */
function getDBFileStruct()
{
    $version    = \JTLShop\SemVer\Parser::parse(APPLICATION_VERSION);
    $versionStr = $version->getMajor().'-'.$version->getMinor().'-'.$version->getPatch();

    if ($version->hasPreRelease()) {
        $preRelease  = $version->getPreRelease();
        $versionStr .= '-'.$preRelease->getGreek();
        if ($preRelease->getReleaseNumber() > 0) {
            $versionStr .= '-'.$preRelease->getReleaseNumber();
        }
    }

    $cDateiListe = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . 'dbstruct_' . $versionStr . '.json';
    if (!file_exists($cDateiListe)) {
        return [];
    }
    $cJSON         = file_get_contents($cDateiListe);
    $oDBFileStruct = json_decode($cJSON);
    if (is_object($oDBFileStruct)) {
        $oDBFileStruct = get_object_vars($oDBFileStruct);
    }

    return $oDBFileStruct;
}

/**
 * @param array $dbFileStruct
 * @param array $dbStruct
 * @return array
 */
function compareDBStruct(array $dbFileStruct, array $dbStruct)
{
    $errors = [];
    foreach ($dbFileStruct as $table => $columns) {
        if (!array_key_exists($table, $dbStruct)) {
            $errors[$table] = __('errorNoTable');
            continue;
        }
        if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_INNODB) === DBMigrationHelper::MIGRATE_INNODB) {
            $errors[$table] = $table . __('errorNoInnoTable');
            continue;
        }
        if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
            $errors[$table] = $table . __('errorWrongCollation');
            continue;
        }

        foreach ($columns as $cColumn) {
            if (!in_array($cColumn, isset($dbStruct[$table]->Columns)
                ? array_keys($dbStruct[$table]->Columns)
                : $dbStruct[$table], true)
            ) {
                $errors[$table] = sprintf(__('errorRowMissing'), $cColumn, $table);
                break;
            }

            if (isset($dbStruct[$table]->Columns[$cColumn])) {
                if (!empty($dbStruct[$table]->Columns[$cColumn]->COLLATION_NAME)
                    && $dbStruct[$table]->Columns[$cColumn]->COLLATION_NAME !== 'utf8_unicode_ci'
                ) {
                    $errors[$table] = sprintf(__('errorWrongCollationRow'), $cColumn);
                    break;
                }
                if ($dbStruct[$table]->Columns[$cColumn]->DATA_TYPE === 'text') {
                    $errors[$table] = __('errorDatatTypeInRow') . $cColumn;
                    break;
                }
            }
        }
    }

    return $errors;
}

/**
 * @param string $action
 * @param array  $tables
 * @return array|bool
 */
function doDBMaintenance(string $action, array $tables)
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

    return count($tables) > 0
        ? Shop::Container()->getDB()->query($cmd . $tableString, \DB\ReturnType::ARRAY_OF_OBJECTS)
        : false;
}

/**
 * @param array $dbStruct
 * @return stdClass
 */
function determineEngineUpdate(array $dbStruct)
{
    $result = new stdClass();

    $result->tableCount = 0;
    $result->dataSize   = 0;
    $result->estimated  = [];

    foreach ($dbStruct as $table => $meta) {
        if (isset($dbStruct[$table]->Migration)
            && $dbStruct[$table]->Migration !== DBMigrationHelper::MIGRATE_NONE
        ) {
            $result->tableCount++;
            $result->dataSize += $dbStruct[$table]->DATA_SIZE;
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
function doEngineUpdateScript(string $fileName, array $shopTables)
{
    $nl = "\r\n";

    $database = Shop::Container()->getDB()->getConfig()['database'];
    $host     = Shop::Container()->getDB()->getConfig()['host'];
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
        $migration   = DBMigrationHelper::isTableNeedMigration($oTable);

        if (!in_array($oTable->TABLE_NAME, $shopTables, true)) {
            continue;
        }

        if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
            // Fulltext indizes are not supported for innoDB on MySQL < 5.6
            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($oTable->TABLE_NAME);

            if ($fulltextIndizes) {
                $result .= "$nl--$nl";
                $result .= "-- remove fulltext indizes because there is no support for innoDB on MySQL < 5.6 $nl";
                foreach ($fulltextIndizes as $fulltextIndex) {
                    $fulltextSQL[] = "ALTER TABLE `{$oTable->TABLE_NAME}` DROP KEY `{$fulltextIndex->INDEX_NAME}`";
                }
            }
        }

        if (($migration & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
            $result .= "$nl--$nl";
            if (($migration & DBMigrationHelper::MIGRATE_TABLE) === DBMigrationHelper::MIGRATE_TABLE) {
                $result .= "-- migrate engine and collation for {$oTable->TABLE_NAME}$nl";
            } elseif (($migration & DBMigrationHelper::MIGRATE_INNODB) === DBMigrationHelper::MIGRATE_INNODB) {
                $result .= "-- migrate engine for {$oTable->TABLE_NAME}$nl";
            } elseif (($migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
                $result .= "-- migrate collation for {$oTable->TABLE_NAME}$nl";
            }
        } else {
            $result .= $nl;
        }

        if (count($fulltextSQL) > 0) {
            $result .= implode(';' . $nl, $fulltextSQL) . ';' . $nl;
        }

        $sql = DBMigrationHelper::sqlMoveToInnoDB($oTable);
        if (!empty($sql)) {
            $result .= '--' . $nl;
            $result .= $sql . ';' . $nl;
        }

        $sql = DBMigrationHelper::sqlConvertUTF8($oTable, $nl);
        if (!empty($sql)) {
            $result .= '--' . $nl;
            $result .= '-- migrate collation and / or datatype for columns in ' . $oTable->TABLE_NAME . $nl;
            $result .= '--' . $nl;
            $result .= $sql . ';' . $nl;
        }
    }

    $result .= $nl;

    if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
        // Fulltext search is not available on MySQL < 5.6
        $result .= '--' . $nl;
        $result .= '-- Fulltext search is not available on MySQL < 5.6' . $nl;
        $result .= '--' . $nl;
        $result .= "UPDATE `teinstellungen` SET `cWert` = 'N' WHERE `cName` = 'suche_fulltext';" . $nl;
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
 * @throws \Exceptions\CircularReferenceException
 * @throws \Exceptions\ServiceNotFoundException
 */
function doMigrateToInnoDB_utf8(string $status = 'start', string $table = '', int $step = 1, array $exclude = [])
{
    $mysqlVersion = DBMigrationHelper::getMySQLVersion();
    $table        = StringHandler::filterXSS($table);
    $result       = new stdClass();
    $db           = Shop::Container()->getDB();

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
                $oTable    = DBMigrationHelper::getTable($table);
                $migration = DBMigrationHelper::isTableNeedMigration($oTable);
                if (is_object($oTable)
                    && $migration !== DBMigrationHelper::MIGRATE_NONE
                    && !in_array($oTable->TABLE_NAME, $exclude, true)
                ) {
                    if (!DBMigrationHelper::isTableInUse($table)) {
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            // If MySQL version is lower than 5.6 use alternative lock method
                            // and delete all fulltext indexes because these are not supported
                            $db->executeQuery(
                                DBMigrationHelper::sqlAddLockInfo($oTable),
                                \DB\ReturnType::QUERYSINGLE
                            );
                            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($oTable->TABLE_NAME);

                            if ($fulltextIndizes) {
                                foreach ($fulltextIndizes as $fulltextIndex) {
                                    $db->executeQuery(
                                        "ALTER TABLE `{$oTable->TABLE_NAME}` 
                                            DROP KEY `{$fulltextIndex->INDEX_NAME}`",
                                        \DB\ReturnType::QUERYSINGLE
                                    );
                                }
                            }
                        }
                        if ((($migration & DBMigrationHelper::MIGRATE_TABLE) === 0)
                            || $db->executeQuery(
                                DBMigrationHelper::sqlMoveToInnoDB($oTable),
                                \DB\ReturnType::QUERYSINGLE
                            )
                        ) {
                            $result->nextTable = $table;
                            $result->nextStep  = 2;
                            $result->status    = 'migrate';
                        } else {
                            $result->status = 'failure';
                        }
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            $db->executeQuery(
                                DBMigrationHelper::sqlClearLockInfo($oTable),
                                \DB\ReturnType::QUERYSINGLE
                            );
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
                        if ($db->executeQuery($sql, \DB\ReturnType::QUERYSINGLE)) {
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
                $cache = Shop::Container()->getCache();
                if ($cache !== null) {
                    $cache->setJtlCacheConfig();
                    $cache->flushAll();
                }
            } catch (Exception $e) {
                Shop::Container()->getLogService()->error(
                    sprintf(__('errorEmptyCache'), $e->getMessage())
                );
            }
            $callback    = function (array $pParameters) {
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
                $db->executeQuery(
                    "UPDATE `teinstellungen` 
                        SET `cWert` = 'N' 
                        WHERE `cName` = 'suche_fulltext'",
                    \DB\ReturnType::QUERYSINGLE
                );
            }
            $result->nextTable = '';
            $result->status    = 'finished';
            break;
    }

    return $result;
}
