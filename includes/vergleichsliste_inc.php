<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Vergleichsliste $oVergleichsliste
 * @return array
 * @deprecated since 5.0.0
 */
function baueMerkmalundVariation($oVergleichsliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::buildAttributeAndVariation($oVergleichsliste);
}

/**
 * @param array $oMerkmale_arr
 * @param int   $kMerkmal
 * @return bool
 * @deprecated since 5.0.0
 */
function istMerkmalEnthalten($oMerkmale_arr, $kMerkmal)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::containsAttribute($oMerkmale_arr, $kMerkmal);
}

/**
 * @param array  $oVariationen_arr
 * @param string $cName
 * @return bool
 * @deprecated since 5.0.0
 */
function istVariationEnthalten($oVariationen_arr, $cName)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::containsVariation($oVariationen_arr, $cName);
}

/**
 * @param array $cExclude
 * @param array $config
 * @return string
 * @deprecated since 5.0.0
 */
function gibMaxPrioSpalteV($cExclude, $config)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::gibMaxPrioSpalteV($cExclude, $config);
}

/**
 * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
 * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
 *
 * @param Vergleichsliste $oVergleichsliste
 * @deprecated since 5.0.0
 */
function setzeVergleich($oVergleichsliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Vergleichsliste::setComparison($oVergleichsliste);
}
