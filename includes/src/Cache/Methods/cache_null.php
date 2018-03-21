<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;

use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_null
 * emergency fallback caching method
 */
class cache_null implements ICachingMethod
{
    use JTLCacheTrait;
    
    /**
     * @var cache_null|null
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->options       = $options;
        $this->journalID     = 'null_journal';
        self::$instance      = $this;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null) : bool
    {
        return false;
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        return false;
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function load($cacheID)
    {
        return false;
    }

    /**
     * @param array $cacheIDs
     * @return bool
     */
    public function loadMulti($cacheIDs)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isAvailable() : bool
    {
        return true;
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID) : bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function flushAll() : bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function getStats() : array
    {
        return [];
    }
}
