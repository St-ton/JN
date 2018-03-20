<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache;


interface JTLCacheInterface
{
    public function getCachingGroups();
    public function setOptions($options = []);
    public function setCache($methodName);
    public function getJtlCacheConfig();
    public function setJtlCacheConfig();
    public function init();
    public function getOptions();
    public function setRedisCredentials($host, $port, $pass = null, $database = null);
    public function setMemcacheCredentials($host, $port);
    public function setMemcachedCredentials($host, $port);
    public function _get($cacheID, $callback = null, $customData = null);
    public function _set($cacheID, $content, $tags = null, $expiration = null);
    public function _setMulti($keyValue, $tags = null, $expiration = null);
    public function _getMulti($cacheIDs);
    public function _isCacheGroupActive($groupID);
    public function getKeysByTag($tags);
    public function _setCacheTag($tags, $cacheID);
    public function _setCacheLifetime($lifetime);
    public function _setCacheDir($dir);
    public function _getActiveMethod();
    public function _flush($cacheID = null, $tags = null, $hookInfo = null);
    public function _flushTags($tags, $hookInfo = null);
    public function _flushAll();
    public function _getResultCode();
    public function getJournal();
    public function getStats();
    public function testMethod();
    public function _isAvailable();
    public function _isActive();
    public function _isPageCacheEnabled();
    public function _getAllMethods();
    public function _checkAvailability();
    public function _getBaseID($hash = false, $customerID = false, $customerGroup = true, $languageID = true, $currencyID = true, $sslStatus = true);
    public function benchmark($methods = 'all', $testData = 'simple string', $runCount = 1000, $repeat = 1, $echo = true, $format = false);

}
