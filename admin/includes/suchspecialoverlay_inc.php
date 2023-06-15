<?php declare(strict_types=1);

use JTL\Media\Image\Overlay;
use JTL\Shop;

/**
 * @return Overlay[]
 * @deprecated since 5.2.0
 */
function gibAlleSuchspecialOverlays(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $overlayID
 * @return Overlay
 * @deprecated since 5.2.0
 */
function gibSuchspecialOverlay(int $overlayID): Overlay
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return Overlay::getInstance($overlayID, (int)$_SESSION['editLanguageID']);
}

/**
 * @param int $overlayID
 * @param array $post
 * @param array $files
 * @param int|null $lang
 * @param string|null $template
 * @return bool
 * @deprecated since 5.2.0
 */
function speicherEinstellung(
    int $overlayID,
    array $post,
    array $files,
    int $lang = null,
    string $template = null
): bool {
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))
        ->saveConfig($overlayID, $post, $files, $lang, $template);
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 * @return bool
 * @deprecated since 5.2.0
 */
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))
        ->imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
}

/**
 * @param string $img
 * @param int    $width
 * @param int    $height
 * @return resource|null
 * @deprecated since 5.2.0
 */
function imageload_alpha($img, int $width, int $height)
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->imageloadAlpha($img, $width, $height);
}

/**
 * @param string $image
 * @param int    $width
 * @param int    $height
 * @param int    $transparency
 * @return resource
 * @deprecated since 5.2.0
 */
function ladeOverlay($image, int $width, int $height, int $transparency)
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->load($image, $width, $height, $transparency);
}

/**
 * @param resource $im
 * @param string   $extension
 * @param string   $path
 * @param int      $quality
 * @return bool
 * @deprecated since 5.2.0
 */
function speicherOverlay($im, string $extension, string $path, int $quality = 80): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->save($im, $extension, $path, $quality);
}

/**
 * @param string $image
 * @param int    $size
 * @param int    $transparency
 * @param string $extension
 * @param string $path
 * @return bool
 * @deprecated since 5.2.0
 */
function erstelleFixedOverlay(string $image, int $size, int $transparency, string $extension, string $path): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))
        ->createFixedOverlay($image, $size, $transparency, $extension, $path);
}

/**
 * @param array   $file
 * @param Overlay $overlay
 * @return bool
 * @deprecated since 5.2.0
 */
function speicherBild(array $file, Overlay $overlay): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->saveImage($file, $overlay);
}

/**
 * @param Overlay $overlay
 * @deprecated since 5.2.0
 */
function loescheBild(Overlay $overlay): void
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->deleteImage($overlay);
}

/**
 * @param string $type
 * @return string
 * @deprecated since 5.2.0
 */
function mappeFileTyp(string $type): string
{
    trigger_error(__FUNCTION__ . ' is deprecated. User JTL\Helpers\Overlay instead.', E_USER_DEPRECATED);
    return (new \JTL\Helpers\Overlay(Shop::Container()->getDB()))->mapFileType($type);
}
