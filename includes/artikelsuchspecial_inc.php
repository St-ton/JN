<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibVaterSQL()
{
    return SearchSpecialHelper::getParentSQL();
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 * @deprecated since 5.0.0
 */
function gibTopAngebote(int $nLimit = 20, int $kKundengruppe = 0)
{
    return SearchSpecialHelper::getTopOffers($nLimit, $kKundengruppe);
}

/**
 * @param array $arr
 * @param int   $limit
 * @return array
 * @deprecated since 5.0.0
 */
function randomizeAndLimit(array $arr, int $limit = 1)
{
    return SearchSpecialHelper::randomizeAndLimit($arr, $limit);
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 * @deprecated since 5.0.0
 */
function gibBestseller(int $nLimit = 20, int $kKundengruppe = 0)
{
    return SearchSpecialHelper::getBestsellers($nLimit, $kKundengruppe);
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 * @deprecated since 5.0.0
 */
function gibSonderangebote(int $nLimit = 20, int $kKundengruppe = 0)
{
    return SearchSpecialHelper::getSpecialOffers($nLimit, $kKundengruppe);
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 * @deprecated since 5.0.0
 */
function gibNeuImSortiment(int $nLimit, int $kKundengruppe = 0)
{
    return SearchSpecialHelper::getNewProducts($nLimit, $kKundengruppe);
}
