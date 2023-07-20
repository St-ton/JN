<?php declare(strict_types=1);

namespace JTL\Helpers;

use JTL\Catalog\Product\Merkmal;
use JTL\Shop;

/**
 * Class Attribute
 * @since 5.0.0
 * @package JTL\Helpers
 */
class Typifier
{
    /**
     * @var array
     */
    private const POSSIBLEBOOLVALUES = [
        'true'  => true,
        'y'     => true,
        'yes'   => true,
        'ja'    => true,
        '1'     => true,
        'false' => false,
        'n'     => false,
        'no'    => false,
        'nein'  => false,
        '0'     => false,
    ];

    public static function stringify($value): string
    {
        return (is_string($value) ? $value : '');
    }

    public static function intify($value): int
    {
        return (int)$value;
    }

    public static function floatify($value): float
    {
        return (float)$value;
    }

    public static function boolify($value): bool
    {
        $value = \strtolower((string)$value);
        if (!\array_key_exists($value, self::POSSIBLEBOOLVALUES)) {
            return false;
        }

        return self::POSSIBLEBOOLVALUES[$value];
    }

    public static function arrify($value): array
    {
        return (\is_array($value)) ? $value : [];
    }

    public static function objectify($value): object
    {
        return (is_object($value)) ? $value : new \stdClass();
    }


}
