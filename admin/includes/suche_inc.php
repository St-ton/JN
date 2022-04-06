<?php declare(strict_types=1);

use Illuminate\Support\Collection;
use JTL\Backend\Settings\Sections\SectionInterface;

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @param bool   $standalonePage - render as standalone page
 * @return string|null
 * @deprecated since 5.2.0
 * @todo!
 */
function adminSearch(string $query, bool $standalonePage = false): ?string
{
    return null;
}

/**
 * @param string $query
 * @return SectionInterface[]
 * @deprecated since 5.2.0
 */
function configSearch(string $query): array
{
    return [];
}

/**
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function adminMenuSearch(string $query): array
{
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
    return new Collection();
}
