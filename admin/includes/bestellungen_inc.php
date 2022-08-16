<?php declare(strict_types=1);

/**
 * @param string $limitSQL
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function gibBestellungsUebersicht(string $limitSQL, string $query): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $query
 * @return int
 * @deprecated since 5.2.0
 */
function gibAnzahlBestellungen(string $query): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param array $orderIDs
 * @return int
 * @deprecated since 5.2.0
 */
function setzeAbgeholtZurueck(array $orderIDs): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 1;
}
