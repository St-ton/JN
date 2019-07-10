<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use Exception;
use JTL\Path;

/**
 * Class AbstractFilesystem
 * @package JTL\Filesystem
 */
abstract class AbstractFilesystem implements IFilesystem
{
    /**
     * @var array options
     */
    protected $options;

    /**
     * @var string
     */
    protected $pathPrefix;

    /**
     * Filesystem constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = \array_merge(
            ['root' => null],
            $options
        );
        $this->setPathPrefix($this->options['root']);
    }

    /**
     * Set the path prefix.
     *
     * @param string $prefix
     */
    public function setPathPrefix(string $prefix) : void
    {
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
    public function getPathPrefix() : string
    {
        return $this->pathPrefix;
    }

    /**
     * Prefix a path.
     *
     * @param string $path
     * @return string prefixed path
     * @throws Exception
     */
    public function applyPathPrefix(string $path) : string
    {
        $path = Path::clean($path);
        if ($this->hasPathPrefix($path)) {
            return $path;
        }
        $rooted = Path::combine($this->getPathPrefix(), $path);
        if (!$this->hasPathPrefix($rooted)) {
            throw new Exception(\sprintf("Path '%s' is not within defined root", $rooted));
        }

        return $rooted;
    }

    /**
     * Remove a path prefix.
     *
     * @param string $path
     * @return string path without the prefix
     */
    public function removePathPrefix(string $path) : string
    {
        if (!$this->hasPathPrefix($path)) {
            return $path;
        }
        $path = \substr($path, \strlen($this->getPathPrefix()) + 1);

        return \ltrim($path, \DIRECTORY_SEPARATOR);
    }

    /**
     * Has path prefix.
     *
     * @param $path
     * @return bool
     */
    public function hasPathPrefix(string $path) : bool
    {
        return \strpos($path, $this->getPathPrefix()) === 0;
    }
}
