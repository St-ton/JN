<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_apc
 * implements the APC Opcode Cache
 */
class cache_apc extends JTLCacheHelper implements ICachingMethod
{
    /**
     * @var cache_apc|null
     */
    public static $instance = null;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->journalID     = 'apc_journal';
        $this->options       = $options;
        self::$instance      = $this;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return cache_apc
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
        return apc_store($this->options['prefix'] . $cacheID, $content, ($expiration === null) ? $this->options['lifetime'] : $expiration);
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     *
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        return apc_store($this->prefixArray($keyValue), null, ($expiration === null) ? $this->options['lifetime'] : $expiration);
    }

    /**
     * @param string $cacheID
     *
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        return apc_fetch($this->options['prefix'] . $cacheID);
    }

    /**
     * @param array $cacheIDs
     *
     * @return bool|mixed
     */
    public function loadMulti($cacheIDs)
    {
        if (!is_array($cacheIDs)) {
            return false;
        }
        $prefixedKeys = array();
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray(apc_fetch($prefixedKeys));

        //fill up with false values
        return array_merge(array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists('apc_store') && function_exists('apc_exists');
    }

    /**
     * @param string $cacheID
     *
     * @return bool
     */
    public function flush($cacheID)
    {
        return apc_delete($this->options['prefix'] . $cacheID);
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return apc_clear_cache('user');
    }

    /**
     * @param string $cacheID
     *
     * @return bool|string[]
     */
    public function keyExists($cacheID)
    {
        return apc_exists($this->options['prefix'] . $cacheID);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        try {
            $tmp   = apc_cache_info('user');
            $stats = array(
                'entries' => (isset($tmp['num_entries'])) ? $tmp['num_entries'] : 0,
                'hits'    => (isset($tmp['num_hits'])) ? $tmp['num_hits'] : 0,
                'misses'  => (isset($tmp['num_misses'])) ? $tmp['num_misses'] : 0,
                'inserts' => (isset($tmp['num_inserts'])) ? $tmp['num_inserts'] : 0,
                'mem'     => (isset($tmp['mem_size'])) ? $tmp['mem_size'] : 0
            );
        } catch (Exception $e) {
            $stats = array();
        }

        return $stats;
    }
}
