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
    return $fPreis > 0
        ? round((float)($fPreis * ((100 + $fSteuersatz) / 100)), 2)
        : 0.0;
}

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisNetto($fPreis, $fSteuersatz)
{
    return $fPreis > 0
        ? round($fPreis * ((100 / (100 + $fSteuersatz)) * 100) / 100, 2)
        : 0.0;
}

/**
 * @param array  $obj_arr
 * @param string $key
 * @return array
 */
function reorganizeObjectArray($obj_arr, $key)
{
    $res = [];
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
    $newArr = [];
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
    for ($i = 0; $i < $cnt; ++$i) {
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
    if (trim($cVersandklassen) === '-1') {
        return ['alle' => true];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tversandklasse
            WHERE kVersandklasse IN (' . implode(',', $uniqueIDs) . ')  
            ORDER BY kVersandklasse',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    ));
    foreach ($PVersandklassen as $vk) {
        $gesetzteVK[$vk->kVersandklasse] = in_array($vk->kVersandklasse, $cVKarr, true);
    }

    return $gesetzteVK;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassenUebersicht($cVersandklassen)
{
    if (trim($cVersandklassen) === '-1') {
        return ['Alle'];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tversandklasse 
            WHERE kVersandklasse IN (' . implode(',', $uniqueIDs) . ')
            ORDER BY kVersandklasse',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    ));
    foreach ($PVersandklassen as $vk) {
        if (in_array($vk->kVersandklasse, $cVKarr, true)) {
            $gesetzteVK[] = $vk->cName;
        }
    }

    return $gesetzteVK;
}

/**
 * @param string $cKundengruppen
 * @return array
 */
function gibGesetzteKundengruppen($cKundengruppen)
{
    $bGesetzteKG_arr   = [];
    $cKG_arr           = explode(';', trim($cKundengruppen));
    $oKundengruppe_arr = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe
            FROM tkundengruppe
            ORDER BY kKundengruppe',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oKundengruppe_arr as $oKundengruppe) {
        $bGesetzteKG_arr[$oKundengruppe->kKundengruppe] = in_array($oKundengruppe->kKundengruppe, $cKG_arr);
    }
    if ($cKundengruppen === '-1') {
        $bGesetzteKG_arr['alle'] = true;
    }

    return $bGesetzteKG_arr;
}

/**
 * @param int   $kVersandart
 * @param array $oSprache_arr
 * @return array
 */
function getShippingLanguage(int $kVersandart, $oSprache_arr)
{
    $oVersandartSpracheAssoc_arr = [];
    $oVersandartSprache_arr      = Shop::Container()->getDB()->selectAll(
        'tversandartsprache',
        'kVersandart',
        $kVersandart
    );
    if (is_array($oSprache_arr)) {
        foreach ($oSprache_arr as $oSprache) {
            $oVersandartSpracheAssoc_arr[$oSprache->cISO] = new stdClass();
        }
    }
    foreach ($oVersandartSprache_arr as $oVersandartSprache) {
        if (isset($oVersandartSprache->kVersandart) && $oVersandartSprache->kVersandart > 0) {
            $oVersandartSpracheAssoc_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
        }
    }

    return $oVersandartSpracheAssoc_arr;
}

/**
 * @param int $kVersandzuschlag
 * @return array
 */
function getZuschlagNames(int $kVersandzuschlag)
{
    $names = [];
    if (!$kVersandzuschlag) {
        return $names;
    }
    $zuschlagnamen = Shop::Container()->getDB()->selectAll(
        'tversandzuschlagsprache',
        'kVersandzuschlag',
        $kVersandzuschlag
    );
    foreach ($zuschlagnamen as $name) {
        $names[$name->cISOSprache] = $name->cName;
    }

    return $names;
}

/**
 * @param string $cSearch
 * @return array
 */
function getShippingByName($cSearch)
{
    $cSearch_arr        = explode(',', $cSearch);
    $allShippingsByName = [];
    foreach ($cSearch_arr as $cSearchPos) {
        trim($cSearchPos);
        if (strlen($cSearchPos) > 2) {
            $shippingByName_arr = Shop::Container()->getDB()->queryPrepared(
                'SELECT va.kVersandart, va.cName
                    FROM tversandart AS va
                    LEFT JOIN tversandartsprache AS vs 
                        ON vs.kVersandart = va.kVersandart
                        AND vs.cName LIKE :search
                    WHERE va.cName LIKE :search
                    OR vs.cName LIKE :search',
                ['search' => '%' . $cSearchPos . '%'],
                \DB\ReturnType::ARRAY_OF_OBJECTS
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

/**
 * @param array $shipClasses
 * @param int   $length
 * @return array
 */
function getCombinations($base, $n)
{
    $baselen = count($base);
    if ($baselen === 0) {

        return [];
    }
    if ($n === 1) {
        $return = [];
        foreach ($base as $b) {
            $return[] = array($b);
        }

        return $return;
    }

    //get one level lower combinations
    $oneLevelLower = getCombinations($base, $n - 1);
    //for every one level lower combinations add one element to them that the last element of a combination is preceeded by the element which follows it in base array if there is none, does not add
    $newCombs = [];
    foreach ($oneLevelLower as $oll) {
        $lastEl = $oll[$n - 2];
        $found  = false;
        foreach ($base as $key => $b) {
            if ($b === $lastEl) {
                $found = true;
                continue;
                //last element found
            }
            if ($found === true) {
                //add to combinations with last element
                if ($key < $baselen) {
                    $tmp              = $oll;
                    $newCombination   = array_slice($tmp, 0);
                    $newCombination[] = $b;
                    $newCombs[]       = array_slice($newCombination, 0);
                }
            }
        }
    }

    return $newCombs;
}

/**
 * @return array|int -1 if too many shipping classes exist
 */
function getMissingShippingClassCombi()
{
    $shippingClasses         = Shop::Container()->getDB()->selectAll('tversandklasse', [], [], 'kVersandklasse');
    $shipClasses             = [];
    $combinationsInShippings = Shop::Container()->getDB()->selectAll('tversandart', [], [], 'cVersandklassen');
    $combinationInUse        = [];

    foreach ($shippingClasses as $sc) {
        $shipClasses[] = $sc->kVersandklasse;
    }

    foreach ($combinationsInShippings as $com) {
        $vk     = trim($com->cVersandklassen);
        $vk_arr = explode(' ', $vk);
        if (is_array($vk_arr)) {
            foreach ($vk_arr as $_vk) {
                $combinationInUse[] = trim($_vk);
            }
        } else {
            $combinationInUse[] = trim($com->cVersandklassen);
        }
    }

    // if a shipping method is valid for all classes return
    if (in_array('-1', $combinationInUse)) {

        return [];
    }

    $len = count($shipClasses);
    if ($len > SHIPPING_CLASS_MAX_VALIDATION_COUNT) {

        return -1;
    }

    $possibleShippingClassCombinations = [];
    for ($i = 1; $i <= $len; $i++) {
        $result = getCombinations($shipClasses, $i);
        foreach ($result as $c) {
            $possibleShippingClassCombinations[] = implode("-", $c);
        }
    }

    $res = array_diff($possibleShippingClassCombinations, $combinationInUse);
    foreach ($res as &$mscc) {
        $mscc = gibGesetzteVersandklassenUebersicht($mscc)[0];
    }

    return $res;
}
