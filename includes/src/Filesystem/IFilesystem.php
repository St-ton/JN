<?php

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
    public function getMeta($path);

    public function get($path, $lock = false);

    public function put($path, $contents, $mode = null);

    public function cwd();

    public function chown($path, $owner);

    public function chgrp($path, $group);

    public function chmod($path, $mode = null);

    public function chdir($path);

    public function copy($path, $target);

    public function move($path, $target);

    public function delete($path);

    public function exists($path);

    public function listContents($directory, $recursive = false);

    public function makeDirectory($path, $mode = null, $recursive = false);

    public function moveDirectory($from, $to, $overwrite = false);

    public function copyDirectory($from, $to, $mode = null);

    public function deleteDirectory($directory, $preserve = false);
}