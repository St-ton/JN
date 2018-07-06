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
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::getParentSQL();
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibTopAngebote(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::getTopOffers($limit, $customerGroupID);
}

/**
 * @param array $arr
 * @param int   $limit
 * @return array
 * @deprecated since 5.0.0
 */
function randomizeAndLimit(array $arr, int $limit = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::randomizeAndLimit($arr, $limit);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibBestseller(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::getBestsellers($limit, $customerGroupID);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibSonderangebote(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::getSpecialOffers($limit, $customerGroupID);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibNeuImSortiment(int $limit, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecialHelper::getNewProducts($limit, $customerGroupID);
}
