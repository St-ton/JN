<?php declare(strict_types=1);

use Illuminate\Support\Collection;

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @param bool   $standalonePage - render as standalone page
 * @return string|null
 * @deprecated since 5.2.0
 */
function adminSearch(string $query, bool $standalonePage = false): ?string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function configSearch(string $query): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function adminMenuSearch(string $query): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $haystack
 * @param string $needle
 * @return string
 * @deprecated since 5.2.0
 */
function highlightSearchTerm(string $haystack, string $needle): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return preg_replace(
        '/\p{L}*?' . preg_quote($needle, '/') . '\p{L}*/ui',
        '<mark>$0</mark>',
        $haystack
    );
}

/**
 * @param string $query
 * @return Collection
 * @deprecated since 5.2.0
 */
function getPlugins(string $query): Collection
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Collection();
}
