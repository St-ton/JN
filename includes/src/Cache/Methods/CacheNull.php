<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;

/**
 * Class CacheNull
 *
 * emergency fallback caching method
 * @package JTL\Cache\Methods
 */
class CacheNull implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setIsInitialized(true);
        $this->setJournalID('null_journal');
        $this->setOptions($options);
        self::$instance = $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, int $expiration = null): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function storeMulti(array $idContent, int $expiration = null): bool
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
    public function flushTags($tags): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        return [];
    }
}
