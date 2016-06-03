<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function getWriteables()
{
    return shop_writeable_paths();
}

/**
 * @return array
 */
function checkWriteables()
{
    $cCheckAssoc_arr = array();
    $cWriteable_arr  = getWriteables();

    foreach ($cWriteable_arr as $cWriteable) {
        $cCheckAssoc_arr[$cWriteable] = false;
        if (is_writable(PFAD_ROOT . $cWriteable)) {
            $cCheckAssoc_arr[$cWriteable] = true;
        }
    }

    return $cCheckAssoc_arr;
}

/**
 * @param array $cDirAssoc_arr
 * @return stdClass
 */
function getPermissionStats($cDirAssoc_arr)
{
    $oStat                = new stdClass();
    $oStat->nCount        = 0;
    $oStat->nCountInValid = 0;

    if (is_array($cDirAssoc_arr) && count($cDirAssoc_arr) > 0) {
        foreach ($cDirAssoc_arr as $cDir => $isValid) {
            $oStat->nCount++;
            if (!$isValid) {
                $oStat->nCountInValid++;
            }
        }
    }

    return $oStat;
}
