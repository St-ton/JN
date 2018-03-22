<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache;

/**
 * Interface JTLCacheInterface
 * @package Cache
 */
interface JTLCacheInterface
{
    /**
     * @return array
     */
    public function getCachingGroups() : array;

    /**
     * @param array $options
     * @return JTLCacheInterface
     */
    public function setOptions(array $options = []);

    /**
     * @param string $methodName
     * @return bool
     */
    public function setCache(string $methodName) : bool;

    /**
     * @return array
     */
    public function getJtlCacheConfig();

    /**
     * @return JTLCacheInterface
     */
    public function setJtlCacheConfig();

    /**
     * @return JTLCacheInterface
     */
    public function init();

    /**
     * @return array
     */
    public function getOptions() : array;

    /**
     * @param string      $host
     * @param int         $port
     * @param null|string $pass
     * @param null|int    $database
     * @return JTLCacheInterface
     */
    public function setRedisCredentials($host, $port, $pass = null, $database = null);

    /**
     * @param string $host
     * @param int    $port
     * @return JTLCacheInterface
     */
    public function setMemcacheCredentials($host, $port);

    /**
     * @param string $host
     * @param int    $port
     * @return JTLCacheInterface
     */
    public function setMemcachedCredentials($host, $port);

    /**
     * @param string        $cacheID
     * @param null|callable $callback
     * @param null|mixed    $customData
     * @return mixed
     */
    public function _get($cacheID, $callback = null, $customData = null);

    /**
     * @param string     $cacheID
     * @param mixed      $content
     * @param null|array $tags
     * @param null|int   $expiration
     * @return mixed
     */
    public function _set($cacheID, $content, $tags = null, $expiration = null);

    /**
     * @param array      $keyValue
     * @param array|null $tags
     * @param array|null $expiration
     * @return bool
     */
    public function _setMulti($keyValue, $tags = null, $expiration = null);

    /**
     * @param array $cacheIDs
     * @return array
     */
    public function _getMulti($cacheIDs);

    /**
     * @param string|array $groupID
     * @return bool
     */
    public function _isCacheGroupActive($groupID) : bool;

    /**
     * @param array|string $tags
     * @return mixed
     */
    public function getKeysByTag($tags) : array;

    /**
     * @param array|string $tags
     * @param string       $cacheID
     * @return bool
     */
    public function _setCacheTag($tags, $cacheID) : bool;

    /**
     * @param int $lifetime
     * @return JTLCacheInterface
     */
    public function _setCacheLifetime($lifetime);

    /**
     * @param string $dir
     * @return JTLCacheInterface
     */
    public function _setCacheDir($dir);

    /**
     * @return JTLCacheInterface
     */
    public function _getActiveMethod();

    /**
     * @param string|null $cacheID
     * @param array|null  $tags
     * @param array|null  $hookInfo
     * @return bool|int
     */
    public function _flush($cacheID = null, $tags = null, $hookInfo = null);

    /**
     * @param array|string $tags
     * @param null         $hookInfo
     * @return int
     */
    public function _flushTags($tags, $hookInfo = null) : int;

    /**
     * @return bool
     */
    public function _flushAll() : bool;

    /**
     * @return int
     */
    public function _getResultCode() : int;

    /**
     * @return array|null
     */
    public function getJournal();

    /**
     * @return array
     */
    public function getStats() : array;

    /**
     * @return bool
     */
    public function testMethod() : bool;

    /**
     * @return bool
     */
    public function _isAvailable() : bool;

    /**
     * @return bool
     */
    public function _isActive() : bool;

    /**
     * @return array
     */
    public function _getAllMethods() : array;

    /**
     * @return array
     */
    public function _checkAvailability() : array;

    /**
     * @param bool $hash
     * @param bool $customerID
     * @param bool $customerGroup
     * @param bool $languageID
     * @param bool $currencyID
     * @param bool $sslStatus
     * @return string
     */
    public function _getBaseID(
        $hash = false,
        $customerID = false,
        $customerGroup = true,
        $languageID = true,
        $currencyID = true,
        $sslStatus = true
    ) : string;

    /**
     * @param string $methods
     * @param string $testData
     * @param int    $runCount
     * @param int    $repeat
     * @param bool   $echo
     * @param bool   $format
     * @return array
     */
    public function benchmark(
        $methods = 'all',
        $testData = 'simple string',
        $runCount = 1000,
        $repeat = 1,
        $echo = true,
        $format = false
    ) : array;
}
