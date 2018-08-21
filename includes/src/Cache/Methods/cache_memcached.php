<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;


use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_memcached
 * Implements the Memcached memory object caching system - notice the "d" at the end
 *
 * @warning Untested
 * @package Cache\Methods
 */
class cache_memcached implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_memcached
     */
    public static $instance;

    /**
     * @var \Memcached
     */
    private $_memcached;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcached($options['memcache_host'], $options['memcache_port']);
            $this->_memcached->setOption(\Memcached::OPT_PREFIX_KEY, $options['prefix']);
            $this->isInitialized = true;
            $test                = $this->test();
            $this->setError($test === true ? '' : $this->_memcached->getResultMessage());
            $this->journalID = 'memcached_journal';
            // @see http://php.net/manual/de/memcached.expiration.php
            $options['lifetime'] = \min(60 * 60 * 24 * 30, $options['lifetime']);
            $this->options       = $options;
            self::$instance      = $this;
        }
    }

    /**
     * @param string $host
     * @param int    $port
     * @return $this
     */
    private function setMemcached($host, $port): ICachingMethod
    {
        if ($this->_memcached !== null) {
            $this->_memcached->quit();
        }
        $this->_memcached = new \Memcached();
        $this->_memcached->addServer($host, (int)$port);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return $this->_memcached->set(
            $cacheID,
            $content,
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($keyValue, $expiration = null): bool
    {
        return $this->_memcached->setMulti($keyValue, $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return $this->_memcached->get($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        if (!\is_array($cacheIDs)) {
            return [];
        }

        return \array_merge(\array_fill_keys($cacheIDs, false), $this->_memcached->getMulti($cacheIDs));
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
        return $this->_memcached->delete($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->_memcached->flush();
    }

    /**
     * @inheritdoc
     */
    public function keyExists($cacheID): bool
    {
        $res = $this->_memcached->get($cacheID);

        return ($res !== false || $this->_memcached->getResultCode() === \Memcached::RES_SUCCESS);
    }

    /**
     * @todo: get the right array index, not just the first one
     * @inheritdoc
     */
    public function getStats(): array
    {
        if (\method_exists($this->_memcached, 'getStats')) {
            $stats = $this->_memcached->getStats();
            if (\is_array($stats)) {
                foreach ($stats as $key => $_stat) {
                    return [
                        'entries' => $_stat['curr_items'],
                        'hits'    => $_stat['get_hits'],
                        'misses'  => $_stat['get_misses'],
                        'inserts' => $_stat['cmd_set'],
                        'mem'     => $_stat['bytes']
                    ];
                }
            }
        }

        return [];
    }
}
