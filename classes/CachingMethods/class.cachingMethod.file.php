<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_file
 * Implements caching via filesystem
 */
class cache_file extends JTLCacheHelper implements ICachingMethod
{
    /**
     * @var cache_file|null
     */
    public static $instance = null;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID     = 'file_journal';
        $this->options       = $options;
        $this->isInitialized = true;
        self::$instance      = $this;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return cache_file
     */
    public static function getInstance($options)
    {
        //check if class was initialized before
        return (self::$instance !== null) ? self::$instance : new self($options);
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     *
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        $dir = $this->options['cache_dir'];
        if (!is_dir($dir)) {
            $createDir = mkdir($dir);
            if ($createDir === false) {
                return false;
            }
        }

        return (file_put_contents(
                $dir . $cacheID . $this->options['file_extension'],
                serialize(
                    array(
                        'value'    => $content,
                        'lifetime' => ($expiration === null) ? $this->options['lifetime'] : $expiration
                    )
                )
            ) !== false) ? true : false;
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     *
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        foreach ($keyValue as $_key => $_value) {
            $this->store($_key, $_value, $expiration);
        }

        return true;
    }

    /**
     * @param string $cacheID
     *
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        $fileName = $this->options['cache_dir'] . $cacheID . $this->options['file_extension'];
        if (file_exists($fileName)) {
            $data = unserialize(file_get_contents($fileName));
            if ($data['lifetime'] === 0 || (time() - filemtime($fileName)) < $data['lifetime']) {
                return $data['value'];
            }
            $this->flush($cacheID);
        }

        return false;
    }

    /**
     * @param array $cacheIDs
     *
     * @return array|bool
     */
    public function loadMulti($cacheIDs)
    {
        $res = array();
        foreach ($cacheIDs as $_cid) {
            $res[$_cid] = $this->load($cacheIDs);
        }

        return $res;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        if (!is_dir($this->options['cache_dir'])) {
            $res = mkdir($this->options['cache_dir']);
            if ($res === false) {
                return false;
            }
        }

        return is_writable($this->options['cache_dir']);
    }

    /**
     * @param string $cacheID
     *
     * @return bool
     */
    public function flush($cacheID)
    {
        $fileName = $this->options['cache_dir'] . $cacheID . $this->options['file_extension'];

        return (file_exists($fileName)) ? unlink($fileName) : false;
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        $res = false;
        if (file_exists($this->options['cache_dir'])) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->options['cache_dir'], RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            $res = true;
            foreach ($iterator as $fileInfo) {
                $func = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
                $res  = $res && $func($fileInfo->getRealPath());
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $dir   = opendir($this->options['cache_dir']);
        $total = 0;
        $num   = 0;
        while ($dir && ($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($this->options['cache_dir'] . $file)) {
                    //read sub dir
                    $subDir = opendir($this->options['cache_dir'] . $file);
                    while ($subDir && ($f = readdir($subDir)) !== false) {
                        if ($f !== '.' && $f !== '..') {
                            $filePath = $this->options['cache_dir'] . $file . '/' . $f;
                            if (time() - filemtime($filePath) > $this->options['lifetime']) {
                                //file expired, delete and don't count
                                $this->flush(str_replace($this->options['file_extension'], '', $f));
                            } else {
                                $size = filesize($filePath);
                                $total += $size;
                                $num++;
                            }
                        }
                    }
                    closedir($subDir);
                } elseif (is_file($this->options['cache_dir'] . $file)) {
                    if (time() - filemtime($this->options['cache_dir'] . $file) > $this->options['lifetime']) {
                        //file expired, delete and don't count
                        $this->flush(str_replace($this->options['file_extension'], '', $file));
                    } else {
                        $size = filesize($this->options['cache_dir'] . $file);
                        $total += $size;
                        $num++;
                    }
                }
            }
        }
        closedir($dir);

        return array(
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        );
    }
}
