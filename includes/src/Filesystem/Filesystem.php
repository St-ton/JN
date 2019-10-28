<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use Exception;
use Generator;
use JTL\Path;
use Symfony\Component\Finder\Finder;
use ZipArchive;

/**
 * Class Filesystem
 * @package JTL\Filesystem
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

    /**
     * @param $path
     * @return FileInfo
     */
    public function getMeta($path) : FileInfo
    {
        $path = Path::clean($path);

        return $this->getAdapter()->getMeta($path);
    }

    /**
     * @param      $path
     * @param null $mode
     * @return string|null
     */
    public function get($path, $mode = null) :? string
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->get($path, $mode);
    }

    /**
     * @param      $path
     * @param      $contents
     * @param null $mode
     * @return bool
     */
    public function put($path, $contents, $mode = null) : bool
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->put($path, $contents, $mode);
    }

    /**
     * @param $path
     * @param $owner
     * @return bool
     */
    public function chown($path, $owner) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chown($path, $owner);
    }

    /**
     * @return string|null
     */
    public function cwd() :? string
    {
        $cwd = $this->getAdapter()->cwd();

        return Path::clean($cwd);
    }

    /**
     * @param $path
     * @return bool
     */
    public function chdir($path) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chdir($path);
    }

    /**
     * @param $path
     * @param $group
     * @return bool
     */
    public function chgrp($path, $group) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->chgrp($path, $group);
    }

    /**
     * @param      $path
     * @param null $mode
     * @return bool
     */
    public function chmod($path, $mode = null) : bool
    {
        $path = Path::clean($path);

        // TODO: Check path type [dir/file]
        $mode = $mode ?: IFilesystem::FILE_PERM;

        return $this->getAdapter()->chmod($path, $mode);
    }

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function copy($path, $target) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->copy($path, $target);
    }

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function move($path, $target) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->move($path, $target);
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->delete($path);
    }

    /**
     * @param $path
     * @return bool
     */
    public function exists($path) : bool
    {
        $path = Path::clean($path);

        return $this->getAdapter()->exists($path);
    }

    /**
     * @param      $directory
     * @param bool $recursive
     * @return Generator
     */
    public function listContents($directory, $recursive = false) : Generator
    {
        $directory = Path::clean($directory);

        return $this->getAdapter()->listContents($directory, $recursive);
    }

    /**
     * @param      $path
     * @param null $mode
     * @param bool $recursive
     * @return bool
     */
    public function makeDirectory($path, $mode = null, $recursive = false) : bool
    {
        $path = Path::clean($path);
        $mode = $mode ?: IFilesystem::DIR_PERM;

        return $this->getAdapter()->makeDirectory($path, $mode, $recursive);
    }

    /**
     * @param      $from
     * @param      $to
     * @param bool $overwrite
     * @return bool
     */
    public function moveDirectory($from, $to, $overwrite = false) : bool
    {
        $from = Path::clean($from);
        $to   = Path::clean($to);

        return $this->getAdapter()->moveDirectory($from, $to, $overwrite);
    }

    /**
     * @param      $from
     * @param      $to
     * @param null $mode
     * @return bool
     */
    public function copyDirectory($from, $to, $mode = null) : bool
    {
        $from = Path::clean($from);
        $to   = Path::clean($to);
        $mode = $mode ?: IFilesystem::DIR_PERM;

        return $this->getAdapter()->copyDirectory($from, $to, $mode);
    }

    /**
     * @param      $directory
     * @param bool $preserve
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false) : bool
    {
        $directory = Path::clean($directory);

        return $this->getAdapter()->deleteDirectory($directory, $preserve);
    }

    /**
     * @param      $directory
     * @param bool $recursive
     * @return array|Generator
     */
    public function listFiles($directory, $recursive = false)
    {
        $list = $this->listContents($directory, $recursive);

        return \array_filter(
            $list,
            function ($item) {
                return $item['type'] === 'file';
            }
        );
    }

    /**
     * @param      $directory
     * @param bool $recursive
     * @return array|Generator
     */
    public function listDirectories($directory, $recursive = false)
    {
        $list = $this->listContents($directory, $recursive);

        return \array_filter(
            $list,
            function ($item) {
                return $item['type'] === 'dir';
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

        if (($code = $zipArchive->open($directory, ZipArchive::CHECKCONS)) !== true) {
            throw new Exception('Incompatible Archive.', $code);
        }

        $directories  = [];
        $archive_size = 0;

        /*
        if (!$this->getMeta($location)->isDir()) {
            $path = \preg_split('![/\\\]!', Path::removeTrailingSlash($location));
            for ($i = \count($path); $i >= 0; $i--) {
                if (empty($path[$i]))
                    continue;
                $dir = \implode('/', \array_slice($path, 0, $i+1));
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

            if (\substr($info['name'], -1) === \DIRECTORY_SEPARATOR) {
                $directory = Path::removeTrailingSlash($info['name']);
            } elseif ($dirName = \dirname($info['name'])) {
                $directory = Path::removeTrailingSlash($dirName);
            }

            $directories[$directory] = $index;
        }

        // Flatten directory depths
        // ['/a', '/a/b', '/a/b/c'] => ['/a/b/c']
        foreach ($directories as $dir => $_) {
            $parent = \dirname($dir);
            if (\array_key_exists($parent, $directories)) {
                unset($directories[$parent]);
            }
        }

        $directories = \array_flip($directories);

        // Create location where to extract the archive
        if (!$this->makeDirectory($location, null, true)) {
            throw new Exception(\sprintf('Could not create directory "%s"', $location));
        }

        // Check available disk space
        // Extracted archive + overwritten files + 10MB buffer
        if ($disk_free_size = @\disk_free_space($location)) {
            $required_size = $archive_size * 2 + 1024 * 1024 * 10;
            if ($disk_free_size && $required_size > $disk_free_size) {
                throw new Exception('Not enough disk space available');
            }
        }

        // Create required directories
        foreach ($directories as $dir) {
            $dir = Path::combine($location, $dir);
            if (!$this->makeDirectory($dir, null, true) && !$this->getMeta($dir)->isDir()) {
                throw new Exception(\sprintf('Could not create directory "%s"', $dir));
            }
        }

        unset($directories);

        // Copy files from archive
        for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
            if (!$info = $zipArchive->statIndex($index)) {
                throw new Exception('Could not retrieve file from archive.');
            }

            // Directories are identified by trailing slash
            if (\substr($info['name'], -1) === '/') {
                continue;
            }

            $contents = $zipArchive->getFromIndex($index);

            if ($contents === false) {
                throw new Exception('Could not extract file from archive.');
            }

            $file = Path::combine($location, $info['name']);

            if ($this->put($file, $contents) === false) {
                throw new Exception(\sprintf('Could not copy file "%s" (%d)', $file, \strlen($contents)));
            }
        }

        $zipArchive->close();

        return true;
    }

    /**
     * @param Finder        $finder
     * @param string        $archivePath
     * @param callable|null $callback
     * @return bool
     */
    public function zip(Finder $finder, string $archivePath, callable $callback = null): bool
    {
        return $this->getAdapter()->zip($finder, $archivePath, $callback);
    }

    /**
     * @param $identity
     * @return array|null
     */
    public function getOwner($identity)
    {
        if (\is_numeric($identity)) {
            if (\function_exists('posix_getpwuid')) {
                return \posix_getpwuid((int)$identity);
            }
        } elseif (\function_exists('posix_getpwnam')) {
            return \posix_getpwnam($identity);
        }

        return null;
    }

    /**
     * @param $identity
     * @return array|null
     */
    public function getGroup($identity)
    {
        if (\is_numeric($identity)) {
            if (\function_exists('posix_getgrgid')) {
                return \posix_getgrgid((int)$identity);
            }
        } elseif (\function_exists('posix_getgrnam')) {
            return \posix_getgrnam($identity);
        }

        return null;
    }
}
