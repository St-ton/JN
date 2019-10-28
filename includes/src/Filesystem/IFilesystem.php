<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use Generator;
use Symfony\Component\Finder\Finder;

/**
 * Interface IFilesystem
 * @package JTL\Filesystem
 */
interface IFilesystem
{
    /**
     * Default directory permissions
     */
    public const DIR_PERM = 0755;

    /**
     * Default file permissions
     */
    public const FILE_PERM = 0644;

    /**
     * @param $path
     *
     * @return FileInfo
     */
    public function getMeta($path): FileInfo;

    /**
     * @param      $path
     * @param bool $lock
     * @return string|null
     */
    public function get($path, $lock = false): ?string;

    /**
     * @param      $path
     * @param      $contents
     * @param null $mode
     * @return bool
     */
    public function put($path, $contents, $mode = null): bool;

    /**
     * @return string|null
     */
    public function cwd(): ?string;

    /**
     * @param $path
     * @param $owner
     * @return bool
     */
    public function chown($path, $owner): bool;

    /**
     * @param $path
     * @param $group
     * @return bool
     */
    public function chgrp($path, $group): bool;

    /**
     * @param      $path
     * @param null $mode
     * @return bool
     */
    public function chmod($path, $mode = null): bool;

    /**
     * @param $path
     * @return bool
     */
    public function chdir($path): bool;

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function copy($path, $target): bool;

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function move($path, $target): bool;

    /**
     * @param $path
     * @return bool
     */
    public function delete($path): bool;

    /**
     * @param $path
     * @return bool
     */
    public function exists($path): bool;

    /**
     * @param      $directory
     * @param bool $recursive
     * @return Generator
     */
    public function listContents($directory, $recursive = false): Generator;

    /**
     * @param      $path
     * @param null $mode
     * @param bool $recursive
     * @return bool
     */
    public function makeDirectory($path, $mode = null, $recursive = false): bool;

    /**
     * @param      $from
     * @param      $to
     * @param bool $overwrite
     * @return bool
     */
    public function moveDirectory($from, $to, $overwrite = false): bool;

    /**
     * @param      $from
     * @param      $to
     * @param null $mode
     * @return bool
     */
    public function copyDirectory($from, $to, $mode = null): bool;

    /**
     * @param      $directory
     * @param bool $preserve
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false): bool;

    /**
     * @param Finder        $finder
     * @param string        $archivePath
     * @param callable|null $callback
     * @return bool
     */
    public function zip(Finder $finder, string $archivePath, callable $callback = null): bool;
}
