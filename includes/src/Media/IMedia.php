<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

/**
 * Interface IMedia
 * @package JTL\Media
 */
interface IMedia
{
    /**
     * @param string $request
     * @return bool
     */
    public function isValid(string $request): bool;

    /**
     * @param string $request
     * @return mixed
     */
    public function handle(string $request);

    /**
     * @param object $mixed
     * @return string
     */
    public static function getCustomName($mixed): string;

    /**
     * @param string      $type
     * @param string      $id
     * @param object      $mixed
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return string
     */
    public static function getThumb(string $type, $id, $mixed, $size, int $number = 1, string $sourcePath = null): string;

    /**
     * @param string      $type
     * @param string|int  $id
     * @param object      $mixed
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return MediaImageRequest
     */
    public static function getRequest(
        string $type,
        $id,
        $mixed,
        string $size,
        int $number = 1,
        string $sourcePath = null
    ): MediaImageRequest;
}
