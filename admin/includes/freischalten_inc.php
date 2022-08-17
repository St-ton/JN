<?php declare(strict_types=1);

/**
 * @param string   $sql
 * @param stdClass $searchSQL
 * @param bool     $checkLanguage
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function gibBewertungFreischalten(string $sql, stdClass $searchSQL, bool $checkLanguage = true): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string   $sql
 * @param stdClass $searchSQL
 * @param bool     $checkLanguage
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function gibSuchanfrageFreischalten(string $sql, stdClass $searchSQL, bool $checkLanguage = true): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string   $sql
 * @param stdClass $searchSQL
 * @param bool     $checkLanguage
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function gibNewskommentarFreischalten(string $sql, stdClass $searchSQL, bool $checkLanguage = true): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string   $sql
 * @param stdClass $searchSQL
 * @param bool     $checkLanguage
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function gibNewsletterEmpfaengerFreischalten(string $sql, stdClass $searchSQL, bool $checkLanguage = true): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param array $reviewIDs
 * @return bool
 * @deprecated since 5.2.0
 */
function schalteBewertungFrei(array $reviewIDs): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $searchQueries
 * @return bool
 * @deprecated since 5.2.0
 */
function schalteSuchanfragenFrei(array $searchQueries): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $newsComments
 * @return bool
 * @deprecated since 5.2.0
 */
function schalteNewskommentareFrei(array $newsComments): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $recipients
 * @return bool
 * @deprecated since 5.2.0
 */
function schalteNewsletterempfaengerFrei(array $recipients): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $ratings
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheBewertung(array $ratings): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $queries
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheSuchanfragen(array $queries): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $comments
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheNewskommentare(array $comments): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $recipients
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheNewsletterempfaenger(array $recipients): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array|mixed $queryIDs
 * @param string      $mapTo
 * @return int
 * @deprecated since 5.2.0
 */
function mappeLiveSuche($queryIDs, string $mapTo): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 2;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibMaxBewertungen(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibMaxSuchanfragen(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibMaxNewskommentare(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibMaxNewsletterEmpfaenger(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}
