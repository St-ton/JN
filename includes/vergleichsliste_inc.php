<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Catalog\Vergleichsliste;

/**
 * @param Vergleichsliste $compareList
 * @return array
 * @deprecated since 5.0.0
 */
function baueMerkmalundVariation($compareList)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::buildAttributeAndVariation($compareList);
}

/**
 * @param array $attributes
 * @param int   $attributeID
 * @return bool
 * @deprecated since 5.0.0
 */
function istMerkmalEnthalten($attributes, $attributeID)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::containsAttribute($attributes, $attributeID);
}

/**
 * @param array  $variations
 * @param string $name
 * @return bool
 * @deprecated since 5.0.0
 */
function istVariationEnthalten($variations, $name)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::containsVariation($variations, $name);
}

/**
 * @param array $exclude
 * @param array $config
 * @return string
 * @deprecated since 5.0.0
 */
function gibMaxPrioSpalteV($exclude, $config)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Vergleichsliste::gibMaxPrioSpalteV($exclude, $config);
}

/**
 * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
 * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
 *
 * @param Vergleichsliste $compareList
 * @deprecated since 5.0.0
 */
function setzeVergleich($compareList)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Vergleichsliste::setComparison($compareList);
}
