<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Generator;
use JTL\DB\DbInterface;
use JTL\Media\Image\StatsItem;

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
     * @param string|null $source
     * @return string
     */
    public static function getThumb(string $type, $id, $mixed, $size, int $number = 1, string $source = null): string;

    /**
     * @param MediaImageRequest $req
     * @return string
     */
    public static function getThumbByRequest(MediaImageRequest $req): string;

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

    /**
     * @param int|string $id
     * @param int|null $number
     * @return string|null
     */
    public static function getPathByID($id, int $number = null): ?string;

    /**
     * @return string
     */
    public static function getStoragePath(): string;

    /**
     * @param bool $filesize
     * @return StatsItem
     */
    public static function getStats(bool $filesize = false): StatsItem;

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return Generator
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator;

    /**
     * @return int
     */
    public static function getTotalImageCount(): int;

    /**
     * @return int
     */
    public static function getUncachedImageCount(): int;

    /**
     * @param bool     $notCached
     * @param int|null $offset
     * @param int|null $limit
     * @return MediaImageRequest[]
     */
    public static function getImages(bool $notCached = false, int $offset = null, int $limit = null): array;

    /**
     * @param MediaImageRequest $req
     * @param bool              $overwrite
     * @return array
     */
    public static function cacheImage(MediaImageRequest $req, bool $overwrite = false): array;

    /**
     * @param null|string|int $id
     */
    public static function clearCache($id = null): void;

    /**
     * @param string $imageUrl
     * @return MediaImageRequest
     */
    public static function toRequest(string $imageUrl): MediaImageRequest;

    /**
     * @param DbInterface $db
     * @param string      $path
     * @return bool
     */
    public static function imageIsUsed(DbInterface $db, string $path): bool;

    /**
     * @param MediaImageRequest $req
     * @return array
     */
    public static function getImageNames(MediaImageRequest $req): array;
}
