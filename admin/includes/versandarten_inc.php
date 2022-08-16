<?php declare(strict_types=1);

use JTL\Language\LanguageModel;

/**
 * @param float|string $price
 * @param float|string $taxRate
 * @return float
 * @deprecated since 5.2.0
 */
function berechneVersandpreisBrutto($price, $taxRate): float
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $price > 0
        ? round((float)($price * ((100 + $taxRate) / 100)), 2)
        : 0.0;
}

/**
 * @param float|string $price
 * @param float|string $taxRate
 * @return float
 * @deprecated since 5.2.0
 */
function berechneVersandpreisNetto($price, $taxRate): float
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $price > 0
        ? round($price * ((100 / (100 + $taxRate)) * 100) / 100, 2)
        : 0.0;
}

/**
 * @param array  $objects
 * @param string $key
 * @return array
 * @deprecated since 5.2.0
 */
function reorganizeObjectArray(array $objects, string $key): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $res = [];
    foreach ($objects as $obj) {
        $arr  = get_object_vars($obj);
        $keys = array_keys($arr);
        if (in_array($key, $keys, true)) {
            $res[$obj->$key]           = new stdClass();
            $res[$obj->$key]->checked  = 'checked';
            $res[$obj->$key]->selected = 'selected';
            foreach ($keys as $k) {
                if ($key !== $k) {
                    $res[$obj->$key]->$k = $obj->$k;
                }
            }
        }
    }

    return $res;
}

/**
 * @param array $arr
 * @return array
 * @deprecated since 5.2.0
 */
function P($arr): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @param object $key
 * @return array
 * @deprecated since 5.2.0
 */
function bauePot($arr, $key): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    foreach ($arr as $val) {
        $obj                 = new stdClass();
        $obj->kVersandklasse = $val->kVersandklasse . '-' . $key->kVersandklasse;
        $obj->cName          = $val->cName . ', ' . $key->cName;
        $arr[]               = $obj;
    }
    $arr[] = $key;

    return $arr;
}

/**
 * @param string $shippingClasses
 * @return array
 * @deprecated since 5.2.0
 */
function gibGesetzteVersandklassen(string $shippingClasses): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $shippingClasses
 * @return array
 * @deprecated since 5.2.0
 */
function gibGesetzteVersandklassenUebersicht($shippingClasses): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $customerGroupsString
 * @return array
 * @deprecated since 5.2.0
 */
function gibGesetzteKundengruppen(string $customerGroupsString): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int             $shippingMethodID
 * @param LanguageModel[] $languages
 * @return array
 * @deprecated since 5.2.0
 */
function getShippingLanguage(int $shippingMethodID, array $languages): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $feeID
 * @return array
 * @deprecated since 5.2.0
 */
function getZuschlagNames(int $feeID): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function getShippingByName(string $query): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param array $shipClasses
 * @param int   $length
 * @return array
 * @deprecated since 5.2.0
 */
function getCombinations(array $shipClasses, int $length): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function getMissingShippingClassCombi()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return -1;
}

/**
 * @param array $data
 * @return stdClass
 * @deprecated since 5.2.0
 */
function saveShippingSurcharge(array $data): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (object)[
        'title'          => '',
        'priceLocalized' => '',
        'id'             => '',
        'reload'         => false,
        'message'        => 'Deprecated',
        'error'          => true
    ];
}

/**
 * @param int $surchargeID
 * @return stdClass
 * @deprecated since 5.2.0
 */
function deleteShippingSurcharge(int $surchargeID): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (object)['surchargeID' => $surchargeID];
}

/**
 * @param int    $surchargeID
 * @param string $ZIP
 * @return stdClass
 * @deprecated since 5.2.0
 */
function deleteShippingSurchargeZIP(int $surchargeID, string $ZIP): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (object)['surchargeID' => $surchargeID, 'ZIP' => $ZIP];
}

/**
 * @param array $data
 * @return stdClass
 * @deprecated since 5.2.0
 */
function createShippingSurchargeZIP(array $data): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (object)['message' => '', 'badges' => [], 'surchargeID' => 0];
}

/**
 * @param int|null $shippingTypeID
 * @return array
 * @deprecated since 5.2.0
 */
function getShippingTypes(int $shippingTypeID = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}
