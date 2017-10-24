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

    if ($extended) {
        $cDBStruct_arr =& $dbStruct['extended'];
    } else {
        $cDBStruct_arr =& $dbStruct['normal'];
    }

    if ($cDBStruct_arr === null) {
        $oData_arr = Shop::DB()->queryPrepared(
            "SELECT `TABLE_NAME`, `ENGINE`, TABLE_COLLATION, DATA_LENGTH + INDEX_LENGTH AS DATA_SIZE
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = :schema
                ORDER BY `TABLE_NAME`", [
                'schema' => Shop::DB()->getConfig()['database']
            ], 2
        );
        foreach ($oData_arr as $oData) {
            $cTable = $oData->TABLE_NAME;

            if ($extended) {
                $cDBStruct_arr[$cTable]          = $oData;
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
                    'schema' => Shop::DB()->getConfig()['database'],
                    'table' => $cTable,
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
            foreach ($cColumn_arr as $cColumn) {
                if (!in_array($cColumn, isset($cDBStruct_arr[$cTable]->Columns) ? array_keys($cDBStruct_arr[$cTable]->Columns) : $cDBStruct_arr[$cTable], true)) {
                    $cDBError_arr[$cTable] = "Spalte $cColumn in $cTable nicht vorhanden";
                    break;
                }
                if (isset($cDBStruct_arr[$cTable]->Columns)
                    && $cDBStruct_arr[$cTable]->Columns[$cColumn]->COLLATION_NAME !== null
                    && $cDBStruct_arr[$cTable]->Columns[$cColumn]->COLLATION_NAME !== $cDBStruct_arr[$cTable]->TABLE_COLLATION) {
                        $cDBError_arr[$cTable] = "Inkonsistente Kollation in Spalte $cColumn in $cTable";
                        break;
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
