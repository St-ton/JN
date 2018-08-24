<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;


use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_memcache
 * Implements the Memcache memory object caching system - no "d" at the end
 * @package Cache\Methods
 */
class cache_memcache implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_memcache
     */
    public static $instance;

    /**
     * @var \Memcache
     */
    private $_memcache;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcache($options['memcache_host'], $options['memcache_port']);
            $this->isInitialized = true;
            $this->journalID     = 'memcache_journal';
            //@see http://php.net/manual/de/memcached.expiration.php
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
    private function setMemcache($host, $port): ICachingMethod
    {
        if ($this->_memcache !== null) {
            $this->_memcache->close();
        }
        $this->_memcache = new \Memcache();
        $this->_memcache->addServer($host, (int)$port);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return $this->_memcache->set(
            $this->options['prefix'] . $cacheID,
            $content,
            0,
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($keyValue, $expiration = null): bool
    {
        return $this->_memcache->set($this->prefixArray($keyValue), $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return $this->_memcache->get($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        if (!\is_array($cacheIDs)) {
            return [];
        }
        $prefixedKeys = [];
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray($this->_memcache->get($prefixedKeys));

        // fill up result
        return \array_merge(\array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return \class_exists('Memcache');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return $this->_memcache->delete($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->_memcache->flush();
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        $stats = $this->_memcache->getStats();

        return [
            'entries' => $stats['curr_items'],
            'hits'    => $stats['get_hits'],
            'misses'  => $stats['get_misses'],
            'inserts' => $stats['cmd_set'],
            'mem'     => $stats['bytes']
        ];
    }
}
