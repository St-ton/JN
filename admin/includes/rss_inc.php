<?php declare(strict_types=1);

use JTL\Export\RSS;
use JTL\Shop;

/**
 * @return bool
 * @deprecated since 5.2.0
 */
function generiereRSSXML(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use JTL\Export\RSS class instead.', E_USER_DEPRECATED);
    return (new RSS(Shop::Container()->getDB(), Shop::Container()->getLogService()))->generateXML();
}

/**
 * @param string $dateString
 * @return bool|string
 * @deprecated since 5.2.0
 */
function bauerfc2822datum($dateString)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use JTL\Export\RSS class instead.', E_USER_DEPRECATED);
    return (new RSS(Shop::Container()->getDB(), Shop::Container()->getLogService()))->asRFC2822($dateString);
}

/**
 * @param string $text
 * @return string
 * @deprecated since 5.2.0
 */
function wandelXMLEntitiesUm($text): string
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use JTL\Export\RSS class instead.', E_USER_DEPRECATED);
    return (new RSS(Shop::Container()->getDB(), Shop::Container()->getLogService()))->asEntity($text);
}
