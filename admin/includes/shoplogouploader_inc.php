<?php declare(strict_types=1);

/**
 * @param array $files
 * @return int
 * @deprecated since 5.2.0
 */
function saveShopLogo(array $files): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 4;
}

/**
 * @return bool
 * @var string $logo
 * @deprecated since 5.2.0
 */
function deleteShopLogo(string $logo): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $type
 * @return string
 * @deprecated since 5.2.0
 */
function mappeFileTyp(string $type): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return match ($type) {
        'image/gif' => '.gif',
        'image/png', 'image/x-png' => '.png',
        'image/bmp' => '.bmp',
        default => '.jpg',
    };
}
