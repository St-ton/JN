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
 * @package Cache\Methods
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
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($keyValue, $expiration = null): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        return [];
    }
}
