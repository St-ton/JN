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
    switch ($type) {
        case 'image/gif':
            return '.gif';
        case 'image/png':
        case 'image/x-png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        case 'image/pjpeg':
        case 'image/jpg':
        case 'image/jpeg':
        default:
            return '.jpg';
    }
}
