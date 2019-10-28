<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use Exception;
use FilesystemIterator;
use Generator;
use JTL\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use ZipArchive;

/**
 * Class LocalFilesystem
 * @package JTL\Filesystem
 */
class LocalFilesystem extends AbstractFilesystem
{
    /**
     * {@inheritdoc}
     */
    public function getMeta($path): FileInfo
    {
        return $this->mapFileInfo(new SplFileInfo($this->applyPathPrefix($path)));
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, $mode = null): ?string
    {
        $location = $this->applyPathPrefix($path);

        return \file_exists($location) ? \file_get_contents($location) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, $mode = null): bool
    {
        $location = $this->applyPathPrefix($path);
        if (\file_put_contents($location, $contents) === false) {
            return false;
        }
        if ($mode !== null) {
            $this->chmod($location, $mode);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cwd(): ?string
    {
        return @\getcwd();
    }

    /**
     * {@inheritdoc}
     */
    public function chdir($path): bool
    {
        return @\chdir($this->applyPathPrefix($path));
    }

    /**
     * {@inheritdoc}
     */
    public function chgrp($path, $group): bool
    {
        return @\chgrp($this->applyPathPrefix($path), $group);
    }

    /**
     * {@inheritdoc}
     */
    public function chmod($path, $mode = null): bool
    {
        $location = $this->applyPathPrefix($path);
        if ($mode) {
            return @\chmod($location, $mode);
        }

        return \substr(\sprintf('%o', \fileperms($location)), -4);
    }

    /**
     * {@inheritdoc}
     */
    public function chown($path, $owner): bool
    {
        return @\chown($this->applyPathPrefix($path), $owner);
    }

    /**
     * {@inheritdoc}
     */
    public function move($path, $target): bool
    {
        return @\rename($this->applyPathPrefix($path), $target);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $target): bool
    {
        return @\copy($this->applyPathPrefix($path), $target);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path): bool
    {
        return @\unlink($this->applyPathPrefix($path));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($path): bool
    {
        return @\file_exists($this->applyPathPrefix($path));
    }

    /**
     * {@inheritdoc}
     */
    public function makeDirectory($path, $mode = null, $recursive = false): bool
    {
        $location = $this->applyPathPrefix($path);
        if (!\is_dir($location)) {
            return @\mkdir($location, $mode, $recursive);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function moveDirectory($from, $to, $overwrite = false): bool
    {
        $location    = $this->applyPathPrefix($from);
        $destination = $this->applyPathPrefix($to);
        if ($overwrite && \is_dir($destination) && !$this->deleteDirectory($destination)) {
            return false;
        }

        return $this->move($location, $destination) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory($from, $to, $mode = null): bool
    {
        $location    = $this->applyPathPrefix($from);
        $destination = $this->applyPathPrefix($to);
        if (!\is_dir($location)) {
            return false;
        }
        if (!\is_dir($destination)) {
            $this->makeDirectory($destination, $mode, true);
        }
        foreach (new FilesystemIterator($location, FilesystemIterator::SKIP_DOTS) as $item) {
            $target = Path::combine($destination, $item->getBasename());
            if ($item->isDir()) {
                $path = $item->getPathname();
                if (!$this->copyDirectory($path, $target, $mode)) {
                    return false;
                }
            } elseif (!$this->copy($item->getPathname(), $target)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory($directory, $preserve = false): bool
    {
        $location = $this->applyPathPrefix($directory);
        if (!\is_dir($location)) {
            return false;
        }
        foreach (new FilesystemIterator($location) as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @\rmdir($location);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false): Generator
    {
        $location = $this->applyPathPrefix($directory);

        if (!\is_dir($location)) {
            return;
        }

        $iterator = $recursive
            ? $this->getRecursiveDirectoryIterator($location)
            : $this->getDirectoryIterator($location);

        foreach ($iterator as $file) {
            yield $this->mapFileInfo($file);//Generator<Item>
        }
    }

    /**
     * Get the normalized path from a SplFileInfo object.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function getFilePath(SplFileInfo $file): string
    {
        return \trim(\str_replace('\\', '/', $this->removePathPrefix($file->getPathname())), '/');
    }

    /**
     * @param SplFileInfo $file
     *
     * @return FileInfo
     */
    protected function mapFileInfo(SplFileInfo $file): FileInfo
    {
        $location = $this->removePathPrefix($file->getPathname());
        $options  = [
            'path'     => $location,
            'filename' => $file->getFilename(),
        ];
        if ($file->isDir() || $file->isFile() || $file->isLink()) {
            $options = \array_merge(
                $options,
                [
                    'type'  => $file->getType(),
                    'perms' => $file->getPerms(),
                    'size'  => $file->getSize(),
                    'owner' => $file->getOwner(),
                    'group' => $file->getGroup(),

                    'aTime' => $file->getATime(),
                    'mTime' => $file->getMTime(),
                    'cTime' => $file->getCTime(),

                    'readable'   => $file->isReadable(),
                    'writable'   => $file->isWritable(),
                    'executable' => $file->isExecutable(),
                ]
            );
        }

        return new FileInfo($options);
    }

    /**
     * @param string $path
     * @param int    $mode
     * @return RecursiveIteratorIterator
     */
    protected function getRecursiveDirectoryIterator($path, $mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            $mode
        );
    }

    /**
     * @param string $path
     * @return FilesystemIterator
     */
    protected function getDirectoryIterator($path): FilesystemIterator
    {
        return new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    }

    /**
     * @param Finder        $finder
     * @param string        $archivePath
     * @param callable|null $callback
     * @return bool
     * @throws Exception
     */
    public function zip(Finder $finder, string $archivePath, callable $callback = null): bool
    {
        $zipArchive = new ZipArchive();
        $count      = $finder->count();
        $index      = 0;
        $basePath   = \rtrim($this->getPathPrefix(), '/') . '/';
        if (($code = $zipArchive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) !== true) {
            throw new Exception('Archive file could not be created.', $code);
        }
        foreach ($finder->files() as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $real = $file->getRealpath();
            if ($real !== false && !$file->isDir()) {
                $zipArchive->addFile($real, \str_replace($basePath, '', $file->getPathname()));
                if (\is_callable($callback)) {
                    $callback($count, $index);
                    ++$index;
                }
            }
        }
        if ($zipArchive->close() !== true) {
            throw new Exception('Archive file could not be created.');
        }

        return true;
    }
}
