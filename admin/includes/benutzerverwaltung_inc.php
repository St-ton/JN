<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kAdminlogin
 * @return mixed
 */
function getAdmin($kAdminlogin)
{
    return Shop::DB()->select('tadminlogin', 'kAdminlogin', (int)$kAdminlogin);
}

/**
 * @return mixed
 */
function getAdminList()
{
    return Shop::DB()->query("SELECT * FROM tadminlogin LEFT JOIN tadminlogingruppe ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe", 2);
}

/**
 * @return array
 */
function getAdminGroups()
{
    $oGroups_arr = Shop::DB()->query("SELECT * FROM tadminlogingruppe", 2);
    foreach ($oGroups_arr as &$oGroup) {
        $oCount         = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tadminlogin WHERE kAdminlogingruppe = " . (int)$oGroup->kAdminlogingruppe, 1);
        $oGroup->nCount = $oCount->nCount;
    }

    return $oGroups_arr;
}

/**
 * @return array
 */
function getAdminDefPermissions()
{
    $oGroups_arr = Shop::DB()->query("SELECT * FROM tadminrechtemodul ORDER BY nSort ASC", 2);
    foreach ($oGroups_arr as &$oGroup) {
        $oGroup->oPermission_arr = Shop::DB()->query("SELECT * FROM tadminrecht WHERE kAdminrechtemodul = " . (int)$oGroup->kAdminrechtemodul, 2);
    }

    return $oGroups_arr;
}

/**
 * @param int $kAdminlogingruppe
 * @return mixed
 */
function getAdminGroup($kAdminlogingruppe)
{
    return Shop::DB()->select('tadminlogingruppe', 'kAdminlogingruppe', (int)$kAdminlogingruppe);
}

/**
 * @param int $kAdminlogingruppe
 * @return array
 */
function getAdminGroupPermissions($kAdminlogingruppe)
{
    $oPerm_arr         = array();
    $oPermission_arr   = Shop::DB()->query("SELECT * FROM tadminrechtegruppe WHERE kAdminlogingruppe = " . (int)$kAdminlogingruppe, 2);
    foreach ($oPermission_arr as $oPermission) {
        $oPerm_arr[] = $oPermission->cRecht;
    }

    return $oPerm_arr;
}

/**
 * @param string     $cRow
 * @param string|int $cValue
 * @return bool
 */
function getInfoInUse($cRow, $cValue)
{
    $oAdmin = Shop::DB()->select('tadminlogin', $cRow, $cValue, null, null, null, null, false, $cRow);

    return (is_object($oAdmin));
}
