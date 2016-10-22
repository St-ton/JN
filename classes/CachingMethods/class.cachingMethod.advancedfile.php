<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_advancedfile
 *
 * Implements caching via filesystem where tags are not stored in a central file
 * but organized in folder and symlinked to the actual cache entry
 */
class cache_advancedfile implements ICachingMethod
{
    use JTLCacheTrait {
        test as traitTest;
    }

    /**
     * @var cache_advancedfile|null
     */
    public static $instance = null;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID     = 'advancedfile_journal';
        $this->options       = $options;
        $this->isInitialized = true;
        self::$instance      = $this;

        return $this;
    }

    /**
     * @param string $cacheID
     * @return bool|string
     */
    private function getFileName($cacheID)
    {
        return $this->options['cache_dir'] . $cacheID . $this->options['file_extension'];
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
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
        $fileName = $this->getFileName($cacheID);
        if ($fileName === false) {
            return false;
        }

        return (file_put_contents(
                $fileName,
                serialize(
                    array(
                        'value'    => $content,
                        'lifetime' => ($expiration === null) ?
                            $this->options['lifetime'] :
                            $expiration
                    )
                )
            ) !== false) ? true : false;
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
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
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        $fileName = $this->getFileName($cacheID);
        if ($fileName !== false && file_exists($fileName)) {
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
        $res = true;
        if (!is_dir($this->options['cache_dir'])) {
            $res = mkdir($this->options['cache_dir']);
        }

        return $res && is_writable($this->options['cache_dir']);
    }

    /**
     * @return bool
     */
    public function test()
    {
        return $this->traitTest() &&
            touch($this->options['cache_dir'] . 'check') &&
            symlink($this->options['cache_dir'] . 'check', $this->options['cache_dir'] . 'link') &&
            readlink($this->options['cache_dir'] . 'link') === $this->options['cache_dir'] . 'check' &&
            unlink($this->options['cache_dir'] . 'link') &&
            unlink($this->options['cache_dir'] . 'check');
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        $fileName = $this->getFileName($cacheID);

        return ($fileName !== false && file_exists($fileName)) ? unlink($fileName) : false;
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        $rdi = new RecursiveDirectoryIterator($this->options['cache_dir'], FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
        foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
            if ($value->isLink() || $value->isFile()) {
                unlink($value);
            } elseif ($value->isDir()) {
                rmdir($value);
            }
        }

        return true;
    }

    /**
     * this currently only calculate size/file count for real cache entries
     * and ignores symlinks which always are located in sub dirs
     *
     * @return array
     */
    public function getStats()
    {
        $dir   = opendir($this->options['cache_dir']);
        $total = 0;
        $num   = 0;
        while ($dir && ($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
//                if (is_dir($this->options['cache_dir'] . $file)) {
//                    //read sub dir
//                    $subDir = opendir($this->options['cache_dir'] . $file);
//                    while ($subDir && ($f = readdir($subDir)) !== false) {
//                        if ($f !== '.' && $f !== '..') {
//                            $filePath = $this->options['cache_dir'] . $file . '/' . $f;
//                            if (!is_link($filePath)) {
//                                $total += filesize($filePath);
//                            }
//                            ++$num;
//                        }
//                    }
//                    closedir($subDir);
//                } else
                if (is_file($this->options['cache_dir'] . $file)) {
                    $total += filesize($this->options['cache_dir'] . $file);
                    ++$num;
                }
            }
        }
        if ($dir !== false) {
            closedir($dir);
        }

        return array(
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        );
    }

    /**
     * @param array  $tags
     * @param string $cacheID
     * @return bool
     */
    public function setCacheTag($tags = array(), $cacheID)
    {
        $fileName = $this->getFileName($cacheID);
        if ($fileName === false) {
            return false;
        }
        $res = false;
        if (is_string($tags)) {
            $tags = array($tags);
        }
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                //create subdirs for every underscore
                $dirs = explode('_', $tag);
                $path = $this->options['cache_dir'];
                foreach ($dirs as $dir) {
                    $path .= $dir . '/';
                    if (!file_exists($path)) {
                        mkdir($path);
                    }
                }
                if (!file_exists($path . $cacheID)) {
                    symlink($fileName, $path . $cacheID);
                }
            }
            $res = true;
        }

        return $res;
    }

    /**
     * removes cache IDs associated with tag from cache
     *
     * @param array $tags
     * @return int
     */
    public function flushTags($tags)
    {
        $deleted = 0;
        if (is_string($tags)) {
            $tags = [$tags];
        }
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $dirs = explode('_', $tag);
                $path = $this->options['cache_dir'];
                foreach ($dirs as $dir) {
                    $path .= $dir . '/';
                }
                if (is_dir($path)) {
                    $rdi = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
                    foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
                        $res = false;
                        if ($value->isLink()) {
                            //cache entries may have multiple tags - so check if the real entry still exists
                            if (($target = readlink($value)) !== false) {
                                if (is_file($target)) {
                                    //delete real cache entry
                                    $res = unlink($target);
                                }
                            }
                            //delete symlink to the entry
                            unlink($value);
                        }
                        if ($res === true) {
                            //only count cache files, not symlinks
                            ++$deleted;
                        }
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * clean up journal after deleting cache entries
     * not needed for this method
     *
     * @param string|array $cacheID
     * @return bool
     */
    public function clearCacheTags($cacheID)
    {
        return true;
    }

    /**
     * get cache IDs by cache tag(s)
     *
     * @param array|string $tags
     * @return array
     */
    public function getKeysByTag($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        if (is_array($tags)) {
            $res = [];
            foreach ($tags as $tag) {
                $dirs = explode('_', $tag);
                $path = $this->options['cache_dir'];
                foreach ($dirs as $dir) {
                    $path .= $dir . '/';
                }
                if (is_dir($path)) {
                    $rdi = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
                    foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
                        if ($value->isFile()) {
                            $res[] = $value->getFilename();
                        }
                    }
                }
            }

            //remove duplicate keys from array and return it
            return array_unique($res);
        }

        return [];
    }
}
