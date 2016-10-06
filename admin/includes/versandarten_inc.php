<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisBrutto($fPreis, $fSteuersatz)
{
    if ($fPreis > 0) {
        return round(doubleval($fPreis * ((100 + $fSteuersatz) / 100)), 2);
    }

    return 0.0;
}

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisNetto($fPreis, $fSteuersatz)
{
    if ($fPreis > 0) {
        return round($fPreis * ((100 / (100 + $fSteuersatz)) * 100) / 100, 2);
    }

    return 0.0;
}

/**
 * @param array  $obj_arr
 * @param string $key
 * @return array
 */
function reorganizeObjectArray($obj_arr, $key)
{
    $res = array();
    if (is_array($obj_arr)) {
        foreach ($obj_arr as $obj) {
            $arr  = get_object_vars($obj);
            $keys = array_keys($arr);
            if (in_array($key, $keys)) {
                $res[$obj->$key]           = new stdClass();
                $res[$obj->$key]->checked  = 'checked';
                $res[$obj->$key]->selected = 'selected';
                foreach ($keys as $k) {
                    if ($key != $k) {
                        $res[$obj->$key]->$k = $obj->$k;
                    }
                }
            }
        }
    }

    return $res;
}

/**
 * @param array $arr
 * @return array
 */
function P($arr)
{
    $newArr = array();
    if (is_array($arr)) {
        foreach ($arr as $ele) {
            $newArr = bauePot($newArr, $ele);
        }
    }

    return $newArr;
}

/**
 * @param array  $arr
 * @param string $key
 * @return array
 */
function bauePot($arr, $key)
{
    $cnt = count($arr);
    for ($i = 0; $i < $cnt; $i++) {
        unset($obj);
        $obj                 = new stdClass();
        $obj->kVersandklasse = $arr[$i]->kVersandklasse . '-' . $key->kVersandklasse;
        $obj->cName          = $arr[$i]->cName . ', ' . $key->cName;
        $arr[]               = $obj;
    }
    $arr[] = $key;

    return $arr;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassen($cVersandklassen)
{
    $gesetzteVK      = array();
    $cVKarr          = explode(' ', trim($cVersandklassen));
    $PVersandklassen = P(Shop::DB()->query("SELECT * FROM tversandklasse ORDER BY kVersandklasse", 2));
    if (is_array($PVersandklassen)) {
        foreach ($PVersandklassen as $vk) {
            if (in_array($vk->kVersandklasse, $cVKarr)) {
                $gesetzteVK[$vk->kVersandklasse] = true;
            } else {
                $gesetzteVK[$vk->kVersandklasse] = false;
            }
        }
    }
    if ($cVersandklassen == '-1') {
        $gesetzteVK['alle'] = true;
    }

    return $gesetzteVK;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassenUebersicht($cVersandklassen)
{
    $gesetzteVK      = array();
    $cVKarr          = explode(' ', trim($cVersandklassen));
    $PVersandklassen = P(Shop::DB()->query("SELECT * FROM tversandklasse ORDER BY kVersandklasse", 2));
    if (is_array($PVersandklassen)) {
        foreach ($PVersandklassen as $vk) {
            if (in_array($vk->kVersandklasse, $cVKarr)) {
                $gesetzteVK[] = $vk->cName;
            }
        }
    }
    if ($cVersandklassen == '-1') {
        $gesetzteVK[] = 'Alle';
    }

    return $gesetzteVK;
}

/**
 * @param string $cKundengruppen
 * @return array
 */
function gibGesetzteKundengruppen($cKundengruppen)
{
    $bGesetzteKG_arr   = array();
    $cKG_arr           = explode(';', trim($cKundengruppen));
    $oKundengruppe_arr = Shop::DB()->query(
        "SELECT kKundengruppe
            FROM tkundengruppe
            ORDER BY kKundengruppe", 2
    );

    if (is_array($oKundengruppe_arr)) {
        foreach ($oKundengruppe_arr as $oKundengruppe) {
            if (in_array($oKundengruppe->kKundengruppe, $cKG_arr)) {
                $bGesetzteKG_arr[$oKundengruppe->kKundengruppe] = true;
            } else {
                $bGesetzteKG_arr[$oKundengruppe->kKundengruppe] = false;
            }
        }
    }
    if ($cKundengruppen == '-1') {
        $bGesetzteKG_arr['alle'] = true;
    }

    return $bGesetzteKG_arr;
}

/**
 * @param int   $kVersandart
 * @param array $oSprache_arr
 * @return array
 */
function getShippingLanguage($kVersandart = 0, $oSprache_arr)
{
    $oVersandartSpracheAssoc_arr = array();
    $oVersandartSprache_arr      = Shop::DB()->selectAll('tversandartsprache', 'kVersandart', (int)$kVersandart);
    if (is_array($oSprache_arr) && count($oSprache_arr) > 0) {
        foreach ($oSprache_arr as $oSprache) {
            $oVersandartSpracheAssoc_arr[$oSprache->cISO] = new stdClass();
        }
    }
    if (is_array($oVersandartSprache_arr) && count($oVersandartSprache_arr) > 0) {
        foreach ($oVersandartSprache_arr as $oVersandartSprache) {
            if (isset($oVersandartSprache->kVersandart) && $oVersandartSprache->kVersandart > 0) {
                $oVersandartSpracheAssoc_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
            }
        }
    }

    return $oVersandartSpracheAssoc_arr;
}

/**
 * @param int $kVersandzuschlag
 * @return array
 */
function getZuschlagNames($kVersandzuschlag)
{
    $namen = array();
    if (!$kVersandzuschlag) {
        return $namen;
    }
    $zuschlagnamen = Shop::DB()->selectAll('tversandzuschlagsprache', 'kVersandzuschlag', (int)$kVersandzuschlag);
    $zCount        = count($zuschlagnamen);
    for ($i = 0; $i < $zCount; $i++) {
        $namen[$zuschlagnamen[$i]->cISOSprache] = $zuschlagnamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param string $cSearch
 * @return array $allShippingsByName
 */
function getShippingByName($cSearch)
{
    // Einstellungen Kommagetrennt?
    $cSearch_arr        = explode(',', $cSearch);
    $allShippingsByName = array();
    foreach ($cSearch_arr as $cSearchPos) {
        trim($cSearchPos);
        if (strlen($cSearchPos) > 2) {
            $shippingByName_arr = Shop::DB()->query(
                "SELECT va.kVersandart, va.cName
                    FROM tversandart AS va
                    LEFT JOIN tversandartsprache AS vs ON vs.kVersandart = va.kVersandart
                        AND vs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'
                    WHERE va.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%' OR vs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'", 2
            );
            if (!empty($shippingByName_arr)) {
                if (count($shippingByName_arr) > 1) {
                    foreach ($shippingByName_arr as $shippingByName) {
                        $allShippingsByName[$shippingByName->kVersandart] = $shippingByName;
                    }
                } else {
                    $allShippingsByName[$shippingByName_arr[0]->kVersandart] = $shippingByName_arr[0];
                }
            }
        }
    }

    return $allShippingsByName;
}
