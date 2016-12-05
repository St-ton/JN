<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $kOberKategorie_arr
 * @param int   $nLevel
 */
function updateKategorieLevel(array $kOberKategorie_arr = null, $nLevel = 1)
{
    $nLevel = (int)$nLevel;
    $cSql   = 'WHERE kOberKategorie = 0';
    if ($kOberKategorie_arr === null) {
        Shop::DB()->query("TRUNCATE tkategorielevel", 4);
    } else {
        $cSql = 'WHERE kOberKategorie IN (' . implode(',', $kOberKategorie_arr) . ')';
    }

    $oKategorie_arr = Shop::DB()->query(
        "SELECT kKategorie, kOberKategorie
            FROM tkategorie
            {$cSql}", 2
    );
    if (count($oKategorie_arr) > 0) {
        $kKategorie_arr = array();
        foreach ($oKategorie_arr as $oKategorie) {
            $kKategorie_arr[] = (int)$oKategorie->kKategorie;

            $oKategorieLevel             = new stdClass();
            $oKategorieLevel->kKategorie = (int)$oKategorie->kKategorie;
            $oKategorieLevel->nLevel     = (int)$nLevel;

            Shop::DB()->insert('tkategorielevel', $oKategorieLevel);
        }

        updateKategorieLevel($kKategorie_arr, $nLevel + 1);
    }
}

/**
 * update lft/rght values for categories in the nested set model
 *
 * @param int $parent_id
 * @param int $left
 * @return int
 */
function rebuildCategoryTree($parent_id, $left)
{
    $left = intval($left);
    // the right value of this node is the left value + 1
    $right = $left + 1;
    // get all children of this node
    $result = Shop::DB()->selectAll('tkategorie', 'kOberKategorie', (int)$parent_id, 'kKategorie', 'nSort, cName');
    foreach ($result as $_res) {
        $right = rebuildCategoryTree($_res->kKategorie, $right);
    }
    // we've got the left value, and now that we've processed the children of this node we also know the right value
    Shop::DB()->update('tkategorie', 'kKategorie', $parent_id, (object)['lft' => $left, 'rght' => $right]);

    // return the right value of this node + 1
    return $right + 1;
}

/**
 * @return void
 */
function Kategorien_xml_Finish()
{
    Jtllog::writeLog('Finish Kategorien_xml: updateKategorieLevel, rebuildCategoryTree', JTLLOG_LEVEL_DEBUG);
    updateKategorieLevel();
    rebuildCategoryTree(0, 1);
}
