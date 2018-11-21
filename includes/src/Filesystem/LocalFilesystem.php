<?php

namespace Filesystem;

use SplFileInfo;

class LocalFilesystem extends AbstractFilesystem
{
    /**
     * {@inheritdoc}
     */
    public function getMeta($path)
    {
        $location = $this->applyPathPrefix($path);

        return $this->mapFileInfo(new SplFileInfo($location));
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, $mode = null)
    {
        $location = $this->applyPathPrefix($path);

        if (file_exists($location)) {
            return file_get_contents($location);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, $mode = null)
    {
        $location = $this->applyPathPrefix($path);

        if (file_put_contents($location, $contents) === false) {
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
    public function cwd()
    {
        return @getcwd();
    }

    /**
     * {@inheritdoc}
     */
    public function chdir($path)
    {
        $location = $this->applyPathPrefix($path);

        return @chdir($location);
    }

    /**
     * {@inheritdoc}
     */
    public function chgrp($path, $group)
    {
        $location = $this->applyPathPrefix($path);

        return @chgrp($location, $group);
    }

    /**
     * {@inheritdoc}
     */
    public function chmod($path, $mode = null)
    {
        $location = $this->applyPathPrefix($path);

        if ($mode) {
            return @chmod($location, $mode);
        }

        return substr(sprintf('%o', fileperms($location)), -4);
    }

    /**
     * {@inheritdoc}
     */
    public function chown($path, $owner)
    {
        $location = $this->applyPathPrefix($path);

        return @chown($location, $owner);
    }

    /**
     * {@inheritdoc}
     */
    public function move($path, $target)
    {
        $location = $this->applyPathPrefix($path);

        return @rename($location, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $target)
    {
        $location = $this->applyPathPrefix($path);

        return @copy($location, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);

        return @unlink($location);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($path)
    {
        $location = $this->applyPathPrefix($path);

        return @file_exists($location);
    }

    /**
     * {@inheritdoc}
     */
    public function makeDirectory($path, $mode = null, $recursive = false)
    {
        $location = $this->applyPathPrefix($path);

        if (!is_dir($location)) {
            return @mkdir($location, $mode, $recursive);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function moveDirectory($from, $to, $overwrite = false)
    {
        $location = $this->applyPathPrefix($from);
        $destination = $this->applyPathPrefix($to);

        if ($overwrite && is_dir($destination)) {
            if (!$this->deleteDirectory($destination)) {
                return false;
            }
        }

        return $this->move($location, $destination) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory($from, $to, $mode = null)
    {
        $location = $this->applyPathPrefix($from);
        $destination = $this->applyPathPrefix($to);

        if (!is_dir($location)) {
            return false;
        }

        if (!is_dir($destination)) {
            $this->makeDirectory($destination, $mode, true);
        }

        $items = new FilesystemIterator($location, FilesystemIterator::SKIP_DOTS);

        foreach ($items as $item) {
            $target = Path::combine($destination, $item->getBasename());

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!$this->copyDirectory($path, $target, $mode)) {
                    return false;
                }
            } else {
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory($directory, $preserve = false)
    {
        $location = $this->applyPathPrefix($directory);

        if (!is_dir($location)) {
            return false;
        }

        $items = new FilesystemIterator($location);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($location);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $location = $this->applyPathPrefix($directory);

        if (!is_dir($location)) {
            return;
        }

        $iterator = $recursive
            ? $this->getRecursiveDirectoryIterator($location)
            : $this->getDirectoryIterator($location);

        foreach ($iterator as $file) {
            yield $this->mapFileInfo($file);
        }
    }

    /**
     * Get the normalized path from a SplFileInfo object.
     *
     * @param \SplFileInfo $file
     *
     * @return string
     */
    protected function getFilePath(SplFileInfo $file)
    {
        $location = $file->getPathname();
        $path = $this->removePathPrefix($location);

        return trim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return FileInfo
     */
    protected function mapFileInfo(SplFileInfo $file)
    {
        $location = $this->removePathPrefix($file->getPath());

        $options = [
            'path' => (string) $location,
            'filename' => $file->getFilename(),
        ];

        if ($file->isDir() || $file->isFile() || $file->isLink()) {
            $options = array_merge($options, [
                'type' => $file->getType(),
                'perms' => $file->getPerms(),
                'size' => $file->getSize(),
                'owner' => $file->getOwner(),
                'group' => $file->getGroup(),

                'aTime' => $file->getATime(),
                'mTime' => $file->getMTime(),
                'cTime' => $file->getCTime(),

                'readable' => $file->isReadable(),
                'writable' => $file->isWritable(),
                'executable' => $file->isExecutable(),
            ]);
        }

        return new FileInfo($options);
    }

    /**
     * @param string $path
     * @param int    $mode
     *
     * @return RecursiveIteratorIterator
     */
    protected function getRecursiveDirectoryIterator($path, $mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            $mode
        );

        return $iterator;
    }

    /**
     * @param string $path
     *
     * @return FilesystemIterator
     */
    protected function getDirectoryIterator($path)
    {
        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

        return $iterator;
    }
}