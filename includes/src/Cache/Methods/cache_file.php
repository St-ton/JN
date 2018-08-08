<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;

use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_file
 * Implements caching via filesystem
 * @package Cache\Methods
 */
class cache_file implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_file
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID     = 'file_journal';
        $this->options       = $options;
        $this->isInitialized = true;
        self::$instance      = $this;
    }

    /**
     * @param string $cacheID
     * @return bool|string
     */
    private function getFileName($cacheID)
    {
        return \is_string($cacheID)
            ? $this->options['cache_dir'] . $cacheID . $this->options['file_extension']
            : false;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        $dir = $this->options['cache_dir'];
        if (!\is_dir($dir) && \mkdir($dir) === false && !\is_dir($dir)) {
            return false;
        }
        $fileName = $this->getFileName($cacheID);
        $info     = \pathinfo($fileName);
        if ($fileName === false || \strpos(\realpath($info['dirname']) . '/', $dir) !== 0) {
            return false;
        }

        return \file_put_contents(
                $fileName,
                \serialize([
                    'value'    => $content,
                    'lifetime' => $expiration ?? $this->options['lifetime']
                ])
            ) !== false;
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($keyValue, $expiration = null): bool
    {
        foreach ($keyValue as $_key => $_value) {
            $this->store($_key, $_value, $expiration);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        $fileName = $this->getFileName($cacheID);
        if ($fileName !== false && \file_exists($fileName)) {
            $data = \unserialize(\file_get_contents($fileName));
            if ($data['lifetime'] === 0 || (\time() - \filemtime($fileName)) < $data['lifetime']) {
                return $data['value'];
            }
            $this->flush($cacheID);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        $res = [];
        foreach ($cacheIDs as $_cid) {
            $res[$_cid] = $this->load($_cid);
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        $res = !\is_dir($this->options['cache_dir'])
            ? \mkdir($this->options['cache_dir']) && \is_dir($this->options['cache_dir'])
            : true;

        return $res && \is_writable($this->options['cache_dir']);
    }

    /**
     * @param string $str
     * @return bool
     */
    private function recursiveDelete(string $str): bool
    {
        if (\is_file($str)) {
            return \unlink($str);
        }
        if (\is_dir($str)) {
            $scan = \glob(\rtrim($str, '/') . '/*');
            foreach ($scan as $index => $path) {
                $this->recursiveDelete($path);
            }

            return ($str === $this->options['cache_dir'])
                ? true
                : \rmdir($str);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        $fileName = $this->getFileName($cacheID);

        return $fileName !== false && \file_exists($fileName) && \unlink($fileName);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        $this->journal = null;

        return $this->recursiveDelete($this->options['cache_dir']);
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        $dir   = \opendir($this->options['cache_dir']);
        $total = 0;
        $num   = 0;
        while ($dir && ($file = \readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (\is_dir($this->options['cache_dir'] . $file)) {
                    //read sub dir
                    $subDir = \opendir($this->options['cache_dir'] . $file);
                    while ($subDir && ($f = \readdir($subDir)) !== false) {
                        if ($f !== '.' && $f !== '..') {
                            $filePath = $this->options['cache_dir'] . $file . '/' . $f;
                            $total    += \filesize($filePath);
                            ++$num;
                        }
                    }
                    \closedir($subDir);
                } elseif (\is_file($this->options['cache_dir'] . $file)) {
                    $total += \filesize($this->options['cache_dir'] . $file);
                    ++$num;
                }
            }
        }
        if ($dir !== false) {
            \closedir($dir);
        }

        return [
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        ];
    }
}
