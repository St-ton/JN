<?php declare(strict_types=1);

use JTL\Update\DBMigrationHelper;

/**
 * @param bool $extended
 * @param bool $clearCache
 * @return array
 * @deprecated since 5.2.0
 */
function getDBStruct(bool $extended = false, bool $clearCache = false): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Update\DBMigrationHelper::getDBStruct() instead.',
        E_USER_DEPRECATED
    );
    return DBMigrationHelper::getDBStruct($extended, $clearCache);
}

/**
 * @return array
 * @deprecated since 5.2.0
 */
function getDBFileStruct(): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Update\DBMigrationHelper::getDBFileStruct() instead.',
        E_USER_DEPRECATED
    );
    return DBMigrationHelper::getDBFileStruct();
}

/**
 * @param string $msg
 * @param bool   $engineError
 * @return stdClass
 * @deprecated since 5.2.0
 */
function createDBStructError(string $msg, bool $engineError = false): stdClass
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Update\DBMigrationHelper::createDBStructError() instead.',
        E_USER_DEPRECATED
    );
    return DBMigrationHelper::createDBStructError($msg, $engineError);
}

/**
 * @param array $dbFileStruct
 * @param array $dbStruct
 * @return object[]
 * @deprecated since 5.2.0
 */
function compareDBStruct(array $dbFileStruct, array $dbStruct): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Update\DBMigrationHelper::compareDBStruct() instead.',
        E_USER_DEPRECATED
    );
    return DBMigrationHelper::compareDBStruct($dbFileStruct, $dbStruct);
}

/**
 * @param string $action
 * @param array  $tables
 * @return bool
 * @deprecated since 5.2.0
 */
function doDBMaintenance(string $action, array $tables): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $dbStruct
 * @return stdClass
 * @deprecated since 5.2.0
 */
function determineEngineUpdate(array $dbStruct): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @param string   $fileName
 * @param string[] $shopTables
 * @return string
 * @deprecated since 5.2.0
 */
function doEngineUpdateScript(string $fileName, array $shopTables): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param string $status
 * @param string $tableName
 * @param int    $step
 * @param array  $exclude
 * @return stdClass
 * @deprecated since 5.2.0
 */
function doMigrateToInnoDB_utf8(
    string $status = 'start',
    string $tableName = '',
    int $step = 1,
    array $exclude = []
): stdClass {
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Update\DBMigrationHelper::doMigrateToInnoDB_utf8() instead.',
        E_USER_DEPRECATED
    );
    return DBMigrationHelper::doMigrateToInnoDB_utf8($status, $tableName, $step, $exclude);
}
