<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filesystem;

interface IFilesystem
{
    /**
     * Default directory permissions
     */
    const DIR_PERM = 0755;

    /**
     * Default file permissions
     */
    const FILE_PERM = 0644;

    /**
     * @param $path
     *
     * @return FileInfo
     */
    public function getMeta($path) : FileInfo;

    public function get($path, $lock = false) :? string;

    public function put($path, $contents, $mode = null) : bool;

    public function cwd() :? string;

    public function chown($path, $owner) : bool;

    public function chgrp($path, $group) : bool;

    public function chmod($path, $mode = null) : bool;

    public function chdir($path) : bool;

    public function copy($path, $target) : bool;

    public function move($path, $target) : bool;

    public function delete($path) : bool;

    public function exists($path) : bool;

    public function listContents($directory, $recursive = false) : Generator;

    public function makeDirectory($path, $mode = null, $recursive = false) : bool;

    public function moveDirectory($from, $to, $overwrite = false) : bool;

    public function copyDirectory($from, $to, $mode = null) : bool;

    public function deleteDirectory($directory, $preserve = false) : bool;
}
