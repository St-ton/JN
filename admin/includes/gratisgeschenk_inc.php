<?php declare(strict_types=1);

/**
 * @param string $sql
 * @return array
 * @deprecated since 5.2.0
 */
function holeAktiveGeschenke(string $sql): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $sql
 * @return array
 * @deprecated since 5.2.0
 */
function holeHaeufigeGeschenke(string $sql): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $sql
 * @return array
 * @deprecated since 5.2.0
 */
function holeLetzten100Geschenke(string $sql): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibAnzahlAktiverGeschenke(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibAnzahlHaeufigGekaufteGeschenke(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function gibAnzahlLetzten100Geschenke(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}
