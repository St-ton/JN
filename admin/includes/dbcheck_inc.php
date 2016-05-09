<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function getDBStruct()
{
    $cDBStruct_arr = array();
    $oData_arr     = Shop::DB()->query("SHOW TABLES", 10);
    foreach ($oData_arr as $oData) {
        $cTable                 = $oData[0];
        $cDBStruct_arr[$cTable] = array();
        $oCol_arr               = Shop::DB()->query("SHOW COLUMNS FROM " . $cTable, 2);
        if ($oCol_arr !== false && is_array($oCol_arr)) {
            foreach ($oCol_arr as $oCol) {
                $cDBStruct_arr[$cTable][] = $oCol->Field;
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
