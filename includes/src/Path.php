<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Path Class
 */
class Path
{
    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function combine(): string
    {
        $paths = func_get_args();

        if (!is_array($paths) || count($paths) === 0) {
            throw new InvalidArgumentException('empty or invalid paths');
        }

        foreach ($paths as $i => $path) {
            $paths[$i] = static::clean($path);
        }

        $path = implode(DIRECTORY_SEPARATOR, $paths);

        $path = static::clean($path);

        return $path;
    }

    /**
     * @param string $path
     * @param bool   $real
     *
     * @return string
     */
    public static function getDirectoryName(string $path, bool $real = true): string
    {
        return ($real && is_dir($path)) ? realpath(dirname($path)) : dirname($path);
    }

    /**
     * @param string $path
     *
     * @return mixed|string
     */
    public static function getFileName(string $path): string
    {
        return self::hasExtension($path) ? self::getFileNameWithoutExtension($path).'.'
            .self::getExtension($path) : self::getFileNameWithoutExtension($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getFileNameWithoutExtension($path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function hasExtension(string $path): bool
    {
        return mb_strlen(self::getExtension($path)) > 0;
    }

    /**
     * Add directory separator.
     *
     * @param $path
     *
     * @return string
     */
    public static function addTrailingSlash($path)
    {
        return static::removeTrailingSlash($path) . DIRECTORY_SEPARATOR;
    }

    /**
     * Remove directory separator.
     *
     * @param $path
     *
     * @return string
     */
    public static function removeTrailingSlash($path)
    {
        return rtrim($path, '/\\');
    }

    /**
     * Normalize path [/var/www/../test => /var/test].
     *
     * @param $path
     * @param bool $trailingSlash
     *
     * @return bool|string
     */
    public static function clean($path, $trailingSlash = false)
    {
        $parts = array();
        $path = strtr($path, '\\', '/');
        $prefix = '';
        $absolute = false;

        if (preg_match('{^([0-9a-z]+:(?://(?:[a-z]:)?)?)}i', $path, $match)) {
            $prefix = $match[1];
            $path = substr($path, strlen($prefix));
        }

        if (substr($path, 0, 1) === '/') {
            $absolute = true;
            $path = substr($path, 1);
        }

        $up = false;
        foreach (explode('/', $path) as $chunk) {
            if ('..' === $chunk && ($absolute || $up)) {
                array_pop($parts);
                $up = !(empty($parts) || '..' === end($parts));
            } elseif ('.' !== $chunk && '' !== $chunk) {
                $parts[] = $chunk;
                $up = '..' !== $chunk;
            }
        }

        $path = $prefix.($absolute ? '/' : '').implode('/', $parts);

        if ($trailingSlash) {
            $path = static::addTrailingSlash($path);
        }

        return $path;
    }
}
