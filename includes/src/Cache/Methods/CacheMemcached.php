<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;
use Memcached;
use function Functional\first;

/**
 * Class CacheMemcached
 * Implements the Memcached memory object caching system - notice the "d" at the end
 *
 * @package JTL\Cache\Methods
 */
class CacheMemcached implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var Memcached
     */
    private ?Memcached $memcached = null;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcached($options['memcache_host'], (int)$options['memcache_port']);
            $this->memcached->setOption(Memcached::OPT_PREFIX_KEY, $options['prefix']);
            $this->setIsInitialized(true);
            $test = $this->test();
            $this->setError($test === true ? '' : $this->memcached->getResultMessage());
            $this->setJournalID('memcached_journal');
            // @see http://php.net/manual/de/memcached.expiration.php
            $options['lifetime'] = \min(60 * 60 * 24 * 30, $options['lifetime']);
            $this->setOptions($options);
            self::$instance = $this;
        }
    }

    /**
     * @param string $host
     * @param int    $port
     * @return $this
     */
    private function setMemcached(string $host, int $port): ICachingMethod
    {
        $this->memcached?->quit();
        $this->memcached = new Memcached();
        $this->memcached->addServer($host, $port);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, int $expiration = null): bool
    {
        return $this->memcached->set(
            $cacheID,
            $content,
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti(array $idContent, int $expiration = null): bool
    {
        return $this->memcached->setMulti($idContent, $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return $this->memcached->get($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        return \array_merge(\array_fill_keys($cacheIDs, false), $this->memcached->getMulti($cacheIDs));
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return \class_exists('Memcached');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return $this->memcached->delete($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key): bool
    {
        $res = $this->memcached->get($key);

        return ($res !== false || $this->memcached->getResultCode() === Memcached::RES_SUCCESS);
    }

    /**
     * @todo: get the right array index, not just the first one
     * @inheritdoc
     */
    public function getStats(): array
    {
        if (\method_exists($this->memcached, 'getStats')) {
            $stats = $this->memcached->getStats();
            if (\is_array($stats) && ($stat = first($stats)) !== null) {
                return [
                    'entries' => $stat['curr_items'],
                    'hits'    => $stat['get_hits'],
                    'misses'  => $stat['get_misses'],
                    'inserts' => $stat['cmd_set'],
                    'mem'     => $stat['bytes']
                ];
            }
        }

        return [];
    }
}
