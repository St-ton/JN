<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

/**
 * Class Filesystem
 * @package Filesystem
 */
class Filesystem implements IFilesystem
{
    /**
     * @var IFilesystem
     */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param IFilesystem $adapter
     */
    public function __construct(IFilesystem $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the Adapter.
     *
     * @return IFilesystem adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getMeta($path)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->getMeta($path);
    }

    public function get($path, $mode = null)
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->get($path, $mode);
    }

    public function put($path, $contents, $mode = null)
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->put($path, $contents, $mode);
    }

    public function chown($path, $owner)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chown($path, $owner);
    }

    public function cwd()
    {
        $cwd = $this->getAdapter()->cwd();

        return Path::clean($cwd);
    }

    public function chdir($path)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chdir($path);
    }

    public function chgrp($path, $group)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chgrp($path, $group);
    }

    public function chmod($path, $mode = null)
    {
        $path = Path::clean($path);

        // TODO: Check path type [dir/file]
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->chmod($path, $mode);
    }

    public function copy($path, $target)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->copy($path, $target);
    }

    public function move($path, $target)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->move($path, $target);
    }

    public function delete($path)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->delete($path);
    }

    public function exists($path)
    {
        $path = Path::clean($path);

        return $this->getAdapter()->exists($path);
    }

    public function listContents($directory, $recursive = false)
    {
        $directory = Path::clean($directory);

        return $this->getAdapter()->listContents($directory, $recursive);
    }

    public function makeDirectory($path, $mode = null, $recursive = false)
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::DIR_PERM;

        return $this->getAdapter()->makeDirectory($path, $mode, $recursive);
    }

    public function moveDirectory($from, $to, $overwrite = false)
    {
        $from = Path::clean($from);
        $to   = Path::clean($to);

        return $this->getAdapter()->moveDirectory($from, $to, $overwrite);
    }

    public function copyDirectory($from, $to, $mode = null)
    {
        $from = Path::clean($from);
        $to   = Path::clean($to);
        $mode = $mode ?: IFilesystem::DIR_PERM;

        return $this->getAdapter()->copyDirectory($from, $to, $mode);
    }

    public function deleteDirectory($directory, $preserve = false)
    {
        $directory = Path::clean($directory);

        return $this->getAdapter()->deleteDirectory($directory, $preserve);
    }

    public function listFiles($directory, $recursive = false)
    {
        $list = $this->listContents($directory, $recursive);

        return array_filter(
            $list,
            function ($item) {
                return $item['type'] == 'file';
            }
        );
    }

    public function listDirectories($directory, $recursive = false)
    {
        $list = $this->listContents($directory, $recursive);

        return array_filter(
            $list,
            function ($item) {
                return $item['type'] == 'dir';
            }
        );
    }

    /**
     * Extract the archive contents.
     *
     * 1. Collect all directories
     * 2.
     *
     * @param $directory
     * @param $path
     *
     * @return bool
     *
     * @throws Exception
     */
    public function unzip($directory, $path)
    {
        $directory = Path::clean($directory);
        $location  = Path::clean($path, true);

        $zipArchive = new ZipArchive();

        if (($code = $zipArchive->open($directory, ZIPARCHIVE::CHECKCONS)) !== true) {
            throw new Exception('Incompatible Archive.', $code);
        }

        $directories  = [];
        $archive_size = 0;

        /*
        if (!$this->getMeta($location)->isDir()) {
            $path = preg_split('![/\\\]!', Path::removeTrailingSlash($location));
            for ($i = count($path); $i >= 0; $i--) {
                if (empty($path[$i]))
                    continue;
                $dir = implode('/', array_slice($path, 0, $i+1));
                if ($this->getMeta($dir)->isDir())
                    break;
                $dirs[] = $dir;
            }
        }
        */

        // Collect all directories to create
        for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
            if (!$info = $zipArchive->statIndex($index)) {
                throw new Exception('Could not retrieve file from archive.');
            }

            $archive_size += $info['size'];

            if (substr($info['name'], -1) === DIRECTORY_SEPARATOR) {
                $directory = Path::removeTrailingSlash($info['name']);
            } elseif ($dirName = dirname($info['name'])) {
                $directory = Path::removeTrailingSlash($dirName);
            }

            $directories[$directory] = $index;
        }

        // Flatten directory depths
        // ['/a', '/a/b', '/a/b/c'] => ['/a/b/c']
        foreach ($directories as $dir => $_) {
            $parent = dirname($dir);
            if (array_key_exists($parent, $directories)) {
                unset($directories[$parent]);
            }
        }

        $directories = array_flip($directories);

        // Create location where to extract the archive
        if (!$this->makeDirectory($location, null, true)) {
            throw new Exception(sprintf('Could not create directory "%s"', $location));
        }

        // Check available disk space
        // Extracted archive + overwritten files + 10MB buffer
        if ($disk_free_size = @disk_free_space($location)) {
            $required_size = $archive_size * 2 + 1024 * 1024 * 10;
            if ($disk_free_size && $required_size > $disk_free_size) {
                throw new Exception('Not enough disk space available');
            }
        }

        // Create required directories
        foreach ($directories as $dir) {
            $dir = Path::combine($location, $dir);
            if (!$this->makeDirectory($dir, null, true) && !$this->getMeta($dir)->isDir()) {
                throw new Exception(sprintf('Could not create directory "%s"', $dir));
            }
        }

        unset($directories);

        // Copy files from archive
        for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
            if (!$info = $zipArchive->statIndex($index)) {
                throw new Exception('Could not retrieve file from archive.');
            }

            // Directories are identified by trailing slash
            if (substr($info['name'], -1) === '/') {
                continue;
            }

            $contents = $zipArchive->getFromIndex($index);

            if ($contents === false) {
                throw new Exception('Could not extract file from archive.');
            }

            $file = Path::combine($location, $info['name']);

            if ($this->put($file, $contents) === false) {
                throw new Exception(sprintf('Could not copy file "%s" (%d)', $file, strlen($contents)));
            }
        }

        $zipArchive->close();

        return true;
    }

    public function getOwner($identity)
    {
        if (is_numeric($identity)) {
            if (function_exists('posix_getpwuid')) {
                return posix_getpwuid((int)$identity);
            }
        } else {
            if (function_exists('posix_getpwnam')) {
                return posix_getpwnam($identity);
            }
        }

        return null;
    }

    public function getGroup($identity)
    {
        if (is_numeric($identity)) {
            if (function_exists('posix_getgrgid')) {
                return posix_getgrgid((int)$identity);
            }
        } else {
            if (function_exists('posix_getgrnam')) {
                return posix_getgrnam($identity);
            }
        }

        return null;
    }
}
