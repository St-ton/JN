<?php

namespace Filesystem;

use Path;

abstract class AbstractFilesystem implements IFilesystem
{
    /**
     * @var array options
     */
    protected $options;

    /**
     * @var string path prefix
     */
    protected $pathPrefix;

    /**
     * Filesystem constructor.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->options = array_merge(array(
            'root' => null,
        ), $options);

        $this->setPathPrefix($this->options['root']);
    }

    /**
     * Set the path prefix.
     *
     * @param string $prefix
     */
    public function setPathPrefix($prefix)
    {
        $prefix = (string) $prefix;

        if ($prefix === '') {
            $this->pathPrefix = null;

            return;
        }

        $this->pathPrefix = Path::clean($prefix);
    }

    /**
     * Get the path prefix.
     *
     * @return string path prefix
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * Prefix a path.
     *
     * @param string $path
     *
     * @return string prefixed path
     *
     * @throws Exception
     */
    public function applyPathPrefix($path)
    {
        $path = Path::clean($path);

        if ($this->hasPathPrefix($path)) {
            return $path;
        }

        $rooted = Path::combine($this->getPathPrefix(), $path);

        if (!$this->hasPathPrefix($rooted)) {
            throw new Exception("Path '{$rooted}' is not within defined root");
        }

        return $rooted;
    }

    /**
     * Remove a path prefix.
     *
     * @param string $path
     *
     * @return string path without the prefix
     */
    public function removePathPrefix($path)
    {
        if (!$this->hasPathPrefix($path)) {
            return $path;
        }

        $path = substr($path, strlen($this->getPathPrefix()) + 1);
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        return $path;
    }

    /**
     * Has path prefix.
     *
     * @param $path
     *
     * @return bool
     */
    public function hasPathPrefix($path)
    {
        $prefix = $this->getPathPrefix();

        return strpos($path, $prefix) === 0;
    }
}