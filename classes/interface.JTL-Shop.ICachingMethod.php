<?php

/**
 * Interface ICachingMethod - interface class for caching methods
 */
interface ICachingMethod
{
    /**
     * store value to cache
     *
     * @param string   $cacheID - key to identify the value
     * @param mixed    $content - the content to save
     * @param int|null $expiration - expiration time in seconds
     *
     * @return bool - success
     */
    public function store($cacheID, $content, $expiration);

    /**
     * store multiple values to multiple keys at once to cache
     *
     * @param array    $idContent - array keys are cache IDs, array values are content to save
     * @param int|null $expiration - expiration time in seconds
     * @return bool
     */
    public function storeMulti($idContent, $expiration);

    /**
     * get value from cache
     *
     * @param string $cacheID
     *
     * @return mixed|bool - the loaded data or false if not found
     */
    public function load($cacheID);

    /**
     * get multiple values at once from cache
     *
     * @param array $cacheIDs
     * @return mixed|bool
     */
    public function loadMulti($cacheIDs);

    /**
     * class singleton getter
     *
     * @param $options
     * @return mixed
     */
    public static function getInstance($options);

    /**
     * check if php functions for using the selected caching method exist
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * clear cache by cid or gid
     *
     * @param string $cacheID
     *
     * @return bool - success
     */
    public function flush($cacheID);

    /**
     * flushes all values from cache
     *
     * @return bool - success
     */
    public function flushAll();

    /**
     * test data integrity and if functions are working properly - default implementation @JTLCacheHelper
     *
     * @return bool - success
     */
    public function test();

    /**
     * get statistical data for caching method if supported
     *
     * @return array|null - null if not supported
     */
    public function getStats();
}
