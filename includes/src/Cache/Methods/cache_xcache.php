<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;

use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_xcache
 * Implements the XCache Opcode Cache
 *
 * @warning Untested
 * @warning Does not support caching groups
 * @package Cache\Methods
 */
class cache_xcache implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_xcache
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID = 'xcache_journal';
        if ($this->isAvailable() === true) {
            $this->options       = $options;
            $this->isInitialized = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return xcache_set(
            $this->options['prefix'] . $cacheID,
            ($this->must_be_serialized($content)
                ? serialize($content)
                : $content),
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($keyValue, $expiration = null): bool
    {
        $res = true;
        foreach ($keyValue as $_key => $_value) {
            $res = $res && $this->store($_key, $_value, $expiration);
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        if (xcache_isset($this->options['prefix'] . $cacheID) === true) {
            $data = xcache_get($this->options['prefix'] . $cacheID);

            return $this->is_serialized($data) ? unserialize($data) : $data;
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
        return \function_exists('xcache_set');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return xcache_unset($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return xcache_unset_by_prefix($this->options['prefix']);
    }

    /**
     * @inheritdoc
     */
    public function keyExists($cacheID): bool
    {
        return xcache_isset($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        return [];
    }
}
